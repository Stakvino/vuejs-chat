<?php

namespace App\Models;

use App\Models\User;
use App\Models\Channel;
use App\Models\MessageSeen;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Message extends Model
{
    /** @use HasFactory<\Database\Factories\MessageFactory> */
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>|bool
     */
    protected $guarded = [];

    /**
     * Get the messages tha the user sent.
     */
    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    /**
     * Get the user that sent this message.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user that sent this message.
     */
    public function seens(): HasMany
    {
        return $this->hasMany(MessageSeen::class);
    }

    /**
     * Get the all users that are subscribed to the channel.
     */
    public function sender(): User
    {
        return $this->user()->select('name', 'personal_color', 'users.id')->first();
    }

    /**
     * Check if this message was sent by the signed user.
     */
    public function isMyMessage(): bool
    {
        return $this->sender()->id === auth()->user()->id;
    }

    /**
     * get the list of the users that saw the message.
     */
    public function usersSeen(): Collection
    {
        return User::join('message_seens', 'message_seens.user_id', 'users.id')
        ->where('message_seens.is_seen', true)
        ->where('message_seens.message_id', $this->id)
        ->whereNot('users.id', auth()->user()->id)
        ->select(
            'users.name', 'users.personal_color', 'users.id',
            \DB::RAW('DATE_FORMAT(message_seens.created_at, "%d/%m/%Y %H:%i") as seen_created_at')
        )->get();
    }

    /**
     * Check if a user saw this message.
     */
    public function isSeenBy(User $user): bool
    {
        return $this->seens()->where('user_id', $user->id)->first()->is_seen;
    }

}
