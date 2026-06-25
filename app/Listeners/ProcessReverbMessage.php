<?php

namespace App\Listeners;

use App\Events\MessageDelivered;
use App\Events\MessageRead;
use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use App\Notifications\NewMessageNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Laravel\Reverb\Events\MessageReceived;

class ProcessReverbMessage
{
    public function handle(MessageReceived $event): void
    {
        $data = json_decode($event->message, true);
        if (! $data || ! isset($data['event'])) {
            return;
        }

        $eventName = $data['event'];
        $channel = $data['channel'] ?? '';
        $payload = $data['data'] ?? [];

        if (! str_starts_with($eventName, 'client-')) {
            return;
        }

        preg_match('/^private-conversation\.(\d+)$/', $channel, $matches);
        if (! $matches) {
            return;
        }

        $conversationId = (int) $matches[1];
        $conversation = Conversation::find($conversationId);
        if (! $conversation) {
            return;
        }

        $connection = $event->connection;
        $userId = $connection->data('user_id') ?? $connection->data('userId');
        if (! $userId) {
            return;
        }

        $user = \App\Models\User::find($userId);
        if (! $user) {
            return;
        }

        if ($conversation->user1_id !== $user->id && $conversation->user2_id !== $user->id) {
            return;
        }

        match ($eventName) {
            'client-MessageSend' => $this->handleSend($user, $conversation, $payload),
            'client-MessageRead' => $this->handleRead($user, $conversation, $payload),
            'client-MessageReact' => $this->handleReact($user, $conversation, $payload),
            'client-MessageDelete' => $this->handleDelete($user, $conversation, $payload),
            'client-VanishToggle' => $this->handleVanishToggle($user, $conversation, $payload),
            default => null,
        };
    }

    protected function handleSend($user, Conversation $conversation, array $payload): void
    {
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

        broadcast(new MessageSent($message));

        if ($otherUser && filled($otherUser->fcm_token)) {
            Notification::send($otherUser, new NewMessageNotification($message));
        }
    }

    protected function handleRead($user, Conversation $conversation, array $payload): void
    {
        $conversation->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        broadcast(new MessageRead($conversation->id, 0, $user->id));
    }

    protected function handleReact($user, Conversation $conversation, array $payload): void
    {
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

        broadcast(new MessageSent($message))->toOthers();
    }

    protected function handleDelete($user, Conversation $conversation, array $payload): void
    {
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

    protected function handleVanishToggle($user, Conversation $conversation, array $payload): void
    {
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

        broadcast(new MessageSent($message))->toOthers();
    }
}
