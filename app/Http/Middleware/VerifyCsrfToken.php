<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Hanya endpoint web yang memang butuh tanpa CSRF (token/digunakan oleh harness eksternal).
        'api/public/ai-chat',
        'api/agent/blog',
        '_boost/browser-logs',
    ];
}
