<?php

use App\Models\Room;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('room.{roomId}', function ($user, int $roomId) {
    return Room::where('id', $roomId)
        ->whereHas('participants', fn ($q) => $q->where('user_id', $user->id))
        ->exists();
});
