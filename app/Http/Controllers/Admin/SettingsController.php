<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\TargetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        $globalDailyTarget = TargetService::globalTarget();

        return view('admin.settings.index', compact('globalDailyTarget'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'global_daily_quiz_target' => ['required', 'integer', 'min:1', 'max:1000'],
        ]);

        TargetService::setGlobalTarget($validated['global_daily_quiz_target']);

        return redirect()->route('admin.settings.index')->with('success', 'Settings updated.');
    }
}
