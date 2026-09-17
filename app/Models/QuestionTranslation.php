<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'language_code',
        'question_text',
        'meaning',
        'explanation',
        'image',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
