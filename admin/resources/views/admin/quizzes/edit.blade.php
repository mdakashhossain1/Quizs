@extends('layouts.admin')

@section('title', 'Edit Quiz')
@section('breadcrumb', 'Quizzes / Edit')

@section('content')

<div class="max-w-3xl">
    <div class="flex items-center justify-between mb-5">
        <h2 class="text-lg font-bold text-gray-900">Edit: {{ $quiz->title }}</h2>
        <a href="{{ route('admin.quizzes.index') }}" class="text-sm text-gray-500 hover:text-gray-800">&larr; Back to quizzes</a>
    </div>

    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <form action="{{ route('admin.quizzes.update', $quiz) }}" method="POST" class="space-y-5">
            @csrf @method('PUT')

            <div class="grid grid-cols-2 gap-5">
                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Category <span class="text-red-500">*</span></label>
                    <select id="category_id" name="category_id" required
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @foreach($categories as $c)
                            <option value="{{ $c->id }}" {{ old('category_id', $quiz->category_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="difficulty" class="block text-sm font-medium text-gray-700 mb-1">Difficulty <span class="text-red-500">*</span></label>
                    <select id="difficulty" name="difficulty" required
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="easy" {{ old('difficulty', $quiz->difficulty) == 'easy' ? 'selected' : '' }}>Easy</option>
                        <option value="medium" {{ old('difficulty', $quiz->difficulty) == 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="hard" {{ old('difficulty', $quiz->difficulty) == 'hard' ? 'selected' : '' }}>Hard</option>
                    </select>
                </div>
            </div>

            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Quiz Title <span class="text-red-500">*</span></label>
                <input type="text" id="title" name="title" value="{{ old('title', $quiz->title) }}" required
                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                <input type="text" id="slug" name="slug" value="{{ old('slug', $quiz->slug) }}"
                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Language</label>
                <div class="inline-flex items-center gap-2 bg-gray-50 border border-gray-200 rounded px-3 py-2 text-sm text-gray-600">
                    {{ $quiz->isBilingual() ? 'Bilingual (English + Hindi)' : ($quiz->language === 'hi' ? 'Hindi only' : 'English only') }}
                    <span class="text-xs text-gray-400">(fixed at creation)</span>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-5">
                <div>
                    <label for="duration_minutes" class="block text-sm font-medium text-gray-700 mb-1">Duration (mins) <span class="text-red-500">*</span></label>
                    <input type="number" id="duration_minutes" name="duration_minutes" value="{{ old('duration_minutes', $quiz->duration_minutes) }}" min="1" required
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="passing_percentage" class="block text-sm font-medium text-gray-700 mb-1">Passing Score (%) <span class="text-red-500">*</span></label>
                    <input type="number" id="passing_percentage" name="passing_percentage" value="{{ old('passing_percentage', $quiz->passing_percentage) }}" min="1" max="100" required
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-1">Display Order</label>
                    <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', $quiz->sort_order) }}"
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea id="description" name="description" rows="3"
                          class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description', $quiz->description) }}</textarea>
            </div>

            <div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $quiz->is_active) ? 'checked' : '' }}
                           class="w-4 h-4 border-gray-300 rounded text-blue-600 focus:ring-blue-500">
                    <span class="text-sm font-medium text-gray-700">Quiz is active and playable</span>
                </label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="{{ route('admin.quizzes.index') }}" class="text-sm text-gray-600 hover:text-gray-900 px-4 py-2">Cancel</a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-5 py-2 rounded transition-colors">
                    Update Quiz
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
