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
        return $this->users()->select('name', 'personal_color', 'users.id', 'last_login_at')
                ->get()->map(function($user){
                    $user->avatar_path = $user->avatarPath();
                    return $user;
                 });
    }

    /**
     * Get the all users that are subscribed to the channel except the signed user.
     */
    public function senders(): Collection
    {
        return $this->users()->whereNot('users.id', auth()->user()->id)
        ->select('name', 'personal_color', 'users.id')->get();
    }

    /**
     * Get the public channel infos.
     */
    public static function publicChannel() : Channel
    {
        $channel = self::find(1);
        $channel->sender = ['name' => 'Public Chat', 'avatar_path' => '/images/chat/public-chat-icon.png'];
        $channel->unseenMessagesCount = $channel->unseenMessages()->count();
        $channel->lastMessage = $channel->lastMessage();
        if ( $channel->lastMessage ) {
            $channel->lastMessage->since = Helpers::dateTimeFormat($channel->lastMessage->created_at);
        }
        return $channel;
    }

    /**
     * Get the messages that of this channel that the user have not seen yet.
     */
    public function unseenMessages() : Collection
    {
        return $this->messages()->join('message_seens as ms', 'ms.message_id', 'messages.id')
        ->where('ms.user_id', auth()->user()->id)->where('is_seen', false)->get();
    }

    /**
     * Get some of the most recent messages in this channel.
     */
    public function latestMessages(Int $messagesCount, string $fromDate = null) : Collection
    {
        $query = $this->messages();
        if ( $fromDate ) {
            $fromDate = date('d-m-Y H:i:s', strtotime($fromDate));
            $query->where('created_at', '<=', $fromDate);
        }
        return $query->OrderBy('created_at', 'desc')
            ->take($messagesCount)->get()->reverse()->values();
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
