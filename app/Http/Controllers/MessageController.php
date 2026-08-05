<?php

namespace App\Http\Controllers;

use App\Http\Resources\PrivateMessageResource;
use App\Models\PrivateMessage;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MessageController extends Controller
{
    /**
     * Display the user's messages inbox.
     */
    public function index(Request $request): Response
    {
        // Get conversation list (latest message from each friend)
        $conversations = $this->getConversations($request->user());

        // Get unread count
        $unreadCount = $request->user()->unreadMessages()->count();

        return Inertia::render('messages/index', [
            'conversations' => $conversations,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Display a conversation with a specific user.
     */
    public function conversation(Request $request, User $user): Response
    {
        // Check if they are friends
        $areFriends = $request->user()->friends()
            ->where('friend_id', $user->id)
            ->exists();

        if (! $areFriends) {
            return Inertia::render('messages/not-friends', [
                'user_name' => $user->name,
            ]);
        }

        // Get messages
        $messages = $this->getMessages($request->user(), $user);

        // Mark messages as read
        $request->user()->receivedMessages()
            ->where('sender_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return Inertia::render('messages/chat', [
            'recipient' => [
                'id' => $user->id,
                'name' => $user->name,
                'profile_photo_url' => $user->profile_photo_url,
            ],
            'messages' => $messages,
            'conversations' => $this->getConversations($request->user()),
        ]);
    }

    /**
     * Send a private message to a friend.
     */
    public function send(Request $request, User $user): JsonResponse
    {
        // Check if they are friends
        $areFriends = $request->user()->friends()
            ->where('friend_id', $user->id)
            ->exists();

        if (! $areFriends) {
            return response()->json(['message' => __('You can only message friends.')], 403);
        }

        $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $message = $request->user()->sentMessages()->create([
            'recipient_id' => $user->id,
            'message' => $request->message,
        ]);

        $message->load(['sender']);

        return response()->json([
            'message' => new PrivateMessageResource($message),
        ]);
    }

    /**
     * Get messages between two users (API).
     */
    public function messages(Request $request, User $user): JsonResponse
    {
        $messages = $this->getMessages($request->user(), $user);

        return response()->json([
            'data' => $messages,
        ]);
    }

    /**
     * Get all unread messages count.
     */
    public function unreadCount(Request $request): JsonResponse
    {
        return response()->json([
            'count' => $request->user()->unreadMessages()->count(),
        ]);
    }

    /**
     * Get conversations list with latest message from each friend.
     */
    private function getConversations($user): array
    {
        $friends = $user->friends()->with(['friend'])->get();
        $conversations = [];

        foreach ($friends as $friend) {
            $friendUser = $friend->friend;

            // Get latest message
            $lastMessage = PrivateMessage::where(function ($query) use ($user, $friendUser) {
                $query->where(function ($q) use ($user, $friendUser) {
                    $q->where('sender_id', $user->id)
                        ->where('recipient_id', $friendUser->id);
                })->orWhere(function ($q) use ($user, $friendUser) {
                    $q->where('sender_id', $friendUser->id)
                        ->where('recipient_id', $user->id);
                });
            })
                ->latest()
                ->first();

            // Get unread count
            $unreadCount = $user->unreadMessages()
                ->where('sender_id', $friendUser->id)
                ->count();

            $conversations[] = [
                'user' => [
                    'id' => $friendUser->id,
                    'name' => $friendUser->name,
                    'profile_photo_url' => $friendUser->profile_photo_url,
                ],
                'last_message' => $lastMessage ? new PrivateMessageResource($lastMessage) : null,
                'unread_count' => $unreadCount,
                'updated_at' => $lastMessage?->created_at?->toIso8601String(),
            ];
        }

        // Sort by most recent
        usort($conversations, function ($a, $b) {
            return strtotime($b['updated_at'] ?? 0) <=> strtotime($a['updated_at'] ?? 0);
        });

        return $conversations;
    }

    /**
     * Get messages between two users.
     */
    private function getMessages($user, $otherUser): array
    {
        $messages = PrivateMessage::where(function ($query) use ($user, $otherUser) {
            $query->where(function ($q) use ($user, $otherUser) {
                $q->where('sender_id', $user->id)
                    ->where('recipient_id', $otherUser->id);
            })->orWhere(function ($q) use ($user, $otherUser) {
                $q->where('sender_id', $otherUser->id)
                    ->where('recipient_id', $user->id);
            });
        })
            ->with(['sender', 'recipient'])
            ->latest()
            ->paginate(50);

        return PrivateMessageResource::collection($messages)->resolve($request = null);
    }
}
