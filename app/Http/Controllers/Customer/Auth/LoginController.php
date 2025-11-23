<?php

namespace App\Http\Controllers\Customer\Auth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class LoginController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Customer/Auth/Login');
    }

    public function terms(): Response
    {
        return Inertia::render('Customer/Auth/Terms');
    }

    public function store(Request $request): RedirectResponse
    {
        \Log::info('Customer login attempt', [
            'email' => $request->email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Check if it's an admin account trying to login
        if ($request->email === 'admin@example.com') {
            $user = User::where('email', $request->email)->first();
            
            if ($user && Hash::check($request->password, $user->password)) {
                // Login as admin using web guard
                Auth::guard('web')->login($user, $request->boolean('remember'));
                \Log::info('Admin login successful via customer portal', ['user_id' => $user->id, 'email' => $user->email]);
                
                $request->session()->regenerate();
                
                // Redirect to admin dashboard
                return redirect('/dashboard');
            } else {
                \Log::warning('Admin login failed via customer portal - invalid credentials', ['email' => $request->email]);
                throw ValidationException::withMessages([
                    'email' => 'The provided credentials do not match our records.',
                ]);
            }
        }

        // Regular customer login process
        $customer = Customer::where('email', $request->email)->first();

        if (! $customer) {
            \Log::warning('Customer login failed - customer not found', ['email' => $request->email]);
            throw ValidationException::withMessages([
                'email' => 'The provided credentials do not match our records.',
            ]);
        }

        if (! Hash::check($request->password, $customer->password)) {
            \Log::warning('Customer login failed - password mismatch', ['email' => $request->email, 'customer_id' => $customer->id]);
            throw ValidationException::withMessages([
                'email' => 'The provided credentials do not match our records.',
            ]);
        }

        if (! $customer->isActive()) {
            \Log::warning('Customer login failed - account not active', ['email' => $request->email, 'status' => $customer->status]);
            throw ValidationException::withMessages([
                'email' => 'Your account has been suspended. Please contact support.',
            ]);
        }

        Auth::guard('customer')->login($customer, $request->boolean('remember'));
        \Log::info('Customer login successful', ['customer_id' => $customer->id, 'email' => $customer->email]);

        $request->session()->regenerate();

        // Debug info
        \Log::info('Customer login redirect debug', [
            'intended_url' => session('url.intended'),
            'is_authenticated' => Auth::guard('customer')->check(),
            'customer_id' => Auth::guard('customer')->id(),
        ]);

        return redirect('/customer/dashboard');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('customer')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/customer/login');
    }
}
