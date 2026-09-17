@extends('layouts.admin')

@section('title', 'Edit Category')
@section('breadcrumb', 'Categories / Edit')

@section('content')

<div class="max-w-2xl">
    <div class="flex items-center justify-between mb-5">
        <h2 class="text-lg font-bold text-gray-900">Edit: {{ $category->name }}</h2>
        <a href="{{ route('admin.categories.index') }}" class="text-sm text-gray-500 hover:text-gray-800">&larr; Back to categories</a>
    </div>

    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <form action="{{ route('admin.categories.update', $category) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf @method('PUT')

            <div>
                <label for="image" class="block text-sm font-medium text-gray-700 mb-1">
                    Category Image <span class="text-gray-400 font-normal">(optional, max 2MB)</span>
                </label>
                <img id="image-preview" src="{{ $category->image_url }}"
                     class="w-20 h-20 object-cover rounded-lg border border-gray-200 mb-2 {{ $category->image_url ? '' : 'hidden' }}">
                <input type="file" id="image" name="image" accept="image/*" onchange="quizsPreviewCategoryImage(this)"
                       class="w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-3 file:rounded file:border-0 file:bg-gray-100 file:text-sm file:font-medium hover:file:bg-gray-200">
                @if($category->image_url)
                    <label class="flex items-center gap-2 mt-2 cursor-pointer">
                        <input type="checkbox" name="remove_image" value="1" class="w-4 h-4 border-gray-300 rounded text-red-600 focus:ring-red-500">
                        <span class="text-sm text-red-600">Remove current image</span>
                    </label>
                @endif
            </div>

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Category Name <span class="text-red-500">*</span></label>
                <input type="text" id="name" name="name" value="{{ old('name', $category->name) }}" required
                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                <input type="text" id="slug" name="slug" value="{{ old('slug', $category->slug) }}"
                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono">
            </div>

            <div class="grid grid-cols-2 gap-5">
                <div>
                    <label for="color" class="block text-sm font-medium text-gray-700 mb-1">Accent Color</label>
                    <div class="flex items-center gap-2">
                        <input type="color" id="color" name="color" value="{{ old('color', $category->color) }}"
                               class="w-10 h-9 p-0.5 border border-gray-300 rounded cursor-pointer">
                        <input type="text" id="colorText" value="{{ old('color', $category->color) }}"
                               oninput="document.getElementById('color').value=this.value"
                               class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono uppercase">
                    </div>
                </div>
                <div>
                    <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                    <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', $category->sort_order) }}"
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea id="description" name="description" rows="3"
                          class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description', $category->description) }}</textarea>
            </div>

            <div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }}
                           class="w-4 h-4 border-gray-300 rounded text-blue-600 focus:ring-blue-500">
                    <span class="text-sm font-medium text-gray-700">Active & visible to mobile players</span>
                </label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="{{ route('admin.categories.index') }}" class="text-sm text-gray-600 hover:text-gray-900 px-4 py-2">Cancel</a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-5 py-2 rounded transition-colors">
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

    function quizsPreviewCategoryImage(input) {
        const preview = document.getElementById('image-preview');
        if (!input.files || !input.files[0]) return;
        preview.src = URL.createObjectURL(input.files[0]);
        preview.classList.remove('hidden');
    }
</script>
@endsection
