<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $rooms = $request->user()
            ->rooms()
            ->with(['participants', 'latestMessage'])
            ->latest()
            ->paginate(20);

        return response()->json($rooms);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'participants' => ['nullable', 'array'],
            'participants.*' => ['exists:users,id'],
        ]);

        $room = $request->user()->ownedRooms()->create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        // Owner auto-joins
        $room->participants()->attach($request->user()->id, ['joined_at' => now()]);

        // Add other participants
        if (! empty($validated['participants'])) {
            $others = collect($validated['participants'])->filter(fn ($id) => $id !== $request->user()->id);
            foreach ($others as $userId) {
                $room->participants()->attach($userId, ['joined_at' => now()]);
            }
        }

        $room->load('participants');

        return response()->json($room, 201);
    }

    public function show(Request $request, Room $room): JsonResponse
    {
        $isParticipant = $room->participants()->where('user_id', $request->user()->id)->exists();

        if (! $isParticipant) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $room->load('participants');

        return response()->json($room);
    }

    public function destroy(Request $request, Room $room): JsonResponse
    {
        if ($room->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $room->delete();

        return response()->json(['message' => 'Room deleted']);
    }

    public function join(Request $request, Room $room): JsonResponse
    {
        $user = $request->user();

        if ($room->participants()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Already a participant']);
        }

        $room->participants()->attach($user->id, ['joined_at' => now()]);

        return response()->json(['message' => 'Joined room']);
    }

    public function leave(Request $request, Room $room): JsonResponse
    {
        $user = $request->user();
        $room->participants()->detach($user->id);

        return response()->json(['message' => 'Left room']);
    }
}
