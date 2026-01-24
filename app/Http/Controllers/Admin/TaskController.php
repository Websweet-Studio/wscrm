<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Task;
use App\Models\TaskCategory;
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

        $scope = $request->get('scope'); // all | assigned | created

        $query = Task::with(['assignedUser.employee', 'creator', 'category'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('category'), fn ($q) => $q->where('task_category_id', $request->category))
            ->when($request->filled('assigned_department'), fn ($q) => $q->where('assigned_department', $request->assigned_department))
            ->when($request->filled('assigned_user_id'), fn ($q) => $q->where('assigned_user_id', $request->assigned_user_id))
            ->orderBy('due_date', 'asc')
            ->orderBy('created_at', 'desc');

        $perPage = 10;
        if ($request->get('view_mode') === 'calendar') {
            $date = $request->get('calendar_date') ? \Carbon\Carbon::parse($request->get('calendar_date')) : now();
            $start = $date->copy()->startOfMonth()->startOfWeek();
            $end = $date->copy()->endOfMonth()->endOfWeek();
            
            $query->whereBetween('due_date', [$start, $end]);
            $perPage = 500;
        }

        // Scope selection
        if ($scope === 'created') {
            $query->where('created_by_user_id', $userId);
        } elseif ($scope === 'all') {
            // show all tasks (no default user filter)
        } else {
            // Default: show tasks assigned to current user
            if (! $request->filled('assigned_user_id') && ! $request->filled('assigned_department')) {
                $query->where('assigned_user_id', $userId);
            }
        }

        $tasks = $query->paginate($perPage);

        $departments = Employee::distinct()->pluck('department')->filter()->values();
        $categories = TaskCategory::orderBy('name')->get();
        $users = User::orderBy('name')->get(['id', 'name', 'email']);
        $userDepartments = Employee::select('user_id', 'department')
            ->whereNotNull('department')
            ->get()
            ->pluck('department', 'user_id');

        $editingTask = null;
        if ($request->filled('edit')) {
            $editingTask = Task::find($request->edit);
        }

        return Inertia::render('Admin/Tasks/Index', [
            'tasks' => $tasks,
            'departments' => $departments,
            'categories' => $categories,
            'users' => $users,
            'userDepartments' => $userDepartments,
            'filters' => $request->only(['status', 'category', 'assigned_user_id', 'assigned_department', 'view_mode', 'calendar_date', 'scope']),
            'editingTask' => $editingTask,
        ]);
    }

    public function store(Request $request)
    {
        $this->checkAdmin();
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'task_category_id' => 'nullable|exists:task_categories,id',
            'description' => 'nullable|string|max:5000',
            'status' => 'nullable|in:todo,in_progress,done,cancelled',
            'priority' => 'nullable|in:low,medium,high',
            'due_date' => 'nullable|date',
            'assigned_user_id' => 'nullable|exists:users,id',
            'assigned_department' => 'nullable|string|max:255',
            'qc_results' => 'nullable|array',
        ]);

        $data = array_merge([
            'status' => 'todo',
            'priority' => 'medium',
        ], $validated);

        $data['created_by_user_id'] = auth()->id();

        if (empty($data['assigned_user_id']) && empty($data['assigned_department'])) {
            $data['assigned_user_id'] = auth()->id();
        }
        
        Task::create($data);

        return redirect()->back()->with('success', 'Task created successfully.');
    }

    public function update(Request $request, Task $task)
    {
        $this->checkAdmin();
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'task_category_id' => 'nullable|exists:task_categories,id',
            'description' => 'nullable|string|max:5000',
            'status' => 'nullable|in:todo,in_progress,done,cancelled',
            'priority' => 'nullable|in:low,medium,high',
            'due_date' => 'nullable|date',
            'assigned_user_id' => 'nullable|exists:users,id',
            'assigned_department' => 'nullable|string|max:255',
            'qc_results' => 'nullable|array',
        ]);

        // Check QC requirements if status is being set to done
        if (isset($validated['status']) && $validated['status'] === 'done') {
            $task->load('category');
            if ($task->category && !empty($task->category->qc_checklist)) {
                $totalQc = count($task->category->qc_checklist);
                $checkedQc = isset($validated['qc_results']) ? count($validated['qc_results']) : 0;
                
                // If only partial update, merge with existing qc_results if not provided?
                // Actually the form sends the full array, so validated['qc_results'] should be authoritative.
                // However, if the request doesn't include qc_results but includes status=done, we should check existing qc_results?
                // In a typical form submission, all fields are sent. But if it's a patch...
                // Let's assume the form sends qc_results if it's being updated.
                // If qc_results is not in validated (e.g. not sent), use existing.
                
                if (!array_key_exists('qc_results', $validated)) {
                     $checkedQc = $task->qc_results ? count($task->qc_results) : 0;
                }

                $percentage = ($totalQc > 0) ? ($checkedQc / $totalQc) * 100 : 100;

                if ($percentage < 70) {
                    return redirect()->back()->withErrors(['status' => 'QC must be at least 70% complete to mark as done. Current: ' . round($percentage) . '%']);
                }
            }
        }

        $task->update($validated);

        return redirect()->back()->with('success', 'Task updated successfully.');
    }

    public function destroy(Task $task)
    {
        $this->checkAdmin();
        $task->delete();
        return redirect()->route('admin.tasks.index')->with('success', 'Task berhasil dihapus.');
    }
}
