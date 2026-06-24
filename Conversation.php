<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = [
        'match_id', 'user1_id', 'user2_id', 'last_message_at',
        'user1_deleted_at', 'user2_deleted_at', 'vanish_mode',
    ];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
            'user1_deleted_at' => 'datetime',
            'user2_deleted_at' => 'datetime',
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

    public function scopeWhereParticipant(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $q) use ($user) {
            $q->where('user1_id', $user->id)
                ->whereNull('user1_deleted_at');
        })->orWhere(function (Builder $q) use ($user) {
            $q->where('user2_id', $user->id)
                ->whereNull('user2_deleted_at');
        });
    }

    public function isDeletedBy(User $user): bool
    {
        if ($user->id === $this->user1_id) {
            return $this->user1_deleted_at !== null;
        }

        if ($user->id === $this->user2_id) {
            return $this->user2_deleted_at !== null;
        }

        return false;
    }

    public function restoreForUser(User $user): void
    {
        if ($user->id === $this->user1_id && $this->user1_deleted_at) {
            $this->update(['user1_deleted_at' => null]);
        } elseif ($user->id === $this->user2_id && $this->user2_deleted_at) {
            $this->update(['user2_deleted_at' => null]);
        }
    }

    public function isVanishMode(): bool
    {
        return $this->vanish_mode !== 'off';
    }

    public function scopeWhereVanishMode(Builder $query, string $mode): Builder
    {
        return $query->where('vanish_mode', $mode);
    }
}
