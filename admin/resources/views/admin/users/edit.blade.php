@extends('layouts.admin')

@section('title', 'Edit User')
@section('breadcrumb', 'Users / Edit')

@section('content')

<div class="max-w-2xl">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
        <h2 class="text-lg font-bold text-gray-900">Edit: {{ $user->name }}</h2>
        <a href="{{ route('admin.users.index') }}" class="text-sm text-gray-500 hover:text-gray-800">&larr; Back to users</a>
    </div>

    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <form action="{{ route('admin.users.update', $user) }}" method="POST" class="space-y-5">
            @csrf @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div>
                <label for="login_id" class="block text-sm font-medium text-gray-700 mb-1">
                    Login ID <span class="text-gray-400 font-normal">(optional, can be used instead of email to sign in)</span>
                </label>
                <input type="text" id="login_id" name="login_id" value="{{ old('login_id', $user->login_id) }}"
                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Role <span class="text-red-500">*</span></label>
                    <select id="role" name="role" required
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="user" {{ old('role', $user->role) === 'user' ? 'selected' : '' }}>User</option>
                        <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                </div>
                <div>
                    <label for="streak" class="block text-sm font-medium text-gray-700 mb-1">Streak (days)</label>
                    <input type="number" id="streak" name="streak" value="{{ old('streak', $user->streak) }}" min="0"
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="score" class="block text-sm font-medium text-gray-700 mb-1">Total Points</label>
                    <input type="number" id="score" name="score" value="{{ old('score', $user->score) }}" min="0"
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                    New Password <span class="text-gray-400 font-normal">(leave blank to keep current — sets it directly and clears the temp-password flag; use "Reset Password" from the list instead to email a temp password)</span>
                </label>
                <input type="password" id="password" name="password" placeholder="Enter new password..."
                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Daily Quiz Target</label>
                <label class="flex items-center gap-2 cursor-pointer mb-2">
                    <input type="checkbox" id="use_global_target"
                           {{ old('custom_daily_target', $user->custom_daily_target) ? '' : 'checked' }}
                           onchange="document.getElementById('custom_daily_target').disabled = this.checked"
                           class="w-4 h-4 border-gray-300 rounded text-blue-600 focus:ring-blue-500">
                    <span class="text-sm text-gray-700">Use Global Target ({{ $globalDailyTarget }})</span>
                </label>
                <input type="number" id="custom_daily_target" name="custom_daily_target" min="1" max="1000"
                       value="{{ old('custom_daily_target', $user->custom_daily_target) }}"
                       {{ old('custom_daily_target', $user->custom_daily_target) ? '' : 'disabled' }}
                       placeholder="Custom target for this user"
                       class="w-40 px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:bg-gray-100 disabled:text-gray-400">
            </div>

            <div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                           class="w-4 h-4 border-gray-300 rounded text-blue-600 focus:ring-blue-500">
                    <span class="text-sm font-medium text-gray-700">Account is active</span>
                </label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="{{ route('admin.users.index') }}" class="text-sm text-gray-600 hover:text-gray-900 px-4 py-2">Cancel</a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-5 py-2 rounded transition-colors">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
