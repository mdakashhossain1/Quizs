<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\EnvFileWriter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Throwable;

class EmailSettingsController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.email', [
            'configured' => config('mail.default') === 'smtp' && filled(config('mail.mailers.smtp.host')),
            'host' => config('mail.mailers.smtp.host'),
            'port' => config('mail.mailers.smtp.port'),
            'username' => config('mail.mailers.smtp.username'),
            'fromAddress' => config('mail.from.address'),
            'fromName' => config('mail.from.name'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $this->validateForm($request, requirePassword: false);

        $values = [
            'MAIL_MAILER' => 'smtp',
            'MAIL_HOST' => $validated['mail_host'],
            'MAIL_PORT' => $validated['mail_port'],
            'MAIL_USERNAME' => $validated['mail_username'],
            'MAIL_FROM_ADDRESS' => $validated['mail_from_address'],
            'MAIL_FROM_NAME' => $validated['mail_from_name'],
        ];

        // The form never echoes the stored password back, so it's only
        // overwritten when the admin actually types a new one.
        if (filled($validated['mail_password'] ?? null)) {
            $values['MAIL_PASSWORD'] = $validated['mail_password'];
        }

        EnvFileWriter::set($values);

        return redirect()->route('admin.settings.email')->with('success', 'Email settings saved.');
    }

    /**
     * Sends a real SMTP test using whatever is currently in the form —
     * deliberately not persisted first, so a broken credential never lands
     * in .env just to find out it doesn't work.
     */
    public function sendTest(Request $request): RedirectResponse
    {
        $validated = $this->validateForm($request, requirePassword: false);
        $validated['test_recipient'] = $request->validate([
            'test_recipient' => ['required', 'email', 'max:255'],
        ])['test_recipient'];

        $password = filled($validated['mail_password'] ?? null)
            ? $validated['mail_password']
            : config('mail.mailers.smtp.password');

        config([
            'mail.mailers.smtp.host' => $validated['mail_host'],
            'mail.mailers.smtp.port' => $validated['mail_port'],
            'mail.mailers.smtp.username' => $validated['mail_username'],
            'mail.mailers.smtp.password' => $password,
            'mail.from.address' => $validated['mail_from_address'],
            'mail.from.name' => $validated['mail_from_name'],
        ]);

        // Forces the mail manager to rebuild its SMTP transport from the
        // config we just overrode, instead of reusing one built earlier
        // (possibly with the old, already-cached credentials) this request.
        app()->forgetInstance('mail.manager');
        app()->forgetInstance('mailer');

        try {
            Mail::mailer('smtp')->raw(
                'This is a test email from the Quizs admin panel to confirm your SMTP settings work.',
                fn ($message) => $message->to($validated['test_recipient'])->subject('Quizs SMTP test'),
            );
        } catch (Throwable $e) {
            return back()->withErrors(['test_recipient' => 'Send failed: '.$e->getMessage()])->withInput();
        }

        return back()->with('success', "Test email sent to {$validated['test_recipient']}.");
    }

    private function validateForm(Request $request, bool $requirePassword): array
    {
        return $request->validate([
            'mail_host' => ['required', 'string', 'max:255'],
            'mail_port' => ['required', 'integer', 'min:1', 'max:65535'],
            'mail_username' => ['required', 'string', 'max:255'],
            'mail_password' => [$requirePassword ? 'required' : 'nullable', 'string', 'max:255'],
            'mail_from_address' => ['required', 'email', 'max:255'],
            'mail_from_name' => ['required', 'string', 'max:255'],
        ]);
    }
}
