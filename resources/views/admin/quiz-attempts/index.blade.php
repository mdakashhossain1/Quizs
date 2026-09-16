@extends('layouts.admin')

@section('title', 'Quiz Attempts')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Quiz Play Logs</h1>
            <p class="text-xs text-slate-400 mt-1">Player performance records, accuracy rates, and time logs</p>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-4">
        <form method="GET" action="{{ route('admin.quiz-attempts.index') }}" class="flex items-center gap-3">
            <select name="quiz_id" class="px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                <option value="">Filter by Quiz</option>
                @foreach($quizzes as $q)
                    <option value="{{ $q->id }}" {{ request('quiz_id') == $q->id ? 'selected' : '' }}>{{ $q->title }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-xl">
                Filter
            </button>
            @if(request('quiz_id'))
                <a href="{{ route('admin.quiz-attempts.index') }}" class="text-xs text-slate-500 hover:text-slate-800 underline">Clear</a>
            @endif
        </form>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-[11px] font-bold uppercase text-slate-400 bg-slate-50/75">
                        <th class="py-3 px-6">User</th>
                        <th class="py-3 px-6">Quiz</th>
                        <th class="py-3 px-6">Score</th>
                        <th class="py-3 px-6">Correct Answers</th>
                        <th class="py-3 px-6">Accuracy</th>
                        <th class="py-3 px-6">Played At</th>
                        <th class="py-3 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($attempts as $attempt)
                        @php
                            $pct = $attempt->total_questions > 0 ? round(($attempt->correct_answers / $attempt->total_questions) * 100) : 0;
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-6">
                                <p class="font-bold text-slate-900">{{ $attempt->user->name ?? 'Unknown User' }}</p>
                                <p class="text-xs text-slate-400">{{ $attempt->user->email ?? '' }}</p>
                            </td>
                            <td class="py-3.5 px-6 font-medium text-slate-800">
                                {{ $attempt->quiz->title ?? 'Deleted Quiz' }}
                            </td>
                            <td class="py-3.5 px-6">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-purple-50 text-purple-700">
                                    +{{ $attempt->score }} pts
                                </span>
                            </td>
                            <td class="py-3.5 px-6 text-xs text-slate-600 font-semibold">
                                {{ $attempt->correct_answers }} / {{ $attempt->total_questions }}
                            </td>
                            <td class="py-3.5 px-6">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $pct >= 60 ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                    {{ $pct }}%
                                </span>
                            </td>
                            <td class="py-3.5 px-6 text-xs text-slate-400">
                                {{ $attempt->completed_at ? $attempt->completed_at->format('M d, Y h:i A') : '-' }}
                            </td>
                            <td class="py-3.5 px-6 text-right">
                                <form action="{{ route('admin.quiz-attempts.destroy', $attempt) }}" method="POST" onsubmit="return confirm('Delete this attempt log?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1 text-rose-600 hover:bg-rose-50 rounded-lg text-xs font-semibold transition-colors">
                                        Remove
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">No attempts logged yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($attempts->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $attempts->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
