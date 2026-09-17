<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class DatabaseMigrationController extends Controller
{
    public function index(): View
    {
        Artisan::call('migrate:status');

        return view('admin.settings.migrations', [
            'status' => Artisan::output(),
        ]);
    }

    /**
     * --force is required because APP_ENV won't be 'local' in production —
     * without it, artisan refuses to run migrations non-interactively there.
     */
    public function run(): RedirectResponse
    {
        Artisan::call('migrate', ['--force' => true]);

        return redirect()->route('admin.settings.migrations')
            ->with('success', 'Migrations ran successfully.')
            ->with('migration_output', Artisan::output());
    }
}
