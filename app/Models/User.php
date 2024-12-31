<?php

namespace App\Models;

use App\Models\Channel;
use App\Models\Message;
use App\Models\ChannelType;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Storage;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Authenticatable implements MustVerifyEmail, CanResetPasswordContract
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'avatar',
        'email',
        'password',
        'personal_color'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

     /** Const values */
     const AVATAR_FOLDER_PATH = "/images/avatars/";
     const DEFAULT_AVATAR_PATH = "/images/avatars/default.png";

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

     /**
     * Get the private chat channels that the user is subscribed to.
     */
    public function channels(): BelongsToMany
    {
        return $this->belongsToMany(Channel::class);
    }

    /**
     * Get the MessageSeen records of this user.
     */
    public function seens(): HasMany
    {
        return $this->hasMany(MessageSeen::class);
    }

    /**
     * Get all the channels the user is subscribed to except the General chat channel.
     *
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function privateChannels(): Collection
    {
        return $this->channels()->where('channels.channel_type_id', ChannelType::PRIVATE_ID)->get();
    }

    /**
     * Get the path of user's avatar image .
     *
     * @return string
     */
    public function avatarPath(): string
    {
        $avatarPath = User::AVATAR_FOLDER_PATH . $this->avatar;
        if ( $this->avatar && Storage::disk('public')->exists($avatarPath) ) {
            return $avatarPath;
        }

        return User::DEFAULT_AVATAR_PATH;
    }

    /**
     *  Delete the current avatar image file of user
     *
     * @return void
     */
    public function deleteAvatar(): void
    {
        $avatarPath = User::AVATAR_FOLDER_PATH . $this->avatar;
        if ( $this->avatar && $avatarPath != User::DEFAULT_AVATAR_PATH ) {
            Storage::disk('public')->delete($avatarPath);
            $this->update(["avatar" => null]);
        }
    }

}
