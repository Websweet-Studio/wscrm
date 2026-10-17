<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use App\Models\RdashDomain;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Data klien WSCRM untuk aplikasi lain (app.digitalkonsultan.com).
 *
 * Hanya BACA — tidak pernah menulis apa pun. Nominal perpanjangan diambil dari
 * ServiceRenewalService::renewalAmounts() supaya angka yang dilihat mitra sama
 * dengan yang akan ditagih oleh cron `invoice:generate-renewals`.
 */
class DkClientDataService
{
    /** Layanan dianggap "segera berakhir" bila sisa hari <= ambang ini. */
    public const AMBANG_SEGERA_HARI = 30;

    /** Status order yang dianggap layanan berjalan. */
    private const STATUS_LAYANAN_AKTIF = ['active', 'suspended'];

    private const PER_HALAMAN_DEFAULT = 25;

    private const PER_HALAMAN_MAKS = 100;

    public function __construct(private ServiceRenewalService $renewal) {}

    public function ringkasan(): array
    {
        $ids = $this->lingkupKlien();

        $layanan = Order::query()
            ->whereIn('status', self::STATUS_LAYANAN_AKTIF)
            ->when($ids, fn ($q, $i) => $q->whereIn('customer_id', $i))
            ->with(['orderItems.hostingPlan', 'orderItems.domainPrice', 'orderItems.servicePlan'])
            ->get();

        $berjalan = $layanan->filter(fn (Order $o) => $o->expires_at === null || $o->daysUntilExpiry() >= 0);
        // Jatuh tempo = sudah lewat ATAU <= ambang (sama dengan daftar layananJatuhTempo()).
        $jatuhTempo = $layanan->filter(fn (Order $o) => $o->expires_at !== null
            && $o->daysUntilExpiry() <= self::AMBANG_SEGERA_HARI);
        $kadaluarsa = $layanan->filter(fn (Order $o) => $o->expires_at !== null
            && $o->daysUntilExpiry() < 0);

        return [
            'klien' => Customer::query()->when($ids, fn ($q, $i) => $q->whereIn('id', $i))->count(),
            'klien_aktif' => Customer::query()->when($ids, fn ($q, $i) => $q->whereIn('id', $i))
                ->where('status', 'active')->count(),
            'layanan_berjalan' => $berjalan->count(),
            'jatuh_tempo_30_hari' => $jatuhTempo->count(),
            'kadaluarsa_belum_diperpanjang' => $kadaluarsa->count(),
            'nominal_jatuh_tempo_30_hari' => round($jatuhTempo->sum(fn (Order $o) => $this->nominalPerpanjangan($o)), 2),
            'total_nominal_perpanjangan' => round($berjalan->sum(fn (Order $o) => $this->nominalPerpanjangan($o)), 2),
            'lingkup_klien' => $ids,
            'dihitung_pada' => Carbon::now()->toIso8601String(),
        ];
    }

    /**
     * Daftar klien + layanannya (halaman).
     *
     * @param  array{q?:string,status?:string,masa_aktif?:string,urut?:string,halaman?:int,per_halaman?:int,sertakan_layanan?:bool}  $filter
     */
    public function daftarKlien(array $filter = []): array
    {
        $perHalaman = min(max((int) ($filter['per_halaman'] ?? self::PER_HALAMAN_DEFAULT), 1), self::PER_HALAMAN_MAKS);
        $halaman = max((int) ($filter['halaman'] ?? 1), 1);
        $urut = $filter['urut'] ?? 'nama';
        $sertakanLayanan = $filter['sertakan_layanan'] ?? true;

        $query = Customer::query()
            ->select('customers.*')
            ->when($this->lingkupKlien(), fn ($q, $ids) => $q->whereIn('customers.id', $ids))
            ->when($filter['q'] ?? null, function ($q, $cari) {
                $cari = trim($cari);
                $q->where(function ($w) use ($cari) {
                    $w->where('name', 'like', "%{$cari}%")
                        ->orWhere('email', 'like', "%{$cari}%")
                        ->orWhere('username', 'like', "%{$cari}%")
                        ->orWhere('phone', 'like', "%{$cari}%")
                        ->orWhere('city', 'like', "%{$cari}%")
                        ->orWhereHas('orders', fn ($o) => $o->where('domain_name', 'like', "%{$cari}%"));
                });
            })
            ->when($filter['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filter['masa_aktif'] ?? null, fn ($q, $masa) => $this->saringMasaAktif($q, $masa));

        // Urutan jatuh tempo butuh subquery; urutan nominal dihitung di PHP
        // (nominal perpanjangan = snapshot item, tidak bisa dijumlah di SQL).
        if ($urut === 'jatuh_tempo') {
            $query->orderByRaw(
                '(SELECT MIN(o.expires_at) FROM orders o WHERE o.customer_id = customers.id AND o.status IN (?, ?)) IS NULL ASC, '.
                '(SELECT MIN(o.expires_at) FROM orders o WHERE o.customer_id = customers.id AND o.status IN (?, ?)) ASC',
                array_merge(self::STATUS_LAYANAN_AKTIF, self::STATUS_LAYANAN_AKTIF)
            )->orderBy('name');
        } elseif ($urut === 'terbaru') {
            $query->orderByDesc('created_at');
        } else {
            $urut = 'nama';
            $query->orderBy('name');
        }

        if ($urut === 'nominal') {
            $items = $query->get()->map(fn (Customer $c) => $this->petakanKlien($c, $sertakanLayanan));
            $items = $items->sortByDesc('total_nominal_perpanjangan')->values();
            $total = $items->count();
            $data = $items->slice(($halaman - 1) * $perHalaman, $perHalaman)->values()->all();
        } else {
            $paginator = $query->paginate($perHalaman, ['*'], 'halaman', $halaman);
            $data = collect($paginator->items())
                ->map(fn (Customer $c) => $this->petakanKlien($c, $sertakanLayanan))
                ->all();
            $total = $paginator->total();
        }

        return [
            'ringkasan' => $this->ringkasan(),
            'data' => $data,
            'meta' => [
                'halaman' => $halaman,
                'per_halaman' => $perHalaman,
                'total_klien' => $total,
                'total_halaman' => (int) ceil($total / $perHalaman),
                'lingkup_klien' => $this->lingkupKlien(),
            ],
        ];
    }

    public function detailKlien(Customer $customer): array
    {
        if (! $this->dalamLingkup($customer->id)) {
            abort(403, 'Klien ini di luar lingkup integrasi aplikasi DK.');
        }

        return $this->petakanKlien($customer, true, true);
    }

    /**
     * Layanan yang jatuh tempo (semua klien) — bahan follow-up perpanjangan.
     */
    public function layananJatuhTempo(int $hari = 30, int $batas = 100): array
    {
        $batasHari = max(min($hari, 3650), 0);

        $layanan = Order::query()
            ->whereIn('status', self::STATUS_LAYANAN_AKTIF)
            ->when($this->lingkupKlien(), fn ($q, $ids) => $q->whereIn('customer_id', $ids))
            ->whereNotNull('expires_at')
            ->whereDate('expires_at', '<=', Carbon::now()->addDays($batasHari)->endOfDay())
            ->with([
                'customer',
                'orderItems.hostingPlan',
                'orderItems.domainPrice',
                'orderItems.servicePlan',
            ])
            ->orderBy('expires_at')
            ->limit(min(max($batas, 1), 500))
            ->get();

        return [
            'data' => $layanan->map(fn (Order $o) => array_merge(
                $this->petakanLayanan($o),
                [
                    'klien' => [
                        'id' => $o->customer?->id,
                        'nama' => $o->customer?->name,
                        'email' => $o->customer?->email,
                        'telepon' => $o->customer?->phone,
                        'kota' => $o->customer?->city,
                        'url_admin' => $o->customer ? $this->urlAdminKlien($o->customer->id) : null,
                    ],
                ]
            ))->values()->all(),
            'meta' => [
                'hari' => $batasHari,
                'jumlah' => $layanan->count(),
                'dihitung_pada' => Carbon::now()->toIso8601String(),
            ],
        ];
    }

    /** Domain yang benar-benar ada di RDash; placeholder "missing" tidak disertakan. */
    public function domainsRdash(): array
    {
        $domains = RdashDomain::query()
            ->whereNotNull('rdash_id')
            ->with(['order.customer'])
            ->orderBy('name')
            ->get();

        return [
            'data' => $domains->map(function ($domain) {
                $customer = $domain->order?->customer;

                return [
                    'id' => $domain->rdash_id,
                    'nama' => $domain->name,
                    'status' => $domain->status,
                    'status_label' => $domain->status_label,
                    'expired_at' => $domain->expired_at?->toDateString(),
                    'match_status' => $domain->match_status,
                    'order_id' => $domain->order_id,
                    'klien' => $customer ? [
                        'id' => $customer->id,
                        'nama' => $customer->name,
                        'email' => $customer->email,
                    ] : null,
                ];
            })->values()->all(),
            'meta' => [
                'jumlah' => $domains->count(),
                'dihitung_pada' => Carbon::now()->toIso8601String(),
            ],
        ];
    }

    // ------------------------------------------------------------------ internal

    /**
     * ID klien yang boleh dibaca aplikasi DK.
     * Kosong = tanpa batas (hanya untuk pemakaian internal/debug).
     *
     * @return int[]
     */
    private function lingkupKlien(): array
    {
        return (array) config('services.dk.klien_ids', []);
    }

    private function dalamLingkup(int $idKlien): bool
    {
        $ids = $this->lingkupKlien();

        return $ids === [] || in_array($idKlien, $ids, true);
    }

    private function saringMasaAktif($query, string $masa)
    {
        $hariIni = Carbon::now()->startOfDay();

        return match ($masa) {
            'kadaluarsa' => $query->whereHas('orders', fn ($o) => $o
                ->whereIn('status', self::STATUS_LAYANAN_AKTIF)
                ->whereDate('expires_at', '<', $hariIni)),
            'segera' => $query->whereHas('orders', fn ($o) => $o
                ->whereIn('status', self::STATUS_LAYANAN_AKTIF)
                ->whereDate('expires_at', '>=', $hariIni)
                ->whereDate('expires_at', '<=', $hariIni->copy()->addDays(self::AMBANG_SEGERA_HARI))),
            'berjalan' => $query->whereHas('orders', fn ($o) => $o
                ->whereIn('status', self::STATUS_LAYANAN_AKTIF)
                ->where(fn ($w) => $w->whereNull('expires_at')
                    ->orWhereDate('expires_at', '>', $hariIni->copy()->addDays(self::AMBANG_SEGERA_HARI)))),
            default => $query,
        };
    }

    private function petakanKlien(Customer $customer, bool $sertakanLayanan = true, bool $muatRelasi = false): array
    {
        if ($muatRelasi || ! $customer->relationLoaded('orders')) {
            $customer->load([
                // Layanan tanpa tanggal (mis. sekali bayar / dihentikan) ditaruh di akhir,
                // supaya baris pertama = layanan yang paling dekat jatuh tempo.
                'orders' => fn ($q) => $q->orderByRaw('expires_at IS NULL ASC')->orderBy('expires_at'),
                'orders.orderItems.hostingPlan',
                'orders.orderItems.domainPrice',
                'orders.orderItems.servicePlan',
                'invoices' => fn ($q) => $q->unpaid(),
            ]);
        }

        $layanan = $customer->orders
            ->filter(fn (Order $o) => $o->isService())
            ->map(fn (Order $o) => $this->petakanLayanan($o))
            ->values();

        // Samakan aturan dengan ringkasan(): "berjalan" = status aktif/ditangguhkan dan belum lewat.
        $berjalan = $layanan->filter(fn (array $l) => in_array($l['status'], self::STATUS_LAYANAN_AKTIF, true)
            && ($l['berakhir'] === null || $l['sisa_hari'] >= 0));
        $terdekat = $berjalan
            ->filter(fn (array $l) => $l['berakhir'] !== null)
            ->sortBy('sisa_hari')
            ->first();

        $tagihan = $customer->relationLoaded('invoices') ? $customer->invoices : collect();

        return [
            'id' => $customer->id,
            'nama' => $customer->name,
            'email' => $customer->email,
            'username' => $customer->username,
            'telepon' => $customer->phone,
            'kota' => $customer->city,
            'status' => $customer->status,
            'status_label' => $this->labelStatusKlien($customer->status),
            'klien_sejak' => $customer->created_at?->toDateString(),
            'url_admin' => $this->urlAdminKlien($customer->id),
            'jumlah_layanan' => $layanan->count(),
            'layanan_berjalan' => $berjalan->count(),
            'layanan_lain' => $layanan->count() - $berjalan->count(),
            'jatuh_tempo_terdekat' => $terdekat['berakhir'] ?? null,
            'sisa_hari_terdekat' => $terdekat['sisa_hari'] ?? null,
            'total_nominal_perpanjangan' => round($berjalan->sum('nominal_perpanjangan'), 2),
            'tagihan_belum_bayar' => $tagihan->count(),
            'nominal_tagihan_belum_bayar' => round($tagihan->sum(fn ($i) => (float) $i->final_amount), 2),
            'layanan' => $sertakanLayanan ? $layanan->all() : [],
            'diambil_pada' => Carbon::now()->toIso8601String(),
        ];
    }

    private function petakanLayanan(Order $order): array
    {
        $sisaHari = $order->expires_at ? $order->daysUntilExpiry() : null;
        $siklusBulan = $order->billingCycleMonths();

        return [
            'order_id' => $order->id,
            'nama' => $this->namaLayanan($order),
            'domain' => $order->domain_name,
            'status' => $order->status,
            'status_label' => ucfirst((string) $order->status),
            'is_layanan' => $order->isService(),
            'siklus' => $order->billing_cycle,
            'siklus_label' => $this->labelSiklus($order->billing_cycle),
            'mulai' => $order->expires_at && $order->isRecurring()
                ? Carbon::parse($order->expires_at)->subMonths($siklusBulan)->toDateString()
                : $order->created_at?->toDateString(),
            'berakhir' => $order->expires_at?->toDateString(),
            'sisa_hari' => $sisaHari,
            'masa_aktif' => $this->labelMasaAktif($sisaHari, $order->isService(), $order->status),
            'nominal_perpanjangan' => $this->nominalPerpanjangan($order),
            'rincian' => $order->orderItems->map(fn ($item) => [
                'jenis' => $item->type_label,
                'nama' => $item->display_name,
                'spesifikasi' => $item->display_spec,
                'jumlah' => (int) $item->quantity,
                'nominal' => round((float) $item->price * (int) $item->quantity, 2),
            ])->values()->all(),
        ];
    }

    private function namaLayanan(Order $order): string
    {
        $rincian = $order->orderItems
            ->map(fn ($item) => $item->display_name)
            ->filter()
            ->unique()
            ->values();

        if ($rincian->isNotEmpty()) {
            return $rincian->take(2)->implode(' + ')
                .($rincian->count() > 2 ? ' +'.($rincian->count() - 2).' lainnya' : '');
        }

        return $order->domain_name ?: ('Layanan #'.$order->id);
    }

    /** Nominal yang akan ditagih saat perpanjangan (sumber sama dengan cron invoice). */
    private function nominalPerpanjangan(Order $order): float
    {
        if (! $order->isService() || $order->orderItems->isEmpty()) {
            return 0.0;
        }

        return round((float) $this->renewal->renewalAmounts($order)['net'], 2);
    }

    private function labelMasaAktif(?int $sisaHari, bool $isLayanan, string $status): string
    {
        if (! $isLayanan) {
            return 'Belum aktif';
        }

        if ($status === 'terminated') {
            return 'Dihentikan';
        }

        if ($sisaHari === null) {
            return 'Berjalan';
        }

        if ($sisaHari < 0) {
            return 'Kadaluarsa';
        }

        return $sisaHari <= self::AMBANG_SEGERA_HARI ? 'Segera berakhir' : 'Berjalan';
    }

    private function labelSiklus(?string $siklus): string
    {
        return match ($siklus) {
            'monthly' => 'Bulanan',
            'quarterly' => 'Triwulan',
            'semi_annually', 'semi_annual' => '6 Bulanan',
            'annually', 'annual' => 'Tahunan',
            'onetime' => 'Sekali Bayar',
            default => $siklus ? ucfirst(str_replace('_', ' ', $siklus)) : '—',
        };
    }

    private function labelStatusKlien(?string $status): string
    {
        return match ($status) {
            'active' => 'Aktif',
            'inactive' => 'Tidak aktif',
            'suspended' => 'Ditangguhkan',
            default => ucfirst((string) $status),
        };
    }

    private function urlAdminKlien(int $id): string
    {
        return rtrim((string) config('app.url'), '/').'/admin/customers/'.$id;
    }
}
