<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\FcmService;
use App\Support\EnvFileWriter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FirebaseSettingsController extends Controller
{
    private const REQUIRED_KEYS = ['type', 'project_id', 'private_key', 'client_email'];

    public function edit(): View
    {
        return view('admin.settings.firebase', [
            'configured' => FcmService::isConfigured(),
            'projectId' => config('services.fcm.project_id'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'credentials_json' => ['required', 'string'],
        ]);

        $decoded = json_decode($validated['credentials_json'], true);

        if (! is_array($decoded) || json_last_error() !== JSON_ERROR_NONE) {
            return back()->withErrors(['credentials_json' => 'That is not valid JSON.'])->withInput();
        }

        foreach (self::REQUIRED_KEYS as $key) {
            if (empty($decoded[$key])) {
                return back()->withErrors(['credentials_json' => "The JSON is missing \"{$key}\"."])->withInput();
            }
        }

        if ($decoded['type'] !== 'service_account') {
            return back()->withErrors([
                'credentials_json' => 'This does not look like a Firebase service-account key (type is not "service_account").',
            ])->withInput();
        }

        $verification = FcmService::verifyCredentials($decoded);

        if (! $verification['ok']) {
            return back()->withErrors([
                'credentials_json' => 'Google rejected these credentials: '.$verification['error'],
            ])->withInput();
        }

        EnvFileWriter::set([
            'FCM_PROJECT_ID' => $decoded['project_id'],
            'FCM_CREDENTIALS_JSON' => json_encode($decoded, JSON_UNESCAPED_SLASHES),
        ]);

        return redirect()->route('admin.settings.firebase')->with('success', 'Firebase credentials verified with Google and saved.');
    }
}
