<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PushNotification extends Model
{
    protected $fillable = [
        'title',
        'body',
        'thumbnail_url',
        'target_type',
        'destination_type',
        'destination_id',
        'payload_data',
        'status',
        'created_by',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'payload_data' => 'array',
            'sent_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(PushNotificationRecipient::class);
    }
}
