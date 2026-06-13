<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = ['conversation_id', 'sender_id', 'content', 'type', 'reply_to_id', 'metadata', 'status', 'read_at'];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
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

    public function markDelivered(): void
    {
        if ($this->status === 'sent') {
            $this->update(['status' => 'delivered']);
        }
    }

    public function markRead(): void
    {
        $this->update(['status' => 'read', 'read_at' => now()]);
    }
}
