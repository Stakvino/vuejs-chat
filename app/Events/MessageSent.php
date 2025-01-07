<?php

namespace App\Events;

use App\Models\Channel;
use App\Models\Message;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $channel;
    public $channelInfo;
    public $message;
    public $messageInfo;

    /**
     * Create a new event instance.
     */
    public function __construct(Channel $channel, Message $message)
    {
        $this->channel = $channel;
        $this->channelInfo = $channel->getInfo();
        $this->message = $message;
        $this->messageInfo = $message->getInfo();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat-channel.' . $this->channel->id),
        ];
    }
}

