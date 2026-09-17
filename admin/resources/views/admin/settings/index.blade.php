@extends('layouts.admin')

@section('title', 'Settings')
@section('breadcrumb', 'Manage your application settings and configurations')

@section('content')

@php
    $card = function (string $icon, string $iconBg, string $iconColor, string $title, string $description, string $route, string $linkLabel, ?bool $status = null) {
        return compact('icon', 'iconBg', 'iconColor', 'title', 'description', 'route', 'linkLabel', 'status');
    };

    $cards = [
        $card('gear', 'bg-blue-50', 'text-blue-600', 'General Settings', 'Configure the daily quiz target and other application-wide defaults.', 'admin.settings.general', 'Manage Settings'),
        $card('mail', 'bg-green-50', 'text-green-600', 'Email Configuration', 'Set up SMTP settings and send a live test email before saving.', 'admin.settings.email', 'Configure Email', $emailConfigured),
        $card('fire', 'bg-orange-50', 'text-orange-600', 'Firebase Credentials', 'Paste the Firebase service-account JSON used for push notifications.', 'admin.settings.firebase', 'Configure Firebase', $firebaseConfigured),
        $card('database', 'bg-indigo-50', 'text-indigo-600', 'Database Migrations', 'Run pending database migrations without shell/SSH access.', 'admin.settings.migrations', 'Run Migrations'),
    ];

    $icons = [
        'gear' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z##M15 12a3 3 0 11-6 0 3 3 0 016 0z',
        'mail' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
        'fire' => 'M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z##M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z',
        'database' => 'M4 7v10c0 1.657 3.582 3 8 3s8-1.343 8-3V7##M4 7c0 1.657 3.582 3 8 3s8-1.343 8-3M4 7c0-1.657 3.582-3 8-3s8 1.343 8 3',
    ];
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach($cards as $c)
        <div class="bg-white border border-gray-200 rounded-lg p-6 card-hover">
            <div class="flex items-start justify-between mb-4">
                <div class="w-11 h-11 {{ $c['iconBg'] }} rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 {{ $c['iconColor'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        @foreach(explode('##', $icons[$c['icon']]) as $path)
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $path }}"/>
                        @endforeach
                    </svg>
                </div>

                @if(! is_null($c['status']))
                    <span class="inline-flex items-center gap-1 text-[11px] font-medium px-2 py-0.5 rounded-full
                        {{ $c['status'] ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-gray-50 text-gray-500 border border-gray-200' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $c['status'] ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                        {{ $c['status'] ? 'Configured' : 'Not configured' }}
                    </span>
                @endif
            </div>

            <h3 class="text-sm font-bold text-gray-900 mb-1">{{ $c['title'] }}</h3>
            <p class="text-xs text-gray-500 mb-4 leading-relaxed">{{ $c['description'] }}</p>

            <a href="{{ route($c['route']) }}" class="inline-flex items-center gap-1 text-xs font-semibold {{ $c['iconColor'] }} hover:underline">
                {{ $c['linkLabel'] }}
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
    @endforeach
</div>

@endsection
