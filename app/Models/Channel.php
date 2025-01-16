<?php

namespace App\Models;

use App\Models\User;
use App\Utils\Helpers;
use App\Models\Message;
use App\Models\ChannelType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Channel extends Model
{
    /** @use HasFactory<\Database\Factories\ChannelFactory> */
    use HasFactory;

    const PUBLIC_CHANNEL_ID = 1;
    const DEFAULT_CHANNEL_ICON = "/images/chat/public-chat-icon.png";
    const DEFAULT_CHANNEL_NAME = "New Channel";

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>|bool
     */
    protected $guarded = [];

    /**
     * Get the type of this channel. ( public | private ... )
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(ChannelType::class, 'channel_type_id');
    }

     /**
     * Get the users that are subscribed to this channel.
     */
    public function users(): BelongsToMany
    {
        return $this->BelongsToMany(User::class);
    }

    /**
     * Get the messages tha the user sent.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Get the all users that are subscribed to the channel.
     */
    public function members(): Collection
    {
        return $this->users()->select('name', 'personal_color', 'users.id', 'last_login_at', 'users.avatar')
                ->get()->map(function($user){
                    $user->avatar_path = $user->avatarPath();
                    return $user;
                 });
    }

    /**
     * Get the all users that are subscribed to the channel except the signed user.
     */
    public function receivers(): Collection
    {
        $users = $this->users()->whereNot('users.id', auth()->user()->id)
        ->select('name', 'personal_color', 'users.id', 'last_login_at', 'is_logged_in', 'users.avatar')->get();

        return $users->map(function ($user) {
            $user->avatar_path = $user->avatarPath();
            return  $user;
        });
    }

    /**
     * Query the messages that of this channel that the user have not seen yet.
     */
    public function scopeUnseenMessages() : HasMany
    {
        return $this->messages()->join('message_seens as ms', 'ms.message_id', 'messages.id')
        ->where('ms.user_id', auth()->user()->id)->where('is_seen', false);
    }

    /**
     * Get all channels.
     *
     * @return Illuminate\Database\Eloquent\Collection
     */
    public static function getAll(int $typeId = null): Collection
    {
        $channels = self::when($typeId, function ($q) use($typeId) {
            return $q->where('channels.channel_type_id', $typeId);
        })
        ->get()->map(function ($channel) {
            $channel->info = $channel->getInfo();
            return $channel;
        });

        // Fix index changes because google chrome will change then back
        return $channels->sortBy('updated_at')->values();
    }

    /**
     * Get the data neccessery for the front-end rendering of the channel.
     */
    public function getInfo(): array
    {
        $receiver =  $this->isPrivate() ? $this->receivers()->first()
        :
        [
            'name' => $this->name,
            'avatar_path' => $this->icon,
            'personal_color' => ''
        ];

        $lastMessage = $this->lastMessage();
        if ( $lastMessage ) {
            $lastMessage->since = Helpers::dateTimeFormat($lastMessage->created_at);
        }

        return [
            'type' => $this->type,
            'receiver' => $receiver,
            'unseenMessagesCount' => $this->scopeUnseenMessages()->count(),
            'lastMessage' => $lastMessage,
        ];
    }

    /**
     * Get one message withh all the other data needed to render it on the fornt-end.
     */
    public function getMessage(Int $messagesId) : Message
    {
        $message = Message::find($messagesId);
        $message['info'] = $message->getInfo();
        return $message;
    }

    /**
     * Get some of the most recent messages in this channel withh all the other data needed to render it on the fornt-end.
     */
    public function getMessages(Int $messagesCount = null, string $fromDate = null) : Collection
    {
        $query = $this->messages()->OrderBy('created_at', 'desc');
        if ( $fromDate ) {
            $query->where('created_at', '<', $fromDate);
        }
        if ( $messagesCount ) {
            $query = $query->take($messagesCount);
        }

        return $query->get()->reverse()->values()->map(function($message) {
            $message->info = $message->getInfo();
            return $message;
        });
    }

    /**
     * Get the the last message that was sent in this channel.
     */
    public function lastMessage() : Message | null
    {
        return $this->messages()->latest()->first();
    }

    /**
     * Check if the channel is private.
     */
    public function isPrivate() : bool
    {
        return $this->type()->first()->id === ChannelType::PRIVATE_ID;
    }

}
