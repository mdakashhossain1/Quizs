@extends('layouts.admin')

@section('title', 'Edit Question')
@section('breadcrumb', 'Questions / Edit')

@section('content')

<div class="max-w-3xl">
    <div class="flex items-center justify-between mb-5">
        <h2 class="text-lg font-bold text-gray-900">Edit Question</h2>
        <a href="{{ route('admin.questions.index', ['quiz_id' => $question->quiz_id]) }}"
           class="text-sm text-gray-500 hover:text-gray-800">&larr; Back to questions</a>
    </div>

    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <form action="{{ route('admin.questions.update', $question) }}" method="POST" class="space-y-5">
            @csrf @method('PUT')

            <div class="grid grid-cols-3 gap-5">
                <div class="col-span-2">
                    <label for="quiz_id" class="block text-sm font-medium text-gray-700 mb-1">Quiz <span class="text-red-500">*</span></label>
                    <select id="quiz_id" name="quiz_id" required
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @foreach($quizzes as $q)
                            <option value="{{ $q->id }}" {{ old('quiz_id', $question->quiz_id) == $q->id ? 'selected' : '' }}>{{ $q->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="points" class="block text-sm font-medium text-gray-700 mb-1">Points <span class="text-red-500">*</span></label>
                    <input type="number" id="points" name="points" value="{{ old('points', $question->points) }}" min="1" required
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div>
                <label for="question_text" class="block text-sm font-medium text-gray-700 mb-1">Question Text <span class="text-red-500">*</span></label>
                <textarea id="question_text" name="question_text" rows="3" required
                          class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('question_text', $question->question_text) }}</textarea>
            </div>

            <div>
                <label for="explanation" class="block text-sm font-medium text-gray-700 mb-1">Explanation <span class="text-gray-400 font-normal">(shown after answering)</span></label>
                <textarea id="explanation" name="explanation" rows="2"
                          class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('explanation', $question->explanation) }}</textarea>
            </div>

            {{-- Answer Options --}}
            <div class="border-t border-gray-100 pt-5">
                <div class="mb-3">
                    <h3 class="text-sm font-semibold text-gray-900">Answer Choices</h3>
                    <p class="text-xs text-gray-400">Select the radio button next to the correct answer</p>
                </div>
                @php $optionsList = $question->options->values(); @endphp
                <div class="space-y-2">
                    @for($i = 0; $i < 4; $i++)
                        @php
                            $currentOption = $optionsList[$i] ?? null;
                            $isSelected = $currentOption && $currentOption->is_correct;
                        @endphp
                        <div class="flex items-center gap-3 bg-gray-50 border border-gray-200 rounded px-3 py-2.5">
                            <label class="flex items-center gap-1.5 cursor-pointer pr-3 border-r border-gray-200">
                                <input type="radio" name="correct_option" value="{{ $i }}"
                                       {{ old('correct_option', $isSelected ? $i : null) == $i ? 'checked' : '' }} required
                                       class="w-4 h-4 text-green-600 focus:ring-green-500">
                                <span class="text-xs font-medium text-green-700">Correct</span>
                            </label>
                            <span class="text-xs text-gray-400 font-mono w-5">{{ chr(65 + $i) }}</span>
                            <input type="text" name="options[{{ $i }}]"
                                   value="{{ old("options.{$i}", $currentOption->option_text ?? '') }}" required
                                   placeholder="Option {{ chr(65 + $i) }}..."
                                   class="flex-1 px-3 py-1.5 text-sm border border-gray-200 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        </div>
                    @endfor
                </div>
            </div>

            <div>
                <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-1">Display Order</label>
                <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', $question->sort_order) }}"
                       class="w-28 px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="{{ route('admin.questions.index', ['quiz_id' => $question->quiz_id]) }}"
                   class="text-sm text-gray-600 hover:text-gray-900 px-4 py-2">Cancel</a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-5 py-2 rounded transition-colors">
                    Update Question
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
