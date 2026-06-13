<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserMatch extends Model
{
    protected $table = 'matches';

    protected $fillable = ['user1_id', 'user2_id', 'matched_at', 'expires_at'];

    protected function casts(): array
    {
        return [
            'matched_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function user1()
    {
        return $this->belongsTo(User::class, 'user1_id');
    }

    public function user2()
    {
        return $this->belongsTo(User::class, 'user2_id');
    }

    public function conversation()
    {
        return $this->hasOne(Conversation::class, 'match_id');
    }

    public function getOtherUser(User $user)
    {
        return $user->id === $this->user1_id ? $this->user2 : $this->user1;
    }
}
