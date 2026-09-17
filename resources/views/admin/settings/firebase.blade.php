@extends('layouts.admin')

@section('title', 'Firebase Credentials')
@section('breadcrumb', 'Service-account JSON for push notifications')

@section('content')

<a href="{{ route('admin.settings.index') }}" class="inline-flex items-center gap-1 text-xs text-gray-500 hover:text-gray-700 mb-4">
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
    Back to Settings
</a>

<div class="max-w-xl">
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <div class="flex items-center justify-between mb-1">
            <h2 class="text-sm font-bold text-gray-900">Firebase Service Account</h2>
            <span class="inline-flex items-center gap-1 text-[11px] font-medium px-2 py-0.5 rounded-full
                {{ $configured ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-gray-50 text-gray-500 border border-gray-200' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ $configured ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                {{ $configured ? 'Configured' : 'Not configured' }}
            </span>
        </div>

        @if($configured)
            <p class="text-xs text-gray-500 mb-5">
                Currently using project <span class="font-mono font-semibold text-gray-700">{{ $projectId }}</span>.
                Pasting a new key below replaces it.
            </p>
        @else
            <p class="text-xs text-gray-400 mb-5">
                Push notifications log instead of sending until this is set.
                Open Firebase Console → Project Settings → Service Accounts → Generate New Private Key,
                then paste the full downloaded JSON file below.
            </p>
        @endif

        <form action="{{ route('admin.settings.firebase.update') }}" method="POST" class="space-y-4">
            @csrf @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Service Account JSON</label>
                <textarea name="credentials_json" rows="10" required placeholder='{"type": "service_account", "project_id": "...", ...}'
                          class="w-full px-3 py-2 text-xs font-mono border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('credentials_json') }}</textarea>
                <p class="text-[11px] text-gray-400 mt-1">
                    Verified against Google's OAuth2 endpoint before it's saved — nothing is written if the key doesn't actually work.
                </p>
            </div>

            <div class="flex items-center justify-end pt-4 border-t border-gray-100">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-5 py-2 rounded transition-colors">
                    Verify &amp; Save
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
