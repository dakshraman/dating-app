<?php

namespace App\Listeners;

use App\Events\MessageRead;
use App\Events\MessageSent;
use App\Events\TypingIndicator;
use App\Events\TypingStopped;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Notifications\NewMessageNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Laravel\Reverb\Application;
use Laravel\Reverb\Events\MessageReceived;
use Throwable;

class ProcessReverbMessage
{
    protected Application $app;

    protected $connection;

    public function handle(MessageReceived $event): void
    {
        $data = json_decode($event->message, true);
        if (! $data || ! isset($data['event'])) {
            return;
        }

        $eventName = $data['event'];
        $payload = $data['data'] ?? [];
        if (is_string($payload)) {
            $payload = json_decode($payload, true) ?: [];
        }

        $this->connection = $event->connection;
        $this->app = $this->connection->app();

        if (! str_starts_with($eventName, 'client-')) {
            return;
        }

        $channel = $data['channel'] ?? '';

        preg_match('/^private-conversation\.(\d+)$/', $channel, $matches);
        if (! $matches) {
            return;
        }

        $conversationId = (int) $matches[1];
        $conversation = Conversation::find($conversationId);
        if (! $conversation) {
            return;
        }

        $userId = $this->resolveUserId($this->connection, $payload, $conversation);
        if (! $userId) {
            Log::warning('[REVERB IN] Could not determine user', [
                'socket_id' => $this->connection->id(),
                'conversation_id' => $conversationId,
            ]);

            return;
        }

        $user = User::find($userId);
        if (! $user) {
            return;
        }

        $this->connection = $event->connection;

        match ($eventName) {
            'client-MessageSend' => $this->handleSend($user, $conversation, $payload),
            'client-MessageRead' => $this->handleRead($user, $conversation, $payload),
            'client-MessageReact' => $this->handleReact($user, $conversation, $payload),
            'client-MessageDelete' => $this->handleDelete($user, $conversation, $payload),
            'client-VanishToggle' => $this->handleVanishToggle($user, $conversation, $payload),
            'client-TypingIndicator' => $this->handleTypingIndicator($user, $conversation, $payload),
            'client-TypingStopped' => $this->handleTypingStopped($user, $conversation, $payload),
            default => null,
        };
    }

    protected function sendToSender(string $eventName, array $data, string $channel): void
    {
        try {
            $this->connection->send(json_encode([
                'event' => $eventName,
                'data' => json_encode($data),
                'channel' => $channel,
            ]));
        } catch (Throwable $e) {
            Log::warning('[REVERB SEND] Failed to send to sender', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function broadcastMessageSent(Message $message): void
    {
        $message->loadMissing('conversation');
        $otherUserId = $message->sender_id === $message->conversation->user1_id
            ? $message->conversation->user2_id
            : $message->conversation->user1_id;

        $data = [
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
        ];
        $replyTo = $message->replyTo;
        if ($replyTo) {
            $data['reply_to'] = [
                'id' => $replyTo->id,
                'content' => $replyTo->content,
                'sender_id' => $replyTo->sender_id,
                'sender' => [
                    'id' => $replyTo->sender->id,
                    'name' => $replyTo->sender->name,
                ],
            ];
        }
        $data['sender'] = [
            'id' => $message->sender->id,
            'name' => $message->sender->name,
            'profile_photo' => $message->sender->profile_photo,
        ];

        // Send to sender's WebSocket directly (instant, no deadlock)
        $this->sendToSender(
            'App\\Events\\MessageSent',
            $data,
            "private-user.{$message->sender_id}"
        );

        // Queue the broadcast event for recipient delivery
        // The queue worker is a separate process, so broadcast() will not deadlock
        try {
            broadcast(new MessageSent($message));
            Log::info('[REVERB BC] Queued broadcast for MessageSent', [
                'message_id' => $message->id,
                'recipient_id' => $otherUserId,
            ]);
        } catch (Throwable $e) {
            Log::warning('[REVERB BC] Failed to queue broadcast', [
                'error' => $e->getMessage(),
                'message_id' => $message->id,
            ]);
        }
    }

    protected function resolveUserId($connection, array &$payload, Conversation $conversation): ?int
    {
        $senderId = $payload['sender_id'] ?? null;
        if ($senderId && $this->isParticipant($senderId, $conversation)) {
            unset($payload['sender_id']);

            return (int) $senderId;
        }

        // Fallback: user_id (used by TypingIndicator/TypingStopped)
        $userId = $payload['user_id'] ?? null;
        if ($userId && $this->isParticipant((int) $userId, $conversation)) {
            return (int) $userId;
        }

        return null;
    }

    protected function isParticipant(int $userId, Conversation $conversation): bool
    {
        return $conversation->user1_id === $userId || $conversation->user2_id === $userId;
    }

    protected function handleSend(User $user, Conversation $conversation, array $payload): void
    {
        Log::info("[REVERB HANDLE] Sending message for user {$user->id} in conv {$conversation->id}");

        $recentDuplicate = $conversation->messages()
            ->where('sender_id', $user->id)
            ->where('content', $payload['content'] ?? '')
            ->where('type', $payload['type'] ?? 'text')
            ->where('created_at', '>=', now()->subSeconds(2))
            ->exists();

        if ($recentDuplicate) {
            Log::info('[REVERB DEDUP] Skipped duplicate message', [
                'user_id' => $user->id,
                'conversation_id' => $conversation->id,
                'content' => $payload['content'] ?? '',
                'type' => $payload['type'] ?? 'text',
            ]);

            return;
        }

        $otherUser = $conversation->getOtherUser($user);

        if ($otherUser->blockedUsers()->where('blocked_id', $user->id)->exists()) {
            return;
        }

        if ($user->blockedUsers()->where('blocked_id', $otherUser->id)->exists()) {
            return;
        }

        if ($conversation->isDeletedBy($user)) {
            $conversation->restoreForUser($user);
        }

        $validator = Validator::make($payload, [
            'content' => 'required|string|max:5000',
            'type' => 'sometimes|string|in:text,image,voice',
            'reply_to_id' => 'nullable|exists:messages,id',
            'metadata' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return;
        }

        if (! isset($payload['type']) || $payload['type'] === 'text') {
            $content = $payload['content'] ?? '';
            $urlPattern = '/(https?:\/\/[^\s]+|www\.[^\s]+|[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,}(?:\/[^\s]*)?)/i';
            $socialPattern = '/(?:instagram\.com\/|snapchat\.com\/add\/|t\.me\/|wa\.me\/|@)[a-zA-Z0-9_.-]+/i';
            $phonePattern = '/(?:\+?\d{1,3}[\s-]?)?(?:\(?\d{3}\)?[\s-]?)?\d{3}[\s-]?\d{4}/';

            if (preg_match($urlPattern, $content) || preg_match($socialPattern, $content) || preg_match($phonePattern, $content)) {
                return;
            }
        }

        $messageData = [
            'sender_id' => $user->id,
            'content' => $payload['content'] ?? '',
            'type' => $payload['type'] ?? 'text',
            'reply_to_id' => $payload['reply_to_id'] ?? null,
            'metadata' => $payload['metadata'] ?? null,
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

        $this->broadcastMessageSent($message);

        if ($otherUser && filled($otherUser->fcm_token)) {
            Notification::send($otherUser, new NewMessageNotification($message));
        }
    }

    protected function handleRead(User $user, Conversation $conversation, array $payload): void
    {
        Log::info("[REVERB HANDLE] Read message for user {$user->id} in conv {$conversation->id}");
        $conversation->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $otherUser = $conversation->getOtherUser($user);

        $this->sendToSender(
            'App\\Events\\MessageRead',
            ['conversation_id' => $conversation->id, 'last_read_message_id' => 0],
            "private-user.{$user->id}"
        );

        try {
            broadcast(new MessageRead($conversation, $user, 0));
        } catch (Throwable $e) {
            Log::warning('[REVERB BC] Failed to queue MessageRead broadcast', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function handleReact(User $user, Conversation $conversation, array $payload): void
    {
        Log::info("[REVERB HANDLE] React message for user {$user->id} in conv {$conversation->id}", $payload);
        $messageId = $payload['message_id'] ?? null;
        $emoji = $payload['emoji'] ?? null;
        if (! $messageId || ! $emoji) {
            return;
        }

        $message = $conversation->messages()->find($messageId);
        if (! $message) {
            return;
        }

        $reactions = $message->metadata['reactions'] ?? [];
        $existing = collect($reactions)->firstWhere('user_id', $user->id);

        if ($existing) {
            $existing['emoji'] = $emoji;
        } else {
            $reactions[] = ['user_id' => $user->id, 'emoji' => $emoji];
        }

        $message->update(['metadata' => array_merge($message->metadata ?? [], ['reactions' => $reactions])]);
        $message->load(['sender', 'replyTo.sender']);

        $this->broadcastMessageSent($message);
    }

    protected function handleDelete(User $user, Conversation $conversation, array $payload): void
    {
        Log::info("[REVERB HANDLE] Delete message for user {$user->id} in conv {$conversation->id}", $payload);
        $messageId = $payload['message_id'] ?? null;
        if (! $messageId) {
            return;
        }

        $message = $conversation->messages()->where('sender_id', $user->id)->find($messageId);
        if (! $message) {
            return;
        }

        $message->delete();
    }

    protected function handleVanishToggle(User $user, Conversation $conversation, array $payload): void
    {
        Log::info("[REVERB HANDLE] Vanish toggle for user {$user->id} in conv {$conversation->id}", $payload);
        $mode = $payload['mode'] ?? 'off';
        if (! in_array($mode, ['off', '24h', 'after_seen'])) {
            return;
        }

        $conversation->update(['vanish_mode' => $mode]);

        $labels = ['off' => 'Off', '24h' => '24 Hours', 'after_seen' => 'After Seen'];
        $label = $labels[$mode] ?? 'Off';

        $messageData = [
            'sender_id' => $user->id,
            'content' => "Vanish mode set to {$label}",
            'type' => 'vanish_update',
            'status' => 'sent',
        ];

        if ($conversation->vanish_mode === '24h') {
            $messageData['expires_at'] = now()->addHours(24);
        } elseif ($conversation->vanish_mode === 'after_seen') {
            $messageData['expires_at'] = now();
        }

        $message = $conversation->messages()->create($messageData);
        $message->load(['sender', 'replyTo.sender']);

        $this->broadcastMessageSent($message);
    }

    protected function handleTypingIndicator(User $user, Conversation $conversation, array $payload): void
    {
        Log::info("[REVERB HANDLE] Typing indicator for user {$user->id} in conv {$conversation->id}");
        $otherUser = $conversation->getOtherUser($user);

        $this->sendToSender(
            'App\\Events\\TypingIndicator',
            ['conversation_id' => $conversation->id, 'user_id' => $user->id, 'user_name' => $user->name],
            "private-user.{$user->id}"
        );

        try {
            broadcast(new TypingIndicator($conversation->id, $user->id, $otherUser->id, $user->name));
        } catch (Throwable $e) {
            Log::warning('[REVERB BC] Failed to queue TypingIndicator broadcast', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function handleTypingStopped(User $user, Conversation $conversation, array $payload): void
    {
        Log::info("[REVERB HANDLE] Typing stopped for user {$user->id} in conv {$conversation->id}");
        $otherUser = $conversation->getOtherUser($user);

        $this->sendToSender(
            'App\\Events\\TypingStopped',
            ['conversation_id' => $conversation->id, 'user_id' => $user->id],
            "private-user.{$user->id}"
        );

        try {
            broadcast(new TypingStopped($conversation->id, $user->id, $otherUser->id, $user->name));
        } catch (Throwable $e) {
            Log::warning('[REVERB BC] Failed to queue TypingStopped broadcast', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
