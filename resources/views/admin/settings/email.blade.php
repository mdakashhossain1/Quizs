@extends('layouts.admin')

@section('title', 'Email Configuration')
@section('breadcrumb', 'SMTP settings and delivery testing')

@section('content')

<a href="{{ route('admin.settings.index') }}" class="inline-flex items-center gap-1 text-xs text-gray-500 hover:text-gray-700 mb-4">
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
    Back to Settings
</a>

<div class="max-w-xl">
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <div class="flex items-center justify-between mb-1">
            <h2 class="text-sm font-bold text-gray-900">SMTP Settings</h2>
            <span class="inline-flex items-center gap-1 text-[11px] font-medium px-2 py-0.5 rounded-full
                {{ $configured ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-gray-50 text-gray-500 border border-gray-200' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ $configured ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                {{ $configured ? 'Configured' : 'Not configured' }}
            </span>
        </div>
        <p class="text-xs text-gray-400 mb-5">
            Used for OTP verification, welcome emails, account credentials, and daily-target notifications.
            The password field is left blank here — it's never sent back to the browser — so it's only overwritten when you type a new one.
        </p>

        <form action="{{ route('admin.settings.email.update') }}" method="POST" class="space-y-4" id="email-form">
            @csrf @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">SMTP Host</label>
                    <input type="text" name="mail_host" value="{{ old('mail_host', $host) }}" required placeholder="smtp.gmail.com"
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Port</label>
                    <input type="number" name="mail_port" value="{{ old('mail_port', $port) }}" required placeholder="465"
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                <input type="text" name="mail_username" value="{{ old('mail_username', $username) }}" required
                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" name="mail_password" placeholder="{{ $configured ? '•••••••••••• (unchanged)' : '' }}" autocomplete="new-password"
                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">From Address</label>
                    <input type="email" name="mail_from_address" value="{{ old('mail_from_address', $fromAddress) }}" required
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">From Name</label>
                    <input type="text" name="mail_from_name" value="{{ old('mail_from_name', $fromName) }}" required
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100">
                <label class="block text-sm font-medium text-gray-700 mb-1">Send a test email to</label>
                <div class="flex gap-2">
                    <input type="email" name="test_recipient" form="test-form" placeholder="you@example.com" required
                           class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <button type="submit" form="test-form" class="bg-white hover:bg-gray-50 border border-gray-300 text-gray-700 text-sm font-medium px-4 py-2 rounded transition-colors whitespace-nowrap">
                        Send Test
                    </button>
                </div>
                <p class="text-[11px] text-gray-400 mt-1">Tests the values currently in this form — nothing is saved by this button.</p>
            </div>

            <div class="flex items-center justify-end pt-4 border-t border-gray-100">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-5 py-2 rounded transition-colors">
                    Save Changes
                </button>
            </div>
        </form>

        {{-- Separate form (same fields, mirrored via JS) so "Send Test" can post
             to a different route without nesting a second <form>. --}}
        <form action="{{ route('admin.settings.email.test') }}" method="POST" id="test-form">
            @csrf
        </form>
    </div>
</div>

<script>
    // Mirror the main form's fields into the hidden test-form on submit, so
    // "Send Test" always uses exactly what's currently typed.
    document.getElementById('test-form').addEventListener('submit', function () {
        const mainForm = document.getElementById('email-form');
        ['mail_host', 'mail_port', 'mail_username', 'mail_password', 'mail_from_address', 'mail_from_name'].forEach(function (name) {
            const source = mainForm.querySelector(`[name="${name}"]`);
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = name;
            hidden.value = source.value;
            this.appendChild(hidden);
        }, this);
    });
</script>

@endsection
