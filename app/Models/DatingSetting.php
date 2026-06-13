<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DatingSetting extends Model
{
    protected $fillable = [
        'daily_swipe_limit',
        'daily_super_like_limit',
        'boost_duration_minutes',
        'verification_required_for_swiping',
    ];

    protected $attributes = [
        'daily_swipe_limit' => 10,
        'daily_super_like_limit' => 3,
        'boost_duration_minutes' => 30,
        'verification_required_for_swiping' => false,
    ];

    protected function casts(): array
    {
        return [
            'daily_swipe_limit' => 'integer',
            'daily_super_like_limit' => 'integer',
            'boost_duration_minutes' => 'integer',
            'verification_required_for_swiping' => 'boolean',
        ];
    }

    public static function instance(): self
    {
        return cache()->remember('dating_settings', 3600, function () {
            return self::first() ?? self::create();
        });
    }
}
