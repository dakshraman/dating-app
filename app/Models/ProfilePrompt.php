<?php

namespace App\Models;

use Database\Factories\ProfilePromptFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfilePrompt extends Model
{
    /** @use HasFactory<ProfilePromptFactory> */
    use HasFactory;

    protected $fillable = ['user_id', 'prompt', 'answer', 'order'];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
