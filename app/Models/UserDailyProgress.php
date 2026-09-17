<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class UserDailyProgress extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'effective_target',
        'completed_quizzes',
        'progress_percentage',
        'target_status',
        'target_completed_at',
    ];

    protected function casts(): array
    {
        return [
            // Deliberately NOT cast to Eloquent's 'date' type: that cast
            // mutates the stored value to a full datetime string on write,
            // which then fails to match the plain 'Y-m-d' string used to
            // look this row up in TargetService::progressForDate, causing
            // firstOrCreate to insert a duplicate and hit the unique
            // constraint. Kept as a plain 'Y-m-d' string throughout instead.
            'effective_target' => 'integer',
            'completed_quizzes' => 'integer',
            'progress_percentage' => 'float',
            'target_completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The stored status only ever reaches 'completed' by count, never
     * 'not_completed' — there's no cron marking days missed. A past day
     * that never hit its target is reclassified as missed here, at read
     * time, instead (roadmap §6.5); today's own row is left as-is so it can
     * still become 'completed' before the day ends.
     */
    public function getDisplayStatusAttribute(): string
    {
        if ($this->target_status === 'completed') {
            return 'completed';
        }

        $businessToday = Carbon::now(config('quiz.business_timezone'))->toDateString();

        if ($this->date < $businessToday) {
            return 'not_completed';
        }

        return $this->target_status;
    }

    public function getRemainingAttribute(): int
    {
        return max(0, $this->effective_target - $this->completed_quizzes);
    }
}
