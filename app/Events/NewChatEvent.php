<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewChatEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private int $userId;
    public int $chatId;
    public string $name;
    public string $avatar;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(int $userId, int $chatId, string $name, string $avatar)
    {
        $this->userId = $userId;
        $this->chatId = $chatId;
        $this->name = $name;
        $this->avatar = $avatar;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('users.' . $this->userId);
    }
}
