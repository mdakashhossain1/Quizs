@extends('layouts.admin')

@section('title', 'Create Quiz')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Create Quiz</h1>
            <p class="text-xs text-slate-400 mt-0.5">Define quiz parameters and requirements</p>
        </div>
        <a href="{{ route('admin.quizzes.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">&larr; Back to list</a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-8">
        <form action="{{ route('admin.quizzes.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="category_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Category *</label>
                    <select id="category_id" name="category_id" required
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="">Select Category</option>
                        @foreach($categories as $c)
                            <option value="{{ $c->id }}" {{ old('category_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="difficulty" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Difficulty *</label>
                    <select id="difficulty" name="difficulty" required
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="easy" {{ old('difficulty') == 'easy' ? 'selected' : '' }}>Easy</option>
                        <option value="medium" {{ old('difficulty', 'medium') == 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="hard" {{ old('difficulty') == 'hard' ? 'selected' : '' }}>Hard</option>
                    </select>
                </div>
            </div>

            <div>
                <label for="title" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Quiz Title *</label>
                <input type="text" id="title" name="title" value="{{ old('title') }}" required
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">
            </div>

            <div>
                <label for="slug" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Slug (Auto-generated if empty)</label>
                <input type="text" id="slug" name="slug" value="{{ old('slug') }}" placeholder="e.g. world-geography-trivia"
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div>
                    <label for="duration_minutes" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Duration (Minutes) *</label>
                    <input type="number" id="duration_minutes" name="duration_minutes" value="{{ old('duration_minutes', 10) }}" min="1" required
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>

                <div>
                    <label for="passing_percentage" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Passing Score (%) *</label>
                    <input type="number" id="passing_percentage" name="passing_percentage" value="{{ old('passing_percentage', 60) }}" min="1" max="100" required
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>

                <div>
                    <label for="sort_order" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Display Order *</label>
                    <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}" required
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
            </div>

            <div>
                <label for="description" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Description</label>
                <textarea id="description" name="description" rows="3"
                          class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">{{ old('description') }}</textarea>
            </div>

            <div class="pt-2">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                           class="w-4 h-4 rounded text-purple-600 focus:ring-purple-500 border-slate-300">
                    <span class="text-sm font-semibold text-slate-700">Quiz is active and playable</span>
                </label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('admin.quizzes.index') }}" class="px-5 py-2.5 text-xs font-semibold text-slate-600 hover:text-slate-900">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-semibold text-xs rounded-xl shadow-lg shadow-purple-600/30 transition-all cursor-pointer">
                    Save Quiz
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
