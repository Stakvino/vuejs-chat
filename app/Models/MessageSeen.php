<?php

namespace App\Models;

use App\Models\User;
use App\Models\Message;
use Illuminate\Database\Eloquent\Model;

class MessageSeen extends Model
{

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>|bool
     */
    protected $guarded = [];

    /**
     * Get the message model of the message_id column.
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    /**
     * Get the user model of the user_id column.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

}
