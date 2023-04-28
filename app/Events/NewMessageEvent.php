<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewMessageEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $chatId;
    public int $userId;
    public ?string $message;
    public ?string $attachment_type;
    public ?string $attachment;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(int $userId, int $chatId, ?string $message, ?string $attachment_type, ?string $attachment)
    {
        $this->userId = $userId;
        $this->chatId = $chatId;
        $this->message = $message;
        $this->attachment_type = $attachment_type;
        $this->attachment = $attachment;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('chats.'.$this->chatId);
    }
}
