<?php

namespace App\Events;

use App\Models\Message;
use App\Models\Room;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use SerializesModels;

    public function __construct(
        public Message $message,
        public Room $room
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('room.'.$this->room->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }
}
