<?php

namespace App\Services\AiAgents;

use App\Models\Task;
use App\Models\TaskCategory;
use App\Models\User;

/**
 * Sub-agent: manajemen tugas (tasks) — daftar, buat, ubah status.
 */
class TaskAgent
{
  public function listTasks(?string $status = null, ?int $categoryId = null, ?int $assignedUserId = null, ?callable $onEvent = null): array
  {
    if ($onEvent) {
      $onEvent('Menarik daftar tugas...', 'loading', 'Task Agent');
    }

    $query = Task::with(['assignedUser.employee', 'category'])
      ->when($status, fn($q) => $q->where('status', $status))
      ->when($categoryId, fn($q) => $q->where('task_category_id', $categoryId))
      ->when($assignedUserId, fn($q) => $q->where('assigned_user_id', $assignedUserId))
      ->orderBy('due_date')
      ->orderBy('created_at', 'desc')
      ->limit(50)
      ->get();

    $statusLabels = [
      'todo' => 'Belum dikerjakan',
      'in_progress' => 'Sedang dikerjakan',
      'done' => 'Selesai',
      'cancelled' => 'Dibatalkan',
    ];

    $list = $query->map(fn(Task $t) => [
      'id' => $t->id,
      'title' => $t->title,
      'status' => $t->status,
      'status_label' => $statusLabels[$t->status] ?? $t->status,
      'priority' => $t->priority,
      'due_date' => $t->due_date?->format('d M Y'),
      'category' => $t->category?->name,
      'assignee' => $t->assignedUser?->employee?->getFullNameAttribute()
        ?? $t->assignedUser?->name
        ?? ($t->assigned_department ?: '-'),
    ])->values()->all();

    if ($onEvent) {
      $onEvent("Ditemukan " . count($list) . " tugas", 'done', 'Task Agent');
    }

    return [
      'tasks' => $list,
      'total' => count($list),
      'summary' => count($list) . ' tugas ditemukan.',
    ];
  }

  public function createTask(array $data, ?callable $onEvent = null): array
  {
    if ($onEvent) {
      $onEvent('Membuat tugas baru...', 'loading', 'Task Agent');
    }

    $validated = validator($data, [
      'title' => 'required|string|max:255',
      'task_category_id' => 'nullable|exists:task_categories,id',
      'order_id' => 'nullable|exists:orders,id',
      'description' => 'nullable|string|max:5000',
      'status' => 'nullable|in:todo,in_progress,done,cancelled',
      'priority' => 'nullable|in:low,medium,high',
      'due_date' => 'nullable|date',
      'assigned_user_id' => 'nullable|exists:users,id',
      'assigned_department' => 'nullable|string|max:255',
    ])->validate();

    $data = array_merge([
      'status' => 'todo',
      'priority' => 'medium',
    ], $validated, [
      'created_by_user_id' => auth()->id(),
    ]);

    if (empty($data['assigned_user_id']) && empty($data['assigned_department'])) {
      $data['assigned_user_id'] = auth()->id();
    }

    $task = Task::create($data);

    if ($onEvent) {
      $onEvent("Tugas '{$task->title}' berhasil dibuat", 'done', 'Task Agent');
    }

    return [
      'success' => true,
      'task_id' => $task->id,
      'title' => $task->title,
      'status' => $task->status,
      'priority' => $task->priority,
      'due_date' => $task->due_date?->format('d M Y'),
      'message' => "Tugas '{$task->title}' berhasil dibuat.",
    ];
  }

  public function updateTaskStatus(int $taskId, string $status, ?callable $onEvent = null): array
  {
    if (!in_array($status, ['todo', 'in_progress', 'done', 'cancelled'], true)) {
      return ['error' => 'Status tidak valid. Gunakan: todo, in_progress, done, cancelled.'];
    }

    $task = Task::find($taskId);
    if (!$task) {
      return ['error' => 'Tugas tidak ditemukan.'];
    }

    if ($onEvent) {
      $onEvent("Mengubah status tugas '{$task->title}' → {$status}...", 'loading', 'Task Agent');
    }

    // QC check: bila status done & kategori punya qc_checklist, minimal 70% wajib terisi
    if ($status === 'done' && $task->task_category_id) {
      $category = TaskCategory::find($task->task_category_id);
      if ($category && !empty($category->qc_checklist)) {
        $results = $task->qc_results ?? [];
        $checked = collect($category->qc_checklist)->filter(fn($item) => in_array($item, $results))->count();
        $percentage = $checked / count($category->qc_checklist) * 100;
        if ($percentage < 70) {
          return ['error' => "QC wajib minimal 70% untuk menandai selesai. Saat ini: " . round($percentage) . "%."];
        }
      }
    }

    $task->update(['status' => $status]);

    if ($onEvent) {
      $onEvent("Status tugas '{$task->title}' diubah ke {$status}", 'done', 'Task Agent');
    }

    return [
      'success' => true,
      'task_id' => $task->id,
      'title' => $task->title,
      'status' => $status,
      'message' => "Tugas '{$task->title}' berhasil diubah ke status {$status}.",
    ];
  }

  /**
   * Daftar singkat karyawan (user) utk assign tugas — dipakai utk melengkapi konteks.
   */
  public static function usersBrief(): array
  {
    return User::orderBy('name')->get(['id', 'name', 'email'])->map(fn($u) => [
      'id' => $u->id,
      'name' => $u->name,
      'email' => $u->email,
    ])->values()->all();
  }

  public static function categoriesBrief(): array
  {
    return TaskCategory::orderBy('name')->get(['id', 'name'])->map(fn($c) => [
      'id' => $c->id,
      'name' => $c->name,
    ])->values()->all();
  }
}
