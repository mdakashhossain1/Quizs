<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProgression extends Model
{
    protected $table = 'user_progression';

    protected $fillable = [
        'user_id',
        'current_level',
        'xp',
        'completed_target_days',
    ];

    protected function casts(): array
    {
        return [
            'current_level' => 'integer',
            'xp' => 'integer',
            'completed_target_days' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
