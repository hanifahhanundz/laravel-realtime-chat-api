<?php

namespace App\Events;

use App\Models\Room;
use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class UserJoined implements ShouldBroadcastNow
{
    use SerializesModels;

    public function __construct(
        public Room $room,
        public User $user
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('room.'.$this->room->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'user.joined';
    }
}
