@extends('layouts.admin')

@section('title', 'Questions Bank')
@section('breadcrumb', 'Manage MCQ questions')

@section('content')

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
    <div>
        <h2 class="text-lg font-bold text-gray-900">Questions Bank</h2>
        <p class="text-xs text-gray-400 mt-0.5" id="ajax-count-label">{{ $questions->total() }} total questions</p>
    </div>
    <a href="{{ route('admin.questions.create', ['quiz_id' => request('quiz_id')]) }}"
       class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add Question
    </a>
</div>

{{-- Filter --}}
<div class="bg-white border border-gray-200 rounded-lg p-3 mb-4">
    <form method="GET" action="{{ route('admin.questions.index') }}" class="flex flex-wrap items-center gap-3" data-ajax-filter>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search question text..."
               class="px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 w-full sm:w-64">
        <select name="quiz_id" class="px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All Quizzes</option>
            @foreach($quizzes as $q)
                <option value="{{ $q->id }}" {{ request('quiz_id') == $q->id ? 'selected' : '' }}>{{ $q->title }}</option>
            @endforeach
        </select>
        <select name="translation_status" class="px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Any Translation Status</option>
            <option value="hi_missing" {{ request('translation_status') === 'hi_missing' ? 'selected' : '' }}>Hindi Missing</option>
            <option value="en_missing" {{ request('translation_status') === 'en_missing' ? 'selected' : '' }}>English Missing</option>
        </select>
        <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white text-sm font-medium px-4 py-2 rounded transition-colors">
            Filter
        </button>
        @if(request('search') || request('quiz_id') || request('translation_status'))
            <a href="{{ route('admin.questions.index') }}" class="text-sm text-gray-500 hover:underline">Clear</a>
        @endif
    </form>
</div>

{{-- Table --}}
@include('admin.questions._table')

@endsection
