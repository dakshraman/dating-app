<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public Message $message
    ) {}

    public function broadcastOn(): array
    {
        $this->message->loadMissing('conversation');

        $otherUserId = $this->message->sender_id === $this->message->conversation->user1_id
            ? $this->message->conversation->user2_id
            : $this->message->conversation->user1_id;

        return [
            new PrivateChannel("user.{$otherUserId}"),
        ];
    }

    public function broadcastWith(): array
    {
        $replyTo = $this->message->replyTo;

        return [
            'id' => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'sender_id' => $this->message->sender_id,
            'content' => $this->message->content,
            'type' => $this->message->type,
            'status' => $this->message->status,
            'metadata' => $this->message->metadata,
            'read_at' => $this->message->read_at,
            'expires_at' => $this->message->expires_at,
            'created_at' => $this->message->created_at,
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
                'id' => $this->message->sender->id,
                'name' => $this->message->sender->name,
                'profile_photo' => $this->message->sender->profile_photo,
            ],
        ];
    }
}
