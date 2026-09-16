@extends('layouts.admin')

@section('title', 'Questions Bank')
@section('breadcrumb', 'Manage MCQ questions')

@section('content')

<div class="flex items-center justify-between mb-5">
    <div>
        <h2 class="text-lg font-bold text-gray-900">Questions Bank</h2>
        <p class="text-xs text-gray-400 mt-0.5">{{ $questions->total() }} total questions</p>
    </div>
    <a href="{{ route('admin.questions.create', ['quiz_id' => request('quiz_id')]) }}"
       class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add Question
    </a>
</div>

{{-- Filter --}}
<div class="bg-white border border-gray-200 rounded-lg p-3 mb-4">
    <form method="GET" action="{{ route('admin.questions.index') }}" class="flex flex-wrap items-center gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search question text..."
               class="px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 w-64">
        <select name="quiz_id" class="px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All Quizzes</option>
            @foreach($quizzes as $q)
                <option value="{{ $q->id }}" {{ request('quiz_id') == $q->id ? 'selected' : '' }}>{{ $q->title }}</option>
            @endforeach
        </select>
        <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white text-sm font-medium px-4 py-2 rounded transition-colors">
            Filter
        </button>
        @if(request('search') || request('quiz_id'))
            <a href="{{ route('admin.questions.index') }}" class="text-sm text-gray-500 hover:underline">Clear</a>
        @endif
    </form>
</div>

{{-- Table --}}
<div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Quiz</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Question</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Options & Answer</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Pts</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">#</th>
                    <th class="text-right text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($questions as $question)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <span class="text-xs font-medium text-blue-700 bg-blue-50 border border-blue-100 px-2 py-0.5 rounded">
                                {{ $question->quiz->title ?? 'Unassigned' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 max-w-xs">
                            <p class="font-medium text-gray-900 line-clamp-2">{{ $question->question_text }}</p>
                            @if($question->explanation)
                                <p class="text-xs text-gray-400 mt-0.5 italic line-clamp-1">{{ $question->explanation }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="space-y-1">
                                @foreach($question->options as $opt)
                                    <div class="flex items-center gap-1.5 text-xs {{ $opt->is_correct ? 'text-green-700 font-semibold' : 'text-gray-500' }}">
                                        @if($opt->is_correct)
                                            <svg class="w-3.5 h-3.5 text-green-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                        @else
                                            <span class="w-1.5 h-1.5 rounded-full bg-gray-300 mx-1 flex-shrink-0"></span>
                                        @endif
                                        <span>{{ $opt->option_text }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-4 py-3 text-gray-700 font-medium text-xs">+{{ $question->points }}</td>
                        <td class="px-4 py-3 text-gray-400 text-xs font-mono">{{ $question->sort_order }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="inline-flex items-center gap-2">
                                <a href="{{ route('admin.questions.edit', $question) }}"
                                   class="text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 px-3 py-1 rounded transition-colors">
                                    Edit
                                </a>
                                <form action="{{ route('admin.questions.destroy', $question) }}" method="POST"
                                      onsubmit="return confirm('Delete this question?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-xs font-medium text-red-600 hover:bg-red-50 px-3 py-1 rounded transition-colors">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-gray-400">
                            No questions found. <a href="{{ route('admin.questions.create') }}" class="text-blue-600 hover:underline">Add one</a>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($questions->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $questions->links() }}
        </div>
    @endif
</div>

@endsection
