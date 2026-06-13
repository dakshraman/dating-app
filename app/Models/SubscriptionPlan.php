<?php

namespace App\Models;

use Database\Factories\SubscriptionPlanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    /** @use HasFactory<SubscriptionPlanFactory> */
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'price', 'duration_days', 'features', 'is_active'];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'duration_days' => 'integer',
            'features' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function userSubscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
