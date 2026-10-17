<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\DkClientDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API data klien untuk app.digitalkonsultan.com (klien "hidayat").
 *
 * Semua endpoint hanya BACA. Autentikasi: Bearer <DK_DATA_TOKEN>
 * (middleware `dk.auth`, token terpisah dari AGENT_API_TOKEN).
 * Dokumentasi kontrak: references/integrasi-data-klien-wscrm.md di skill laravel-business-apps.
 *
 * Lingkup klien dibatasi lewat DK_KLIEN_IDS di .env (mis. "39" = hanya akun Hidayat).
 */
class DkCustomerController extends Controller
{
    public function __construct(private DkClientDataService $data) {}

    /** Cek konektivitas + versi kontrak. */
    public function ping(): JsonResponse
    {
        $ringkas = $this->data->ringkasan();

        return $this->balas([
            'status' => 'ok',
            'aplikasi' => config('app.name'),
            'versi_kontrak' => 1,
            'lingkup_klien' => $ringkas['lingkup_klien'] ?? [],
            'jumlah_klien' => $ringkas['klien'] ?? null,
            'waktu_server' => now()->toIso8601String(),
        ]);
    }

    public function ringkasan(): JsonResponse
    {
        return $this->balas(['data' => $this->data->ringkasan()]);
    }

    /** GET /api/dk/klien — daftar klien + layanan. */
    public function klien(Request $request): JsonResponse
    {
        $filter = [
            'q' => $request->query('q') ?? $request->query('search'),
            'status' => $request->query('status'),
            'masa_aktif' => $request->query('masa_aktif'),
            'urut' => $request->query('urut'),
            'halaman' => (int) $request->query('halaman', 1),
            'per_halaman' => (int) $request->query('per_halaman', 25),
            'sertakan_layanan' => $request->boolean('sertakan_layanan', true),
        ];

        return $this->balas($this->data->daftarKlien($filter));
    }

    /** GET /api/dk/klien/{customer} — satu klien, seluruh layanannya. */
    public function klienDetail(Customer $customer): JsonResponse
    {
        return $this->balas(['data' => $this->data->detailKlien($customer)]);
    }

    /** GET /api/dk/layanan/jatuh-tempo?hari=30 — bahan follow-up perpanjangan. */
    public function jatuhTempo(Request $request): JsonResponse
    {
        return $this->balas($this->data->layananJatuhTempo(
            (int) $request->query('hari', 30),
            (int) $request->query('batas', 100),
        ));
    }

    /** GET /api/dk/domains — daftar domain aktual dari RDash lintas klien. */
    public function domains(): JsonResponse
    {
        return $this->balas($this->data->domainsRdash());
    }

    /**
     * @param  array<string, mixed>  $isi
     */
    private function balas(array $isi): JsonResponse
    {
        return response()
            ->json($isi)
            ->header('Cache-Control', 'no-store');
    }
}
