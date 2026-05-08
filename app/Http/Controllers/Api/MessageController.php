<?php

namespace App\Http\Controllers\Api;

use App\Events\MessageSent;
use App\Events\UserTyping;
use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index(Request $request, Room $room): JsonResponse
    {
        $isParticipant = $room->participants()->where('user_id', $request->user()->id)->exists();

        if (! $isParticipant) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $messages = $room->messages()
            ->with('user:id,name,email')
            ->latest()
            ->paginate(50);

        return response()->json($messages);
    }

    public function store(Request $request, Room $room): JsonResponse
    {
        $isParticipant = $room->participants()->where('user_id', $request->user()->id)->exists();

        if (! $isParticipant) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $message = $room->messages()->create([
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
        ]);

        $message->load('user:id,name,email');

        broadcast(new MessageSent($message, $room))->toOthers();

        return response()->json($message, 201);
    }

    public function typing(Request $request, Room $room): JsonResponse
    {
        $isParticipant = $room->participants()->where('user_id', $request->user()->id)->exists();

        if (! $isParticipant) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        broadcast(new UserTyping($room, $request->user()))->toOthers();

        return response()->json(['ok' => true]);
    }

    public function markRead(Request $request, Room $room, Message $message): JsonResponse
    {
        if ($message->room_id !== $room->id || $message->user_id === $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $message->update(['read_at' => now()]);

        return response()->json(['ok' => true]);
    }
}
