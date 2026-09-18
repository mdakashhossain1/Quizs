@extends('layouts.admin')

@section('title', 'Users')
@section('breadcrumb', 'Manage registered players and admins')

@section('content')

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
    <div>
        <h2 class="text-lg font-bold text-gray-900">Users</h2>
        <p class="text-xs text-gray-400 mt-0.5" id="ajax-count-label">{{ $users->total() }} total users</p>
    </div>
    <a href="{{ route('admin.users.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded transition-colors">
        + Add User
    </a>
</div>

{{-- Filter --}}
<div class="bg-white border border-gray-200 rounded-lg p-3 mb-4">
    <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap items-center gap-3" data-ajax-filter>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or email..."
               class="px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 w-full sm:w-64">
        <select name="role" class="px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All Roles</option>
            <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
            <option value="user" {{ request('role') === 'user' ? 'selected' : '' }}>User</option>
        </select>
        <select name="online_status" class="px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Online or Offline</option>
            <option value="online" {{ request('online_status') === 'online' ? 'selected' : '' }}>Online</option>
            <option value="offline" {{ request('online_status') === 'offline' ? 'selected' : '' }}>Offline</option>
        </select>
        <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white text-sm font-medium px-4 py-2 rounded transition-colors">
            Filter
        </button>
        @if(request('search') || request('role') || request('online_status'))
            <a href="{{ route('admin.users.index') }}" class="text-sm text-gray-500 hover:underline">Clear</a>
        @endif
    </form>
</div>

{{-- Table --}}
@include('admin.users._table')

@endsection
