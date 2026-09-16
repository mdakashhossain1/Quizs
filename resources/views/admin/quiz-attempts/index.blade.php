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
    <form method="GET" action="{{ route('admin.quiz-attempts.index') }}" class="flex items-center gap-3">
        <select name="quiz_id" class="px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All Quizzes</option>
            @foreach($quizzes as $q)
                <option value="{{ $q->id }}" {{ request('quiz_id') == $q->id ? 'selected' : '' }}>{{ $q->title }}</option>
            @endforeach
        </select>
        <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white text-sm font-medium px-4 py-2 rounded transition-colors">
            Filter
        </button>
        @if(request('quiz_id'))
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
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Played At</th>
                    <th class="text-right text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($attempts as $attempt)
                    @php
                        $pct = $attempt->total_questions > 0 ? round(($attempt->correct_answers / $attempt->total_questions) * 100) : 0;
                    @endphp
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
                            <span class="text-xs font-semibold px-2 py-0.5 rounded {{ $pct >= 60 ? 'text-green-700 bg-green-50 border border-green-200' : 'text-red-700 bg-red-50 border border-red-200' }}">
                                {{ $pct }}%
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-400 text-xs">
                            {{ $attempt->completed_at ? $attempt->completed_at->format('M d, Y H:i') : '-' }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <form action="{{ route('admin.quiz-attempts.destroy', $attempt) }}" method="POST"
                                  onsubmit="return confirm('Delete this attempt log?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs font-medium text-red-600 hover:bg-red-50 px-3 py-1 rounded transition-colors">
                                    Remove
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-gray-400">No attempts logged yet.</td>
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
