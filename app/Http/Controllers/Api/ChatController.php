<?php

namespace App\Http\Controllers\Api;

use App\Events\ConversationDeleted;
use App\Events\MessageDelivered;
use App\Events\MessageRead;
use App\Events\MessageSent;
use App\Events\TypingIndicator;
use App\Events\TypingStopped;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Notifications\NewMessageNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    public function conversations(Request $request): JsonResponse
    {
        $user = $request->user();

        $conversations = Conversation::whereParticipant($user)
            ->with(['user1', 'user2', 'match'])
            ->orderBy('last_message_at', 'desc')
            ->get()
            ->map(function ($conv) use ($user) {
                $other = $conv->getOtherUser($user);

                if ($other->blockedUsers()->where('blocked_id', $user->id)->exists()) {
                    return null;
                }

                $lastMessage = $conv->messages()->latest()->first();
                $unreadCount = $conv->messages()
                    ->where('sender_id', '!=', $user->id)
                    ->whereNull('read_at')
                    ->count();

                return [
                    'id' => $conv->id,
                    'match_id' => $conv->match_id,
                    'user' => [
                        'id' => $other->id,
                        'name' => $other->name,
                        'profile_photo' => $other->profile_photo,
                        'last_seen_at' => $other->last_seen_at,
                        'is_online' => $other->last_seen_at && $other->last_seen_at->gt(now()->subMinutes(2)),
                    ],
                    'last_message' => $lastMessage ? [
                        'id' => $lastMessage->id,
                        'content' => $lastMessage->content,
                        'type' => $lastMessage->type,
                        'created_at' => $lastMessage->created_at,
                        'sender_id' => $lastMessage->sender_id,
                        'status' => $lastMessage->status,
                    ] : null,
                    'unread_count' => $unreadCount,
                    'last_message_at' => $conv->last_message_at,
                ];
            })
            ->filter()
            ->values();

        return response()->json($conversations);
    }

    public function messages(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();

        if ($conversation->user1_id !== $user->id && $conversation->user2_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $otherUser = $conversation->getOtherUser($user);

        if ($otherUser->blockedUsers()->where('blocked_id', $user->id)->exists()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $query = $conversation->messages()
            ->with(['sender', 'replyTo.sender']);

        if ($request->filled('before')) {
            $query->where('id', '<', $request->integer('before'));
        }

        $limit = min($request->integer('limit', 50), 100);

        $messages = $query->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get()
            ->map(function ($msg) {
                $replyTo = $msg->replyTo;

                return [
                    'id' => $msg->id,
                    'conversation_id' => $msg->conversation_id,
                    'sender_id' => $msg->sender_id,
                    'content' => $msg->content,
                    'type' => $msg->type,
                    'status' => $msg->status,
                    'metadata' => $msg->metadata,
                    'read_at' => $msg->read_at,
                    'created_at' => $msg->created_at,
                    'reply_to' => $replyTo ? [
                        'id' => $replyTo->id,
                        'content' => $replyTo->content,
                        'sender_id' => $replyTo->sender_id,
                        'sender' => [
                            'id' => $replyTo->sender->id,
                            'name' => $replyTo->sender->name,
                        ],
                    ] : null,
                    'sender' => [
                        'id' => $msg->sender->id,
                        'name' => $msg->sender->name,
                        'profile_photo' => $msg->sender->profile_photo,
                    ],
                ];
            });

        $unreadOwn = $conversation->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->get();

        foreach ($unreadOwn as $msg) {
            $msg->markDelivered();
            broadcast(new MessageDelivered($conversation->id, $msg->id, $user->id))->toOthers();
        }

        return response()->json($messages);
    }

    public function sendMessage(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();

        if ($conversation->user1_id !== $user->id && $conversation->user2_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $otherUser = $conversation->getOtherUser($user);

        if ($otherUser->blockedUsers()->where('blocked_id', $user->id)->exists()) {
            return response()->json(['message' => 'You cannot send messages to this user'], 403);
        }

        if ($user->blockedUsers()->where('blocked_id', $otherUser->id)->exists()) {
            return response()->json(['message' => 'Unblock the user to send messages'], 403);
        }

        if ($conversation->isDeletedBy($user)) {
            $conversation->restoreForUser($user);
        }

        $validator = Validator::make($request->all(), [
            'content' => 'required_without:file|string|max:5000',
            'type' => 'sometimes|string|in:text,image,voice',
            'reply_to_id' => 'nullable|exists:messages,id',
            'metadata' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $messageData = [
            'sender_id' => $user->id,
            'content' => $request->content ?? '',
            'type' => $request->type ?? 'text',
            'reply_to_id' => $request->reply_to_id,
            'metadata' => $request->metadata,
            'status' => 'sent',
        ];

        $message = $conversation->messages()->create($messageData);
        $conversation->update(['last_message_at' => now()]);
        $message->load(['sender', 'replyTo.sender']);

        broadcast(new MessageSent($message))->toOthers();

        if ($otherUser && filled($otherUser->fcm_tokens)) {
            Notification::send($otherUser, new NewMessageNotification($message));
        }

        return response()->json([
            'id' => $message->id,
            'conversation_id' => $message->conversation_id,
            'sender_id' => $message->sender_id,
            'content' => $message->content,
            'type' => $message->type,
            'status' => $message->status,
            'metadata' => $message->metadata,
            'read_at' => $message->read_at,
            'created_at' => $message->created_at,
            'reply_to' => $message->replyTo ? [
                'id' => $message->replyTo->id,
                'content' => $message->replyTo->content,
                'sender_id' => $message->replyTo->sender_id,
                'sender' => [
                    'id' => $message->replyTo->sender->id,
                    'name' => $message->replyTo->sender->name,
                ],
            ] : null,
            'sender' => [
                'id' => $message->sender->id,
                'name' => $message->sender->name,
                'profile_photo' => $message->sender->profile_photo,
            ],
        ], 201);
    }

    public function uploadMedia(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:jpeg,png,jpg,gif,mp3,ogg,wav,mp4|max:50240',
            'type' => 'required|in:image,voice,video',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $path = $request->file('file')->store('chat-media', 'public');

        return response()->json([
            'url' => Storage::disk('public')->url($path),
            'type' => $request->type,
        ], 201);
    }

    public function markAsRead(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();

        if ($conversation->user1_id !== $user->id && $conversation->user2_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $messages = $conversation->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->get();

        $now = now();
        foreach ($messages as $message) {
            $message->markRead();
        }

        $lastReadId = $messages->last()?->id;
        broadcast(new MessageRead($conversation, $user, $lastReadId ?? 0))->toOthers();

        return response()->json(['message' => 'Marked as read', 'last_read_message_id' => $lastReadId]);
    }

    public function typing(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();

        if ($conversation->user1_id !== $user->id && $conversation->user2_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $otherUser = $conversation->getOtherUser($user);

        if ($otherUser->blockedUsers()->where('blocked_id', $user->id)->exists()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        broadcast(new TypingIndicator($conversation->id, $user->id, $user->name))->toOthers();

        return response()->json(['message' => 'Typing indicator sent']);
    }

    public function stopTyping(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();

        if ($conversation->user1_id !== $user->id && $conversation->user2_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        broadcast(new TypingStopped($conversation->id, $user->id, $user->name))->toOthers();

        return response()->json(['message' => 'Typing stopped']);
    }

    public function deleteConversation(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();

        if ($conversation->user1_id !== $user->id && $conversation->user2_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $column = $user->id === $conversation->user1_id ? 'user1_deleted_at' : 'user2_deleted_at';
        $conversation->update([$column => now()]);

        broadcast(new ConversationDeleted($conversation, $user->id))->toOthers();

        return response()->json(['message' => 'Conversation deleted']);
    }

    public function updateLastSeen(Request $request): JsonResponse
    {
        $request->user()->update(['last_seen_at' => now()]);

        return response()->json(['message' => 'Updated']);
    }

    public function reactToMessage(Request $request, Conversation $conversation, Message $message): JsonResponse
    {
        $user = $request->user();

        if ($conversation->user1_id !== $user->id && $conversation->user2_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $request->validate(['emoji' => 'required|string']);
        $metadata = $message->metadata ?? [];
        $metadata['emoji'] = $request->emoji;
        $message->update(['metadata' => $metadata]);

        return response()->json(['message' => 'Reaction updated']);
    }

    public function deleteMessage(Request $request, Conversation $conversation, Message $message): JsonResponse
    {
        $user = $request->user();

        if ($message->sender_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $message->delete();

        return response()->json(['message' => 'Deleted']);
    }

    public function blockUser(Request $request): JsonResponse
    {
        $request->validate(['blocked_id' => 'required|exists:users,id']);

        $userId = $request->integer('blocked_id');

        if ($request->user()->id === $userId) {
            return response()->json(['message' => 'Cannot block yourself'], 422);
        }

        $request->user()->blockedUsers()->syncWithoutDetaching([$userId]);

        return response()->json(['message' => 'User blocked']);
    }

    public function unblockUser(Request $request, string $id): JsonResponse
    {
        $request->user()->blockedUsers()->detach((int) $id);

        return response()->json(['message' => 'User unblocked']);
    }

    public function blockedUsers(Request $request): JsonResponse
    {
        $blocked = $request->user()->blockedUsers()
            ->get(['users.id', 'users.name', 'users.profile_photo']);

        return response()->json($blocked);
    }

    public function reportUser(Request $request): JsonResponse
    {
        $request->validate([
            'reported_id' => 'required|exists:users,id',
            'reason' => 'required|string',
            'details' => 'nullable|string',
        ]);

        $request->user()->reports()->create([
            'reported_id' => $request->integer('reported_id'),
            'reason' => $request->input('reason'),
            'details' => $request->input('details'),
        ]);

        return response()->json(['message' => 'Report submitted']);
    }
}
