<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminAuthorization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated via customer guard
        if (Auth::guard('customer')->check()) {
            // Customer trying to access admin area - show error and redirect
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Anda tidak memiliki otorisasi untuk mengakses halaman ini.',
                    'redirect' => route('home'),
                ], 403);
            }

            // Flash message for non-JSON requests
            session()->flash('toast', [
                'type' => 'error',
                'title' => 'Akses Ditolak',
                'message' => 'Anda tidak memiliki otorisasi untuk mengakses halaman admin.',
            ]);

            return redirect()->route('home');
        }

        // User is either admin/web guard or not authenticated
        // If not authenticated, the 'auth' middleware will handle redirection
        return $next($request);
    }
}
