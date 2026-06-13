<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'phone', 'gender', 'birth_date', 'bio', 'location', 'latitude', 'longitude', 'profile_photo'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'birth_date' => 'date',
            'is_verified' => 'boolean',
            'is_active' => 'boolean',
            'last_active_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'password' => 'hashed',
            'fcm_tokens' => 'array',
        ];
    }

    public function photos()
    {
        return $this->hasMany(UserPhoto::class)->orderBy('order');
    }

    public function preferences()
    {
        return $this->hasOne(UserPreference::class);
    }

    public function interests()
    {
        return $this->belongsToMany(Interest::class);
    }

    public function sentSwipes()
    {
        return $this->hasMany(Swipe::class, 'swiper_id');
    }

    public function receivedSwipes()
    {
        return $this->hasMany(Swipe::class, 'swiped_id');
    }

    public function matchesAsUser1()
    {
        return $this->hasMany(UserMatch::class, 'user1_id');
    }

    public function matchesAsUser2()
    {
        return $this->hasMany(UserMatch::class, 'user2_id');
    }

    public function matches()
    {
        $user1Matches = $this->matchesAsUser1()->get();
        $user2Matches = $this->matchesAsUser2()->get();

        return $user1Matches->merge($user2Matches);
    }

    public function conversations()
    {
        $conversations = Conversation::where('user1_id', $this->id)
            ->orWhere('user2_id', $this->id)
            ->orderBy('last_message_at', 'desc');

        return $conversations;
    }

    public function routeNotificationForFcm()
    {
        return $this->fcm_tokens ?? [];
    }

    public function age()
    {
        return $this->birth_date?->age;
    }

    public function blockedUsers()
    {
        return $this->belongsToMany(User::class, 'blocked_users', 'user_id', 'blocked_id');
    }

    public function reports()
    {
        return $this->hasMany(Report::class, 'reporter_id');
    }

    public function scopeDiscoverable($query, User $user)
    {
        $pref = $user->preferences;

        return $query->where('id', '!=', $user->id)
            ->where('is_active', true)
            ->whereNotNull('birth_date')
            ->whereNotNull('gender')
            ->whereNotNull('profile_photo')
            ->whereNotIn('id', $user->sentSwipes()->pluck('swiped_id'))
            ->when($pref, function ($q) use ($pref) {
                $q->where('gender', $pref->gender_preference);
            });
    }
}
