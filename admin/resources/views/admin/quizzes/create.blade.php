@extends('layouts.admin')

@section('title', 'Create Quiz')
@section('breadcrumb', 'Quizzes / Create')

@section('content')

<div class="max-w-3xl">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
        <h2 class="text-lg font-bold text-gray-900">Create Quiz</h2>
        <a href="{{ route('admin.quizzes.index') }}" class="text-sm text-gray-500 hover:text-gray-800">&larr; Back to quizzes</a>
    </div>

    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <form action="{{ route('admin.quizzes.store') }}" method="POST" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Category <span class="text-red-500">*</span></label>
                    <select id="category_id" name="category_id" required
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Select category...</option>
                        @foreach($categories as $c)
                            <option value="{{ $c->id }}" {{ old('category_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="difficulty" class="block text-sm font-medium text-gray-700 mb-1">Difficulty <span class="text-red-500">*</span></label>
                    <select id="difficulty" name="difficulty" required
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="easy" {{ old('difficulty') == 'easy' ? 'selected' : '' }}>Easy</option>
                        <option value="medium" {{ old('difficulty', 'medium') == 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="hard" {{ old('difficulty') == 'hard' ? 'selected' : '' }}>Hard</option>
                    </select>
                </div>
            </div>

            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Quiz Title <span class="text-red-500">*</span></label>
                <input type="text" id="title" name="title" value="{{ old('title') }}" required
                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">Slug <span class="text-gray-400 font-normal">(auto-generated if empty)</span></label>
                <input type="text" id="slug" name="slug" value="{{ old('slug') }}" placeholder="e.g. world-geography-trivia"
                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Language <span class="text-red-500">*</span></label>
                <p class="text-xs text-gray-400 mb-2">Cannot be changed after the quiz is created.</p>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <label class="flex items-center gap-2 border border-gray-200 rounded px-3 py-2 cursor-pointer has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                        <input type="radio" name="language" value="en" {{ old('language', 'en') === 'en' ? 'checked' : '' }} required>
                        <span class="text-sm">English only</span>
                    </label>
                    <label class="flex items-center gap-2 border border-gray-200 rounded px-3 py-2 cursor-pointer has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                        <input type="radio" name="language" value="hi" {{ old('language') === 'hi' ? 'checked' : '' }} required>
                        <span class="text-sm">Hindi only</span>
                    </label>
                    <label class="flex items-center gap-2 border border-gray-200 rounded px-3 py-2 cursor-pointer has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                        <input type="radio" name="language" value="bilingual" {{ old('language') === 'bilingual' ? 'checked' : '' }} required>
                        <span class="text-sm">Bilingual (English + Hindi)</span>
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div>
                    <label for="duration_minutes" class="block text-sm font-medium text-gray-700 mb-1">Duration (mins) <span class="text-red-500">*</span></label>
                    <input type="number" id="duration_minutes" name="duration_minutes" value="{{ old('duration_minutes', 10) }}" min="1" required
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="passing_percentage" class="block text-sm font-medium text-gray-700 mb-1">Passing Score (%) <span class="text-red-500">*</span></label>
                    <input type="number" id="passing_percentage" name="passing_percentage" value="{{ old('passing_percentage', 60) }}" min="1" max="100" required
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-1">Display Order</label>
                    <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}"
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea id="description" name="description" rows="3"
                          class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description') }}</textarea>
            </div>

            <div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                           class="w-4 h-4 border-gray-300 rounded text-blue-600 focus:ring-blue-500">
                    <span class="text-sm font-medium text-gray-700">Quiz is active and playable</span>
                </label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="{{ route('admin.quizzes.index') }}" class="text-sm text-gray-600 hover:text-gray-900 px-4 py-2">Cancel</a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-5 py-2 rounded transition-colors">
                    Save Quiz
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
