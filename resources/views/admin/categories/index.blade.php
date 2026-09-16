@extends('layouts.admin')

@section('title', 'Categories Management')

@section('content')
<div class="space-y-6">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Quiz Categories</h1>
            <p class="text-xs text-slate-400 mt-1">Organize quizzes by scientific and general knowledge subjects</p>
        </div>
        <a href="{{ route('admin.categories.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-semibold text-xs rounded-xl shadow-lg shadow-purple-600/30 transition-all self-start">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add New Category
        </a>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-[11px] font-bold uppercase text-slate-400 bg-slate-50/75">
                        <th class="py-3 px-6">Color</th>
                        <th class="py-3 px-6">Name & Slug</th>
                        <th class="py-3 px-6">Quizzes</th>
                        <th class="py-3 px-6">Order</th>
                        <th class="py-3 px-6">Status</th>
                        <th class="py-3 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($categories as $cat)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-6">
                                <span class="w-5 h-5 rounded-full inline-block shadow-sm border border-black/10" style="background-color: {{ $cat->color }};"></span>
                            </td>
                            <td class="py-3.5 px-6">
                                <p class="font-bold text-slate-900">{{ $cat->name }}</p>
                                <p class="text-xs text-slate-400 font-mono">{{ $cat->slug }}</p>
                            </td>
                            <td class="py-3.5 px-6">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-purple-50 text-purple-700">
                                    {{ $cat->quizzes_count }} Quizzes
                                </span>
                            </td>
                            <td class="py-3.5 px-6 text-xs text-slate-500 font-mono">{{ $cat->sort_order }}</td>
                            <td class="py-3.5 px-6">
                                @if($cat->is_active)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700">Active</span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">Hidden</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-6 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('admin.categories.edit', $cat) }}" class="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition-colors">
                                        Edit
                                    </a>
                                    <form action="{{ route('admin.categories.destroy', $cat) }}" method="POST" onsubmit="return confirm('Delete this category? Associated quizzes will also be deleted.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2.5 py-1 text-rose-600 hover:bg-rose-50 rounded-lg text-xs font-semibold transition-colors">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">No categories created yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($categories->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $categories->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
