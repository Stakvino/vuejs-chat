<?php

namespace App\Models;

use App\Models\User;
use App\Models\Channel;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChannelUser extends Pivot
{

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>|bool
     */
    protected $guarded = [];

    /**
     * Get the channel model of the channel_id column.
     */
    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    /**
     * Get the user model of the user_id column.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
