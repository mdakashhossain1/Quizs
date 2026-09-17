@extends('layouts.admin')

@use('Illuminate\Support\Carbon')

@section('title', 'Notification Details')
@section('breadcrumb', 'Push Notifications / Details')

@section('content')

<div class="max-w-3xl">
    <div class="flex items-center justify-between mb-5">
        <h2 class="text-lg font-bold text-gray-900">{{ $pushNotification->title }}</h2>
        <a href="{{ route('admin.push-notifications.index') }}" class="text-sm text-gray-500 hover:text-gray-800">&larr; Back to notifications</a>
    </div>

    <div class="bg-white border border-gray-200 rounded-lg p-6 space-y-4">
        @if($pushNotification->thumbnail_url)
            <img src="{{ $pushNotification->thumbnail_url }}" alt="" class="w-full max-w-sm rounded-lg border border-gray-200">
        @endif

        <p class="text-sm text-gray-700">{{ $pushNotification->body }}</p>

        <div class="grid grid-cols-2 gap-4 text-sm border-t border-gray-100 pt-4">
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wide">Audience</p>
                <p class="font-medium text-gray-900 capitalize">{{ $pushNotification->target_type }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wide">Destination</p>
                <p class="font-medium text-gray-900 capitalize">{{ str_replace('_', ' ', $pushNotification->destination_type) }}{{ $pushNotification->destination_id ? " (#{$pushNotification->destination_id})" : '' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wide">Sent By</p>
                <p class="font-medium text-gray-900">{{ $pushNotification->creator?->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wide">Sent At</p>
                <p class="font-medium text-gray-900">{{ $pushNotification->sent_at ? Carbon::parse($pushNotification->sent_at)->format('M j, Y g:i A') : '—' }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden mt-5">
        <div class="px-4 py-3 border-b border-gray-200">
            <h3 class="text-sm font-semibold text-gray-900">Recipients ({{ $pushNotification->recipients->count() }})</h3>
        </div>
        <div class="overflow-x-auto max-h-96 overflow-y-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200 sticky top-0">
                    <tr>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-2">User</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-2">Read</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($pushNotification->recipients as $recipient)
                        <tr>
                            <td class="px-4 py-2">
                                <p class="text-gray-900">{{ $recipient->user?->name ?? 'Deleted user' }}</p>
                                <p class="text-xs text-gray-400">{{ $recipient->user?->email }}</p>
                            </td>
                            <td class="px-4 py-2 text-xs">
                                {{ $recipient->read_at ? Carbon::parse($recipient->read_at)->format('M j, g:i A') : 'Unread' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-4 py-8 text-center text-gray-400">No recipients.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
