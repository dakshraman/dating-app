<?php

namespace App\Models;

use Database\Factories\ProfileVisitFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileVisit extends Model
{
    /** @use HasFactory<ProfileVisitFactory> */
    use HasFactory;

    protected $fillable = ['visitor_id', 'visited_id'];

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'visitor_id');
    }

    public function visited(): BelongsTo
    {
        return $this->belongsTo(User::class, 'visited_id');
    }
}
