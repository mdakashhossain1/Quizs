<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfferwallTransaction extends Model
{
    protected $fillable = [
        'user_id', 'provider', 'transaction_id', 'transaction_key', 'offer_id', 'offer_name',
        'status', 'completed_at', 'provider_payload',
    ];

    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
            'provider_payload' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
