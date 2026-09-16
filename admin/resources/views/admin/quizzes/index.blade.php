@extends('layouts.admin')

@section('title', 'Quizzes Management')

@section('content')
<div class="space-y-6">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Quizzes Catalog</h1>
            <p class="text-xs text-slate-400 mt-1">Manage game topics, difficulty levels, and pass criteria</p>
        </div>
        <a href="{{ route('admin.quizzes.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-semibold text-xs rounded-xl shadow-lg shadow-purple-600/30 transition-all self-start">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Create New Quiz
        </a>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex flex-wrap items-center gap-4">
        <form method="GET" action="{{ route('admin.quizzes.index') }}" class="flex flex-wrap items-center gap-3 w-full">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search quizzes..."
                   class="px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs w-64 focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">

            <select name="category_id" class="px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                <option value="">All Categories</option>
                @foreach($categories as $c)
                    <option value="{{ $c->id }}" {{ request('category_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>

            <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-xl">
                Filter
            </button>
            @if(request('search') || request('category_id'))
                <a href="{{ route('admin.quizzes.index') }}" class="text-xs text-slate-500 hover:text-slate-800 underline">Reset</a>
            @endif
        </form>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-[11px] font-bold uppercase text-slate-400 bg-slate-50/75">
                        <th class="py-3 px-6">Quiz Title</th>
                        <th class="py-3 px-6">Category</th>
                        <th class="py-3 px-6">Questions</th>
                        <th class="py-3 px-6">Difficulty</th>
                        <th class="py-3 px-6">Duration</th>
                        <th class="py-3 px-6">Status</th>
                        <th class="py-3 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($quizzes as $quiz)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-6">
                                <p class="font-bold text-slate-900">{{ $quiz->title }}</p>
                                <p class="text-xs text-slate-400 font-mono">{{ $quiz->slug }}</p>
                            </td>
                            <td class="py-3.5 px-6">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold text-slate-700 bg-slate-100">
                                    <span class="w-2 h-2 rounded-full" style="background-color: {{ $quiz->category->color ?? '#8B5CF6' }}"></span>
                                    {{ $quiz->category->name ?? 'Uncategorized' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-6">
                                <a href="{{ route('admin.questions.index', ['quiz_id' => $quiz->id]) }}" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-purple-50 text-purple-700 hover:bg-purple-100">
                                    {{ $quiz->questions_count }} Questions &rarr;
                                </a>
                            </td>
                            <td class="py-3.5 px-6">
                                @if($quiz->difficulty === 'easy')
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700">Easy</span>
                                @elseif($quiz->difficulty === 'medium')
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700">Medium</span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700">Hard</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-6 text-xs text-slate-600">{{ $quiz->duration_minutes }} mins</td>
                            <td class="py-3.5 px-6">
                                @if($quiz->is_active)
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700">Active</span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">Draft</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-6 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('admin.questions.create', ['quiz_id' => $quiz->id]) }}" class="px-2.5 py-1 bg-purple-50 hover:bg-purple-100 text-purple-700 rounded-lg text-xs font-semibold transition-colors">
                                        + Q
                                    </a>
                                    <a href="{{ route('admin.quizzes.edit', $quiz) }}" class="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition-colors">
                                        Edit
                                    </a>
                                    <form action="{{ route('admin.quizzes.destroy', $quiz) }}" method="POST" onsubmit="return confirm('Delete this quiz and all its questions?')">
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
                            <td colspan="7" class="py-12 text-center text-slate-400">No quizzes found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($quizzes->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $quizzes->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
