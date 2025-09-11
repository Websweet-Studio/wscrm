<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonateController extends Controller
{
    public function impersonate(Customer $customer)
    {
        \Log::info("Impersonation started for customer: {$customer->id} by admin: " . Auth::id());
        
        // Store admin user ID in session for later restoration
        session(['admin_user_id' => Auth::id()]);
        
        // Login as the customer
        Auth::guard('customer')->login($customer);
        
        // Set impersonation flag
        session(['is_impersonating' => true]);
        
        \Log::info("Impersonation session set. Redirecting to customer dashboard.");
        
        return redirect('/customer/dashboard')->with('success', "Berhasil login sebagai {$customer->name}");
    }
    
    public function stopImpersonation()
    {
        if (!session('is_impersonating')) {
            return redirect('/admin/dashboard');
        }
        
        // Logout from customer account
        Auth::guard('customer')->logout();
        
        // Login back as admin
        $adminUserId = session('admin_user_id');
        if ($adminUserId) {
            Auth::loginUsingId($adminUserId);
        }
        
        // Clear impersonation session data
        session()->forget(['is_impersonating', 'admin_user_id']);
        
        return redirect('/admin/dashboard')->with('success', 'Berhasil kembali ke akun admin');
    }
}
