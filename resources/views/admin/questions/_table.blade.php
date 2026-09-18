<div id="ajax-table" data-count-text="{{ $questions->total() }} total questions" class="bg-white border border-gray-200 rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Quiz</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Question</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Options & Answer</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Translations</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Pts</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">#</th>
                    <th class="text-right text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($questions as $question)
                    @php
                        $bilingual = $question->quiz?->isBilingual() ?? false;
                        $preview = $question->previewContent();
                        $hasEn = $question->translations->contains('language_code', 'en');
                        $hasHi = $question->translations->contains('language_code', 'hi');
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <span class="whitespace-nowrap text-xs font-medium text-blue-700 bg-blue-50 border border-blue-100 px-2 py-0.5 rounded">
                                {{ $question->quiz->title ?? 'Unassigned' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 max-w-xs">
                            <p class="font-medium text-gray-900 line-clamp-2">{{ $preview['question_text'] }}</p>
                            @if($preview['explanation'])
                                <p class="text-xs text-gray-400 mt-0.5 italic line-clamp-1">{{ $preview['explanation'] }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="space-y-1">
                                @foreach($question->options as $opt)
                                    <div class="flex items-center gap-1.5 text-xs {{ $opt->is_correct ? 'text-green-700 font-semibold' : 'text-gray-500' }}">
                                        @if($opt->is_correct)
                                            <svg class="w-3.5 h-3.5 text-green-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                        @else
                                            <span class="w-1.5 h-1.5 rounded-full bg-gray-300 mx-1 flex-shrink-0"></span>
                                        @endif
                                        <span>{{ $bilingual ? $opt->textFor($preview['served_language'] ?? 'en') : $opt->option_text }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            @if($bilingual)
                                <div class="flex items-center gap-1">
                                    <span class="whitespace-nowrap text-xs px-1.5 py-0.5 rounded {{ $hasEn ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-600' }}">EN {{ $hasEn ? '✓' : 'Missing' }}</span>
                                    <span class="whitespace-nowrap text-xs px-1.5 py-0.5 rounded {{ $hasHi ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-600' }}">HI {{ $hasHi ? '✓' : 'Missing' }}</span>
                                </div>
                            @else
                                <span class="whitespace-nowrap text-xs px-1.5 py-0.5 rounded bg-gray-100 text-gray-600 uppercase">{{ $question->quiz->language ?? '—' }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-700 font-medium text-xs">+{{ $question->points }}</td>
                        <td class="px-4 py-3 text-gray-400 text-xs font-mono">{{ $question->sort_order }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="inline-flex items-center gap-2">
                                <a href="{{ route('admin.questions.edit', $question) }}"
                                   class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 px-3 py-1 rounded transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    Edit
                                </a>
                                <form action="{{ route('admin.questions.destroy', $question) }}" method="POST"
                                      data-ajax-delete data-confirm="Delete this question?">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="inline-flex items-center gap-1.5 text-xs font-medium text-red-600 hover:bg-red-50 px-3 py-1 rounded transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-gray-400">
                            No questions found. <a href="{{ route('admin.questions.create') }}" class="text-blue-600 hover:underline">Add one</a>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($questions->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $questions->links() }}
        </div>
    @endif
</div>
