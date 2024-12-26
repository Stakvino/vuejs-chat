<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Auth\Passwords\CanResetPassword;
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
