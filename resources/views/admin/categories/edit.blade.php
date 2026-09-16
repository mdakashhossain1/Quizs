@extends('layouts.admin')

@section('title', 'Edit Category')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Edit Category: {{ $category->name }}</h1>
            <p class="text-xs text-slate-400 mt-0.5">Update category configuration</p>
        </div>
        <a href="{{ route('admin.categories.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">&larr; Back to list</a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-8">
        <form action="{{ route('admin.categories.update', $category) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Category Name *</label>
                <input type="text" id="name" name="name" value="{{ old('name', $category->name) }}" required
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">
            </div>

            <div>
                <label for="slug" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Slug</label>
                <input type="text" id="slug" name="slug" value="{{ old('slug', $category->slug) }}"
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="color" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Accent Color *</label>
                    <div class="flex items-center gap-3">
                        <input type="color" id="color" name="color" value="{{ old('color', $category->color) }}"
                               class="w-12 h-10 p-1 border border-slate-200 rounded-xl cursor-pointer">
                        <input type="text" value="{{ old('color', $category->color) }}" id="colorText" oninput="document.getElementById('color').value = this.value"
                               class="flex-1 px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono uppercase">
                    </div>
                </div>

                <div>
                    <label for="sort_order" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Sort Order</label>
                    <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', $category->sort_order) }}" required
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
            </div>

            <div>
                <label for="description" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Description</label>
                <textarea id="description" name="description" rows="3"
                          class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">{{ old('description', $category->description) }}</textarea>
            </div>

            <div class="pt-2">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }}
                           class="w-4 h-4 rounded text-purple-600 focus:ring-purple-500 border-slate-300">
                    <span class="text-sm font-semibold text-slate-700">Category is active & visible to mobile players</span>
                </label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('admin.categories.index') }}" class="px-5 py-2.5 text-xs font-semibold text-slate-600 hover:text-slate-900">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-semibold text-xs rounded-xl shadow-lg shadow-purple-600/30 transition-all cursor-pointer">
                    Update Category
                </button>
            </div>
        </form>
    </div>

</div>

<script>
    document.getElementById('color').addEventListener('input', function(e) {
        document.getElementById('colorText').value = e.target.value;
    });
</script>
@endsection
