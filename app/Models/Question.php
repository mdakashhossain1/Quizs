<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'question_text',
        'meaning',
        'explanation',
        'points',
        'sort_order',
    ];

    protected $casts = [
        'points' => 'integer',
        'sort_order' => 'integer',
    ];

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(Option::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(QuestionTranslation::class);
    }

    /**
     * Resolves this question's display content for $lang. A legacy
     * (single-language) quiz's question always answers from its own base
     * columns, unchanged from before this feature existed. A bilingual
     * question resolves from question_translations, falling back to the
     * configured fallback language when the requested one is missing
     * (bilingual_question_management_prd.md §16) — never a hard failure.
     *
     * @return array{question_text: string, meaning: ?string, explanation: ?string, served_language: ?string, translation_fallback: bool}
     */
    public function contentFor(string $lang): array
    {
        if (! $this->quiz->isBilingual()) {
            return [
                'question_text' => $this->question_text,
                'meaning' => $this->meaning,
                'explanation' => $this->explanation,
                'served_language' => $this->quiz->language,
                'translation_fallback' => false,
            ];
        }

        $fallbackLang = config('quiz.fallback_language', 'en');
        $translation = $this->translations->firstWhere('language_code', $lang)
            ?? $this->translations->firstWhere('language_code', $fallbackLang);

        return [
            'question_text' => $translation?->question_text ?? '',
            'meaning' => $translation?->meaning,
            'explanation' => $translation?->explanation,
            'served_language' => $translation?->language_code,
            'translation_fallback' => $translation !== null && $translation->language_code !== $lang,
        ];
    }

    /**
     * Admin-listing preview text: prefers the fallback language but falls
     * back further to whatever translation actually has content, so a
     * question isn't shown blank just because English hasn't been entered
     * yet.
     */
    public function previewContent(): array
    {
        if (! $this->quiz->isBilingual()) {
            return $this->contentFor(config('quiz.fallback_language', 'en'));
        }

        $preferredOrder = array_unique([config('quiz.fallback_language', 'en'), ...config('quiz.supported_languages', ['en', 'hi'])]);
        foreach ($preferredOrder as $lang) {
            if ($this->translations->firstWhere('language_code', $lang)) {
                return $this->contentFor($lang);
            }
        }

        return $this->contentFor(config('quiz.fallback_language', 'en'));
    }

    /** Whether every configured language has a translation with real content. */
    public function hasCompleteTranslations(array $languages): bool
    {
        foreach ($languages as $lang) {
            $translation = $this->translations->firstWhere('language_code', $lang);
            if (! $translation || trim($translation->question_text) === '') {
                return false;
            }
        }

        return true;
    }
}
