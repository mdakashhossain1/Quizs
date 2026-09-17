<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\FcmService;
use App\Services\TargetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        return view('admin.settings.index', [
            'emailConfigured' => config('mail.default') === 'smtp' && filled(config('mail.mailers.smtp.host')),
            'firebaseConfigured' => FcmService::isConfigured(),
        ]);
    }

    public function general(): View
    {
        $globalDailyTarget = TargetService::globalTarget();

        return view('admin.settings.general', compact('globalDailyTarget'));
    }

    public function updateGeneral(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'global_daily_quiz_target' => ['required', 'integer', 'min:1', 'max:1000'],
        ]);

        TargetService::setGlobalTarget($validated['global_daily_quiz_target']);

        return redirect()->route('admin.settings.general')->with('success', 'Settings updated.');
    }
}
