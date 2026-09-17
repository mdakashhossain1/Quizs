<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use App\Services\PushNotificationService;
use App\Services\TargetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    /**
     * Dedicated attendance page (roadmap §7.1): pick a date, mark every
     * user Present/Absent/Leave for it in one form.
     */
    public function index(Request $request): View
    {
        $date = $request->input('date') ?: TargetService::businessToday()->toDateString();

        $query = User::where('role', 'user');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'not_marked') {
                $query->whereDoesntHave('attendances', fn ($q) => $q->where('date', $date));
            } else {
                $status = $request->status;
                $query->whereHas('attendances', fn ($q) => $q->where('date', $date)->where('status', $status));
            }
        }

        $users = $query->orderBy('name')->paginate(25)->withQueryString();

        $existing = Attendance::where('date', $date)
            ->whereIn('user_id', $users->pluck('id'))
            ->get()
            ->keyBy('user_id');

        return view('admin.attendance.index', compact('date', 'users', 'existing'));
    }

    /**
     * Bulk-saves whichever rows the admin actually set a status for on this
     * page (a blank dropdown is left untouched, not cleared), and sends an
     * attendance-updated push only to users whose status is new or changed
     * (roadmap §7.4) — not to every row on the page.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'statuses' => ['array'],
            'statuses.*' => ['nullable', 'in:present,absent,leave'],
            'notes' => ['array'],
            'notes.*' => ['nullable', 'string', 'max:255'],
        ]);

        $date = $validated['date'];
        $changedUserIds = [];

        foreach ($validated['statuses'] ?? [] as $userId => $status) {
            if (! $status || ! User::where('id', $userId)->exists()) {
                continue;
            }

            $previousStatus = Attendance::where('user_id', $userId)->where('date', $date)->value('status');

            Attendance::updateOrCreate(
                ['user_id' => $userId, 'date' => $date],
                [
                    'status' => $status,
                    'marked_by' => Auth::id(),
                    'note' => $validated['notes'][$userId] ?? null,
                ],
            );

            if ($previousStatus !== $status) {
                $changedUserIds[(int) $userId] = $status;
            }
        }

        if (! empty($changedUserIds)) {
            // Deferred until after the response is sent: each push is a
            // blocking FCM HTTP call, and firing one per changed user
            // synchronously here risked a web-server timeout on a large
            // roster (push_notification_prd.md §13).
            dispatch(function () use ($changedUserIds, $date) {
                foreach ($changedUserIds as $userId => $status) {
                    // Routed through PushNotificationService (not FcmService
                    // directly) so this also lands in the user's in-app
                    // notification inbox.
                    PushNotificationService::send([
                        'title' => 'Attendance Updated',
                        'body' => "Your attendance for {$date} has been marked as " . ucfirst($status) . '.',
                        'thumbnail_url' => null,
                        'target_type' => 'single',
                        'target_user_ids' => [$userId],
                        'destination_type' => 'attendance',
                        'destination_id' => null,
                    ]);
                }
            })->afterResponse();
        }

        return redirect()->route('admin.attendance.index', ['date' => $date])
            ->with('success', count($changedUserIds) . ' user(s) notified of a status change.');
    }
}
