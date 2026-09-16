<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Quiz;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class QuizController extends Controller
{
    public function index(Request $request): View
    {
        $query = Quiz::with('category')->withCount('questions');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $quizzes = $query->orderBy('sort_order')->latest()->paginate(15);
        $categories = Category::orderBy('name')->get();

        return view('admin.quizzes.index', compact('quizzes', 'categories'));
    }

    public function create(): View
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        return view('admin.quizzes.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:quizzes,slug'],
            'description' => ['nullable', 'string'],
            'duration_minutes' => ['required', 'integer', 'min:1'],
            'passing_percentage' => ['required', 'integer', 'min:1', 'max:100'],
            'difficulty' => ['required', 'in:easy,medium,hard'],
            'sort_order' => ['required', 'integer'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['slug'] = !empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['title']);
        $validated['is_active'] = $request->has('is_active');

        Quiz::create($validated);

        return redirect()->route('admin.quizzes.index')->with('success', 'Quiz created successfully.');
    }

    public function edit(Quiz $quiz): View
    {
        $categories = Category::orderBy('name')->get();
        return view('admin.quizzes.edit', compact('quiz', 'categories'));
    }

    public function update(Request $request, Quiz $quiz): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:quizzes,slug,' . $quiz->id],
            'description' => ['nullable', 'string'],
            'duration_minutes' => ['required', 'integer', 'min:1'],
            'passing_percentage' => ['required', 'integer', 'min:1', 'max:100'],
            'difficulty' => ['required', 'in:easy,medium,hard'],
            'sort_order' => ['required', 'integer'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['slug'] = !empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['title']);
        $validated['is_active'] = $request->has('is_active');

        $quiz->update($validated);

        return redirect()->route('admin.quizzes.index')->with('success', 'Quiz updated successfully.');
    }

    public function destroy(Quiz $quiz): RedirectResponse
    {
        $quiz->delete();
        return redirect()->route('admin.quizzes.index')->with('success', 'Quiz deleted successfully.');
    }
}
