<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;

class EmployeeController extends Controller
{
    /**
     * Check if user is admin.
     */
    private function checkAdmin()
    {
        if (! auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access.');
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->checkAdmin();
        $query = Employee::with('user');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nik', 'like', "%{$search}%")
                    ->orWhere('position', 'like', "%{$search}%")
                    ->orWhere('department', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('department')) {
            $query->byDepartment($request->department);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $employees = $query->latest()->paginate(10);

        $departments = Employee::distinct()->pluck('department')->filter();

        return Inertia::render('Admin/Employees/Index', [
            'employees' => $employees,
            'departments' => $departments,
            'filters' => $request->only(['search', 'department', 'status']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $departments = Employee::distinct()->pluck('department')->filter();

        return Inertia::render('Admin/Employees/Create', [
            'departments' => $departments,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'username' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:8',
            'nik' => 'required|string|unique:employees',
            'position' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'hire_date' => 'required|date',
            'salary' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive,terminated',
            'notes' => 'nullable|string|max:1000',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
            'role' => 'user',
        ]);

        Employee::create([
            'user_id' => $user->id,
            'nik' => $validated['nik'],
            'position' => $validated['position'],
            'department' => $validated['department'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'hire_date' => $validated['hire_date'],
            'salary' => $validated['salary'],
            'status' => $validated['status'],
            'notes' => $validated['notes'],
        ]);

        return redirect()->route('admin.employees.index')
            ->with('success', 'Karyawan berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Employee $employee)
    {
        $employee->load('user');

        return Inertia::render('Admin/Employees/Show', [
            'employee' => $employee,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Employee $employee)
    {
        $employee->load('user');
        $departments = Employee::distinct()->pluck('department')->filter();

        return Inertia::render('Admin/Employees/Edit', [
            'employee' => $employee,
            'departments' => $departments,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($employee->user_id),
            ],
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users')->ignore($employee->user_id),
            ],
            'nik' => [
                'required',
                'string',
                Rule::unique('employees')->ignore($employee->id),
            ],
            'position' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'hire_date' => 'required|date',
            'salary' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive,terminated',
            'notes' => 'nullable|string|max:1000',
        ]);

        $employee->user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $validated['username'],
        ]);

        $employee->update([
            'nik' => $validated['nik'],
            'position' => $validated['position'],
            'department' => $validated['department'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'hire_date' => $validated['hire_date'],
            'salary' => $validated['salary'],
            'status' => $validated['status'],
            'notes' => $validated['notes'],
        ]);

        return redirect()->route('admin.employees.index')
            ->with('success', 'Data karyawan berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee)
    {
        $employee->user->delete();
        $employee->delete();

        return redirect()->route('admin.employees.index')
            ->with('success', 'Karyawan berhasil dihapus.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $this->checkAdmin();

        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'distinct'],
        ]);

        $ids = array_values(array_unique(array_map('intval', $validated['ids'])));
        $employees = Employee::with('user')->whereIn('id', $ids)->get();

        $deleted = 0;
        $skipped = [];
        $currentUserId = (int) (auth()->id() ?? 0);

        foreach ($employees as $employee) {
            if ($employee->user_id === $currentUserId) {
                $skipped[] = "{$employee->nik} (akun sendiri)";
                continue;
            }

            if ($employee->user && method_exists($employee->user, 'isSuperAdmin') && $employee->user->isSuperAdmin()) {
                $skipped[] = "{$employee->nik} (super admin)";
                continue;
            }

            DB::transaction(function () use ($employee) {
                $employee->user?->delete();
                $employee->delete();
            });

            $deleted++;
        }

        $message = "{$deleted} karyawan berhasil dihapus.";
        if (! empty($skipped)) {
            $message .= ' Dilewati: '.implode(', ', array_slice($skipped, 0, 10)).(count($skipped) > 10 ? '…' : '');
        }

        return redirect()->route('admin.employees.index')->with('success', $message);
    }

    /**
     * Reset password for employee.
     */
    public function resetPassword(Employee $employee)
    {
        $newPassword = Str::random(12);

        if ($employee->user->sendCredentials($newPassword)) {
            return redirect()->route('admin.employees.index')
                ->with('success', 'Password berhasil direset. Email dengan password baru telah dikirim.');
        }

        return redirect()->route('admin.employees.index')
            ->with('error', 'Gagal mengirim password baru. Silakan coba lagi.');
    }
}
