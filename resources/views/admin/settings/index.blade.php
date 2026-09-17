@extends('layouts.admin')

@section('title', 'Settings')
@section('breadcrumb', 'Application-wide configuration')

@section('content')

<div class="max-w-xl">
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <h2 class="text-sm font-bold text-gray-900 mb-1">Daily Quiz Target</h2>
        <p class="text-xs text-gray-400 mb-5">
            The default number of quizzes a user must successfully complete each day.
            Individual users can be given a custom target from their edit page, which overrides this value.
        </p>

        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-4">
            @csrf @method('PUT')

            <div>
                <label for="global_daily_quiz_target" class="block text-sm font-medium text-gray-700 mb-1">
                    Global Daily Target
                </label>
                <input type="number" id="global_daily_quiz_target" name="global_daily_quiz_target" min="1" max="1000"
                       value="{{ old('global_daily_quiz_target', $globalDailyTarget) }}" required
                       class="w-40 px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="flex items-center justify-end pt-4 border-t border-gray-100">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-5 py-2 rounded transition-colors">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
