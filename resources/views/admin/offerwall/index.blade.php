@extends('layouts.admin')

@section('title', 'Offerwall')
@section('breadcrumb', 'Offer completion activity')

@section('content')
<div class="mb-5">
    <h2 class="text-lg font-bold text-gray-900">Offerwall</h2>
    <p class="text-sm text-gray-500 mt-1">Provider-confirmed completions. Dates are shown in {{ config('app.timezone') }}.</p>
</div>

<form method="GET" action="{{ route('admin.offerwall.index') }}" class="bg-white border border-gray-200 rounded-lg p-4 mb-4 flex flex-wrap items-end gap-3">
    <label class="text-sm text-gray-600">User
        <input type="search" name="search" value="{{ request('search') }}" placeholder="Name, email or login ID" class="block mt-1 px-3 py-2 border border-gray-300 rounded w-full sm:w-56">
    </label>
    <label class="text-sm text-gray-600">User ID
        <input type="number" min="1" name="user_id" value="{{ request('user_id') }}" class="block mt-1 px-3 py-2 border border-gray-300 rounded w-28">
    </label>
    <label class="text-sm text-gray-600">From
        <input type="date" name="from" value="{{ request('from') }}" class="block mt-1 px-3 py-2 border border-gray-300 rounded">
    </label>
    <label class="text-sm text-gray-600">To
        <input type="date" name="to" value="{{ request('to') }}" class="block mt-1 px-3 py-2 border border-gray-300 rounded">
    </label>
    <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white text-sm font-medium px-4 py-2 rounded">Filter</button>
    <a href="{{ route('admin.offerwall.index') }}" class="text-sm text-gray-500 hover:underline py-2">Clear</a>
</form>

<div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
    <div class="px-4 py-3 text-sm text-gray-500 border-b border-gray-100">{{ number_format($transactions->total()) }} records</div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 border-b border-gray-200 text-gray-500">
                <tr>
                    <th class="px-4 py-3">User</th>
                    <th class="px-4 py-3">Offer</th>
                    <th class="px-4 py-3">Provider / transaction</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Completed at</th>
                    <th class="px-4 py-3">Received at</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($transactions as $transaction)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <a class="font-semibold text-blue-600 hover:underline" href="{{ route('admin.offerwall.index', array_merge(request()->except('page', 'search'), ['user_id' => $transaction->user_id])) }}">{{ $transaction->user->name }} #{{ $transaction->user_id }}</a>
                            <p class="text-xs text-gray-500">{{ $transaction->user->email }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <p>{{ $transaction->offer_name ?? 'Not supplied' }}</p>
                            <p class="text-xs text-gray-500">{{ $transaction->offer_id }}</p>
                        </td>
                        <td class="px-4 py-3 break-all">
                            <p>{{ $transaction->provider }}</p>
                            <p class="text-xs text-gray-500">{{ $transaction->transaction_id }}</p>
                        </td>
                        <td class="px-4 py-3">{{ ucfirst($transaction->status) }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            {{ $transaction->completed_at?->format('d M Y H:i:s') ?? 'Not supplied' }}
                            @if(($transaction->provider_payload['time_source'] ?? null) === 'received_at')
                                <p class="text-xs text-gray-500">Time first received</p>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $transaction->created_at->format('d M Y H:i:s') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-12 text-center text-gray-500">No Offerwall records match these filters.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($transactions->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $transactions->links() }}</div>
    @endif
</div>
@endsection
