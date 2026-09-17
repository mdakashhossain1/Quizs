<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'quiz_id',
        'language_used',
        'started_at',
        'status',
        'score',
        'total_questions',
        'attempted_questions',
        'correct_answers',
        'wrong_answers',
        'unanswered_questions',
        'accuracy',
        'completed_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'score' => 'integer',
        'total_questions' => 'integer',
        'attempted_questions' => 'integer',
        'correct_answers' => 'integer',
        'wrong_answers' => 'integer',
        'unanswered_questions' => 'integer',
        'accuracy' => 'float',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(QuizAttemptAnswer::class);
    }
}
