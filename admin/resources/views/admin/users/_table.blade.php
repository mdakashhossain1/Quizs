<div id="ajax-table" data-count-text="{{ $users->total() }} total users" class="bg-white border border-gray-200 rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-3 py-3">User</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-3 py-3">Role</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-3 py-3">Streak</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-3 py-3">Score</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-3 py-3">Today's Quizzes</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-3 py-3">Today's Qs (R / W)</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-3 py-3">This Month</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-3 py-3">Activity</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-3 py-3">Status</th>
                    <th class="text-right text-xs font-semibold text-gray-500 uppercase tracking-wide px-3 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($users as $u)
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-3">
                            <div class="flex items-center gap-2.5">
                                @if($u->avatar)
                                    <img src="{{ $u->avatar }}" alt="" class="w-8 h-8 rounded-full object-cover flex-shrink-0 border border-gray-200">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-blue-600 text-white text-xs font-bold flex items-center justify-center flex-shrink-0">
                                        {{ strtoupper(substr($u->name, 0, 1)) }}
                                    </div>
                                @endif
                                <div>
                                    <p class="font-semibold text-gray-900">{{ $u->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $u->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-3 py-3">
                            <span class="whitespace-nowrap text-xs font-medium px-2 py-0.5 rounded {{ $u->role === 'admin' ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-gray-100 text-gray-600' }}">
                                {{ ucfirst($u->role) }}
                            </span>
                        </td>
                        <td class="px-3 py-3 text-sm text-gray-600">🔥 {{ $u->streak }} days</td>
                        <td class="px-3 py-3 text-sm font-semibold text-blue-700">{{ number_format($u->score) }} pts</td>
                        <td class="px-3 py-3 text-sm">
                            <div class="flex items-center gap-1">
                                <span class="font-bold text-gray-900">{{ $u->today_completed_quizzes_count ?? 0 }}</span>
                                <span class="text-xs text-gray-400">today</span>
                            </div>
                            <span class="text-xs text-gray-400 block">{{ $u->quiz_attempts_count }} total</span>
                        </td>
                        <td class="px-3 py-3 whitespace-nowrap text-xs">
                            <div class="flex items-center gap-1.5">
                                <span class="inline-flex items-center gap-0.5 font-semibold text-green-700 bg-green-50 border border-green-200 px-1.5 py-0.5 rounded" title="Right answers today">
                                    ✓ {{ (int) ($u->today_right_sum ?? 0) }}
                                </span>
                                <span class="inline-flex items-center gap-0.5 font-semibold text-red-700 bg-red-50 border border-red-200 px-1.5 py-0.5 rounded" title="Wrong answers today">
                                    ✗ {{ (int) ($u->today_wrong_sum ?? 0) }}
                                </span>
                            </div>
                            <span class="text-xs text-gray-400 block mt-0.5">{{ ((int) ($u->today_right_sum ?? 0)) + ((int) ($u->today_wrong_sum ?? 0)) }} Qs today</span>
                        </td>
                        <td class="px-3 py-3 text-sm">
                            <span class="font-bold text-purple-700">{{ (int) ($u->this_month_questions_sum ?? 0) }}</span>
                            <span class="text-xs text-gray-400 block">questions</span>
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.users.activity', $u) }}" class="block hover:underline">
                                @if($u->is_online)
                                    <span class="inline-flex items-center gap-1 whitespace-nowrap text-xs font-medium text-green-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Online
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 whitespace-nowrap text-xs font-medium text-gray-500">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-300"></span> Offline
                                    </span>
                                @endif
                                <p class="text-xs text-gray-400 mt-0.5">
                                    {{ $u->last_active_at ? $u->last_active_at->diffForHumans() : 'Never active' }}
                                </p>
                            </a>
                        </td>
                        <td class="px-4 py-3">
                            @if($u->is_active)
                                <span class="inline-flex items-center gap-1 whitespace-nowrap text-xs font-medium text-green-700 bg-green-50 border border-green-200 px-2 py-0.5 rounded">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Active
                                </span>
                            @else
                                <span class="whitespace-nowrap text-xs font-medium text-red-700 bg-red-50 border border-red-200 px-2 py-0.5 rounded">Blocked</span>
                            @endif
                            @if($u->must_change_password)
                                <span class="block mt-1 whitespace-nowrap text-xs font-medium text-amber-700 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded w-fit">Temp password</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="inline-flex items-center gap-2">
                                <a href="{{ route('admin.users.edit', $u) }}"
                                   class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 px-3 py-1 rounded transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    Edit
                                </a>
                                <form action="{{ route('admin.users.reset-password', $u) }}" method="POST"
                                      onsubmit="return confirm('Reset password for {{ addslashes($u->name) }}? A new temporary password will be emailed to them.')">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center gap-1.5 text-xs font-medium text-amber-700 bg-amber-50 hover:bg-amber-100 px-3 py-1 rounded transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                        Reset Password
                                    </button>
                                </form>
                                @if(Auth::id() !== $u->id)
                                    <form action="{{ route('admin.users.destroy', $u) }}" method="POST"
                                          data-ajax-delete data-confirm="Delete user {{ addslashes($u->name) }}?">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-1.5 text-xs font-medium text-red-600 hover:bg-red-50 px-3 py-1 rounded transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            Delete
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="px-4 py-10 text-center text-gray-400">No users found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $users->links() }}
        </div>
    @endif
</div>
