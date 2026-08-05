<?php

namespace App\Http\Controllers;

use App\Http\Resources\FriendshipResource;
use App\Http\Resources\UserResource;
use App\Models\Friendship;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FriendshipController extends Controller
{
    /**
     * Display the user's friends list.
     */
    public function index(Request $request): Response
    {
        $friends = $request->user()->friends()
            ->with(['friend.profile'])
            ->latest('friendships.created_at')
            ->paginate(20);

        return Inertia::render('friends/index', [
            'friends' => FriendshipResource::collection($friends),
            'pagination' => [
                'current_page' => $friends->currentPage(),
                'last_page' => $friends->lastPage(),
                'total' => $friends->total(),
            ],
        ]);
    }

    /**
     * Display pending friend requests.
     */
    public function requests(Request $request): Response
    {
        $receivedRequests = $request->user()->pendingFriendRequestsReceived()
            ->with(['user.profile'])
            ->latest()
            ->get();

        $sentRequests = $request->user()->pendingFriendRequests()
            ->with(['friend.profile'])
            ->latest()
            ->get();

        return Inertia::render('friends/requests', [
            'received' => FriendshipResource::collection($receivedRequests),
            'sent' => FriendshipResource::collection($sentRequests),
        ]);
    }

    /**
     * Search for users to add as friends.
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => ['required', 'string', 'min:2'],
        ]);

        $users = User::where('name', 'like', '%'.$request->q.'%')
            ->orWhere('email', 'like', '%'.$request->q.'%')
            ->where('id', '!=', $request->user()->id)
            ->limit(20)
            ->get();

        // Add friend status to each user
        $users = $users->map(function ($user) use ($request) {
            $friendship = $request->user()->friendships()->where('friend_id', $user->id)->first()
                ?? $request->user()->friendOf()->where('user_id', $user->id)->first();

            return [
                'user' => new UserResource($user),
                'friendship_status' => $friendship?->status ?? null,
            ];
        });

        return response()->json(['results' => $users]);
    }

    /**
     * Send a friend request.
     */
    public function send(Request $request, User $user): JsonResponse
    {
        // Cannot send request to yourself
        if ($request->user()->id === $user->id) {
            return response()->json(['message' => __('You cannot add yourself as a friend.')], 400);
        }

        // Check if friendship already exists
        $existing = $request->user()->friendships()
            ->where('friend_id', $user->id)
            ->first();

        if ($existing) {
            return response()->json(['message' => __('Friend request already sent.')], 400);
        }

        $existingReverse = $request->user()->friendOf()
            ->where('user_id', $user->id)
            ->first();

        if ($existingReverse) {
            if ($existingReverse->status === 'accepted') {
                return response()->json(['message' => __('You are already friends.')], 400);
            }
            if ($existingReverse->status === 'pending') {
                // Accept the pending request instead
                $existingReverse->update([
                    'status' => 'accepted',
                    'accepted_at' => now(),
                ]);

                return response()->json(['message' => __('Friend request accepted.')]);
            }
        }

        // Create new friend request
        $request->user()->friendships()->create([
            'friend_id' => $user->id,
            'status' => 'pending',
        ]);

        return response()->json(['message' => __('Friend request sent.')]);
    }

    /**
     * Accept or reject a friend request.
     */
    public function respond(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'action' => ['required', 'in:accept,decline'],
        ]);

        $friendship = $request->user()->friendOf()
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if (! $friendship) {
            return response()->json(['message' => __('No pending friend request.')], 404);
        }

        if ($request->action === 'accept') {
            $friendship->update([
                'status' => 'accepted',
                'accepted_at' => now(),
            ]);

            return response()->json(['message' => __('Friend request accepted.')]);
        }

        $friendship->update(['status' => 'declined']);

        return response()->json(['message' => __('Friend request declined.')]);
    }

    /**
     * Remove a friend (unfriend).
     */
    public function destroy(Request $request, User $user): JsonResponse
    {
        // Remove both directions of friendship
        $request->user()->friendships()
            ->where('friend_id', $user->id)
            ->delete();

        $request->user()->friendOf()
            ->where('user_id', $user->id)
            ->delete();

        return response()->json(['message' => __('Friend removed.')]);
    }
}
