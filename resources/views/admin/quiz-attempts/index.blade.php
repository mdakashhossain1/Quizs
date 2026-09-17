@extends('layouts.admin')

@section('title', 'Quiz Attempts')
@section('breadcrumb', 'Player performance logs')

@section('content')

<div class="flex items-center justify-between mb-5">
    <div>
        <h2 class="text-lg font-bold text-gray-900">Quiz Attempts</h2>
        <p class="text-xs text-gray-400 mt-0.5">{{ $attempts->total() }} total attempts</p>
    </div>
</div>

{{-- Filter --}}
<div class="bg-white border border-gray-200 rounded-lg p-3 mb-4">
    <form method="GET" action="{{ route('admin.quiz-attempts.index') }}" class="flex flex-wrap items-center gap-3">
        <input type="text" name="user" value="{{ request('user') }}" placeholder="Search by user name or email..."
               class="px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 w-56">
        <select name="category_id" class="px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All Categories</option>
            @foreach($categories as $c)
                <option value="{{ $c->id }}" {{ request('category_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
            @endforeach
        </select>
        <select name="quiz_id" class="px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All Quizzes</option>
            @foreach($quizzes as $q)
                <option value="{{ $q->id }}" {{ request('quiz_id') == $q->id ? 'selected' : '' }}>{{ $q->title }}</option>
            @endforeach
        </select>
        <select name="status" class="px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All Statuses</option>
            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
            <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress / Abandoned</option>
        </select>
        <input type="date" name="date_from" value="{{ request('date_from') }}"
               class="px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
        <span class="text-sm text-gray-400">to</span>
        <input type="date" name="date_to" value="{{ request('date_to') }}"
               class="px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
        <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white text-sm font-medium px-4 py-2 rounded transition-colors">
            Filter
        </button>
        @if(request('quiz_id') || request('status') || request('user') || request('category_id') || request('date_from') || request('date_to'))
            <a href="{{ route('admin.quiz-attempts.index') }}" class="text-sm text-gray-500 hover:underline">Clear</a>
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
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Quiz</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Score</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Correct</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Accuracy</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Status</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Played At</th>
                    <th class="text-right text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($attempts as $attempt)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <p class="font-semibold text-gray-900">{{ $attempt->user->name ?? 'Unknown' }}</p>
                            <p class="text-xs text-gray-400">{{ $attempt->user->email ?? '' }}</p>
                        </td>
                        <td class="px-4 py-3 text-gray-700">{{ $attempt->quiz->title ?? 'Deleted Quiz' }}</td>
                        <td class="px-4 py-3">
                            <span class="text-xs font-semibold text-blue-700 bg-blue-50 border border-blue-100 px-2 py-0.5 rounded">+{{ $attempt->score }} pts</span>
                        </td>
                        <td class="px-4 py-3 text-gray-600 text-xs font-medium">{{ $attempt->correct_answers }} / {{ $attempt->total_questions }}</td>
                        <td class="px-4 py-3">
                            @if($attempt->accuracy !== null)
                                <span class="text-xs font-semibold px-2 py-0.5 rounded {{ $attempt->accuracy >= 60 ? 'text-green-700 bg-green-50 border border-green-200' : 'text-red-700 bg-red-50 border border-red-200' }}">
                                    {{ number_format($attempt->accuracy, 0) }}%
                                </span>
                            @else
                                <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($attempt->status === 'completed')
                                <span class="text-xs font-medium text-green-700 bg-green-50 border border-green-200 px-2 py-0.5 rounded">Completed</span>
                            @else
                                <span class="text-xs font-medium text-amber-700 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded">In Progress</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-400 text-xs">
                            {{ $attempt->completed_at ? $attempt->completed_at->format('M d, Y H:i') : ($attempt->started_at?->format('M d, Y H:i') ?? '-') }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="inline-flex items-center gap-2">
                                <a href="{{ route('admin.quiz-attempts.show', $attempt) }}"
                                   class="text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 px-3 py-1 rounded transition-colors">
                                    View
                                </a>
                                <form action="{{ route('admin.quiz-attempts.destroy', $attempt) }}" method="POST"
                                      onsubmit="return confirm('Delete this attempt log?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-xs font-medium text-red-600 hover:bg-red-50 px-3 py-1 rounded transition-colors">
                                        Remove
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-10 text-center text-gray-400">No attempts logged yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($attempts->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $attempts->links() }}
        </div>
    @endif
</div>

@endsection
