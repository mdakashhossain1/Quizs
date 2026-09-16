@extends('layouts.admin')

@section('title', 'Add Question')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Add Question</h1>
            <p class="text-xs text-slate-400 mt-0.5">Define question text, points, and 4 choices with correct answer</p>
        </div>
        <a href="{{ route('admin.questions.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">&larr; Back to list</a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-8">
        <form action="{{ route('admin.questions.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div class="sm:col-span-2">
                    <label for="quiz_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Quiz *</label>
                    <select id="quiz_id" name="quiz_id" required
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="">Select Quiz</option>
                        @foreach($quizzes as $q)
                            <option value="{{ $q->id }}" {{ old('quiz_id', $selectedQuizId) == $q->id ? 'selected' : '' }}>{{ $q->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="points" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Points *</label>
                    <input type="number" id="points" name="points" value="{{ old('points', 10) }}" min="1" required
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
            </div>

            <div>
                <label for="question_text" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Question Text *</label>
                <textarea id="question_text" name="question_text" rows="3" required placeholder="Type the question here..."
                          class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">{{ old('question_text') }}</textarea>
            </div>

            <div>
                <label for="explanation" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Explanation (Shown after answering)</label>
                <textarea id="explanation" name="explanation" rows="2" placeholder="Why is this answer correct?"
                          class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">{{ old('explanation') }}</textarea>
            </div>

            <!-- Answer Options Section -->
            <div class="pt-4 border-t border-slate-100 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Answer Choices</h3>
                        <p class="text-xs text-slate-400">Enter choices and select the radio button for the correct answer</p>
                    </div>
                </div>

                <div class="space-y-3">
                    @for($i = 0; $i < 4; $i++)
                        <div class="flex items-center gap-3 p-3.5 bg-slate-50/80 border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors">
                            <label class="flex items-center gap-2 cursor-pointer pr-2 border-r border-slate-200">
                                <input type="radio" name="correct_option" value="{{ $i }}" {{ old('correct_option', 0) == $i ? 'checked' : '' }} required
                                       class="w-4 h-4 text-emerald-600 focus:ring-emerald-500">
                                <span class="text-xs font-bold text-emerald-700">Correct</span>
                            </label>
                            <span class="text-xs font-bold text-slate-400 w-6">#{{ $i + 1 }}</span>
                            <input type="text" name="options[{{ $i }}]" value="{{ old("options.{$i}") }}" required placeholder="Option {{ $i + 1 }} text..."
                                   class="flex-1 px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-purple-500">
                        </div>
                    @endfor
                </div>
            </div>

            <div>
                <label for="sort_order" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Question Order</label>
                <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}" required
                       class="w-32 px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('admin.questions.index') }}" class="px-5 py-2.5 text-xs font-semibold text-slate-600 hover:text-slate-900">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-semibold text-xs rounded-xl shadow-lg shadow-purple-600/30 transition-all cursor-pointer">
                    Save Question
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
