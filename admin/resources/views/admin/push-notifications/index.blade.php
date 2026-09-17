@extends('layouts.admin')

@use('Illuminate\Support\Carbon')

@section('title', 'Push Notifications')
@section('breadcrumb', 'Send and review push notifications')

@section('content')

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
    <div>
        <h2 class="text-lg font-bold text-gray-900">Push Notifications</h2>
        <p class="text-xs text-gray-400 mt-0.5">Server-driven notifications sent to the app</p>
    </div>
    <a href="{{ route('admin.push-notifications.create') }}"
       class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded transition-colors">
        New Notification
    </a>
</div>

<div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Title</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Audience</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Destination</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Recipients</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Sent By</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Sent At</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($notifications as $notification)
                    <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('admin.push-notifications.show', $notification) }}'">
                        <td class="px-4 py-3">
                            <p class="font-semibold text-gray-900">{{ $notification->title }}</p>
                            <p class="text-xs text-gray-400 truncate max-w-xs">{{ $notification->body }}</p>
                        </td>
                        <td class="px-4 py-3 capitalize">{{ $notification->target_type }}</td>
                        <td class="px-4 py-3 capitalize">{{ str_replace('_', ' ', $notification->destination_type) }}</td>
                        <td class="px-4 py-3">{{ $notification->recipients_count }}</td>
                        <td class="px-4 py-3">{{ $notification->creator?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-xs text-gray-400">{{ $notification->sent_at ? Carbon::parse($notification->sent_at)->format('M j, Y g:i A') : '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-gray-400">No notifications sent yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($notifications->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $notifications->links() }}
        </div>
    @endif
</div>

@endsection
