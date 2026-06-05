<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PaymentAccountController extends Controller
{
    public function index(): Response
    {
        $payments = PaymentAccount::query()
            ->orderBy('is_active', 'desc')
            ->orderBy('type')
            ->orderBy('sort')
            ->orderBy('name')
            ->paginate(10);

        return Inertia::render('Admin/Banks/Index', [
            'payments' => $payments,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePayload($request);

        $payload = $this->toPayload($validated, $request);

        PaymentAccount::create($payload);

        return redirect()->route('admin.payments.index')->with('success', 'Metode pembayaran berhasil ditambahkan.');
    }

    public function update(Request $request, PaymentAccount $payment): RedirectResponse
    {
        $validated = $this->validatePayload($request, $payment);

        $payload = $this->toPayload($validated, $request, $payment);

        $payment->update($payload);

        return redirect()->route('admin.payments.index')->with('success', 'Metode pembayaran berhasil diperbarui.');
    }

    public function destroy(PaymentAccount $payment): RedirectResponse
    {
        if ($payment->invoices()->exists()) {
            return redirect()->route('admin.payments.index')->with('error', 'Tidak dapat menghapus karena masih dipakai di invoice.');
        }

        $this->deleteQrisImageIfExists($payment->qris_image_path);

        $payment->delete();

        return redirect()->route('admin.payments.index')->with('success', 'Metode pembayaran berhasil dihapus.');
    }

    public function toggleStatus(PaymentAccount $payment): RedirectResponse
    {
        $payment->update([
            'is_active' => ! $payment->is_active,
        ]);

        return redirect()->route('admin.payments.index')->with('success', 'Status metode pembayaran berhasil diperbarui.');
    }

    private function validatePayload(Request $request, ?PaymentAccount $payment = null): array
    {
        $rules = [
            'type' => ['required', Rule::in(['bank', 'ewallet', 'qris'])],
            'name' => 'required|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'account_name' => 'nullable|string|max:255',
            'qris_provider' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'qris_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ];

        $validated = $request->validate($rules);

        if ($validated['type'] === 'bank') {
            $request->validate([
                'account_number' => 'required|string|max:255',
                'account_name' => 'required|string|max:255',
            ]);
        }

        if ($validated['type'] === 'ewallet') {
            $request->validate([
                'account_number' => 'required|string|max:255',
            ]);
        }

        if ($validated['type'] === 'qris' && $payment === null) {
            $request->validate([
                'qris_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
            ]);
        }

        return $validated;
    }

    private function toPayload(array $validated, Request $request, ?PaymentAccount $payment = null): array
    {
        $type = $validated['type'];

        $payload = [
            'type' => $type,
            'name' => $validated['name'],
            'sort' => (int) ($validated['sort'] ?? 0),
            'is_active' => (bool) ($validated['is_active'] ?? true),
            'account_number' => in_array($type, ['bank', 'ewallet'], true) ? ($validated['account_number'] ?? null) : null,
            'account_name' => $type === 'bank' ? ($validated['account_name'] ?? null) : null,
            'qris_provider' => $type === 'qris' ? ($validated['qris_provider'] ?? $validated['name']) : null,
        ];

        if ($type !== 'qris') {
            $payload['qris_image_path'] = null;
        }

        if ($request->hasFile('qris_image')) {
            if ($payment) {
                $this->deleteQrisImageIfExists($payment->qris_image_path);
            }

            $path = $request->file('qris_image')->store('payments/qris', 'public');
            $payload['qris_image_path'] = Storage::url($path);
        }

        if ($type === 'qris' && empty($payload['qris_provider'])) {
            $payload['qris_provider'] = $validated['name'];
        }

        return $payload;
    }

    private function deleteQrisImageIfExists(?string $publicPath): void
    {
        if (! $publicPath) {
            return;
        }

        $relativePath = str_replace('/storage/', '', $publicPath);

        if (Storage::disk('public')->exists($relativePath)) {
            Storage::disk('public')->delete($relativePath);
        }
    }
}
