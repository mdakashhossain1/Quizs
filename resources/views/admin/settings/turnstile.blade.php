@extends('layouts.admin')

@section('title', 'Login Captcha')
@section('breadcrumb', 'Cloudflare Turnstile credentials for the admin login form')

@section('content')

<a href="{{ route('admin.settings.index') }}" class="inline-flex items-center gap-1 text-xs text-gray-500 hover:text-gray-700 mb-4">
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
    Back to Settings
</a>

<div class="max-w-xl">
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <div class="flex items-center justify-between mb-1">
            <h2 class="text-sm font-bold text-gray-900">Cloudflare Turnstile</h2>
            <span class="inline-flex items-center gap-1 text-[11px] font-medium px-2 py-0.5 rounded-full
                {{ $usingTestKeys ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-green-50 text-green-700 border border-green-200' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ $usingTestKeys ? 'bg-amber-500' : 'bg-green-500' }}"></span>
                {{ $usingTestKeys ? 'Using test keys' : 'Live keys configured' }}
            </span>
        </div>

        <p class="text-xs text-gray-400 mb-5">
            @if($usingTestKeys)
                Currently using Cloudflare's public test keys — these always pass and provide no real bot protection.
                Get real keys from your
                <a href="https://dash.cloudflare.com/?to=/:account/turnstile" target="_blank" rel="noopener" class="text-blue-600 hover:underline">Turnstile dashboard</a>.
            @else
                Live keys are configured. Pasting new ones below replaces them.
            @endif
        </p>

        <form action="{{ route('admin.settings.turnstile.update') }}" method="POST" class="space-y-4">
            @csrf @method('PUT')

            <div class="flex items-center justify-between border border-gray-200 rounded p-3">
                <div>
                    <label for="enabled" class="text-sm font-medium text-gray-700 cursor-pointer">Require captcha on login</label>
                    <p class="text-[11px] text-gray-400 mt-0.5">When off, the widget is hidden and no verification runs.</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="enabled" name="enabled" value="1" {{ $enabled ? 'checked' : '' }} class="sr-only peer">
                    <div class="w-10 h-5.5 bg-gray-200 peer-checked:bg-blue-600 rounded-full transition-colors relative
                        after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-4.5 after:w-4.5 after:transition-transform peer-checked:after:translate-x-4.5"></div>
                </label>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Site Key</label>
                <input type="text" name="site_key" value="{{ old('site_key', $siteKey) }}" required
                       class="w-full px-3 py-2 text-xs font-mono border @error('site_key') border-red-400 @else border-gray-300 @enderror rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('site_key')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Secret Key</label>
                <input type="password" name="secret_key" placeholder="Leave blank to keep the current secret" autocomplete="off"
                       class="w-full px-3 py-2 text-xs font-mono border @error('secret_key') border-red-400 @else border-gray-300 @enderror rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('secret_key')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end pt-4 border-t border-gray-100">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-5 py-2 rounded transition-colors">
                    Save
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
