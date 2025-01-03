<?php

namespace App\Models;

use App\Utils\Helpers;
use App\Models\Channel;
use App\Models\Message;
use App\Models\ChannelType;
use App\Models\ChannelUser;
use App\Models\MessageSeen;
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
     * Get all the private channels the user is subscribed to.
     *
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function privateChannels(): Collection
    {
        $channels = $this->channels()
        ->where('channels.channel_type_id', ChannelType::PRIVATE_ID)
        ->get();

        foreach ($channels as $key => $channel) {
            $channel->receiver = $channel->receivers()->first();
            $channel->unseenMessagesCount = $channel->scopeUnseenMessages()->count();
            $channel->lastMessage = $channel->lastMessage();
        }

        // Formating dates and adding users avatar path
        $channels = $channels->map(function($channel) {
            $channel->receiver->avatar_path = $channel->receiver->avatarPath();
            if ( $channel->lastMessage ) {
                $channel->lastMessage->since = Helpers::dateTimeFormat($channel->lastMessage->created_at);
            }

            return $channel;
        });

        /*
        $channels = $channels->sortBy(function ($channel) {
            return $channel->lastMessage->created_at;
        });
        */

        // Fix index changes because google chrome will change then back
        return $channels->sortBy('updated_at')->values();
    }

    /**
     * Get all the public channels the user is subscribed to.
     *
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function publicChannels(): Collection
    {
        $channels = $this->channels()
        ->where('channels.channel_type_id', ChannelType::PUBLIC_ID)
        ->get()->map(function($channel) {
            $channel->receiver = [
                'name' => 'Public Chat',
                'avatar_path' => Channel::DEFAULT_CHANNEL_ICON
            ];
            $channel->unseenMessagesCount = $channel->scopeUnseenMessages()->count();
            $channel->lastMessage = $channel->lastMessage();
            if ( $channel->lastMessage ) {
                $channel->lastMessage->since = Helpers::dateTimeFormat($channel->lastMessage->created_at);
            }

            return $channel;
        });

        return $channels->sortBy('updated_at')->values();;
    }

    /**
     *  Check if user is subscribed to channel
     *
     * @return App\Models\ChannelUser
     */
    public function isSubscribedTo(Channel $channel): ChannelUser
    {
        return ChannelUser::where('user_id', $this->id)
        ->where('channel_id', $channel->id)->first();
    }

    /**
     *  Subscribe user to channel
     *
     * @return App\Models\ChannelUser
     */
    public function subscribeTo(Channel $channel): ChannelUser
    {
        return ChannelUser::create([
            'user_id' => $this->id,
            'channel_id' => $channel->id
        ]);
    }

    /**
     *  Store message and dispatch event
     *
     * @return App\Models\Message
     */
    public function sendMessage(Channel $channel, string $text): Message
    {
        $message = Message::create([
            'text' => $text,
            'channel_id' => $channel->id,
            'user_id' => $this->id,
        ]);

        $messageSeens = $channel->members()->map(function($member) use($message) {
            return [
                'message_id' => $message->id,
                'user_id' => $member->id,
                'is_seen' => $member->id === $this->id ? true : false
            ];
        });

        MessageSeen::insert($messageSeens->toArray());
        $channel->update(['updated_at' => now()]);

        // Dispatch event

        return $message;
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
