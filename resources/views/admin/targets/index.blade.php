@extends('layouts.admin')

@use('Illuminate\Support\Carbon')

@section('title', 'Daily Targets')
@section('breadcrumb', 'Per-user daily quiz target progress')

@section('content')

<div class="flex items-center justify-between mb-5">
    <div>
        <h2 class="text-lg font-bold text-gray-900">Daily Targets</h2>
        <p class="text-xs text-gray-400 mt-0.5">{{ $progress->total() }} recorded days</p>
    </div>
</div>

{{-- Filter --}}
<div class="bg-white border border-gray-200 rounded-lg p-3 mb-4">
    <form method="GET" action="{{ route('admin.targets.index') }}" class="flex flex-wrap items-center gap-3">
        <input type="text" name="user" value="{{ request('user') }}" placeholder="Search by user name or email..."
               class="px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 w-56">
        <select name="status" class="px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All Statuses</option>
            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
            <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress (today)</option>
            <option value="not_started" {{ request('status') === 'not_started' ? 'selected' : '' }}>Not Started (today)</option>
            <option value="not_completed" {{ request('status') === 'not_completed' ? 'selected' : '' }}>Not Completed (past days)</option>
        </select>
        <input type="date" name="date" value="{{ request('date') }}" title="Exact date (takes priority over the range below)"
               class="px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
        <span class="text-sm text-gray-400">or range</span>
        <input type="date" name="date_from" value="{{ request('date_from') }}"
               class="px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
        <span class="text-sm text-gray-400">to</span>
        <input type="date" name="date_to" value="{{ request('date_to') }}"
               class="px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
        <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white text-sm font-medium px-4 py-2 rounded transition-colors">
            Filter
        </button>
        @if(request('user') || request('date') || request('date_from') || request('date_to') || request('status'))
            <a href="{{ route('admin.targets.index') }}" class="text-sm text-gray-500 hover:underline">Clear</a>
        @endif
    </form>
</div>

{{-- Table --}}
<div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">User</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Date</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Target</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Completed</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Remaining</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Progress</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($progress as $p)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <p class="font-semibold text-gray-900">{{ $p->user->name ?? 'Unknown' }}</p>
                            <p class="text-xs text-gray-400">{{ $p->user->email ?? '' }}</p>
                        </td>
                        <td class="px-4 py-3 text-gray-600 text-xs">{{ Carbon::parse($p->date)->format('M j, Y') }}</td>
                        <td class="px-4 py-3 text-gray-700 text-xs font-medium">{{ $p->effective_target }}</td>
                        <td class="px-4 py-3 text-gray-700 text-xs font-medium">{{ $p->completed_quizzes }}</td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $p->remaining }}</td>
                        <td class="px-4 py-3 text-xs text-gray-600">{{ number_format($p->progress_percentage, 0) }}%</td>
                        <td class="px-4 py-3">
                            @php
                                $statusStyles = [
                                    'completed' => 'text-green-700 bg-green-50 border border-green-200',
                                    'in_progress' => 'text-blue-700 bg-blue-50 border border-blue-200',
                                    'not_completed' => 'text-red-700 bg-red-50 border border-red-200',
                                    'not_started' => 'text-gray-500 bg-gray-100',
                                ];
                                $statusLabels = [
                                    'completed' => 'Completed',
                                    'in_progress' => 'In Progress',
                                    'not_completed' => 'Not Completed',
                                    'not_started' => 'Not Started',
                                ];
                            @endphp
                            <span class="text-xs font-medium px-2 py-0.5 rounded {{ $statusStyles[$p->display_status] }}">
                                {{ $statusLabels[$p->display_status] }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-gray-400">No target progress recorded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($progress->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $progress->links() }}
        </div>
    @endif
</div>

@endsection
