@extends('layouts.admin')

@section('title', 'Database Migrations')
@section('breadcrumb', 'Run pending migrations without shell access')

@section('content')

<a href="{{ route('admin.settings.index') }}" class="inline-flex items-center gap-1 text-xs text-gray-500 hover:text-gray-700 mb-4">
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
    Back to Settings
</a>

<div class="max-w-2xl">
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <h2 class="text-sm font-bold text-gray-900 mb-1">Migration Status</h2>
        <p class="text-xs text-gray-400 mb-4">
            Output of <span class="font-mono">php artisan migrate:status</span>. Migrations already run are never re-run; this only applies new ones.
        </p>

        <pre class="bg-gray-900 text-gray-100 text-xs rounded-lg p-4 overflow-x-auto whitespace-pre-wrap">{{ $status }}</pre>

        @if(session('migration_output'))
            <div class="mt-4">
                <h3 class="text-xs font-bold text-gray-700 mb-1">Last run output</h3>
                <pre class="bg-gray-900 text-green-300 text-xs rounded-lg p-4 overflow-x-auto whitespace-pre-wrap">{{ session('migration_output') }}</pre>
            </div>
        @endif

        <form action="{{ route('admin.settings.migrations.run') }}" method="POST"
              onsubmit="return confirm('Run all pending migrations now? This changes the production database schema and cannot be undone by this button.');"
              class="flex items-center justify-end pt-4 mt-4 border-t border-gray-100">
            @csrf
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2 rounded transition-colors">
                Run Migrations
            </button>
        </form>
    </div>
</div>

@endsection
