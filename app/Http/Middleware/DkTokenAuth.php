<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Autentikasi token mesin untuk konsumen data klien (app.digitalkonsultan.com).
 *
 * Token TERPISAH dari token agent (AGENT_API_TOKEN): konsumen hanya boleh membaca
 * data klien, dan bisa dicabut sendiri tanpa mengganggu integrasi lain.
 */
class DkTokenAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = config('services.dk.token');

        if (! $token) {
            return response()->json(['message' => 'Endpoint data klien belum dikonfigurasi.'], 503);
        }

        $dikirim = (string) $request->bearerToken();
        $kunci = (string) $request->header('X-Dk-Token');

        if ($dikirim === '' && $kunci !== '') {
            $dikirim = $kunci;
        }

        if ($dikirim === '' || ! hash_equals((string) $token, $dikirim)) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return $next($request);
    }
}
