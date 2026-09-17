@extends('layouts.admin')

@section('title', 'Add Category')
@section('breadcrumb', 'Categories / Create')

@section('content')

<div class="max-w-2xl">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
        <h2 class="text-lg font-bold text-gray-900">Create Category</h2>
        <a href="{{ route('admin.categories.index') }}" class="text-sm text-gray-500 hover:text-gray-800">&larr; Back to categories</a>
    </div>

    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <div>
                <label for="image" class="block text-sm font-medium text-gray-700 mb-1">
                    Category Image <span class="text-gray-400 font-normal">(optional, max 2MB)</span>
                </label>
                <img id="image-preview" class="hidden w-20 h-20 object-cover rounded-lg border border-gray-200 mb-2">
                <input type="file" id="image" name="image" accept="image/*" onchange="quizsPreviewCategoryImage(this)"
                       class="w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-3 file:rounded file:border-0 file:bg-gray-100 file:text-sm file:font-medium hover:file:bg-gray-200">
            </div>

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Category Name <span class="text-red-500">*</span></label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">Slug <span class="text-gray-400 font-normal">(auto-generated if empty)</span></label>
                <input type="text" id="slug" name="slug" value="{{ old('slug') }}" placeholder="e.g. science-nature"
                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="color" class="block text-sm font-medium text-gray-700 mb-1">Accent Color</label>
                    <div class="flex items-center gap-2">
                        <input type="color" id="color" name="color" value="{{ old('color', '#2563eb') }}"
                               class="w-10 h-9 p-0.5 border border-gray-300 rounded cursor-pointer">
                        <input type="text" id="colorText" value="{{ old('color', '#2563eb') }}"
                               oninput="document.getElementById('color').value=this.value"
                               class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono uppercase">
                    </div>
                </div>
                <div>
                    <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                    <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}"
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea id="description" name="description" rows="3"
                          class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('description') }}</textarea>
            </div>

            <div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                           class="w-4 h-4 border-gray-300 rounded text-blue-600 focus:ring-blue-500">
                    <span class="text-sm font-medium text-gray-700">Active & visible to mobile players</span>
                </label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="{{ route('admin.categories.index') }}" class="text-sm text-gray-600 hover:text-gray-900 px-4 py-2">Cancel</a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-5 py-2 rounded transition-colors">
                    Save Category
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById('color').addEventListener('input', function(e) {
        document.getElementById('colorText').value = e.target.value;
    });

    function quizsPreviewCategoryImage(input) {
        const preview = document.getElementById('image-preview');
        if (!input.files || !input.files[0]) {
            preview.classList.add('hidden');
            return;
        }
        preview.src = URL.createObjectURL(input.files[0]);
        preview.classList.remove('hidden');
    }
</script>
@endsection
