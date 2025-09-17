<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo($request): ?string
    {
        if (! $request->expectsJson()) {
            // Check if this is a customer route
            if ($request->is('customer/*')) {
                return route('customer.login');
            }

            // Default admin login
            return route('login');
        }

        return null;
    }
}
