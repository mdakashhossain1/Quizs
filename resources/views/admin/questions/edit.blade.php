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
                    <select id="quiz_id" name="quiz_id" required onchange="quizsToggleBilingualForm()"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @foreach($quizzes as $q)
                            <option value="{{ $q->id }}" data-bilingual="{{ $q->isBilingual() ? '1' : '0' }}"
                                    {{ old('quiz_id', $question->quiz_id) == $q->id ? 'selected' : '' }}>
                                {{ $q->title }} @if($q->isBilingual()) (Bilingual) @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="points" class="block text-sm font-medium text-gray-700 mb-1">Points <span class="text-red-500">*</span></label>
                    <input type="number" id="points" name="points" value="{{ old('points', $question->points) }}" min="1" required
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            {{-- Legacy single-language fields --}}
            <div id="quizs-legacy-fields" class="space-y-5">
                <div>
                    <label for="question_text" class="block text-sm font-medium text-gray-700 mb-1">Question Text <span class="text-red-500">*</span></label>
                    <textarea id="question_text" name="question_text" rows="3"
                              class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('question_text', $question->quiz->isBilingual() ? '' : $question->question_text) }}</textarea>
                </div>

                <div>
                    <label for="explanation" class="block text-sm font-medium text-gray-700 mb-1">Explanation <span class="text-gray-400 font-normal">(shown after answering)</span></label>
                    <textarea id="explanation" name="explanation" rows="2"
                              class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('explanation', $question->quiz->isBilingual() ? '' : $question->explanation) }}</textarea>
                </div>

                <div class="border-t border-gray-100 pt-5">
                    <div class="mb-3">
                        <h3 class="text-sm font-semibold text-gray-900">Answer Choices</h3>
                        <p class="text-xs text-gray-400">Select the radio button next to the correct answer</p>
                    </div>
                    @php $legacyOptions = $question->quiz->isBilingual() ? collect() : $question->options->values(); @endphp
                    <div class="space-y-2">
                        @for($i = 0; $i < 4; $i++)
                            @php
                                $currentOption = $legacyOptions[$i] ?? null;
                                $isSelected = $currentOption && $currentOption->is_correct;
                            @endphp
                            <div class="flex items-center gap-3 bg-gray-50 border border-gray-200 rounded px-3 py-2.5">
                                <label class="flex items-center gap-1.5 cursor-pointer pr-3 border-r border-gray-200">
                                    <input type="radio" name="correct_option" value="{{ $i }}"
                                           class="quizs-legacy-correct-option w-4 h-4 text-green-600 focus:ring-green-500"
                                           {{ old('correct_option', $isSelected ? $i : null) == $i ? 'checked' : '' }}>
                                    <span class="text-xs font-medium text-green-700">Correct</span>
                                </label>
                                <span class="text-xs text-gray-400 font-mono w-5">{{ chr(65 + $i) }}</span>
                                <input type="text" name="options[{{ $i }}]"
                                       value="{{ old("options.{$i}", $currentOption->option_text ?? '') }}"
                                       placeholder="Option {{ chr(65 + $i) }}..."
                                       class="flex-1 px-3 py-1.5 text-sm border border-gray-200 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                            </div>
                        @endfor
                    </div>
                </div>
            </div>

            {{-- Bilingual fields --}}
            <div id="quizs-bilingual-fields" class="hidden space-y-5">
                <div class="flex gap-2 border-b border-gray-200">
                    <button type="button" onclick="quizsShowLangTab('en')" id="quizs-tab-en"
                            class="px-4 py-2 text-sm font-medium border-b-2 border-blue-600 text-blue-600">English</button>
                    <button type="button" onclick="quizsShowLangTab('hi')" id="quizs-tab-hi"
                            class="px-4 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500">हिन्दी</button>
                </div>

                @php $bilingualOptions = $question->quiz->isBilingual() ? $question->options->values() : collect(); @endphp
                @foreach(['en' => 'English', 'hi' => 'हिन्दी'] as $lang => $label)
                    @php $translation = $question->translations->firstWhere('language_code', $lang); @endphp
                    <div id="quizs-lang-panel-{{ $lang }}" class="space-y-4 {{ $lang !== 'en' ? 'hidden' : '' }}">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                {{ $label }} Question {{ $lang === 'en' ? '' : '(optional — can be added later)' }}
                            </label>
                            <textarea name="translations[{{ $lang }}][question_text]" rows="3"
                                      class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old("translations.{$lang}.question_text", $translation?->question_text) }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ $label }} Explanation</label>
                            <textarea name="translations[{{ $lang }}][explanation]" rows="2"
                                      class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old("translations.{$lang}.explanation", $translation?->explanation) }}</textarea>
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-gray-900 mb-2">{{ $label }} Answer Choices</h4>
                            <div class="space-y-2">
                                @for($i = 0; $i < 4; $i++)
                                    @php
                                        $opt = $bilingualOptions[$i] ?? null;
                                        $optText = $opt?->translations->firstWhere('language_code', $lang)?->option_text;
                                    @endphp
                                    <div class="flex items-center gap-3 bg-gray-50 border border-gray-200 rounded px-3 py-2.5">
                                        <span class="text-xs text-gray-400 font-mono w-5">{{ chr(65 + $i) }}</span>
                                        <input type="text" name="translations[{{ $lang }}][options][{{ $i }}]"
                                               value="{{ old("translations.{$lang}.options.{$i}", $optText) }}"
                                               placeholder="Option {{ chr(65 + $i) }} ({{ $label }})..."
                                               class="flex-1 px-3 py-1.5 text-sm border border-gray-200 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                    </div>
                                @endfor
                            </div>
                        </div>
                    </div>
                @endforeach

                <div class="border-t border-gray-100 pt-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Correct Answer <span class="text-red-500">*</span></label>
                    <div class="flex gap-3">
                        @for($i = 0; $i < 4; $i++)
                            @php $bilingualSelected = ($bilingualOptions[$i] ?? null)?->is_correct; @endphp
                            <label class="flex items-center gap-1.5 cursor-pointer bg-gray-50 border border-gray-200 rounded px-3 py-2">
                                <input type="radio" name="correct_option" value="{{ $i }}"
                                       class="quizs-bilingual-correct-option w-4 h-4 text-green-600 focus:ring-green-500"
                                       {{ old('correct_option', $bilingualSelected ? $i : null) == $i ? 'checked' : '' }}>
                                <span class="text-sm font-medium">{{ chr(65 + $i) }}</span>
                            </label>
                        @endfor
                    </div>
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

<script>
    function quizsToggleBilingualForm() {
        const select = document.getElementById('quiz_id');
        const option = select.options[select.selectedIndex];
        const bilingual = option && option.dataset.bilingual === '1';

        document.getElementById('quizs-legacy-fields').classList.toggle('hidden', bilingual);
        document.getElementById('quizs-bilingual-fields').classList.toggle('hidden', !bilingual);

        document.getElementById('question_text').required = !bilingual;
        document.querySelectorAll('#quizs-legacy-fields input[name^="options"]').forEach(el => el.required = !bilingual);
        document.querySelectorAll('#quizs-legacy-fields .quizs-legacy-correct-option').forEach(el => el.disabled = bilingual);
        document.querySelectorAll('#quizs-bilingual-fields .quizs-bilingual-correct-option').forEach(el => el.disabled = !bilingual);
        document.querySelector('#quizs-bilingual-fields textarea[name="translations[en][question_text]"]').required = bilingual;
    }

    function quizsShowLangTab(lang) {
        ['en', 'hi'].forEach(l => {
            document.getElementById(`quizs-lang-panel-${l}`).classList.toggle('hidden', l !== lang);
            document.getElementById(`quizs-tab-${l}`).classList.toggle('border-blue-600', l === lang);
            document.getElementById(`quizs-tab-${l}`).classList.toggle('text-blue-600', l === lang);
            document.getElementById(`quizs-tab-${l}`).classList.toggle('border-transparent', l !== lang);
            document.getElementById(`quizs-tab-${l}`).classList.toggle('text-gray-500', l !== lang);
        });
    }

    quizsToggleBilingualForm();
</script>

@endsection
