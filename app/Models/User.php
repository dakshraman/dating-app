<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'phone', 'gender', 'birth_date', 'bio', 'location', 'latitude', 'longitude', 'profile_photo', 'verification_photo', 'is_verified', 'is_active', 'last_active_at', 'remaining_swipes', 'remaining_super_likes', 'fcm_token', 'is_banned', 'ban_reason', 'banned_at', 'mask_name', 'incognito_mode', 'travel_latitude', 'travel_longitude'])]
#[Hidden(['password', 'remember_token'])]
/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string|null $phone
 * @property string|null $gender
 * @property Carbon|null $birth_date
 * @property string|null $bio
 * @property string|null $location
 * @property float|null $latitude
 * @property float|null $longitude
 * @property string|null $profile_photo
 * @property string|null $verification_photo
 * @property bool $is_verified
 * @property bool $is_active
 * @property Carbon|null $last_active_at
 * @property int $remaining_swipes
 * @property int $remaining_super_likes
 * @property string|null $fcm_token
 * @property bool $is_banned
 * @property string|null $ban_reason
 * @property Carbon|null $banned_at
 * @property bool $mask_name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
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
            'password' => 'hashed',
            'fcm_token' => 'string',
            'remaining_swipes' => 'integer',
            'remaining_super_likes' => 'integer',
            'incognito_mode' => 'boolean',
            'travel_latitude' => 'float',
            'travel_longitude' => 'float',
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
        return UserMatch::where('user1_id', $this->id)
            ->orWhere('user2_id', $this->id)
            ->get();
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
        return $this->fcm_token ? [$this->fcm_token] : [];
    }

    public function age()
    {
        return $this->birth_date?->age;
    }

    public function blockedUsers()
    {
        return $this->belongsToMany(User::class, 'blocked_users', 'user_id', 'blocked_id');
    }

    public function blockedByUsers()
    {
        return $this->belongsToMany(User::class, 'blocked_users', 'blocked_id', 'user_id');
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
        $myInterests = $this->interests->pluck('id');
        $theirInterests = $other->interests->pluck('id');

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
        return $this->hasActiveSubscription() ? PHP_INT_MAX : (int) ($this->remaining_swipes ?? 0);
    }

    public function getRemainingSuperLikes(): int
    {
        return $this->hasActiveSubscription() ? PHP_INT_MAX : (int) ($this->remaining_super_likes ?? 0);
    }

    public function scopeDiscoverable(Builder $query, User $user)
    {
        $pref = $user->preferences;

        $excludeIds = $user->sentSwipes()->pluck('swiped_id')
            ->merge($user->blockedUsers()->pluck('blocked_users.blocked_id'))
            ->merge($user->blockedByUsers()->pluck('users.id'))
            ->unique()
            ->values()
            ->toArray();

        return $query->where('users.id', '!=', $user->id)
            ->where('users.is_active', true)
            ->where('users.is_banned', false)
            ->where('users.incognito_mode', false)
            ->whereNotNull('users.birth_date')
            ->whereNotNull('users.gender')
            ->whereNotNull('users.profile_photo')
            ->when($excludeIds, fn ($q) => $q->whereNotIn('users.id', $excludeIds))
            ->when($pref && $pref->gender_preference, function ($q) use ($pref) {
                $q->where('users.gender', $pref->gender_preference);
            })
            ->when($pref?->religion_preference, function ($q) use ($pref) {
                $q->where('users.religion', $pref->religion_preference);
            })
            ->when($pref?->mother_tongue_preference, function ($q) use ($pref) {
                $q->where('users.mother_tongue', $pref->mother_tongue_preference);
            })
            ->when($pref?->dietary_preference, function ($q) use ($pref) {
                $q->where('users.dietary_preference', $pref->dietary_preference);
            });
    }
}
