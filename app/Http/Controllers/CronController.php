<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

/**
 * Webhook entry points for HTTP-triggered ("webcron") services like
 * cron-job.org, for hosts where a persistent `queue:work` process or
 * shell-based crontab isn't available. Each hit runs the equivalent
 * artisan command for a bounded window and then exits.
 */
class CronController extends Controller
{
    /**
     * Drain the mail/notification queue. Point a cron-job.org job at this
     * URL (e.g. every minute): GET /api/cron/run-queue?key=CRON_SECRET
     */
    public function runQueue(Request $request): JsonResponse
    {
        if (! $this->hasValidKey($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Bounded by --max-time so one request can't run past typical
        // shared-host execution limits; --stop-when-empty exits early
        // once nothing is left, so idle ticks return almost instantly.
        $seconds = (int) $request->query('seconds', 50);
        $seconds = max(5, min($seconds, 55));

        Artisan::call('queue:work', [
            '--stop-when-empty' => true,
            '--max-time' => $seconds,
            '--tries' => 3,
            '--sleep' => 1,
        ]);

        return response()->json([
            'ok' => true,
            'ran_for_seconds' => $seconds,
            'output' => trim(Artisan::output()),
        ]);
    }

    private function hasValidKey(Request $request): bool
    {
        $expected = (string) config('services.cron.secret');
        $given = (string) $request->query('key', '');

        return $expected !== '' && hash_equals($expected, $given);
    }
}
