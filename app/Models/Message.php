<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = ['conversation_id', 'sender_id', 'content', 'type', 'reply_to_id', 'metadata', 'status', 'read_at', 'expires_at'];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
            'expires_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function replyTo()
    {
        return $this->belongsTo(Message::class, 'reply_to_id');
    }

    public function isRead()
    {
        return $this->read_at !== null;
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function markDelivered(): void
    {
        if ($this->status === 'sent') {
            $this->update(['status' => 'delivered']);
        }
    }

    public function markRead(): void
    {
        $this->update(['status' => 'read', 'read_at' => now()]);

        if ($this->shouldSelfDestruct()) {
            $this->delete();
        }
    }

    public function shouldSelfDestruct(): bool
    {
        $conversation = $this->conversation;

        if (! $conversation || $conversation->vanish_mode === 'off') {
            return false;
        }

        if ($conversation->vanish_mode === 'after_seen') {
            $otherUserId = $this->sender_id === $conversation->user1_id
                ? $conversation->user2_id
                : $conversation->user1_id;

            $otherHasRead = Message::where('conversation_id', $conversation->id)
                ->where('sender_id', $otherUserId)
                ->whereNull('read_at')
                ->where('id', '<=', $this->id)
                ->exists();

            return ! $otherHasRead;
        }

        return false;
    }
}
