<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPreference extends Model
{
    protected $fillable = ['user_id', 'gender_preference', 'min_age', 'max_age', 'max_distance'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
