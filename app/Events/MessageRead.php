<?php

namespace App\Events;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class MessageRead implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public Conversation $conversation,
        public User $user,
        public int $lastReadMessageId
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("conversation.{$this->conversation->id}"),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->user->id,
            'last_read_message_id' => $this->lastReadMessageId,
            'conversation_id' => $this->conversation->id,
        ];
    }
}
