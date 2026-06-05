<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

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
            'account_number' => 'required|string|max:255',
            'account_name' => 'required|string|max:255',
        ]);

        Bank::create([
            'bank_name' => $validated['bank_name'],
            'bank_code' => $this->generateUniqueBankCode($validated['bank_name']),
            'account_number' => $validated['account_number'],
            'account_name' => $validated['account_name'],
            'branch' => null,
            'swift_code' => null,
            'description' => null,
            'is_active' => true,
            'admin_fee' => 0,
            'bank_type' => 'local',
        ]);

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
            'account_number' => 'required|string|max:255',
            'account_name' => 'required|string|max:255',
        ]);

        $bank->update($validated);

        return redirect()->route('admin.banks.index')
            ->with('success', 'Bank berhasil diperbarui.');
    }

    private function generateUniqueBankCode(string $bankName): string
    {
        $ascii = Str::ascii($bankName);
        $clean = strtoupper((string) preg_replace('/[^A-Z0-9]+/', '', $ascii));
        $base = substr($clean !== '' ? $clean : 'BANK', 0, 10);

        if (! Bank::where('bank_code', $base)->exists()) {
            return $base;
        }

        for ($i = 2; $i <= 999; $i++) {
            $suffix = (string) $i;
            $prefixLen = max(1, 10 - strlen($suffix));
            $candidate = substr($base, 0, $prefixLen).$suffix;

            if (! Bank::where('bank_code', $candidate)->exists()) {
                return $candidate;
            }
        }

        do {
            $candidate = 'BK'.str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
        } while (Bank::where('bank_code', $candidate)->exists());

        return $candidate;
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
            'is_active' => ! $bank->is_active,
        ]);

        $status = $bank->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('admin.banks.index')
            ->with('success', "Bank berhasil {$status}.");
    }
}
