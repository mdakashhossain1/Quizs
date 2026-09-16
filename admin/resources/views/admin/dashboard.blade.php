@extends('layouts.admin')

@section('title', 'System Dashboard')

@section('content')
<div class="space-y-8">

    <!-- Top Welcome Banner -->
    <div class="bg-gradient-to-r from-purple-700 via-indigo-700 to-slate-900 rounded-3xl p-8 text-white shadow-xl relative overflow-hidden">
        <div class="absolute right-0 top-0 w-96 h-96 bg-white/5 rounded-full blur-2xl pointer-events-none"></div>
        <div class="relative z-10 max-w-2xl">
            <span class="px-3 py-1 bg-white/10 backdrop-blur-md rounded-full text-xs font-semibold tracking-wide uppercase text-purple-200">Standard Laravel Architecture</span>
            <h1 class="text-3xl font-extrabold mt-3 tracking-tight">Quizs Management System</h1>
            <p class="text-purple-100 text-sm mt-2 leading-relaxed">
                Direct Blade templates, RESTful controllers, and Tailwind CSS. Fast, reliable, and completely free of complex Livewire overhead.
            </p>
            <div class="flex items-center gap-3 mt-6">
                <a href="{{ route('admin.quizzes.create') }}" class="px-4 py-2 bg-white text-purple-900 font-semibold text-xs rounded-xl hover:bg-purple-50 transition-all shadow-md">
                    + New Quiz
                </a>
                <a href="{{ route('admin.questions.create') }}" class="px-4 py-2 bg-purple-600/60 border border-purple-400/30 text-white font-semibold text-xs rounded-xl hover:bg-purple-600 transition-all">
                    + Add Questions
                </a>
            </div>
        </div>
    </div>

    <!-- 5 Stats Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5">
        <!-- Card 1: Users -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Users</p>
                <h3 class="text-2xl font-black text-slate-900 mt-1">{{ number_format($stats['total_users']) }}</h3>
                <span class="text-[11px] text-emerald-600 font-semibold mt-0.5 inline-block">Registered players</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
        </div>

        <!-- Card 2: Categories -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Categories</p>
                <h3 class="text-2xl font-black text-slate-900 mt-1">{{ number_format($stats['total_categories']) }}</h3>
                <span class="text-[11px] text-blue-600 font-semibold mt-0.5 inline-block">Topics active</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            </div>
        </div>

        <!-- Card 3: Quizzes -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Quizzes</p>
                <h3 class="text-2xl font-black text-slate-900 mt-1">{{ number_format($stats['total_quizzes']) }}</h3>
                <span class="text-[11px] text-indigo-600 font-semibold mt-0.5 inline-block">Published quizzes</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            </div>
        </div>

        <!-- Card 4: Questions -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Question Bank</p>
                <h3 class="text-2xl font-black text-slate-900 mt-1">{{ number_format($stats['total_questions']) }}</h3>
                <span class="text-[11px] text-amber-600 font-semibold mt-0.5 inline-block">Total MCQ items</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <!-- Card 5: Attempts -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Quiz Attempts</p>
                <h3 class="text-2xl font-black text-slate-900 mt-1">{{ number_format($stats['total_attempts']) }}</h3>
                <span class="text-[11px] text-emerald-600 font-semibold mt-0.5 inline-block">Plays completed</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
            </div>
        </div>
    </div>

    <!-- Tables Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Recent Quiz Attempts (2 cols) -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Recent Quiz Activity</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Latest attempts submitted by mobile users</p>
                </div>
                <a href="{{ route('admin.quiz-attempts.index') }}" class="text-xs font-semibold text-purple-600 hover:text-purple-700">View All &rarr;</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 text-[11px] font-bold uppercase text-slate-400 bg-slate-50/75">
                            <th class="py-3 px-6">User</th>
                            <th class="py-3 px-6">Quiz Title</th>
                            <th class="py-3 px-6">Score</th>
                            <th class="py-3 px-6">Accuracy</th>
                            <th class="py-3 px-6">Time</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($recentAttempts as $att)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3.5 px-6 font-medium text-slate-900">{{ $att->user->name ?? 'Guest User' }}</td>
                                <td class="py-3.5 px-6">{{ $att->quiz->title ?? 'Quiz' }}</td>
                                <td class="py-3.5 px-6">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-purple-50 text-purple-700">
                                        +{{ $att->score }} pts
                                    </span>
                                </td>
                                <td class="py-3.5 px-6">
                                    <span class="text-xs font-semibold text-slate-600">{{ $att->correct_answers }} / {{ $att->total_questions }}</span>
                                </td>
                                <td class="py-3.5 px-6 text-xs text-slate-400">{{ $att->completed_at ? $att->completed_at->diffForHumans() : '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-400 text-sm">No attempts recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Registered Users (1 col) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-900 text-base">New Players</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Recently joined community members</p>
                </div>
                <a href="{{ route('admin.users.index') }}" class="text-xs font-semibold text-purple-600 hover:text-purple-700">All &rarr;</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($recentUsers as $u)
                    <div class="p-4 flex items-center justify-between hover:bg-slate-50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-purple-500 to-indigo-500 text-white font-bold text-xs flex items-center justify-center">
                                {{ strtoupper(substr($u->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-900">{{ $u->name }}</p>
                                <p class="text-[11px] text-slate-400">{{ $u->email }}</p>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $u->role === 'admin' ? 'bg-rose-50 text-rose-600' : 'bg-slate-100 text-slate-600' }}">
                            {{ ucfirst($u->role) }}
                        </span>
                    </div>
                @empty
                    <div class="p-6 text-center text-slate-400 text-xs">No users registered yet.</div>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection
