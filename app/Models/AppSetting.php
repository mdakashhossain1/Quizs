<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Simple admin-editable key/value store (e.g. the global daily quiz target).
 * Technical/system configuration stays in config/quiz.php + .env instead —
 * see that file's comments for which settings belong where.
 */
class AppSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, mixed $default = null): mixed
    {
        return static::where('key', $key)->value('value') ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
