@extends('layouts.admin')

@section('title', 'Questions Bank')

@section('content')
<div class="space-y-6">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Question Bank</h1>
            <p class="text-xs text-slate-400 mt-1">Manage multiple-choice questions, answer options, and explanations</p>
        </div>
        <a href="{{ route('admin.questions.create', ['quiz_id' => request('quiz_id')]) }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-semibold text-xs rounded-xl shadow-lg shadow-purple-600/30 transition-all self-start">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add New Question
        </a>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex flex-wrap items-center gap-4">
        <form method="GET" action="{{ route('admin.questions.index') }}" class="flex flex-wrap items-center gap-3 w-full">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search question text..."
                   class="px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs w-64 focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">

            <select name="quiz_id" class="px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                <option value="">All Quizzes</option>
                @foreach($quizzes as $q)
                    <option value="{{ $q->id }}" {{ request('quiz_id') == $q->id ? 'selected' : '' }}>{{ $q->title }}</option>
                @endforeach
            </select>

            <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-xl">
                Filter
            </button>
            @if(request('search') || request('quiz_id'))
                <a href="{{ route('admin.questions.index') }}" class="text-xs text-slate-500 hover:text-slate-800 underline">Reset</a>
            @endif
        </form>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-[11px] font-bold uppercase text-slate-400 bg-slate-50/75">
                        <th class="py-3 px-6">Quiz</th>
                        <th class="py-3 px-6">Question Text</th>
                        <th class="py-3 px-6">Options & Correct Answer</th>
                        <th class="py-3 px-6">Points</th>
                        <th class="py-3 px-6">Order</th>
                        <th class="py-3 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($questions as $question)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-4 px-6">
                                <span class="font-bold text-xs text-purple-700 bg-purple-50 px-2.5 py-1 rounded-lg">
                                    {{ $question->quiz->title ?? 'Unassigned' }}
                                </span>
                            </td>
                            <td class="py-4 px-6 max-w-sm">
                                <p class="font-semibold text-slate-900 line-clamp-2">{{ $question->question_text }}</p>
                                @if($question->explanation)
                                    <p class="text-xs text-slate-400 mt-1 italic line-clamp-1">Tip: {{ $question->explanation }}</p>
                                @endif
                            </td>
                            <td class="py-4 px-6">
                                <div class="space-y-1 text-xs">
                                    @foreach($question->options as $opt)
                                        <div class="flex items-center gap-1.5 {{ $opt->is_correct ? 'text-emerald-700 font-bold' : 'text-slate-500' }}">
                                            @if($opt->is_correct)
                                                <svg class="w-3.5 h-3.5 text-emerald-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                            @else
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-300 ml-1 mr-1"></span>
                                            @endif
                                            <span>{{ $opt->option_text }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td class="py-4 px-6 font-semibold text-xs text-slate-700">+{{ $question->points }} pts</td>
                            <td class="py-4 px-6 text-xs text-slate-400 font-mono">{{ $question->sort_order }}</td>
                            <td class="py-4 px-6 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('admin.questions.edit', $question) }}" class="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition-colors">
                                        Edit
                                    </a>
                                    <form action="{{ route('admin.questions.destroy', $question) }}" method="POST" onsubmit="return confirm('Delete this question?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2.5 py-1 text-rose-600 hover:bg-rose-50 rounded-lg text-xs font-semibold transition-colors">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">No questions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($questions->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $questions->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
