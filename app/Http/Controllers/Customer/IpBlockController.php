<?php

namespace App\Http\Controllers\Customer;

use App\Models\ShieldWhitelistRequest;
use App\Services\ShieldClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Halaman pelanggan "IP Block" (/customer/ip-block).
 *
 * Isi: IP pelanggan sekarang + blokir aktif pada domain miliknya + pengajuan
 * whitelist SEMENTARA yang langsung diterima (tanpa persetujuan admin).
 *
 * Rancangan aturan (dipilih 21 Sep 2026):
 *  - Pengajuan hanya sah untuk IP yang SEDANG diblokir dan muncul di daftar blokir
 *    layanan pelanggan itu (Shield juga memeriksa ulang hal yang sama).
 *  - Masa berlaku dibatasi (WSD_SHIELD_JAM, bawaan 24 jam) — setelah itu aturan
 *    blokir berlaku lagi, baik di Shield maupun di agent.
 *  - Pengajuan langsung diproses Shield tanpa batas jumlah; IP tetap harus sedang
 *    diblokir pada domain milik pelanggan.
 *  - Setiap pengajuan meninggalkan jejak: tabel shield_whitelist_requests + event
 *    "whitelist-pelanggan" di dashboard Shield, dengan keterangan siapa yang minta.
 */
class IpBlockController extends CustomerBaseController
{
    public function index(Request $request, ShieldClient $shield): Response
    {
        $customer = $this->customer();
        $domains = $this->domainsPelanggan($customer->id);
        $ipSaya = (string) $request->ip();

        $hasil = $shield->blokir($domains, $ipSaya);

        $rows = array_map(function (array $r) use ($hasil, $ipSaya) {
            $r['whitelist'] = $hasil['whitelist'][$r['ip']] ?? null;
            $r['ip_saya'] = $r['ip'] === $ipSaya;

            return $r;
        }, $hasil['rows']);

        // Blokir yang menyangkut IP pelanggan sekarang dipisah supaya langsung terlihat.
        // Daftar blokir lain dipotong: satu klien bisa punya ratusan IP penyerang terkunci
        // (uji 21 Sep 2026: 112 baris pada satu domain). Sisanya diakses lewat kotak cari.
        $blokirIpSaya = array_values(array_filter($rows, fn ($r) => $r['ip_saya']));
        $q = trim((string) $request->query('q'));
        $maksTampil = 20;

        $layananSemua = array_values(array_filter($rows, fn ($r) => ! $r['ip_saya']));
        $totalLayanan = count($layananSemua);

        if ($q !== '') {
            $layananSemua = array_values(array_filter($layananSemua, fn ($r) => stripos($r['ip'], $q) !== false
                || stripos((string) $r['reason'], $q) !== false
                || stripos((string) $r['domain'], $q) !== false));
        }

        $blokirLayanan = array_slice($layananSemua, 0, $maksTampil);

        return Inertia::render('Customer/IpBlock/Index', [
            'ipSaya' => $ipSaya,
            'blokirIpSaya' => $blokirIpSaya,
            'blokirLayanan' => $blokirLayanan,
            'total' => count($rows),
            'totalLayanan' => $totalLayanan,
            'tampil' => count($blokirLayanan),
            'q' => $q,
            'domains' => $domains,
            'layananSiap' => $shield->aktif(),
            'error' => $hasil['error'],
            'jam' => (int) config('services.shield.jam', 24),
            'riwayat' => ShieldWhitelistRequest::where('customer_id', $customer->id)
                ->latest()
                ->limit(10)
                ->get(['id', 'ip', 'domain', 'reason', 'hours', 'status', 'note', 'server', 'expires_at', 'created_at'])
                ->map(fn ($r) => [
                    'id' => $r->id,
                    'ip' => $r->ip,
                    'domain' => $r->domain,
                    'reason' => $r->reason,
                    'hours' => $r->hours,
                    'status' => $r->status,
                    'note' => $r->note,
                    'server' => $r->server,
                    // Zona aplikasi = UTC; jamnya ditampilkan WIB supaya sebaris dengan
                    // waktu yang dikirim Shield ("21/9/2026, 11.45.21").
                    'expires_at' => $r->expires_at?->copy()->setTimezone('Asia/Jakarta')->toDateTimeString(),
                    'created_at' => $r->created_at?->copy()->setTimezone('Asia/Jakarta')->toDateTimeString(),
                ]),
        ]);
    }

    public function store(Request $request, ShieldClient $shield): RedirectResponse
    {
        $customer = $this->customer();

        $data = $request->validate([
            'ip' => ['required', 'string', 'max:64'],
            'reason' => ['nullable', 'string', 'max:160'],
        ], [], ['ip' => 'IP', 'reason' => 'alasan']);

        $ip = trim($data['ip']);
        if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
            return back()->with('error', 'Format IP tidak sah.');
        }

        // Pengajuan otomatis disetujui tanpa kuota; Shield tetap memastikan IP sedang diblokir.
        $jam = (int) config('services.shield.jam', 24);

        // Cek ulang ke Shield: IP itu harus benar-benar sedang diblokir pada layanan
        // pelanggan ini. Ini yang mencegah halaman ini dipakai memutihkan IP sembarang.
        $domains = $this->domainsPelanggan($customer->id);
        $hasil = $shield->blokir($domains, (string) $request->ip());

        if (! $hasil['ok']) {
            return back()->with('error', $hasil['error'] ?: 'Layanan keamanan tidak bisa dihubungi.');
        }

        $cocok = collect($hasil['rows'])->firstWhere('ip', $ip);
        if (! $cocok) {
            return back()->with('error', 'IP '.$ip.' tidak sedang diblokir di layanan kamu.');
        }

        $oleh = Str::limit($customer->name ?: $customer->email, 40, '').' (pelanggan #'.$customer->id.')';
        $kirim = $shield->ajukanWhitelist($ip, $cocok['domain'] ?? null, $oleh, $jam, $data['reason'] ?? null);

        ShieldWhitelistRequest::create([
            'customer_id' => $customer->id,
            'ip' => $ip,
            'domain' => $cocok['domain'] ?? null,
            'reason' => $data['reason'] ?? null,
            'hours' => $kirim['hours'] ?: $jam,
            'status' => $kirim['ok'] ? 'diterima' : 'gagal',
            'note' => $kirim['ok'] ? null : $kirim['error'],
            'server' => $kirim['server'] ?? ($cocok['agent'] ?? null),
            'expires_at' => $kirim['ok'] && $kirim['expires'] ? $this->parseWaktu($kirim['expires']) : null,
        ]);

        if (! $kirim['ok']) {
            return back()->with('error', $kirim['error'] ?: 'Pengajuan gagal diproses.');
        }

        return back()->with('success', 'Blokir IP '.$ip.' dibuka sementara sampai '.$kirim['expires']
            .' ('.$kirim['hours'].' jam). Setelah itu aturan blokir berlaku lagi.');
    }

    /**
     * Domain milik pelanggan: dari pesanan (domain utama layanan) + situs klien.
     * Dinormalkan (huruf kecil, tanpa www) karena log blokir memakai nama domain polos.
     *
     * @return array<int, string>
     */
    protected function domainsPelanggan(int $customerId): array
    {
        $dariPesanan = \App\Models\Order::where('customer_id', $customerId)
            ->whereIn('status', ['active', 'suspended', 'expired'])
            ->whereNotNull('domain_name')
            ->pluck('domain_name');

        $dariSitus = \App\Models\WebsiteClient::where('customer_id', $customerId)
            ->get(['url', 'name'])
            ->map(function ($w) {
                $url = trim((string) $w->url);
                if ($url === '') {
                    return null;
                }
                if (! str_contains($url, '://')) {
                    $url = 'http://'.$url;
                }

                return parse_url($url, PHP_URL_HOST) ?: null;
            });

        return $dariPesanan->concat($dariSitus)
            ->filter()
            ->map(fn ($d) => strtolower(preg_replace('/^www\./', '', trim((string) $d))))
            ->filter(fn ($d) => $d !== '' && strlen($d) < 200)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Waktu dari Shield berbentuk "21/9/2026, 11.45.21" (zona Asia/Jakarta).
     *
     * Dikembalikan dalam UTC karena zona aplikasi = UTC: tanpa ->utc(), jam lokal (WIB)
     * tersimpan apa adanya sehingga tampil +7 jam saat dibaca kembali (uji 21 Sep 2026:
     * berlaku s/d 17.00 padahal seharusnya 10.00).
     */
    protected function parseWaktu(?string $teks): ?\Illuminate\Support\Carbon
    {
        if (! $teks) {
            return null;
        }

        try {
            return \Illuminate\Support\Carbon::createFromFormat('j/n/Y, H.i.s', $teks, 'Asia/Jakarta')->utc();
        } catch (\Throwable $e) {
            return null;
        }
    }
}
