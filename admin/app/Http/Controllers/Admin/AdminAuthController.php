<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminAuthController extends Controller
{
    /**
     * Show admin login form.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check() && Auth::user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    /**
     * Handle login submission.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (config('services.turnstile.enabled')) {
            $this->verifyTurnstile($request);
        }

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();

            if ($user->role !== 'admin') {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Access denied. Administrator privileges required.',
                ])->onlyInput('email');
            }

            if (! $user->is_active) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Your account has been deactivated.',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();

            return redirect()->route('admin.dashboard')
                ->with('success', 'Welcome back, ' . $user->name . '!');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Handle admin logout.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('success', 'You have been logged out.');
    }

    /**
     * Verifies the Cloudflare Turnstile widget response server-side; throws
     * a validation error (rendered next to the widget) when it's missing or
     * rejected, so a bot can't skip the challenge by dropping the token.
     */
    private function verifyTurnstile(Request $request): void
    {
        $token = $request->string('cf-turnstile-response')->toString();

        if ($token === '') {
            throw ValidationException::withMessages([
                'cf-turnstile-response' => 'Please complete the captcha challenge.',
            ]);
        }

        $response = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret' => config('services.turnstile.secret_key'),
            'response' => $token,
            'remoteip' => $request->ip(),
        ]);

        if (! $response->json('success', false)) {
            throw ValidationException::withMessages([
                'cf-turnstile-response' => 'Captcha verification failed. Please try again.',
            ]);
        }
    }
}
