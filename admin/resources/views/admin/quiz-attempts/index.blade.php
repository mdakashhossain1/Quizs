@extends('layouts.admin')

@section('title', 'Quiz Attempts')
@section('breadcrumb', 'Player performance logs')

@section('content')

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
    <div>
        <h2 class="text-lg font-bold text-gray-900">Quiz Attempts</h2>
        <p class="text-xs text-gray-400 mt-0.5" id="ajax-count-label">{{ $attempts->total() }} total attempts</p>
    </div>
</div>

{{-- Filter --}}
<div class="bg-white border border-gray-200 rounded-lg p-3 mb-4">
    <form method="GET" action="{{ route('admin.quiz-attempts.index') }}" class="flex flex-wrap items-center gap-3" data-ajax-filter>
        <input type="text" name="user" value="{{ request('user') }}" placeholder="Search by user name or email..."
               class="px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 w-full sm:w-56">
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

        <span class="ml-auto flex items-center gap-2">
            <span class="text-xs text-gray-400">Download summary:</span>
            <a href="{{ route('admin.quiz-attempts.summary-list.pdf', request()->query()) }}"
               class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 px-3 py-1.5 rounded transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2-9H8a2 2 0 00-2 2v14a2 2 0 002 2h8a2 2 0 002-2V9l-6-6z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 3v5a1 1 0 001 1h5"/></svg>
                PDF
            </a>
            <a href="{{ route('admin.quiz-attempts.summary-list.csv', request()->query()) }}"
               class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 px-3 py-1.5 rounded transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5V19a2 2 0 002 2h14a2 2 0 002-2v-2.5M7 10l5 5 5-5M12 15V3"/></svg>
                CSV
            </a>
        </span>
    </form>
</div>

{{-- Table --}}
@include('admin.quiz-attempts._table')

@endsection
