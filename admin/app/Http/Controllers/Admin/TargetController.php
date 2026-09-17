<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserDailyProgress;
use App\Services\TargetService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TargetController extends Controller
{
    /**
     * Target monitoring (roadmap §6.7): effective target, completed,
     * remaining, progress and status per user/date. Only dates a user has
     * actually touched (started or completed a quiz, or been queried by the
     * app) have a row — this shows real recorded progress rather than
     * synthesizing one for every user on every date.
     */
    public function index(Request $request): View
    {
        $query = UserDailyProgress::with('user')->latest('date');

        if ($request->filled('user')) {
            $search = $request->user;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // A single exact date takes precedence over a range if both are
        // somehow submitted, since the UI only ever sends one or the other.
        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        } else {
            if ($request->filled('date_from')) {
                $query->whereDate('date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('date', '<=', $request->date_to);
            }
        }

        if ($request->filled('status')) {
            $this->applyStatusFilter($query, $request->status);
        }

        $progress = $query->paginate(20)->withQueryString();

        return view('admin.targets.index', compact('progress'));
    }

    /**
     * Mirrors UserDailyProgress::getDisplayStatusAttribute as a query
     * filter — 'not_completed' isn't a value ever stored in target_status,
     * it's derived at read time for a past day that never hit 'completed'.
     */
    private function applyStatusFilter(Builder $query, string $status): void
    {
        $today = TargetService::businessToday()->toDateString();

        match ($status) {
            'completed' => $query->where('target_status', 'completed'),
            'not_completed' => $query->where('target_status', '!=', 'completed')->whereDate('date', '<', $today),
            'in_progress' => $query->where('target_status', 'in_progress')->whereDate('date', '=', $today),
            'not_started' => $query->where('target_status', 'not_started')->whereDate('date', '=', $today),
            default => null,
        };
    }
}
