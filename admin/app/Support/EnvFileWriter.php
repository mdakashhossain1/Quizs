<?php

namespace App\Support;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

/**
 * Persists settings-panel changes (SMTP, Firebase credentials, ...) straight
 * into .env, for shared hosting where there's no reliable private storage
 * path or shell access to edit it by hand. Every write is backed up first
 * and followed by a config cache clear so the change takes effect on the
 * very next request.
 */
class EnvFileWriter
{
    private const BACKUP_DIR = 'app/private/env-backups';

    private const MAX_BACKUPS = 20;

    public static function set(array $values): void
    {
        $path = base_path('.env');
        $content = File::exists($path) ? File::get($path) : '';

        self::backup($content);

        foreach ($values as $key => $value) {
            $line = $key.'='.self::quote((string) $value);
            $pattern = '/^'.preg_quote($key, '/').'=.*$/m';

            $content = preg_match($pattern, $content)
                ? preg_replace($pattern, $line, $content, 1)
                : rtrim($content).PHP_EOL.$line.PHP_EOL;
        }

        File::put($path, $content);

        Artisan::call('config:clear');
    }

    private static function backup(string $content): void
    {
        if ($content === '') {
            return;
        }

        $dir = storage_path(self::BACKUP_DIR);
        File::ensureDirectoryExists($dir, 0700);

        File::put($dir.'/env-'.now()->format('Ymd-His-u').'.bak', $content);

        $backups = collect(File::files($dir))->sortByDesc(fn ($f) => $f->getMTime());
        $backups->slice(self::MAX_BACKUPS)->each(fn ($f) => File::delete($f->getPathname()));
    }

    /**
     * Single-quotes by default: Dotenv treats a single-quoted value as
     * completely literal — no escape processing, no ${VAR} interpolation —
     * which matters for content like JSON/PEM keys containing literal
     * backslash-n sequences that must survive untouched. Falls back to
     * double-quoting only when the value itself contains a single quote.
     */
    private static function quote(string $value): string
    {
        if (! str_contains($value, "'")) {
            return "'".$value."'";
        }

        $escaped = str_replace(['\\', '"', '$'], ['\\\\', '\\"', '\\$'], $value);

        return '"'.$escaped.'"';
    }
}
