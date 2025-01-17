<?php

namespace App\Events;

use App\Models\User;
use App\Models\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class UserWriting implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $channel;
    public $isWriting;
    public $userTypingId;

    /**
     * Create a new event instance.
     */
    public function __construct(User $user, Channel $channel, bool $isWriting)
    {
        $this->user = $user;
        $this->channel = $channel;
        $this->isWriting = $isWriting;
        $this->userTypingId = auth()->user()->id;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user-writing.' . $this->user->id),
        ];
    }
}
