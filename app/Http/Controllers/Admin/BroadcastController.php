<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\BroadcastMail;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class BroadcastController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Broadcast/Index', [
            'customerCount' => Customer::count(),
            'activeCustomerCount' => Customer::active()->count(),
        ]);
    }

    public function send(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'target' => 'required|in:all,active',
        ]);

        $query = Customer::query()
            ->whereNotNull('email')
            ->where('email', '!=', '');

        if ($validated['target'] === 'active') {
            $query->active();
        }

        $customers = $query->get(['id', 'name', 'email']);
        $sent = 0;

        foreach ($customers as $customer) {
            try {
                Mail::to($customer->email)->queue(new BroadcastMail(
                    $customer,
                    $validated['subject'],
                    $validated['body'],
                ));
                $sent++;
            } catch (\Throwable $e) {
                report($e);
            }
        }

        if ($sent === 0) {
            return back()->with('error', 'Tidak ada customer dengan email valid untuk dikirimi broadcast.');
        }

        return back()->with('success', "Broadcast berhasil diantrekan ke {$sent} customer.");
    }
}
