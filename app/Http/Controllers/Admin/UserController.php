<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\NewAccountMail;
use App\Models\User;
use App\Services\ProfileStatsService;
use App\Services\TargetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::withCount('quizAttempts');

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Same recency threshold User::isOnline uses — replicated here so it
        // can run as a SQL filter instead of loading every user into PHP.
        if ($request->filled('online_status')) {
            $threshold = now()->subSeconds(config('quiz.online_timeout_seconds'));
            if ($request->online_status === 'online') {
                $query->where('last_active_at', '>=', $threshold);
            } elseif ($request->online_status === 'offline') {
                $query->where(function ($q) use ($threshold) {
                    $q->whereNull('last_active_at')->orWhere('last_active_at', '<', $threshold);
                });
            }
        }

        $users = $query->latest()->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        return view('admin.users.create');
    }

    /**
     * Admin-created account: generates a temporary password, forces a
     * password change on first login, and emails the credentials.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'login_id' => ['nullable', 'string', 'max:255', 'unique:users,login_id'],
            'role' => ['required', 'in:admin,user'],
        ]);

        $temporaryPassword = Str::password(12);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'login_id' => $validated['login_id'] ?: null,
            'role' => $validated['role'],
            'password' => Hash::make($temporaryPassword),
            'must_change_password' => true,
            'email_verified_at' => now(),
            'streak' => 0,
            'score' => 0,
            'is_active' => true,
        ]);

        $emailSent = $this->sendAccountMail($user, $temporaryPassword);

        return redirect()->route('admin.users.index')->with(
            'success',
            $emailSent
                ? "User created. Temporary password: {$temporaryPassword} (also emailed to {$user->email})."
                : "User created. Temporary password: {$temporaryPassword} (email delivery failed — share this password with the user directly)."
        );
    }

    /**
     * Never lets an SMTP failure crash the request: the temporary password
     * is only ever known to the admin at this moment, so a 500 here would
     * leave the account created with a password nobody can recover.
     */
    private function sendAccountMail(User $user, string $temporaryPassword, bool $isReset = false): bool
    {
        try {
            Mail::to($user->email)->send(new NewAccountMail($user, $temporaryPassword, isReset: $isReset));
            return true;
        } catch (Throwable $e) {
            Log::warning('Account credential email failed to send', [
                'user_id' => $user->id,
                'is_reset' => $isReset,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function edit(User $user): View
    {
        $globalDailyTarget = TargetService::globalTarget();

        return view('admin.users.edit', compact('user', 'globalDailyTarget'));
    }

    /**
     * Login/session history and current activity status for one user
     * (roadmap §4.1).
     */
    public function activity(Request $request, User $user): View
    {
        $sessionsQuery = $user->activitySessions()->latest('authenticated_at');

        if ($request->filled('date_from')) {
            $sessionsQuery->whereDate('authenticated_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $sessionsQuery->whereDate('authenticated_at', '<=', $request->date_to);
        }

        $sessions = $sessionsQuery->paginate(20)->withQueryString();

        // Same computation the mobile app's /profile/stats uses, so this
        // page can never show numbers that disagree with the app (roadmap
        // §9.5, §14).
        $stats = ProfileStatsService::compute($user);

        return view('admin.users.activity', compact('user', 'sessions', 'stats'));
    }

    /**
     * Issue a fresh temporary password for an existing user, force them to
     * change it on next login, and revoke any sessions they currently hold.
     */
    public function resetPassword(User $user): RedirectResponse
    {
        $temporaryPassword = Str::password(12);

        $user->forceFill([
            'password' => Hash::make($temporaryPassword),
            'must_change_password' => true,
        ])->save();

        $user->tokens()->delete();

        $emailSent = $this->sendAccountMail($user, $temporaryPassword, isReset: true);

        return redirect()->route('admin.users.index')->with(
            'success',
            $emailSent
                ? "Password reset for {$user->name}. Temporary password: {$temporaryPassword} (also emailed)."
                : "Password reset for {$user->name}. Temporary password: {$temporaryPassword} (email delivery failed — share this password with the user directly)."
        );
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'login_id' => ['nullable', 'string', 'max:255', 'unique:users,login_id,' . $user->id],
            'role' => ['required', 'in:admin,user'],
            'streak' => ['required', 'integer', 'min:0'],
            'score' => ['required', 'integer', 'min:0'],
            'password' => ['nullable', 'string', 'min:6'],
            'is_active' => ['nullable', 'boolean'],
            'custom_daily_target' => ['nullable', 'integer', 'min:1', 'max:1000'],
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'login_id' => $validated['login_id'] ?: null,
            'role' => $validated['role'],
            'streak' => $validated['streak'],
            'score' => $validated['score'],
            'is_active' => $request->has('is_active'),
            // Absent (checkbox "Use Global Target" checked, so the disabled
            // field wasn't submitted) means null — removing the override
            // falls back to the global target (roadmap §6.3).
            'custom_daily_target' => $validated['custom_daily_target'] ?? null,
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
            $updateData['must_change_password'] = false;
        }

        $user->update($updateData);

        return redirect()->route('admin.users.index')->with('success', 'User profile updated.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if (Auth::id() === $user->id) {
            return back()->with('error', 'You cannot delete your own admin account.');
        }

        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }
}
