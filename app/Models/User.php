<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'phone', 'gender', 'birth_date', 'bio', 'location', 'latitude', 'longitude', 'profile_photo', 'verification_photo', 'is_verified', 'is_active', 'last_active_at', 'last_seen_at', 'remaining_swipes', 'remaining_super_likes', 'fcm_tokens', 'is_banned', 'ban_reason', 'banned_at', 'mask_name'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
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
            'is_banned' => 'boolean',
            'mask_name' => 'boolean',
            'banned_at' => 'datetime',
            'last_active_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'password' => 'hashed',
            'fcm_tokens' => 'array',
            'remaining_swipes' => 'integer',
            'remaining_super_likes' => 'integer',
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

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'reporter_id');
    }

    public function prompts(): HasMany
    {
        return $this->hasMany(ProfilePrompt::class)->orderBy('order');
    }

    public function visits(): HasMany
    {
        return $this->hasMany(ProfileVisit::class, 'visited_id');
    }

    public function visitors(): HasMany
    {
        return $this->hasMany(ProfileVisit::class, 'visited_id');
    }

    public function compatibilityWith(User $other): int
    {
        $myInterests = $this->interests()->pluck('interest_id');
        $theirInterests = $other->interests()->pluck('interest_id');

        $shared = $myInterests->intersect($theirInterests);
        $total = $myInterests->union($theirInterests)->count();

        if ($total === 0) {
            return 0;
        }

        return (int) round(($shared->count() / $total) * 100);
    }

    public function subscription(): HasMany
    {
        return $this->hasMany(UserSubscription::class)->active()->latest();
    }

    public function hasActiveSubscription(): bool
    {
        return $this->subscription()->exists();
    }

    public function dailySwipeUsage(): HasMany
    {
        return $this->hasMany(DailySwipeUsage::class);
    }

    public function profileBoosts(): HasMany
    {
        return $this->hasMany(ProfileBoost::class);
    }

    public function hasActiveBoost(): bool
    {
        return $this->profileBoosts()->where('is_active', true)
            ->where('started_at', '<=', now())
            ->where('expires_at', '>=', now())
            ->exists();
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function getRemainingSwipes(): int
    {
        return $this->hasActiveSubscription() ? PHP_INT_MAX : $this->remaining_swipes;
    }

    public function getRemainingSuperLikes(): int
    {
        return $this->hasActiveSubscription() ? PHP_INT_MAX : $this->remaining_super_likes;
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
            })
            ->when($pref?->religion_preference, function ($q) use ($pref) {
                $q->where('religion', $pref->religion_preference);
            })
            ->when($pref?->mother_tongue_preference, function ($q) use ($pref) {
                $q->where('mother_tongue', $pref->mother_tongue_preference);
            })
            ->when($pref?->dietary_preference, function ($q) use ($pref) {
                $q->where('dietary_preference', $pref->dietary_preference);
            });
    }
}
