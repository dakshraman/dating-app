<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = ['match_id', 'user1_id', 'user2_id', 'last_message_at'];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
        ];
    }

    public function match()
    {
        return $this->belongsTo(UserMatch::class, 'match_id');
    }

    public function user1()
    {
        return $this->belongsTo(User::class, 'user1_id');
    }

    public function user2()
    {
        return $this->belongsTo(User::class, 'user2_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function getOtherUser(User $user)
    {
        return $user->id === $this->user1_id ? $this->user2 : $this->user1;
    }
}
