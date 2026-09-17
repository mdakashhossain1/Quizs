@extends('layouts.admin')

@section('title', 'User Activity')
@section('breadcrumb', 'Users / Activity')

@section('content')

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
    <div>
        <h2 class="text-lg font-bold text-gray-900">{{ $user->name }}</h2>
        <p class="text-xs text-gray-400 mt-0.5">{{ $user->email }} @if($user->login_id) &middot; Login ID: {{ $user->login_id }} @endif</p>
    </div>
    <a href="{{ route('admin.users.index') }}" class="text-sm text-gray-500 hover:text-gray-800">&larr; Back to users</a>
</div>

{{-- Current status --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <div class="bg-white border border-gray-200 rounded-lg p-4">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Status</p>
        @if($user->is_online)
            <span class="inline-flex items-center gap-1.5 text-sm font-semibold text-green-700">
                <span class="w-2 h-2 rounded-full bg-green-500"></span> Online
            </span>
        @else
            <span class="inline-flex items-center gap-1.5 text-sm font-semibold text-gray-500">
                <span class="w-2 h-2 rounded-full bg-gray-300"></span> Offline
            </span>
        @endif
    </div>
    <div class="bg-white border border-gray-200 rounded-lg p-4">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Last Active</p>
        <p class="text-sm font-semibold text-gray-900">{{ $user->last_active_at ? $user->last_active_at->diffForHumans() : 'Never' }}</p>
    </div>
    <div class="bg-white border border-gray-200 rounded-lg p-4">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Total Quiz Attempts</p>
        <p class="text-sm font-semibold text-gray-900">{{ $user->quizAttempts()->count() }}</p>
    </div>
    <div class="bg-white border border-gray-200 rounded-lg p-4">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Account Created</p>
        <p class="text-sm font-semibold text-gray-900">{{ $user->created_at->format('M j, Y') }}</p>
    </div>
</div>

{{-- Profile stats — identical computation to the mobile app's Profile screen (roadmap §9.5) --}}
<div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-4 mb-6">
    <div class="bg-white border border-gray-200 rounded-lg p-4">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Rank</p>
        <p class="text-lg font-bold text-gray-900">#{{ $stats['rank'] }}</p>
    </div>
    <div class="bg-white border border-gray-200 rounded-lg p-4">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Level</p>
        <p class="text-lg font-bold text-purple-700">{{ $stats['level']['level'] }}</p>
        <p class="text-xs text-gray-400">{{ $stats['level']['xp'] }} XP</p>
    </div>
    <div class="bg-white border border-gray-200 rounded-lg p-4">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Today's Target</p>
        <p class="text-lg font-bold text-gray-900">{{ $stats['today_target']['completed_quizzes'] }}/{{ $stats['today_target']['effective_target'] }}</p>
        <p class="text-xs text-gray-400">{{ number_format($stats['today_target']['progress_percentage'], 0) }}%</p>
    </div>
    <div class="bg-white border border-gray-200 rounded-lg p-4">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Accuracy</p>
        <p class="text-lg font-bold text-gray-900">{{ number_format($stats['accuracy'], 1) }}%</p>
    </div>
    <div class="bg-white border border-gray-200 rounded-lg p-4">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Quiz Played</p>
        <p class="text-lg font-bold text-gray-900">{{ $stats['quiz_played'] }}</p>
    </div>
    <div class="bg-white border border-gray-200 rounded-lg p-4">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Right</p>
        <p class="text-lg font-bold text-green-700">{{ $stats['right'] }}</p>
    </div>
    <div class="bg-white border border-gray-200 rounded-lg p-4">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Wrong</p>
        <p class="text-lg font-bold text-red-700">{{ $stats['wrong'] }}</p>
    </div>
    <div class="bg-white border border-gray-200 rounded-lg p-4">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">This Month</p>
        <p class="text-lg font-bold text-gray-900">{{ $stats['this_month'] }}</p>
    </div>
</div>

{{-- Session history --}}
<div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
    <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between flex-wrap gap-3">
        <div>
            <h3 class="font-semibold text-gray-900 text-sm">Session History</h3>
            <p class="text-xs text-gray-400">Individual app-usage sessions, derived from heartbeat activity</p>
        </div>
        <form method="GET" action="{{ route('admin.users.activity', $user) }}" class="flex flex-wrap items-center gap-2">
            <input type="date" name="date_from" value="{{ request('date_from') }}"
                   class="px-2 py-1.5 text-xs border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            <span class="text-xs text-gray-400">to</span>
            <input type="date" name="date_to" value="{{ request('date_to') }}"
                   class="px-2 py-1.5 text-xs border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white text-xs font-medium px-3 py-1.5 rounded transition-colors">
                Filter
            </button>
            @if(request('date_from') || request('date_to'))
                <a href="{{ route('admin.users.activity', $user) }}" class="text-xs text-gray-500 hover:underline">Clear</a>
            @endif
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Device</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Started</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Last Active</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Ended</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($sessions as $session)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-600 font-mono text-xs">{{ mb_strimwidth($session->device_id, 0, 24, '…') }}</td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $session->authenticated_at->format('M j, g:i A') }}</td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $session->last_active_at->format('M j, g:i A') }}</td>
                        <td class="px-4 py-3 text-gray-500 text-xs">
                            @if($session->explicit_logout_at)
                                {{ $session->explicit_logout_at->format('M j, g:i A') }} <span class="text-gray-400">(logout)</span>
                            @elseif($session->session_ended_at)
                                {{ $session->session_ended_at->format('M j, g:i A') }} <span class="text-gray-400">(inactivity)</span>
                            @else
                                &mdash;
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($session->status === 'active')
                                <span class="text-xs font-medium text-green-700 bg-green-50 border border-green-200 px-2 py-0.5 rounded">Active</span>
                            @else
                                <span class="text-xs font-medium text-gray-600 bg-gray-100 px-2 py-0.5 rounded">Ended</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-gray-400">No sessions recorded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($sessions->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $sessions->links() }}
        </div>
    @endif
</div>

@endsection
