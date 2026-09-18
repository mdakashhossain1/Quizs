<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\EnvFileWriter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TurnstileSettingsController extends Controller
{
    private const TEST_SITE_KEY = '1x00000000000000000000AA';

    public function edit(): View
    {
        return view('admin.settings.turnstile', [
            'enabled' => (bool) config('services.turnstile.enabled'),
            'siteKey' => config('services.turnstile.site_key'),
            'usingTestKeys' => config('services.turnstile.site_key') === self::TEST_SITE_KEY,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'site_key' => ['required', 'string', 'max:255'],
            'secret_key' => ['nullable', 'string', 'max:255'],
        ]);

        $values = [
            'TURNSTILE_SITE_KEY' => $validated['site_key'],
            'TURNSTILE_ENABLED' => $request->boolean('enabled') ? 'true' : 'false',
        ];

        // The secret is never echoed back into the form, so it's only
        // overwritten when the admin actually types a new one.
        if (filled($validated['secret_key'] ?? null)) {
            $values['TURNSTILE_SECRET_KEY'] = $validated['secret_key'];
        }

        EnvFileWriter::set($values);

        return redirect()->route('admin.settings.turnstile')->with('success', 'Turnstile settings saved.');
    }
}
