<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSession extends Model
{
    protected $fillable = [
        'user_id',
        'device_id',
        'authenticated_at',
        'last_active_at',
        'explicit_logout_at',
        'session_ended_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'authenticated_at' => 'datetime',
            'last_active_at' => 'datetime',
            'explicit_logout_at' => 'datetime',
            'session_ended_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
