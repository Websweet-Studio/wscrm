<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Show the login page.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => $request->session()->get('status'),
            'csrf_token' => csrf_token(),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->ensureIsNotRateLimited();

        $login = (string) $request->input('login');
        $password = (string) $request->input('password');
        $remember = $request->boolean('remember');

        if (Auth::guard('web')->attempt(['email' => $login, 'password' => $password], $remember)) {
            RateLimiter::clear($request->throttleKey());

            $request->session()->regenerate();
            $request->session()->regenerateToken();

            return redirect()->intended(route('dashboard'));
        }

        try {
            if (Schema::hasColumn('users', 'username')) {
                $user = \App\Models\User::where('username', $login)->first();
                if ($user && Hash::check($password, $user->password)) {
                    Auth::guard('web')->login($user, $remember);
                    RateLimiter::clear($request->throttleKey());

                    $request->session()->regenerate();
                    $request->session()->regenerateToken();

                    return redirect()->intended(route('dashboard'));
                }
            }
        } catch (\Throwable $e) {
        }

        $customer = Customer::where('email', $login)->first();
        if ($customer && Hash::check($password, $customer->password) && $customer->isActive()) {
            Auth::guard('customer')->login($customer, $remember);
            RateLimiter::clear($request->throttleKey());

            $request->session()->regenerate();
            $request->session()->regenerateToken();

            return redirect()->intended('/customer/dashboard');
        }

        RateLimiter::hit($request->throttleKey());

        throw ValidationException::withMessages([
            'login' => 'Email/Username atau password salah.',
        ]);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
