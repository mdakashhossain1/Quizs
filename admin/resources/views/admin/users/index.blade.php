@extends('layouts.admin')

@section('title', 'Users')
@section('breadcrumb', 'Manage registered players and admins')

@section('content')

<div class="flex items-center justify-between mb-5">
    <div>
        <h2 class="text-lg font-bold text-gray-900">Users</h2>
        <p class="text-xs text-gray-400 mt-0.5">{{ $users->total() }} total users</p>
    </div>
</div>

{{-- Filter --}}
<div class="bg-white border border-gray-200 rounded-lg p-3 mb-4">
    <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap items-center gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or email..."
               class="px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 w-64">
        <select name="role" class="px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All Roles</option>
            <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
            <option value="user" {{ request('role') === 'user' ? 'selected' : '' }}>User</option>
        </select>
        <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white text-sm font-medium px-4 py-2 rounded transition-colors">
            Filter
        </button>
        @if(request('search') || request('role'))
            <a href="{{ route('admin.users.index') }}" class="text-sm text-gray-500 hover:underline">Clear</a>
        @endif
    </form>
</div>

{{-- Table --}}
<div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">User</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Role</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Streak</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Score</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Plays</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Status</th>
                    <th class="text-right text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($users as $u)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-full bg-blue-600 text-white text-xs font-bold flex items-center justify-center flex-shrink-0">
                                    {{ strtoupper(substr($u->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">{{ $u->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $u->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs font-medium px-2 py-0.5 rounded {{ $u->role === 'admin' ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-gray-100 text-gray-600' }}">
                                {{ ucfirst($u->role) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">🔥 {{ $u->streak }} days</td>
                        <td class="px-4 py-3 text-sm font-semibold text-blue-700">{{ number_format($u->score) }} pts</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $u->quiz_attempts_count }}</td>
                        <td class="px-4 py-3">
                            @if($u->is_active)
                                <span class="inline-flex items-center gap-1 text-xs font-medium text-green-700 bg-green-50 border border-green-200 px-2 py-0.5 rounded">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Active
                                </span>
                            @else
                                <span class="text-xs font-medium text-red-700 bg-red-50 border border-red-200 px-2 py-0.5 rounded">Blocked</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="inline-flex items-center gap-2">
                                <a href="{{ route('admin.users.edit', $u) }}"
                                   class="text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 px-3 py-1 rounded transition-colors">
                                    Edit
                                </a>
                                @if(Auth::id() !== $u->id)
                                    <form action="{{ route('admin.users.destroy', $u) }}" method="POST"
                                          onsubmit="return confirm('Delete user {{ addslashes($u->name) }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-xs font-medium text-red-600 hover:bg-red-50 px-3 py-1 rounded transition-colors">
                                            Delete
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-gray-400">No users found.</td>
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

@endsection
