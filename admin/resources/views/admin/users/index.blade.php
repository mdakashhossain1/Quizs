@extends('layouts.admin')

@section('title', 'Users & Accounts')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Registered Users</h1>
            <p class="text-xs text-slate-400 mt-1">Manage player profiles, administrator roles, and game streaks</p>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-4">
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap items-center gap-3 w-full">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or email..."
                   class="px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs w-64 focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">

            <select name="role" class="px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                <option value="">All Roles</option>
                <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="user" {{ request('role') === 'user' ? 'selected' : '' }}>User</option>
            </select>

            <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-xl">
                Filter
            </button>
            @if(request('search') || request('role'))
                <a href="{{ route('admin.users.index') }}" class="text-xs text-slate-500 hover:text-slate-800 underline">Reset</a>
            @endif
        </form>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-[11px] font-bold uppercase text-slate-400 bg-slate-50/75">
                        <th class="py-3 px-6">User</th>
                        <th class="py-3 px-6">Role</th>
                        <th class="py-3 px-6">Streak</th>
                        <th class="py-3 px-6">Score</th>
                        <th class="py-3 px-6">Attempts</th>
                        <th class="py-3 px-6">Status</th>
                        <th class="py-3 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($users as $u)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-purple-600 to-indigo-500 text-white font-bold text-xs flex items-center justify-center shadow-sm">
                                        {{ strtoupper(substr($u->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-900">{{ $u->name }}</p>
                                        <p class="text-xs text-slate-400">{{ $u->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-6">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $u->role === 'admin' ? 'bg-rose-50 text-rose-700 border border-rose-200/60' : 'bg-slate-100 text-slate-700' }}">
                                    {{ ucfirst($u->role) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-6">
                                <span class="inline-flex items-center gap-1 font-semibold text-xs text-amber-600">
                                    🔥 {{ $u->streak }} days
                                </span>
                            </td>
                            <td class="py-3.5 px-6 font-bold text-xs text-purple-700">
                                {{ number_format($u->score) }} pts
                            </td>
                            <td class="py-3.5 px-6 text-xs text-slate-500">
                                {{ $u->quiz_attempts_count }} plays
                            </td>
                            <td class="py-3.5 px-6">
                                @if($u->is_active)
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700">Active</span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700">Blocked</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-6 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('admin.users.edit', $u) }}" class="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition-colors">
                                        Edit
                                    </a>
                                    @if(Auth::id() !== $u->id)
                                        <form action="{{ route('admin.users.destroy', $u) }}" method="POST" onsubmit="return confirm('Delete user {{ $u->name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2.5 py-1 text-rose-600 hover:bg-rose-50 rounded-lg text-xs font-semibold transition-colors">
                                                Delete
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $users->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
