<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'language',
        'title',
        'slug',
        'description',
        'image',
        'duration_minutes',
        'passing_percentage',
        'difficulty',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'duration_minutes' => 'integer',
        'passing_percentage' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('sort_order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    /**
     * A bilingual quiz has no single fixed language — its questions carry
     * their own per-language translations instead (bilingual_question_
     * management_prd.md). A non-null `language` means the older single-
     * language model: every question's text lives directly on the question/
     * option rows, exactly as before this feature existed.
     */
    public function isBilingual(): bool
    {
        return $this->language === null;
    }
}
