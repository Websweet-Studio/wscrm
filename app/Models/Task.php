<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'assigned_user_id',
        'assigned_department',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
        ];
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function scopeMy($query, int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('assigned_user_id', $userId)
                ->orWhere('created_by_user_id', $userId);
        });
    }

    public function scopeByDepartment($query, ?string $department)
    {
        if ($department) {
            $query->where('assigned_department', $department);
        }
        return $query;
    }

    public function scopeByStatus($query, ?string $status)
    {
        if ($status) {
            $query->where('status', $status);
        }
        return $query;
    }
}

