<?php

namespace App\Events;

use App\Models\User;
use App\Models\Channel;
use Illuminate\Support\Collection;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class MessageSeen implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $channel;
    public $messagesIds;
    public $user;

    /**
     * Create a new event instance.
     */
    public function __construct(User $user, Channel $channel, array $messagesIds)
    {
        $this->channel = $channel;
        $this->messagesIds = $messagesIds;
        $this->user = $user;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('message-seen.' . $this->user->id),
        ];
    }
}
