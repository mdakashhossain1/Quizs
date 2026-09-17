@extends('layouts.admin')

@section('title', 'Dashboard')
@section('breadcrumb', 'Overview of your quiz platform')

@section('content')

{{-- Stats Row --}}
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">

    {{-- Total Users --}}
    <div class="bg-white border border-gray-200 rounded-lg p-4">
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Users</p>
            <div class="w-8 h-8 bg-blue-50 rounded flex items-center justify-center">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_users']) }}</p>
        <p class="text-xs text-gray-400 mt-0.5">Registered players</p>
    </div>

    {{-- Online Now --}}
    <div class="bg-white border border-gray-200 rounded-lg p-4">
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Online Now</p>
            <div class="w-8 h-8 bg-green-50 rounded flex items-center justify-center">
                <span class="w-2.5 h-2.5 rounded-full bg-green-500"></span>
            </div>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['online_users']) }}</p>
        <p class="text-xs text-gray-400 mt-0.5">Active in the last {{ (int) (config('quiz.online_timeout_seconds') / 60) ?: 1 }} min</p>
    </div>

    {{-- Categories --}}
    <div class="bg-white border border-gray-200 rounded-lg p-4">
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Categories</p>
            <div class="w-8 h-8 bg-purple-50 rounded flex items-center justify-center">
                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
            </div>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_categories']) }}</p>
        <p class="text-xs text-gray-400 mt-0.5">Active topics</p>
    </div>

    {{-- Quizzes --}}
    <div class="bg-white border border-gray-200 rounded-lg p-4">
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Quizzes</p>
            <div class="w-8 h-8 bg-indigo-50 rounded flex items-center justify-center">
                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            </div>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_quizzes']) }}</p>
        <p class="text-xs text-gray-400 mt-0.5">Published quizzes</p>
    </div>

    {{-- Questions --}}
    <div class="bg-white border border-gray-200 rounded-lg p-4">
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Questions</p>
            <div class="w-8 h-8 bg-amber-50 rounded flex items-center justify-center">
                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_questions']) }}</p>
        <p class="text-xs text-gray-400 mt-0.5">MCQ items</p>
    </div>

    {{-- Attempts --}}
    <div class="bg-white border border-gray-200 rounded-lg p-4">
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Attempts</p>
            <div class="w-8 h-8 bg-green-50 rounded flex items-center justify-center">
                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_attempts']) }}</p>
        <p class="text-xs text-gray-400 mt-0.5">Plays completed</p>
    </div>

</div>

{{-- Quick Actions --}}
<div class="flex flex-wrap items-center gap-3 mb-6">
    <a href="{{ route('admin.quizzes.create') }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        New Quiz
    </a>
    <a href="{{ route('admin.questions.create') }}" class="inline-flex items-center gap-2 bg-white border border-gray-300 hover:border-gray-400 text-gray-700 text-sm font-medium px-4 py-2 rounded transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add Question
    </a>
    <a href="{{ route('admin.categories.create') }}" class="inline-flex items-center gap-2 bg-white border border-gray-300 hover:border-gray-400 text-gray-700 text-sm font-medium px-4 py-2 rounded transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add Category
    </a>
</div>

{{-- Tables Row --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

    {{-- Recent Attempts --}}
    <div class="lg:col-span-2 bg-white border border-gray-200 rounded-lg overflow-hidden">
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
            <div>
                <h3 class="font-semibold text-gray-900 text-sm">Recent Quiz Attempts</h3>
                <p class="text-xs text-gray-400">Latest attempts by mobile users</p>
            </div>
            <a href="{{ route('admin.quiz-attempts.index') }}" class="text-xs text-blue-600 hover:underline font-medium">View all</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-2.5">User</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-2.5">Quiz</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-2.5">Score</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-2.5">Result</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-2.5">When</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($recentAttempts as $att)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $att->user->name ?? 'Guest' }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $att->quiz->title ?? 'Quiz' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-50 text-blue-700">+{{ $att->score }} pts</span>
                            </td>
                            <td class="px-4 py-3 text-gray-500 text-xs">{{ $att->correct_answers }} / {{ $att->total_questions }}</td>
                            <td class="px-4 py-3 text-gray-400 text-xs">{{ $att->completed_at ? $att->completed_at->diffForHumans() : '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-400 text-sm">No attempts recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Recent Users --}}
    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
            <div>
                <h3 class="font-semibold text-gray-900 text-sm">New Players</h3>
                <p class="text-xs text-gray-400">Recently registered users</p>
            </div>
            <a href="{{ route('admin.users.index') }}" class="text-xs text-blue-600 hover:underline font-medium">View all</a>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($recentUsers as $u)
                <div class="flex items-center justify-between px-4 py-3 hover:bg-gray-50">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                            {{ strtoupper(substr($u->name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $u->name }}</p>
                            <p class="text-xs text-gray-400">{{ $u->email }}</p>
                        </div>
                    </div>
                    <span class="text-xs px-2 py-0.5 rounded font-medium {{ $u->role === 'admin' ? 'bg-red-50 text-red-600 border border-red-200' : 'bg-gray-100 text-gray-600' }}">
                        {{ ucfirst($u->role) }}
                    </span>
                </div>
            @empty
                <div class="px-4 py-8 text-center text-gray-400 text-sm">No users registered yet.</div>
            @endforelse
        </div>
    </div>

</div>

@endsection
