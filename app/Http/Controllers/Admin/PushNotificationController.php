<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PushNotification;
use App\Models\Quiz;
use App\Models\User;
use App\Services\PushNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PushNotificationController extends Controller
{
    public function index(): View
    {
        $notifications = PushNotification::withCount('recipients')
            ->with('creator')
            ->latest()
            ->paginate(20);

        return view('admin.push-notifications.index', compact('notifications'));
    }

    public function create(): View
    {
        $users = User::eligible()->orderBy('name')->get(['id', 'name', 'email']);
        $quizzes = Quiz::orderBy('title')->get(['id', 'title']);

        return view('admin.push-notifications.create', compact('users', 'quizzes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:1000'],
            'thumbnail' => ['nullable', 'image', 'max:2048'],
            'audience' => ['required', 'in:all,specific'],
            'user_ids' => ['required_if:audience,specific', 'array'],
            'user_ids.*' => ['integer', 'exists:users,id'],
            'destination_type' => ['required', 'in:none,attendance,quiz_details,target_progress,achievement,profile'],
            'destination_id' => ['required_if:destination_type,quiz_details', 'nullable', 'integer', 'exists:quizzes,id'],
        ]);

        $thumbnailUrl = null;
        if ($request->hasFile('thumbnail')) {
            // Never trust the original filename — generate a fresh one so an
            // uploaded file can't collide with or overwrite another public
            // asset (roadmap PRD §6/§15).
            $filename = Str::uuid().'.'.$request->file('thumbnail')->getClientOriginalExtension();
            Storage::disk('notification_thumbnails')->putFileAs('', $request->file('thumbnail'), $filename);
            $thumbnailUrl = Storage::disk('notification_thumbnails')->url($filename);
        }

        $userIds = $validated['audience'] === 'specific' ? $validated['user_ids'] : [];

        PushNotificationService::send([
            'title' => $validated['title'],
            'body' => $validated['body'],
            'thumbnail_url' => $thumbnailUrl,
            'target_type' => $validated['audience'] === 'all' ? 'all' : (count($userIds) === 1 ? 'single' : 'selected'),
            'target_user_ids' => $userIds,
            'destination_type' => $validated['destination_type'],
            'destination_id' => $validated['destination_id'] ?? null,
        ], Auth::user());

        return redirect()->route('admin.push-notifications.index')->with('success', 'Notification sent.');
    }

    public function show(PushNotification $pushNotification): View
    {
        $pushNotification->load(['creator', 'recipients.user']);

        return view('admin.push-notifications.show', compact('pushNotification'));
    }
}
