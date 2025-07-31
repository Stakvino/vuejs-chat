<?php

namespace App\Models;

use App\Utils\Helpers;
use App\Models\Channel;
use App\Models\Message;
use App\Models\BlockUser;
use App\Models\ChannelType;
use App\Models\ChannelUser;
use App\Models\MessageSeen;
use App\Models\MessageType;
use App\Models\AudioMessage;
use Laravel\Sanctum\HasApiTokens;
use App\Mail\PublicMessageReceived;
use Illuminate\Support\Facades\Mail;
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

    const CHAT_BOT_ID = 1;
    const SUPER_ADMIN_EMAIL = "admin@oussama-cheriguene.com";
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
        'personal_color',
        'last_login_at',
        'is_logged_in'
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
    const ALLOWED_COLLUMNS = [
        'id', 'name', 'username', 'email', 'personal_color',
        'email_verified_at', 'avatar', 'last_login_at'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
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
     * Get the super admin user.
     */
    public static function getSuperAdmin(): User
    {
        return User::where("email", User::SUPER_ADMIN_EMAIL)->first();
    }

    /**
     * check if user is super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->email === User::SUPER_ADMIN_EMAIL;
    }

    /**
     * check if user is a chatbot.
     */
    public function isChatBot(): bool
    {
        return $this->id === User::CHAT_BOT_ID;
    }

    /**
     * Get the channels of the user.
     *
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function getChannels(int $typeId = null, bool $isSubscribed = true): Collection
    {
        $subscribedChannelsIds = $this->channels()->pluck('channels.id')->toArray();
         if ( $isSubscribed ) {
            $query = Channel::whereIn('id', $subscribedChannelsIds);
         }
         else {
            $query = Channel::whereNotIn('id', $subscribedChannelsIds);
         }

        $channels = $query->when($typeId, function ($q) use($typeId) {
            return $q->where('channels.channel_type_id', $typeId);
        })
        ->get()
        ->filter(function ($channel) {
            return !auth()->user()->hasBlockedChannel($channel);
        })
        ->map(function ($channel) {
            $channel->info = $channel->getInfo();
            return $channel;
        });

        // Fix index changes because google chrome will change then back
        return $channels->sortByDesc('updated_at')->values();
    }

    /**
     *  Check if user is subscribed to a private channel with another user
     *
     * @return App\Models\Channel | null
     */
    public function inChannelWith(User $user): Channel | null
    {
        $authChannels = ChannelUser::join('channels', 'channels.id', 'channel_user.channel_id')
        ->where('channels.channel_type_id', ChannelType::PRIVATE_ID)
        ->where('channel_user.user_id', $this->id)->get();

        $userChannels = ChannelUser::join('channels', 'channels.id', 'channel_user.channel_id')
        ->where('channels.channel_type_id', ChannelType::PRIVATE_ID)
        ->where('channel_user.user_id', $user->id)->get();

        foreach ($authChannels as $authChannel) {
            foreach ($userChannels as $userChannel) {
                if ( $authChannel->channel_id === $userChannel->channel_id ) {
                    return Channel::find($authChannel->channel_id);
                }
            }
        }

        return null;
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
     *  Unsubscribe user from channel
     *
     * @return bool
     */
    public function unsubscribeFrom(Channel $channel): bool
    {
        return ChannelUser::where([
            'user_id' => $this->id,
            'channel_id' => $channel->id
        ])->delete();
    }

    /**
     *  Check if user has blocked $user
     *
     * @return bool
     */
    public function hasBlocked(User $user): bool
    {
        return !!BlockUser::where([
            'user_id' => $this->id,
            'blocked_user_id' => $user->id
        ])->first();
    }

    /**
     *  Check if user has blocked private channel
     *
     * @return bool
     */
    public function hasBlockedChannel(Channel $channel): bool
    {
        $receiver = $channel->receivers()->first();
        return $channel->isPrivate() && $this->hasBlocked($receiver);
    }

    /**
     *  Block a user
     *
     * @return App\Models\BlockUser
     */
    public function block(User $user): BlockUser
    {
        return BlockUser::create([
            'user_id' => $this->id,
            'blocked_user_id' => $user->id
        ]);
    }

    /**
     *  Unblock a user
     *
     * @return bool
     */
    public function unblock(User $user): bool
    {
        return BlockUser::where([
            'user_id' => $this->id,
            'blocked_user_id' => $user->id
        ])->delete();
    }

    /**
     *  Get all the blocked users
     *
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function getBlockedUsers(): Collection
    {
        $blockedUsersIds = BlockUser::where('user_id', $this->id)
            ->get()->pluck('blocked_user_id')->toArray();

        return User::whereIn('id', $blockedUsersIds)
            ->select(...User::ALLOWED_COLLUMNS)->orderBy('name')
            ->get()->map(function($user) {
                $user->avatar_path = $user->avatarPath();
                return $user;
            });
    }

    /**
     *  Store message and dispatch event
     *
     * @return App\Models\Message | null
     */
    public function sendMessage(Channel $channel, array $message): Message | null
    {
        // Trying to send a message to a blocked user
        if ( $channel->isPrivate() ) {
            $receiver = $channel->receivers()->first();
            if ( auth()->user()->hasBlocked($receiver) ) {
                return null;
            }
        }

        $createdMessage = Message::create([
            'text' => $message['text'],
            'channel_id' => $channel->id,
            'user_id' => $this->id,
            'message_type_id' => MessageType::TEXT_ID
        ]);

        if ( isset($message['attachment']) ) {
            $fileName = $createdMessage->id . "-" . now()->format('d_m_y_h_i_s');
            $message['attachment']->storeAs('/chat-files/attachments', $fileName, 'public' );
            FileMessage::create([
                'message_id' => $createdMessage->id,
                'original_file_name' => $message['attachment']->getClientOriginalName(),
                'file_path' => FileMessage::FOLDER_PATH . $fileName,
                'is_image' => \str_starts_with($message['attachment']->getClientMimeType(), 'image'),
            ]);
            $createdMessage->update(["message_type_id" => MessageType::FILE_ID]);
        }
        if ( isset($message['audio']) ) {
            $fileName = $createdMessage->id . "-" . now()->format('d_m_y_h_i_s');
            $message['audio']->storeAs('/chat-files/audio-messages', $fileName, 'public' );
            AudioMessage::create([
                'message_id' => $createdMessage->id,
                'file_path' => AudioMessage::FOLDER_PATH . $fileName,
                'duration' => $message['audio-duration'],
            ]);
            $createdMessage->update(["message_type_id" => MessageType::AUDIO_ID]);
        }

        $now = now();
        $messageSeens = $channel->members()->map(function($member) use($createdMessage, $now) {
            return [
                'message_id' => $createdMessage->id,
                'user_id' => $member->id,
                'is_seen' => $member->id === $this->id ? true : false,
                'created_at' => $now
            ];
        });

        MessageSeen::insert($messageSeens->toArray());
        $channel->update(['updated_at' => now()]);

        // Send an email to myself to know that someone sent a message in the public channel
        if ( $channel->isPublic() && !$this->isSuperAdmin() && !$this->isChatBot() ) {
            Mail::to( User::getSuperAdmin() )
            ->send( new PublicMessageReceived( $this, $createdMessage ) );
        }

        return $createdMessage;
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
