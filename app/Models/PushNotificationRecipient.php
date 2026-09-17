<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PushNotificationRecipient extends Model
{
    protected $fillable = ['push_notification_id', 'user_id', 'read_at'];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public function notification(): BelongsTo
    {
        return $this->belongsTo(PushNotification::class, 'push_notification_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
