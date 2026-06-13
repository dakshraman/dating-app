<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailySwipeUsage extends Model
{
    use HasFactory;

    protected $table = 'daily_swipe_usage';

    protected $fillable = ['user_id', 'date', 'count'];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForDate($query, $date)
    {
        return $query->where('date', $date);
    }
}
