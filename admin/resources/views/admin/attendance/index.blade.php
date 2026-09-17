@extends('layouts.admin')

@use('Illuminate\Support\Carbon')

@section('title', 'Attendance')
@section('breadcrumb', 'Mark daily attendance')

@section('content')

<div class="flex items-center justify-between mb-5">
    <div>
        <h2 class="text-lg font-bold text-gray-900">Attendance</h2>
        <p class="text-xs text-gray-400 mt-0.5">{{ Carbon::parse($date)->format('l, M j, Y') }}</p>
    </div>
</div>

{{-- Date + search filter --}}
<div class="bg-white border border-gray-200 rounded-lg p-3 mb-4">
    <form method="GET" action="{{ route('admin.attendance.index') }}" class="flex flex-wrap items-center gap-3">
        <input type="date" name="date" value="{{ $date }}"
               class="px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or email..."
               class="px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 w-56">
        <select name="status" class="px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Any Status (this date)</option>
            <option value="present" {{ request('status') === 'present' ? 'selected' : '' }}>Present</option>
            <option value="absent" {{ request('status') === 'absent' ? 'selected' : '' }}>Absent</option>
            <option value="leave" {{ request('status') === 'leave' ? 'selected' : '' }}>Leave</option>
            <option value="not_marked" {{ request('status') === 'not_marked' ? 'selected' : '' }}>Not Marked</option>
        </select>
        <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white text-sm font-medium px-4 py-2 rounded transition-colors">
            Go
        </button>
        @if(request('search') || request('status'))
            <a href="{{ route('admin.attendance.index', ['date' => $date]) }}" class="text-sm text-gray-500 hover:underline">Clear</a>
        @endif
    </form>
</div>

<form action="{{ route('admin.attendance.store') }}" method="POST">
    @csrf
    <input type="hidden" name="date" value="{{ $date }}">

    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">User</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Status</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Note</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($users as $u)
                        @php $current = $existing->get($u->id); @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <p class="font-semibold text-gray-900">{{ $u->name }}</p>
                                <p class="text-xs text-gray-400">{{ $u->email }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <select name="statuses[{{ $u->id }}]"
                                        class="px-3 py-1.5 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="" {{ !$current ? 'selected' : '' }}>Not marked</option>
                                    <option value="present" {{ $current?->status === 'present' ? 'selected' : '' }}>Present</option>
                                    <option value="absent" {{ $current?->status === 'absent' ? 'selected' : '' }}>Absent</option>
                                    <option value="leave" {{ $current?->status === 'leave' ? 'selected' : '' }}>Leave</option>
                                </select>
                            </td>
                            <td class="px-4 py-3">
                                <input type="text" name="notes[{{ $u->id }}]" value="{{ $current?->note }}" placeholder="Optional note"
                                       class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-10 text-center text-gray-400">No users found.</td>
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

        @if($users->isNotEmpty())
            <div class="px-4 py-3 border-t border-gray-100 flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-5 py-2 rounded transition-colors">
                    Save Attendance
                </button>
            </div>
        @endif
    </div>
</form>

@endsection
