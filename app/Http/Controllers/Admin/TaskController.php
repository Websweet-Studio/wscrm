<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    private function checkAdmin()
    {
        if (! auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access.');
        }
    }

    public function index(Request $request): Response
    {
        $this->checkAdmin();
        $userId = auth()->id();

        $query = Task::with(['assignedUser', 'creator'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('assigned_department'), fn ($q) => $q->where('assigned_department', $request->assigned_department))
            ->when($request->filled('assigned_user_id'), fn ($q) => $q->where('assigned_user_id', $request->assigned_user_id))
            ->orderBy('due_date', 'asc')
            ->orderBy('created_at', 'desc');

        // Default: show my tasks (assigned to me or created by me)
        if (! $request->filled('assigned_user_id') && ! $request->filled('assigned_department')) {
            $query->my($userId);
        }

        $tasks = $query->paginate(10);

        $departments = Employee::distinct()->pluck('department')->filter()->values();
        $users = User::orderBy('name')->get(['id', 'name', 'email']);

        return Inertia::render('Admin/Tasks/Index', [
            'tasks' => $tasks,
            'departments' => $departments,
            'users' => $users,
            'filters' => $request->only(['status', 'assigned_user_id', 'assigned_department']),
        ]);
    }

    public function store(Request $request)
    {
        $this->checkAdmin();
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'status' => 'nullable|in:todo,in_progress,done,cancelled',
            'priority' => 'nullable|in:low,medium,high',
            'due_date' => 'nullable|date',
            'assigned_user_id' => 'nullable|exists:users,id',
            'assigned_department' => 'nullable|string|max:255',
        ]);

        $data = array_merge([
            'status' => 'todo',
            'priority' => 'medium',
            'created_by_user_id' => auth()->id(),
        ], $validated);

        if (empty($data['assigned_user_id']) && empty($data['assigned_department'])) {
            $data['assigned_user_id'] = auth()->id();
        }

        Task::create($data);

        return redirect()->route('admin.tasks.index')->with('success', 'Task berhasil dibuat.');
    }

    public function update(Request $request, Task $task)
    {
        $this->checkAdmin();
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:5000',
            'status' => 'nullable|in:todo,in_progress,done,cancelled',
            'priority' => 'nullable|in:low,medium,high',
            'due_date' => 'nullable|date',
            'assigned_user_id' => 'nullable|exists:users,id',
            'assigned_department' => 'nullable|string|max:255',
        ]);

        $task->update($validated);

        return redirect()->route('admin.tasks.index')->with('success', 'Task berhasil diperbarui.');
    }

    public function destroy(Task $task)
    {
        $this->checkAdmin();
        $task->delete();
        return redirect()->route('admin.tasks.index')->with('success', 'Task berhasil dihapus.');
    }
}

