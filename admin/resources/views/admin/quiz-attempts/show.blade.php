@extends('layouts.admin')

@section('title', 'Attempt Detail')
@section('breadcrumb', 'Quiz Attempts / Detail')

@section('content')

@php
    $answersByQuestion = $quizAttempt->answers->keyBy('question_id');
@endphp

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
    <div>
        <h2 class="text-lg font-bold text-gray-900">{{ $quizAttempt->quiz->title ?? 'Deleted Quiz' }}</h2>
        <p class="text-xs text-gray-400 mt-0.5">{{ $quizAttempt->user->name ?? 'Unknown user' }} &middot; {{ $quizAttempt->user->email ?? '' }}</p>
    </div>
    <div class="flex items-center gap-3">
        <div class="flex items-center gap-2">
            <span class="text-xs text-gray-400">Summary export:</span>
            <a href="{{ route('admin.quiz-attempts.summary.pdf', $quizAttempt) }}"
               class="text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 px-3 py-1.5 rounded transition-colors">PDF</a>
            <a href="{{ route('admin.quiz-attempts.summary.csv', $quizAttempt) }}"
               class="text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 px-3 py-1.5 rounded transition-colors">CSV</a>
        </div>
        <a href="{{ route('admin.quiz-attempts.index') }}" class="text-sm text-gray-500 hover:text-gray-800">&larr; Back to attempts</a>
    </div>
</div>

{{-- Summary --}}
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
    <div class="bg-white border border-gray-200 rounded-lg p-4">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Status</p>
        @if($quizAttempt->status === 'completed')
            <span class="text-xs font-medium text-green-700 bg-green-50 border border-green-200 px-2 py-0.5 rounded">Completed</span>
        @elseif($quizAttempt->isStale())
            <span class="text-xs font-medium text-gray-600 bg-gray-100 border border-gray-200 px-2 py-0.5 rounded">Abandoned</span>
        @else
            <span class="text-xs font-medium text-amber-700 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded">In Progress</span>
        @endif
    </div>
    <div class="bg-white border border-gray-200 rounded-lg p-4">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Score</p>
        <p class="text-sm font-semibold text-gray-900">{{ $quizAttempt->score }} pts</p>
    </div>
    <div class="bg-white border border-gray-200 rounded-lg p-4">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Correct / Wrong</p>
        <p class="text-sm font-semibold text-gray-900">{{ $quizAttempt->correct_answers }} / {{ $quizAttempt->wrong_answers }}</p>
    </div>
    <div class="bg-white border border-gray-200 rounded-lg p-4">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Unanswered</p>
        <p class="text-sm font-semibold text-gray-900">{{ $quizAttempt->unanswered_questions }}</p>
    </div>
    <div class="bg-white border border-gray-200 rounded-lg p-4">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Accuracy</p>
        <p class="text-sm font-semibold text-gray-900">{{ $quizAttempt->accuracy !== null ? number_format($quizAttempt->accuracy, 1) . '%' : '—' }}</p>
    </div>
    <div class="bg-white border border-gray-200 rounded-lg p-4">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Started / Completed</p>
        <p class="text-xs text-gray-700">{{ $quizAttempt->started_at?->format('M j, g:i A') ?? '—' }}</p>
        <p class="text-xs text-gray-400">{{ $quizAttempt->completed_at?->format('M j, g:i A') ?? 'not yet' }}</p>
    </div>
</div>

{{-- Question-by-question breakdown --}}
<div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
    <div class="px-4 py-3 border-b border-gray-100">
        <h3 class="font-semibold text-gray-900 text-sm">Question Breakdown</h3>
        <p class="text-xs text-gray-400">Selected answer vs. correct answer for every question</p>
    </div>
    <div class="divide-y divide-gray-100">
        @forelse($quizAttempt->quiz->questions ?? [] as $i => $question)
            @php
                $answer = $answersByQuestion->get($question->id);
                $selectedOption = $answer ? $question->options->firstWhere('id', $answer->selected_option_id) : null;
                $correctOption = $question->options->firstWhere('is_correct', true);
            @endphp
            <div class="px-4 py-3">
                <p class="text-sm font-medium text-gray-900 mb-2">{{ $i + 1 }}. {{ $question->question_text }}</p>
                <div class="flex flex-wrap items-center gap-2 text-xs">
                    @if(!$answer)
                        <span class="px-2 py-0.5 rounded font-medium bg-gray-100 text-gray-500">No answer</span>
                    @else
                        <span class="px-2 py-0.5 rounded font-medium {{ $answer->is_correct ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200' }}">
                            Selected: {{ $selectedOption->option_text ?? '—' }}
                        </span>
                    @endif
                    <span class="px-2 py-0.5 rounded font-medium bg-blue-50 text-blue-700 border border-blue-200">
                        Correct: {{ $correctOption->option_text ?? '—' }}
                    </span>
                    @if($answer)
                        <span class="px-2 py-0.5 rounded font-medium {{ $answer->is_correct ? 'text-green-700' : 'text-red-700' }}">
                            {{ $answer->is_correct ? 'Correct' : 'Wrong' }}
                        </span>
                    @endif
                </div>
            </div>
        @empty
            <div class="px-4 py-10 text-center text-gray-400">This quiz has no questions.</div>
        @endforelse
    </div>
</div>

@endsection
