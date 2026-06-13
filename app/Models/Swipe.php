<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Swipe extends Model
{
    protected $fillable = ['swiper_id', 'swiped_id', 'direction', 'is_super_like'];

    protected function casts(): array
    {
        return [
            'is_super_like' => 'boolean',
        ];
    }

    public function swiper()
    {
        return $this->belongsTo(User::class, 'swiper_id');
    }

    public function swiped()
    {
        return $this->belongsTo(User::class, 'swiped_id');
    }
}
