@extends('layouts.admin')

@section('title', 'Categories')
@section('breadcrumb', 'Manage quiz categories')

@section('content')

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
    <div>
        <h2 class="text-lg font-bold text-gray-900">Quiz Categories</h2>
        <p class="text-xs text-gray-400 mt-0.5">{{ $categories->total() }} total categories</p>
    </div>
    <a href="{{ route('admin.categories.create') }}"
       class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add Category
    </a>
</div>

<div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Image</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Color</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Name</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Slug</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Quizzes</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Order</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Status</th>
                    <th class="text-right text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($categories as $cat)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            @if($cat->image_url)
                                <img src="{{ $cat->image_url }}" alt="" class="w-10 h-10 object-cover rounded-lg border border-gray-200">
                            @else
                                <span class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-gray-100 text-gray-300 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-block w-5 h-5 rounded border border-gray-300" style="background-color: {{ $cat->color }};"></span>
                        </td>
                        <td class="px-4 py-3 font-semibold text-gray-900">{{ $cat->name }}</td>
                        <td class="px-4 py-3 text-gray-500 font-mono text-xs">{{ $cat->slug }}</td>
                        <td class="px-4 py-3">
                            <span class="text-xs font-medium text-blue-700 bg-blue-50 border border-blue-100 px-2 py-0.5 rounded">{{ $cat->quizzes_count }}</span>
                        </td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $cat->sort_order }}</td>
                        <td class="px-4 py-3">
                            @if($cat->is_active)
                                <span class="inline-flex items-center gap-1 text-xs font-medium text-green-700 bg-green-50 border border-green-200 px-2 py-0.5 rounded">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Active
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 text-xs font-medium text-gray-600 bg-gray-100 border border-gray-200 px-2 py-0.5 rounded">Hidden</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="inline-flex items-center gap-2">
                                <a href="{{ route('admin.categories.edit', $cat) }}"
                                   class="text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 px-3 py-1 rounded transition-colors">
                                    Edit
                                </a>
                                <form action="{{ route('admin.categories.destroy', $cat) }}" method="POST"
                                      onsubmit="return confirm('Delete this category?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        class="text-xs font-medium text-red-600 hover:bg-red-50 px-3 py-1 rounded transition-colors">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-10 text-center text-gray-400">No categories found. <a href="{{ route('admin.categories.create') }}" class="text-blue-600 hover:underline">Create one</a>.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($categories->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $categories->links() }}
        </div>
    @endif
</div>

@endsection
