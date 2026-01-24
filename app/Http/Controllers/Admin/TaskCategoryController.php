<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TaskCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TaskCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = TaskCategory::withCount('tasks')->get();
        return Inertia::render('Admin/TaskCategories/Index', [
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:task_categories,name',
            'color' => 'nullable|string|max:7',
            'description' => 'nullable|string',
        ]);

        TaskCategory::create($validated);

        return redirect()->back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TaskCategory $taskCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:task_categories,name,' . $taskCategory->id,
            'color' => 'nullable|string|max:7',
            'description' => 'nullable|string',
        ]);

        $taskCategory->update($validated);

        return redirect()->back()->with('success', 'Kategori berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TaskCategory $taskCategory)
    {
        $taskCategory->delete();

        return redirect()->back()->with('success', 'Kategori berhasil dihapus.');
    }
}
