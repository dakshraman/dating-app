<?php

namespace App\Listeners;

use App\Models\Conversation;
use App\Models\Message;
use App\Notifications\NewMessageNotification;
use App\Models\User;
use App\Services\ConnectionTracker;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Laravel\Reverb\Events\MessageReceived;
use Laravel\Reverb\Protocols\Pusher\Contracts\ChannelManager;

class ProcessReverbMessage
{
    protected \Laravel\Reverb\Application $app;
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

        // Track user connection via private-user.{id} subscriptions
        if ($eventName === 'pusher:subscribe') {
            $subChannel = $payload['channel'] ?? '';
            if (str_starts_with($subChannel, 'private-user.')) {
                $userId = (int) substr($subChannel, strlen('private-user.'));
                ConnectionTracker::setUserConnection($userId, $this->connection);
                \Illuminate\Support\Facades\Log::info('[REVERB TRACK] User connected', [
                    'user_id' => $userId,
                    'socket_id' => $this->connection->id(),
                ]);
            }
            return;
        }

        if ($eventName === 'pusher:unsubscribe') {
            $subChannel = $payload['channel'] ?? '';
            if (str_starts_with($subChannel, 'private-user.')) {
                ConnectionTracker::removeUserByUserId(
                    (int) substr($subChannel, strlen('private-user.'))
                );
                \Illuminate\Support\Facades\Log::info('[REVERB TRACK] User disconnected', [
                    'channel' => $subChannel,
                    'socket_id' => $this->connection->id(),
                ]);
            }
            return;
        }

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
            \Illuminate\Support\Facades\Log::warning('[REVERB IN] Could not determine user', [
                'socket_id' => $this->connection->id(),
                'conversation_id' => $conversationId,
            ]);
            return;
        }

        $user = User::find($userId);
        if (! $user) {
            return;
        }

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

    protected function sendToUser(int $userId, string $eventName, array $data): void
    {
        $connection = ConnectionTracker::getConnection($userId);
        if (! $connection) {
            \Illuminate\Support\Facades\Log::warning('[REVERB SEND] User not connected', ['user_id' => $userId]);
            return;
        }

        $connection->send(json_encode([
            'event' => $eventName,
            'data' => json_encode($data),
            'channel' => "private-user.{$userId}",
        ]));
    }

    protected function broadcastMessageSent(\App\Models\Message $message): void
    {
        $message->loadMissing('conversation');
        $otherUserId = $message->sender_id === $message->conversation->user1_id
            ? $message->conversation->user2_id
            : $message->conversation->user1_id;

        $data = $this->formatMessageData($message);

        // Send to sender directly (they're this connection)
        $this->connection->send(json_encode([
            'event' => 'App\\Events\\MessageSent',
            'data' => json_encode($data),
            'channel' => "private-user.{$message->sender_id}",
        ]));

        // Send to recipient via tracker
        $this->sendToUser($otherUserId, 'App\\Events\\MessageSent', $data);
    }

    protected function formatMessageData(\App\Models\Message $message): array
    {
        $replyTo = $message->replyTo;
        return [
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
                'id' => $message->sender->id,
                'name' => $message->sender->name,
                'profile_photo' => $message->sender->profile_photo,
            ],
        ];
    }

    protected function resolveUserId($connection, array &$payload, Conversation $conversation): ?int
    {
        $senderId = $payload['sender_id'] ?? null;
        if ($senderId && $this->isParticipant($senderId, $conversation)) {
            unset($payload['sender_id']);
            return (int) $senderId;
        }

        // Fallback: check ConnectionTracker
        foreach (ConnectionTracker::getAllUserIds() as $userId) {
            $conn = ConnectionTracker::getConnection($userId);
            if ($conn && $conn->id() === $connection->id()) {
                return $userId;
            }
        }

        return null;
    }

    protected function isParticipant(int $userId, Conversation $conversation): bool
    {
        return $conversation->user1_id === $userId || $conversation->user2_id === $userId;
    }

    protected function handleSend(User $user, Conversation $conversation, array $payload): void
    {
        \Illuminate\Support\Facades\Log::info("[REVERB HANDLE] Sending message for user {$user->id} in conv {$conversation->id}");
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
        \Illuminate\Support\Facades\Log::info("[REVERB HANDLE] Read message for user {$user->id} in conv {$conversation->id}");
        $conversation->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $otherUser = $conversation->getOtherUser($user);
        $data = ['conversation_id' => $conversation->id, 'last_read_message_id' => 0];

        $this->connection->send(json_encode([
            'event' => 'App\\Events\\MessageRead',
            'data' => json_encode($data),
            'channel' => "private-user.{$user->id}",
        ]));

        $this->sendToUser($otherUser->id, 'App\\Events\\MessageRead', $data);
    }

    protected function handleReact(User $user, Conversation $conversation, array $payload): void
    {
        \Illuminate\Support\Facades\Log::info("[REVERB HANDLE] React message for user {$user->id} in conv {$conversation->id}", $payload);
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
        \Illuminate\Support\Facades\Log::info("[REVERB HANDLE] Delete message for user {$user->id} in conv {$conversation->id}", $payload);
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
        \Illuminate\Support\Facades\Log::info("[REVERB HANDLE] Vanish toggle for user {$user->id} in conv {$conversation->id}", $payload);
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
        \Illuminate\Support\Facades\Log::info("[REVERB HANDLE] Typing indicator for user {$user->id} in conv {$conversation->id}");
        $otherUser = $conversation->getOtherUser($user);
        $data = ['conversation_id' => $conversation->id, 'user_id' => $user->id, 'user_name' => $user->name];
        $this->sendToUser($otherUser->id, 'App\\Events\\TypingIndicator', $data);
    }

    protected function handleTypingStopped(User $user, Conversation $conversation, array $payload): void
    {
        \Illuminate\Support\Facades\Log::info("[REVERB HANDLE] Typing stopped for user {$user->id} in conv {$conversation->id}");
        $otherUser = $conversation->getOtherUser($user);
        $data = ['conversation_id' => $conversation->id, 'user_id' => $user->id];
        $this->sendToUser($otherUser->id, 'App\\Events\\TypingStopped', $data);
    }
}
