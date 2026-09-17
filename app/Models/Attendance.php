<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'status',
        'marked_by',
        'note',
    ];

    // 'date' is deliberately NOT cast to Eloquent's 'date' type — see the
    // identical note on UserDailyProgress for why that breaks
    // firstOrCreate/updateOrCreate lookups.

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function markedByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
    }
}
