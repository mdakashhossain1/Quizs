<div id="ajax-table" data-count-text="{{ $attempts->total() }} total attempts" class="bg-white border border-gray-200 rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">User</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Quiz</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Score</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Correct</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Accuracy</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Status</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Played At</th>
                    <th class="text-right text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($attempts as $attempt)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <p class="font-semibold text-gray-900">{{ $attempt->user->name ?? 'Unknown' }}</p>
                            <p class="text-xs text-gray-400">{{ $attempt->user->email ?? '' }}</p>
                        </td>
                        <td class="px-4 py-3 text-gray-700">{{ $attempt->quiz->title ?? 'Deleted Quiz' }}</td>
                        <td class="px-4 py-3">
                            <span class="whitespace-nowrap text-xs font-semibold text-blue-700 bg-blue-50 border border-blue-100 px-2 py-0.5 rounded">+{{ $attempt->score }} pts</span>
                        </td>
                        <td class="px-4 py-3 text-gray-600 text-xs font-medium">{{ $attempt->correct_answers }} / {{ $attempt->total_questions }}</td>
                        <td class="px-4 py-3">
                            @if($attempt->accuracy !== null)
                                <span class="text-xs font-semibold px-2 py-0.5 rounded {{ $attempt->accuracy >= 60 ? 'text-green-700 bg-green-50 border border-green-200' : 'text-red-700 bg-red-50 border border-red-200' }}">
                                    {{ number_format($attempt->accuracy, 0) }}%
                                </span>
                            @else
                                <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($attempt->status === 'completed')
                                <span class="whitespace-nowrap text-xs font-medium text-green-700 bg-green-50 border border-green-200 px-2 py-0.5 rounded">Completed</span>
                            @elseif($attempt->isStale())
                                <span class="whitespace-nowrap text-xs font-medium text-gray-600 bg-gray-100 border border-gray-200 px-2 py-0.5 rounded">Abandoned</span>
                            @else
                                <span class="whitespace-nowrap text-xs font-medium text-amber-700 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded">In Progress</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-400 text-xs">
                            {{ $attempt->completed_at ? $attempt->completed_at->format('M d, Y H:i') : ($attempt->started_at?->format('M d, Y H:i') ?? '-') }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="inline-flex items-center gap-2">
                                <a href="{{ route('admin.quiz-attempts.show', $attempt) }}"
                                   class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 px-3 py-1 rounded transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    View
                                </a>
                                <form action="{{ route('admin.quiz-attempts.destroy', $attempt) }}" method="POST"
                                      data-ajax-delete data-confirm="Delete this attempt log?">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="inline-flex items-center gap-1.5 text-xs font-medium text-red-600 hover:bg-red-50 px-3 py-1 rounded transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        Remove
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-10 text-center text-gray-400">No attempts logged yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($attempts->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $attempts->links() }}
        </div>
    @endif
</div>
