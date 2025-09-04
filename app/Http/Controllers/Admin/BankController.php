<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class BankController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $banks = Bank::query()
            ->orderBy('is_active', 'desc')
            ->orderBy('bank_name')
            ->paginate(10);

        return Inertia::render('Admin/Banks/Index', [
            'banks' => $banks,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Banks/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'bank_name' => 'required|string|max:255',
            'bank_code' => 'required|string|max:10|unique:banks,bank_code',
            'account_number' => 'required|string|max:255',
            'account_name' => 'required|string|max:255',
            'branch' => 'nullable|string|max:255',
            'swift_code' => 'nullable|string|max:11',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'admin_fee' => 'numeric|min:0',
            'bank_type' => 'required|in:local,international',
        ]);

        Bank::create($validated);

        return redirect()->route('admin.banks.index')
            ->with('success', 'Bank berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Bank $bank): Response
    {
        $bank->load(['invoices' => function ($query) {
            $query->latest()->limit(10);
        }]);

        return Inertia::render('Admin/Banks/Show', [
            'bank' => $bank,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Bank $bank): Response
    {
        return Inertia::render('Admin/Banks/Edit', [
            'bank' => $bank,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Bank $bank): RedirectResponse
    {
        $validated = $request->validate([
            'bank_name' => 'required|string|max:255',
            'bank_code' => 'required|string|max:10|unique:banks,bank_code,' . $bank->id,
            'account_number' => 'required|string|max:255',
            'account_name' => 'required|string|max:255',
            'branch' => 'nullable|string|max:255',
            'swift_code' => 'nullable|string|max:11',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'admin_fee' => 'numeric|min:0',
            'bank_type' => 'required|in:local,international',
        ]);

        $bank->update($validated);

        return redirect()->route('admin.banks.index')
            ->with('success', 'Bank berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bank $bank): RedirectResponse
    {
        // Check if bank has associated invoices
        if ($bank->invoices()->exists()) {
            return redirect()->route('admin.banks.index')
                ->with('error', 'Bank tidak dapat dihapus karena masih memiliki invoice terkait.');
        }

        $bank->delete();

        return redirect()->route('admin.banks.index')
            ->with('success', 'Bank berhasil dihapus.');
    }

    /**
     * Toggle bank status (active/inactive)
     */
    public function toggleStatus(Bank $bank): RedirectResponse
    {
        $bank->update([
            'is_active' => !$bank->is_active,
        ]);

        $status = $bank->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('admin.banks.index')
            ->with('success', "Bank berhasil {$status}.");
    }
}
