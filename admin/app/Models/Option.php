<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Option extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'option_text',
        'is_correct',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(OptionTranslation::class);
    }

    /**
     * `is_correct` lives on this row regardless of language, so which
     * option is correct can never disagree between an option's English and
     * Hindi text (bilingual_question_management_prd.md §2 — the answer
     * mapping must remain identical across translations).
     */
    public function textFor(string $lang): string
    {
        if (! $this->question->quiz->isBilingual()) {
            return $this->option_text;
        }

        $fallbackLang = config('quiz.fallback_language', 'en');
        $translation = $this->translations->firstWhere('language_code', $lang)
            ?? $this->translations->firstWhere('language_code', $fallbackLang);

        return $translation?->option_text ?? '';
    }
}
