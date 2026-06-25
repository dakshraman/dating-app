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
use Illuminate\Filesystem\FilesystemAdapter;
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
            ->map(function (Conversation $conv) use ($user) {
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
                        'last_active_at' => $other->last_active_at,
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
                    'vanish_mode' => $conv->vanish_mode,
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
            ->with(['sender', 'replyTo.sender'])
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });

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
                    'expires_at' => $msg->expires_at,
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

        $conversation->messages()
            ->where('sender_id', '!=', $user->id)
            ->where('status', 'sent')
            ->update(['status' => 'delivered']);

        $deliveryOtherUser = $conversation->getOtherUser($user);
        broadcast(new MessageDelivered($conversation->id, 0, $user->id, $deliveryOtherUser->id))->toOthers();

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
            'content' => $request->input('content', ''),
            'type' => $request->input('type', 'text'),
            'reply_to_id' => $request->input('reply_to_id'),
            'metadata' => $request->input('metadata'),
            'status' => 'sent',
        ];

        if ($conversation->vanish_mode === '24h') {
            $messageData['expires_at'] = now()->addHours(24);
        } elseif ($conversation->vanish_mode === 'after_seen') {
            $messageData['expires_at'] = now();
        }

        $message = $conversation->messages()->create($messageData);
        $conversation->update(['last_message_at' => now()]);
        $message->load(['sender', 'replyTo.sender']);

        broadcast(new MessageSent($message))->toOthers();

        if ($otherUser && filled($otherUser->fcm_token)) {
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
            'expires_at' => $message->expires_at,
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

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('public');

        return response()->json([
            'url' => $disk->url($path),
            'type' => $request->input('type'),
        ], 201);
    }

    public function markAsRead(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();

        if ($conversation->user1_id !== $user->id && $conversation->user2_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $unreadMessages = $conversation->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->get();

        $now = now();
        foreach ($unreadMessages as $message) {
            $message->markRead();
        }

        $lastReadId = $unreadMessages->last()?->id;

        if ($conversation->vanish_mode === 'after_seen') {
            $conversation->messages()
                ->where('sender_id', '!=', $user->id)
                ->whereNotNull('read_at')
                ->where('expires_at', '<=', $now)
                ->delete();
        }

        broadcast(new MessageRead($conversation, $user, $lastReadId ?? 0))->toOthers();

        return response()->json(['message' => 'Marked as read', 'last_read_message_id' => $lastReadId]);
    }

    public function toggleVanishMode(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();

        if ($conversation->user1_id !== $user->id && $conversation->user2_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $request->validate(['mode' => 'required|in:off,24h,after_seen']);

        $conversation->update(['vanish_mode' => $request->mode]);

        $modeLabels = [
            'off' => 'Off',
            '24h' => '24 Hours',
            'after_seen' => 'After Seen',
        ];

        $message = $conversation->messages()->create([
            'sender_id' => $user->id,
            'content' => 'Vanish mode set to '.($modeLabels[$request->mode] ?? $request->mode),
            'type' => 'vanish_update',
            'status' => 'sent',
        ]);

        $message->load('sender');

        broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'message' => 'Vanish mode updated',
            'vanish_mode' => $conversation->vanish_mode,
            'system_message' => $message,
        ]);
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

        broadcast(new TypingIndicator($conversation->id, $user->id, $otherUser->id, $user->name))->toOthers();

        return response()->json(['message' => 'Typing indicator sent']);
    }

    public function stopTyping(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();

        if ($conversation->user1_id !== $user->id && $conversation->user2_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $otherUser = $conversation->getOtherUser($user);

        broadcast(new TypingStopped($conversation->id, $user->id, $otherUser->id, $user->name))->toOthers();

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

        $deleteOtherUser = $conversation->getOtherUser($user);
        broadcast(new ConversationDeleted($conversation, $user->id, $deleteOtherUser->id))->toOthers();

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
        $reactions = $metadata['reactions'] ?? [];

        $userId = (string) $user->id;
        $emoji = $request->emoji;

        if (isset($reactions[$userId]) && $reactions[$userId] === $emoji) {
            unset($reactions[$userId]);
        } else {
            $reactions[$userId] = $emoji;
        }

        $metadata['reactions'] = $reactions;
        $message->update(['metadata' => $metadata]);

        return response()->json(['message' => 'Reaction updated', 'reactions' => $reactions]);
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
