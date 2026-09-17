<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OptionTranslation extends Model
{
    use HasFactory;

    protected $fillable = ['option_id', 'language_code', 'option_text'];

    public function option(): BelongsTo
    {
        return $this->belongsTo(Option::class);
    }
}
