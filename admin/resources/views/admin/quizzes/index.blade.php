@extends('layouts.admin')

@section('title', 'Quizzes')
@section('breadcrumb', 'Manage all quiz content')

@section('content')

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
    <div>
        <h2 class="text-lg font-bold text-gray-900">Quizzes</h2>
        <p class="text-xs text-gray-400 mt-0.5">{{ $quizzes->total() }} total quizzes</p>
    </div>
    <a href="{{ route('admin.quizzes.create') }}"
       class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Create Quiz
    </a>
</div>

{{-- Filter --}}
<div class="bg-white border border-gray-200 rounded-lg p-3 mb-4">
    <form method="GET" action="{{ route('admin.quizzes.index') }}" class="flex flex-wrap items-center gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search quizzes..."
               class="px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 w-full sm:w-56">
        <select name="category_id" class="px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All Categories</option>
            @foreach($categories as $c)
                <option value="{{ $c->id }}" {{ request('category_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white text-sm font-medium px-4 py-2 rounded transition-colors">
            Filter
        </button>
        @if(request('search') || request('category_id'))
            <a href="{{ route('admin.quizzes.index') }}" class="text-sm text-gray-500 hover:underline">Clear</a>
        @endif
    </form>
</div>

{{-- Table --}}
<div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Quiz Title</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Category</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Questions</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Difficulty</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Duration</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Status</th>
                    <th class="text-right text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($quizzes as $quiz)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <p class="font-semibold text-gray-900">{{ $quiz->title }}</p>
                            <p class="text-xs text-gray-400 font-mono">{{ $quiz->slug }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-1.5 whitespace-nowrap text-xs font-medium text-gray-700 bg-gray-100 px-2 py-0.5 rounded">
                                <span class="w-2 h-2 rounded-full" style="background-color: {{ $quiz->category->color ?? '#6b7280' }}"></span>
                                {{ $quiz->category->name ?? 'Uncategorized' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.questions.index', ['quiz_id' => $quiz->id]) }}"
                               class="text-xs font-medium text-blue-600 hover:underline">
                                {{ $quiz->questions_count }} questions &rarr;
                            </a>
                        </td>
                        <td class="px-4 py-3">
                            @if($quiz->difficulty === 'easy')
                                <span class="whitespace-nowrap text-xs font-medium text-green-700 bg-green-50 border border-green-200 px-2 py-0.5 rounded">Easy</span>
                            @elseif($quiz->difficulty === 'medium')
                                <span class="whitespace-nowrap text-xs font-medium text-amber-700 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded">Medium</span>
                            @else
                                <span class="whitespace-nowrap text-xs font-medium text-red-700 bg-red-50 border border-red-200 px-2 py-0.5 rounded">Hard</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $quiz->duration_minutes }} min</td>
                        <td class="px-4 py-3">
                            @if($quiz->is_active)
                                <span class="inline-flex items-center gap-1 whitespace-nowrap text-xs font-medium text-green-700 bg-green-50 border border-green-200 px-2 py-0.5 rounded">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Active
                                </span>
                            @else
                                <span class="whitespace-nowrap text-xs font-medium text-gray-600 bg-gray-100 border border-gray-200 px-2 py-0.5 rounded">Draft</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="inline-flex items-center gap-2">
                                <a href="{{ route('admin.questions.create', ['quiz_id' => $quiz->id]) }}"
                                   class="text-xs font-medium text-blue-600 hover:bg-blue-50 px-2.5 py-1 rounded transition-colors border border-blue-200">
                                    + Q
                                </a>
                                <a href="{{ route('admin.quizzes.edit', $quiz) }}"
                                   class="text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 px-3 py-1 rounded transition-colors">
                                    Edit
                                </a>
                                <form action="{{ route('admin.quizzes.destroy', $quiz) }}" method="POST"
                                      onsubmit="return confirm('Delete this quiz and all its questions?')">
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
                        <td colspan="7" class="px-4 py-10 text-center text-gray-400">
                            No quizzes found. <a href="{{ route('admin.quizzes.create') }}" class="text-blue-600 hover:underline">Create one</a>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($quizzes->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $quizzes->links() }}
        </div>
    @endif
</div>

@endsection
