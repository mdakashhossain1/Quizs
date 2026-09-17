@extends('layouts.admin')

@section('title', 'New Push Notification')
@section('breadcrumb', 'Push Notifications / New')

@section('content')

<div class="max-w-2xl">
    <div class="flex items-center justify-between mb-5">
        <h2 class="text-lg font-bold text-gray-900">New Push Notification</h2>
        <a href="{{ route('admin.push-notifications.index') }}" class="text-sm text-gray-500 hover:text-gray-800">&larr; Back to notifications</a>
    </div>

    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <form action="{{ route('admin.push-notifications.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                <input type="text" id="title" name="title" value="{{ old('title') }}" required maxlength="255"
                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label for="body" class="block text-sm font-medium text-gray-700 mb-1">Message <span class="text-red-500">*</span></label>
                <textarea id="body" name="body" rows="3" required maxlength="1000"
                          class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('body') }}</textarea>
            </div>

            <div>
                <label for="thumbnail" class="block text-sm font-medium text-gray-700 mb-1">
                    Thumbnail <span class="text-gray-400 font-normal">(optional image, max 2MB)</span>
                </label>
                <input type="file" id="thumbnail" name="thumbnail" accept="image/*"
                       class="w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-3 file:rounded file:border-0 file:bg-gray-100 file:text-sm file:font-medium hover:file:bg-gray-200">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Audience <span class="text-red-500">*</span></label>
                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-sm">
                        <input type="radio" name="audience" value="all" onchange="document.getElementById('user-picker').classList.add('hidden')"
                               {{ old('audience', 'all') === 'all' ? 'checked' : '' }}>
                        All Users
                    </label>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="radio" name="audience" value="specific" onchange="document.getElementById('user-picker').classList.remove('hidden')"
                               {{ old('audience') === 'specific' ? 'checked' : '' }}>
                        Specific Users
                    </label>
                </div>

                <div id="user-picker" class="{{ old('audience') === 'specific' ? '' : 'hidden' }} mt-3 border border-gray-200 rounded max-h-48 overflow-y-auto p-3 space-y-1.5">
                    @foreach($users as $user)
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="user_ids[]" value="{{ $user->id }}"
                                   {{ in_array($user->id, old('user_ids', [])) ? 'checked' : '' }}>
                            {{ $user->name }} <span class="text-gray-400 text-xs">{{ $user->email }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="grid grid-cols-2 gap-5">
                <div>
                    <label for="destination_type" class="block text-sm font-medium text-gray-700 mb-1">Destination <span class="text-red-500">*</span></label>
                    <select id="destination_type" name="destination_type" required
                            onchange="document.getElementById('quiz-picker').classList.toggle('hidden', this.value !== 'quiz_details')"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @foreach(['none' => 'None (open notifications)', 'attendance' => 'Attendance', 'quiz_details' => 'Quiz Details', 'target_progress' => 'Daily Target', 'achievement' => 'Achievement', 'profile' => 'Profile'] as $value => $label)
                            <option value="{{ $value }}" {{ old('destination_type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div id="quiz-picker" class="{{ old('destination_type') === 'quiz_details' ? '' : 'hidden' }}">
                    <label for="destination_id" class="block text-sm font-medium text-gray-700 mb-1">Quiz</label>
                    <select id="destination_id" name="destination_id"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @foreach($quizzes as $quiz)
                            <option value="{{ $quiz->id }}" {{ (string) old('destination_id') === (string) $quiz->id ? 'selected' : '' }}>{{ $quiz->title }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="{{ route('admin.push-notifications.index') }}" class="text-sm text-gray-600 hover:text-gray-900 px-4 py-2">Cancel</a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-5 py-2 rounded transition-colors">
                    Send Notification
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
