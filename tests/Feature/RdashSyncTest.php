<?php

use App\Models\Customer;
use App\Models\DomainPrice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RdashDomain;
use App\Services\DomainAvailabilityService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Lapis 1 — sinkronisasi domain RDASH ↔ order WSCRM
|--------------------------------------------------------------------------
| Tidak memanggil API asli: semua request di-fake (Http::fake) sehingga tes ini
| aman dijalankan tanpa kredensial produksi.
*/

beforeEach(function () {
    config()->set('services.rdash.reseller_id', '123');
    config()->set('services.rdash.api_key', 'rahasia-api-key');
    config()->set('services.rdash.base_url', 'https://api.rdash.id/v1');
    Storage::fake('local');
    $GLOBALS['rdash_captured'] = [];
});

/** Semua request yang dikirim ke API RDASH pada tes yang sedang jalan. */
function rdashCallsTo(string $needle = ''): array
{
    return collect($GLOBALS['rdash_captured'] ?? [])
        ->filter(fn (array $call) => str_contains($call['url'], 'api.rdash.id') && str_contains($call['url'], $needle))
        ->values()
        ->all();
}

/** Buat order domain + item domain-nya (nama domain di tabel orders, mengikuti data produksi). */
function rdashOrderItem(string $domain, ?string $expires, string $statusItem = 'active'): OrderItem
{
    $customer = Customer::factory()->create();

    $order = Order::query()->create([
        'customer_id' => $customer->id,
        'order_type' => 'domain',
        'service_type' => 'domain',
        'domain_name' => $domain,
        'total_amount' => 150000,
        'status' => 'active',
        'billing_cycle' => 'annually',
        'expires_at' => $expires,
    ]);

    return OrderItem::query()->create([
        'order_id' => $order->id,
        'item_type' => 'domain',
        'item_id' => 1,
        'domain_name' => null,
        'quantity' => 1,
        'price' => 150000,
        'billing_cycle' => 'annually',
        'expires_at' => $expires,
        'status' => $statusItem,
    ]);
}

/** Baris domain seperti balasan GET /domains. */
function rdashDomainRow(string $name, array $overrides = []): array
{
    return array_merge([
        'id' => crc32($name) % 100000,
        'name' => $name,
        'status' => 1,
        'status_label' => 'Active',
        'status_reason' => null,
        'verification_status' => 0,
        'verification_status_label' => 'Waiting',
        'is_premium' => 0,
        'is_locked' => 1,
        'is_registrar_locked' => 1,
        'customer_id' => 9,
        'nameserver_1' => 'ns1.websweetstudio.com',
        'nameserver_2' => 'ns2.websweetstudio.com',
        'notes' => null,
        'expired_at' => '2027-09-21 00:00:00',
        'created_at' => '2026-09-21T00:00:00.000000Z',
    ], $overrides);
}

/**
 * Fake seluruh endpoint RDASH yang dipakai Lapis 1.
 *
 * @param  array<int, array<string, mixed>>  $domains
 * @param  array<int, array<string, mixed>>  $prices
 */
function rdashFakeApi(array $domains = [], array $prices = [], ?int $status = 200, string $message = 'Success'): void
{
    Http::fake(function ($request) use ($domains, $prices, $status, $message) {
        $url = $request->url();
        $GLOBALS['rdash_captured'][] = [
            'url' => $url,
            'auth' => $request->header('Authorization')[0] ?? null,
        ];

        if ($status !== 200) {
            return Http::response(['success' => false, 'message' => $message], $status);
        }

        if (str_contains($url, '/account/profile')) {
            return Http::response(['success' => true, 'data' => [
                'id' => 123, 'name' => 'Websweet Studio', 'email' => 'admin@websweetstudio.com',
                'subdomain' => 'websweet', 'currency' => 'IDR',
            ]], 200);
        }

        if (str_contains($url, '/account/balance')) {
            return Http::response(['success' => true, 'data' => ['currency' => 'IDR', 'balance' => '1500000.00']], 200);
        }

        if (str_contains($url, '/account/prices')) {
            return Http::response(['data' => $prices, 'meta' => ['current_page' => 1, 'last_page' => 1, 'per_page' => 100, 'total' => count($prices)]], 200);
        }

        if (str_contains($url, '/domains/availability')) {
            $name = $domains[0]['name'] ?? 'contoh.my.id';

            return Http::response(['success' => true, 'data' => [
                'name' => $name, 'available' => 1, 'message' => 'available',
                'is_premium_name' => false, 'premium_registration_price' => 0,
            ]], 200);
        }

        if (str_contains($url, '/domains')) {
            return Http::response([
                'data' => $domains,
                'meta' => ['current_page' => 1, 'last_page' => 1, 'per_page' => 100, 'total' => count($domains)],
            ], 200);
        }

        return Http::response(['success' => false, 'message' => 'endpoint tak terduga: '.$url], 404);
    });
}

it('menolak jalan tanpa kredensial dan menyebut variabel env yang kurang', function () {
    config()->set('services.rdash.reseller_id', null);
    config()->set('services.rdash.api_key', null);

    $this->artisan('rdash:sync-domains')
        ->expectsOutputToContain('RDASH_RESELLER_ID')
        ->assertExitCode(1);

    expect(RdashDomain::query()->count())->toBe(0);
});

it('memvalidasi kredensial lewat rdash:check', function () {
    rdashFakeApi([rdashDomainRow('websweetstudio.com')]);

    $this->artisan('rdash:check')
        ->expectsOutputToContain('Kredensial RDASH valid')
        ->assertExitCode(0);

    // Basic Auth = base64(resellerId:apiKey)
    expect(rdashCallsTo('/account/profile')[0]['auth'])->toBe('Basic '.base64_encode('123:rahasia-api-key'));
});

it('memberi petunjuk whitelist IP ketika API menolak autentikasi', function () {
    rdashFakeApi([], [], 401, 'Unauthenticated.');

    $this->artisan('rdash:sync-domains')
        ->expectsOutputToContain('whitelist')
        ->assertExitCode(1);
});

it('dry-run melaporkan drift tanpa menulis ke database', function () {
    rdashOrderItem('websweetstudio.com', '2027-09-01');

    rdashFakeApi([rdashDomainRow('websweetstudio.com', ['expired_at' => '2027-09-21 00:00:00'])]);

    $this->artisan('rdash:sync-domains')
        ->expectsOutputToContain('DRY-RUN')
        ->assertExitCode(0);

    expect(RdashDomain::query()->count())->toBe(0)
        ->and(OrderItem::query()->first()->expires_at->toDateString())->toBe('2027-09-01');
});

it('apply menyimpan cermin domain beserta status pencocokan', function () {
    $item = rdashOrderItem('websweetstudio.com', '2027-09-01');
    rdashOrderItem('domainpalsu.id', '2027-01-01');   // tidak ada di RDash → missing

    rdashFakeApi([
        rdashDomainRow('websweetstudio.com', ['expired_at' => '2027-09-21 00:00:00', 'id' => 555]),
        rdashDomainRow('belumada.com', ['id' => 777]),
    ]);

    $this->artisan('rdash:sync-domains --apply')->assertExitCode(0);

    $drift = RdashDomain::query()->where('name', 'websweetstudio.com')->first();
    expect($drift->match_status)->toBe('drift')
        ->and($drift->drift_days)->toBe(20)
        ->and($drift->rdash_id)->toBe(555)
        ->and($drift->order_item_id)->toBe($item->id)
        ->and($drift->order_expires_at->toDateString())->toBe('2027-09-01')
        ->and($drift->expired_at->toDateString())->toBe('2027-09-21');

    expect(RdashDomain::query()->where('name', 'belumada.com')->first()->match_status)->toBe('unmatched');
    expect(RdashDomain::query()->where('name', 'domainpalsu.id')->first()->match_status)->toBe('missing');
});

it('fix-expiry memperbaiki tanggal order dan menulis skrip rollback', function () {
    $item = rdashOrderItem('websweetstudio.com', '2027-09-01');

    rdashFakeApi([rdashDomainRow('websweetstudio.com', ['expired_at' => '2027-09-21 00:00:00'])]);

    $this->artisan('rdash:sync-domains --apply --fix-expiry')->assertExitCode(0);

    expect($item->fresh()->expires_at->toDateString())->toBe('2027-09-21');

    $files = Storage::disk('local')->files('rdash');
    expect($files)->toHaveCount(1);

    $sql = Storage::disk('local')->get($files[0]);
    expect($sql)->toContain("UPDATE order_items SET expires_at = '2027-09-01' WHERE id = {$item->id};");
});

it('tidak memperpendek masa layanan klien kecuali --both-ways', function () {
    $item = rdashOrderItem('websweetstudio.com', '2027-12-31');

    rdashFakeApi([rdashDomainRow('websweetstudio.com', ['expired_at' => '2027-09-21 00:00:00'])]);

    $this->artisan('rdash:sync-domains --apply --fix-expiry')
        ->expectsOutputToContain('dilewati')
        ->assertExitCode(0);

    expect($item->fresh()->expires_at->toDateString())->toBe('2027-12-31');

    $this->artisan('rdash:sync-domains --apply --fix-expiry --both-ways')->assertExitCode(0);

    expect($item->fresh()->expires_at->toDateString())->toBe('2027-09-21');
});

it('menghormati toleransi selisih hari', function () {
    rdashOrderItem('websweetstudio.com', '2027-09-20');
    rdashFakeApi([rdashDomainRow('websweetstudio.com', ['expired_at' => '2027-09-21 00:00:00'])]);

    $this->artisan('rdash:sync-domains --apply')->assertExitCode(0);
    expect(RdashDomain::query()->first()->match_status)->toBe('drift');

    $this->artisan('rdash:sync-domains --apply --tolerance=1')->assertExitCode(0);
    expect(RdashDomain::query()->first()->match_status)->toBe('matched');
});

it('sinkronisasi harga modal menambahkan PPN dan tidak mengubah harga jual', function () {
    $local = DomainPrice::query()->create([
        'extension' => '.my.id',
        'base_cost' => 10000,
        'renewal_cost' => 22000,
        'selling_price' => 15000,
        'renewal_price_with_tax' => 25000,
        'is_active' => true,
    ]);

    rdashFakeApi([], [[
        'id' => 71,
        'domain_extension' => ['id' => 2, 'extension' => '.my.id'],
        'currency' => 'IDR',
        'registration' => ['1' => 12000, '2' => 24000],
        'renewal' => ['1' => 25000],
        'transfer' => '12000.00',
    ]]);

    $this->artisan('rdash:sync-prices --apply')->assertExitCode(0);

    $local->refresh();
    // Harga RDash eksklusif pajak → modal = harga API + PPN 11% (12000 × 1,11 = 13320).
    expect((float) $local->base_cost)->toBe(13320.0)
        ->and((float) $local->renewal_cost)->toBe(27750.0)
        ->and((float) $local->selling_price)->toBe(15000.0);
});

it('opsi --no-tax menyimpan modal persis seperti harga RDash', function () {
    DomainPrice::query()->create([
        'extension' => '.my.id',
        'base_cost' => 10000,
        'renewal_cost' => 22000,
        'selling_price' => 15000,
        'renewal_price_with_tax' => 25000,
        'is_active' => true,
    ]);

    rdashFakeApi([], [[
        'id' => 71,
        'domain_extension' => ['id' => 2, 'extension' => '.my.id'],
        'currency' => 'IDR',
        'registration' => ['1' => 12000, '2' => 24000],
        'renewal' => ['1' => 25000],
        'transfer' => '12000.00',
    ]]);

    $this->artisan('rdash:sync-prices --apply --no-tax')->assertExitCode(0);

    $local = DomainPrice::query()->where('extension', '.my.id')->first();
    expect((float) $local->base_cost)->toBe(12000.0)
        ->and((float) $local->renewal_cost)->toBe(25000.0);
});

it('opsi --keep mempertahankan harga jual TLD promo walau marginnya negatif', function () {
    $local = DomainPrice::query()->create([
        'extension' => '.com',
        'base_cost' => 227550,
        'renewal_cost' => 227550,
        'selling_price' => 225000,
        'renewal_price_with_tax' => 265000,
        'is_active' => true,
    ]);

    rdashFakeApi([], [[
        'id' => 72,
        'domain_extension' => ['id' => 3, 'extension' => '.com'],
        'currency' => 'IDR',
        'registration' => ['1' => 205000],
        'renewal' => ['1' => 205000],
        'transfer' => '205000.00',
    ]]);

    // --fix-selling dengan margin 15% + --keep=.com → harga jual dibiarkan 225.000.
    $this->artisan('rdash:sync-prices --apply --fix-selling --min-margin=15 --keep=.com')->assertExitCode(0);

    $local->refresh();
    expect((float) $local->selling_price)->toBe(225000.0)
        ->and((float) $local->renewal_price_with_tax)->toBe(265000.0)
        ->and((float) $local->base_cost)->toBe(227550.0);
});

it('tanpa --keep harga jual TLD promo ikut diperbaiki ke modal + markup', function () {
    $local = DomainPrice::query()->create([
        'extension' => '.com',
        'base_cost' => 227550,
        'renewal_cost' => 227550,
        'selling_price' => 225000,
        'renewal_price_with_tax' => 265000,
        'is_active' => true,
    ]);

    rdashFakeApi([], [[
        'id' => 72,
        'domain_extension' => ['id' => 3, 'extension' => '.com'],
        'currency' => 'IDR',
        'registration' => ['1' => 205000],
        'renewal' => ['1' => 205000],
        'transfer' => '205000.00',
    ]]);

    // Daftar --keep diisi TLD lain → .com kembali dihitung (265.000 = 227.550 + 15%, bulat 5.000).
    $this->artisan('rdash:sync-prices --apply --fix-selling --min-margin=15 --keep=.id')->assertExitCode(0);

    $local->refresh();
    expect((float) $local->selling_price)->toBe(265000.0);
});

it('konfigurasi RDASH_KEEP_SELLING melindungi harga jual dari cron/sinkronisasi', function () {
    config()->set('services.rdash.keep_selling', '.com');

    $local = DomainPrice::query()->create([
        'extension' => '.com',
        'base_cost' => 227550,
        'renewal_cost' => 227550,
        'selling_price' => 225000,
        'renewal_price_with_tax' => 265000,
        'is_active' => true,
    ]);

    rdashFakeApi([], [[
        'id' => 72,
        'domain_extension' => ['id' => 3, 'extension' => '.com'],
        'currency' => 'IDR',
        'registration' => ['1' => 205000],
        'renewal' => ['1' => 205000],
        'transfer' => '205000.00',
    ]]);

    $this->artisan('rdash:sync-prices --apply --fix-selling --min-margin=15')->assertExitCode(0);

    $local->refresh();
    expect((float) $local->selling_price)->toBe(225000.0);
});

it('margin nominal 10.000 dengan pembulatan ke atas 5.000', function () {
    $local = DomainPrice::query()->create([
        'extension' => '.example',
        'base_cost' => 50000,
        'renewal_cost' => 50000,
        'selling_price' => 60000,
        'renewal_price_with_tax' => 60000,
        'is_active' => true,
    ]);

    rdashFakeApi([], [[
        'id' => 73,
        'domain_extension' => ['id' => 4, 'extension' => '.example'],
        'currency' => 'IDR',
        'registration' => ['1' => 97000],
        'renewal' => ['1' => 97000],
        'transfer' => '97000.00',
    ]]);

    $this->artisan('rdash:sync-prices --apply --fix-selling --margin=10000 --no-tax')->assertExitCode(0);

    $local->refresh();
    // 97.000 + 10.000 = 107.000 → dibulatkan ke atas ke 110.000.
    expect((float) $local->base_cost)->toBe(97000.0)
        ->and((float) $local->selling_price)->toBe(110000.0)
        ->and((float) $local->renewal_price_with_tax)->toBe(110000.0);
});

it('margin nominal juga menurunkan harga jual yang terlalu tinggi', function () {
    $local = DomainPrice::query()->create([
        'extension' => '.example',
        'base_cost' => 100000,
        'renewal_cost' => 100000,
        'selling_price' => 300000,
        'renewal_price_with_tax' => 300000,
        'is_active' => true,
    ]);

    rdashFakeApi([], [[
        'id' => 73,
        'domain_extension' => ['id' => 4, 'extension' => '.example'],
        'currency' => 'IDR',
        'registration' => ['1' => 100000],
        'renewal' => ['1' => 100000],
        'transfer' => '100000.00',
    ]]);

    $this->artisan('rdash:sync-prices --apply --fix-selling --margin=10000 --no-tax')->assertExitCode(0);

    $local->refresh();
    expect((float) $local->selling_price)->toBe(110000.0);
});

/*
|--------------------------------------------------------------------------
| Harga promo registrasi (promo_registration) dari RDASH
|--------------------------------------------------------------------------
| RDash hanya memberi promo untuk registrasi siklus 1 tahun — perpanjangan
| dan transfer tetap harga normal. Promo disimpan terpisah dari harga normal
| sehingga harga jual otomatis kembali normal begitu periode promo lewat.
*/

it('menyimpan harga promo registrasi dan memakai modal promo untuk harga jual', function () {
    $local = DomainPrice::query()->create([
        'extension' => '.com',
        'base_cost' => 227550,
        'renewal_cost' => 227550,
        'selling_price' => 225000,
        'renewal_price_with_tax' => 225000,
        'is_active' => true,
    ]);

    rdashFakeApi([], [[
        'id' => 1796,
        'domain_extension' => ['id' => 5, 'extension' => '.com'],
        'currency' => 'IDR',
        'registration' => ['1' => 205000],
        'renewal' => ['1' => 205000],
        'transfer' => '205000.00',
        'promo_registration' => [
            'registration' => ['1' => '190000'],
            'start_date' => '2026-03-31T18:00:00.000000Z',
            'end_date' => '2026-09-30T16:59:00.000000Z',
            'description' => '<ul><li>Harga promo hanya berlaku untuk registrasi siklus 1 tahun</li><li>Tidak berlaku untuk perpanjang dan transfer domain</li></ul>',
        ],
    ]]);

    $this->artisan('rdash:sync-prices --apply --fix-selling --margin=10000 --keep=.com')->assertExitCode(0);

    $local->refresh();

    // Modal promo = 190.000 × 1,11 = 210.900 → + margin 10.000 = 220.900 → bulat ke atas 5.000 = 225.000
    expect((float) $local->promo_price)->toBe(190000.0)
        ->and((float) $local->promo_base_cost)->toBe(210900.0)
        ->and((float) $local->promo_selling_price)->toBe(225000.0)
        ->and((float) $local->selling_price)->toBe(225000.0)
        // --keep hanya mengunci REGISTRASI; perpanjangan ikut aturan margin:
        // 227.550 + 10.000 = 237.550 → bulat ke atas 5.000 = 240.000
        ->and((float) $local->renewal_price_with_tax)->toBe(240000.0)
        ->and($local->promo_starts_at?->setTimezone('UTC')->toDateString())->toBe('2026-03-31')
        ->and($local->promo_ends_at?->setTimezone('UTC')->toDateString())->toBe('2026-09-30')
        ->and($local->promo_note)->toContain('registrasi siklus 1 tahun')
        ->and($local->promo_note)->not->toContain('<li>');
});

it('harga perpanjangan ikut margin 10.000 + bulat 5.000 walau registrasi dikunci --keep', function () {
    $local = DomainPrice::query()->create([
        'extension' => '.com',
        'base_cost' => 227550,
        'renewal_cost' => 227550,
        'selling_price' => 225000,
        'renewal_price_with_tax' => 225000,
        'is_active' => true,
    ]);

    rdashFakeApi([], [[
        'id' => 72,
        'domain_extension' => ['id' => 3, 'extension' => '.com'],
        'currency' => 'IDR',
        'registration' => ['1' => 205000],
        'renewal' => ['1' => 205000],
        'transfer' => '205000.00',
    ]]);

    $this->artisan('rdash:sync-prices --apply --fix-selling --margin=10000 --keep=.com')->assertExitCode(0);

    $local->refresh();

    // Registrasi tetap 225.000 (dikunci), perpanjangan naik ke modal + 10.000 dibulatkan.
    expect((float) $local->selling_price)->toBe(225000.0)
        ->and((float) $local->renewal_price_with_tax)->toBe(240000.0);
});

it('mengabaikan promo yang bukan untuk siklus 1 tahun (mis. .id hanya promo 2 tahun)', function () {
    $local = DomainPrice::query()->create([
        'extension' => '.id',
        'base_cost' => 233100,
        'renewal_cost' => 238650,
        'selling_price' => 245000,
        'renewal_price_with_tax' => 250000,
        'is_active' => true,
    ]);

    rdashFakeApi([], [[
        'id' => 1802,
        'domain_extension' => ['id' => 3, 'extension' => '.id'],
        'currency' => 'IDR',
        'registration' => ['1' => 210000, '2' => 420000],
        'renewal' => ['1' => 215000],
        'transfer' => '210000.00',
        'promo_registration' => [
            // Promo hanya berlaku untuk siklus 2 tahun → HARUS diabaikan untuk harga 1 tahun.
            'registration' => ['1' => '', '2' => '300000'],
            'start_date' => '2026-09-15T01:00:00.000000Z',
            'end_date' => '2026-12-31T16:59:00.000000Z',
            'description' => 'promo 2 tahun',
        ],
    ]]);

    $this->artisan('rdash:sync-prices --apply --fix-selling --margin=10000')->assertExitCode(0);

    $local->refresh();

    expect($local->promo_selling_price)->toBeNull()
        ->and($local->promo_price)->toBeNull()
        ->and((float) $local->selling_price)->toBe(245000.0)
        ->and($local->priceNow())->toBe(245000.0);
});

it('memperpanjang TIDAK memakai harga promo — perpanjangan tetap harga normal', function () {
    $local = DomainPrice::query()->create([
        'extension' => '.xyz',
        'base_cost' => 277500,
        'renewal_cost' => 277500,
        'selling_price' => 290000,
        'renewal_price_with_tax' => 290000,
        'is_active' => true,
    ]);

    rdashFakeApi([], [[
        'id' => 1800,
        'domain_extension' => ['id' => 9, 'extension' => '.xyz'],
        'currency' => 'IDR',
        'registration' => ['1' => 250000],
        'renewal' => ['1' => 250000],
        'transfer' => '250000.00',
        'promo_registration' => [
            'registration' => ['1' => '38000'],
            'start_date' => '2025-02-24T08:00:00.000000Z',
            'end_date' => '2026-09-30T16:59:00.000000Z',
            'description' => 'Harga promo hanya untuk registrasi 1 tahun',
        ],
    ]]);

    $this->artisan('rdash:sync-prices --apply --fix-selling --margin=10000')->assertExitCode(0);

    $local->refresh();

    // Registrasi pakai modal promo (38.000 × 1,11 = 42.180 → +10.000 → 55.000),
    // perpanjangan tetap dihitung dari modal normal (250.000 × 1,11 = 277.500 → +10.000 → 290.000).
    expect((float) $local->promo_selling_price)->toBe(55000.0)
        ->and((float) $local->renewal_price_with_tax)->toBe(290000.0)
        ->and((float) $local->selling_price)->toBe(290000.0);
});

it('harga jual efektif mengikuti promo dan otomatis kembali normal di luar periode promo', function () {
    $row = DomainPrice::query()->create([
        'extension' => '.cloud',
        'base_cost' => 482850,
        'renewal_cost' => 482850,
        'selling_price' => 495000,
        'renewal_price_with_tax' => 495000,
        'promo_price' => 39900,
        'promo_base_cost' => 44289,
        'promo_selling_price' => 55000,
        'promo_starts_at' => now('UTC')->subDays(2),
        'promo_ends_at' => now('UTC')->addDays(3),
        'is_active' => true,
    ]);

    // Promo sedang berjalan → harga jual = harga promo, modal = modal promo.
    expect($row->priceNow())->toBe(55000.0)
        ->and($row->costNow())->toBe(44289.0)
        ->and($row->promoIsActive())->toBeTrue()
        ->and($row->promoDaysRemaining())->toBe(3)
        ->and($row->promoCutAmount())->toBe(440000.0)
        ->and($row->effective_selling_price)->toBe(55000.0)
        ->and($row->promo_active)->toBeTrue();

    // Promo berakhir → harga jual & modal otomatis kembali ke harga normal.
    $row->promo_ends_at = now('UTC')->subDay();
    $row->save();
    $row->refresh();

    expect($row->promoIsActive())->toBeFalse()
        ->and($row->priceNow())->toBe(495000.0)
        ->and($row->costNow())->toBe(482850.0)
        ->and($row->promoCutAmount())->toBeNull();

    // Promo berakhir tepat kemarin → tidak aktif tetapi ada data promo.
    expect($row->promo_active)->toBeFalse()
        ->and((float) $row->promo_selling_price)->toBe(55000.0);

    // Belum mulai → juga memakai harga normal.
    $row->promo_starts_at = now('UTC')->addDay();
    $row->promo_ends_at = now('UTC')->addDays(5);
    $row->save();

    expect($row->promoIsActive())->toBeFalse()
        ->and($row->priceNow())->toBe(495000.0);
});

it('promo yang tidak menurunkan harga klien tidak dianggap promo (kasus .com dikunci 225.000)', function () {
    // Kondisi nyata hasil `--keep=.com`: promo RDash 190.000 dipetakan jadi harga
    // jual promo 225.000 — sama dengan harga jual normal yang dikunci manual.
    $row = DomainPrice::query()->create([
        'extension' => '.com',
        'base_cost' => 227550,
        'renewal_cost' => 227550,
        'selling_price' => 225000,
        'renewal_price_with_tax' => 240000,
        'promo_price' => 190000,
        'promo_base_cost' => 210900,
        'promo_selling_price' => 225000,
        'promo_starts_at' => now('UTC')->subDay(),
        'promo_ends_at' => now('UTC')->addDays(3),
        'is_active' => true,
    ]);

    // Jendela promo terbuka & data promo tersimpan, tapi harga klien tidak turun
    // → jangan ditandai promo (badge PROMO + harga coret akan menyesatkan).
    expect($row->promoWindowOpen())->toBeTrue()
        ->and($row->promoIsCheaper())->toBeFalse()
        ->and($row->promoIsActive())->toBeFalse()
        ->and($row->promoIsMuted())->toBeTrue()
        ->and($row->priceNow())->toBe(225000.0)
        ->and($row->effective_selling_price)->toBe(225000.0)
        ->and($row->promo_active)->toBeFalse()
        ->and($row->promo_muted)->toBeTrue()
        ->and($row->promoCutAmount())->toBeNull()
        ->and($row->promoDaysRemaining())->toBeNull()
        ->and($row->costNow())->toBe(227550.0);

    // Tidak ikut terhitung di daftar promo aktif, sedangkan TLD yang benar-benar
    // lebih murah tetap masuk.
    expect(DomainPrice::query()->withActivePromo()->pluck('extension')->all())->not->toContain('.com');

    $cheaper = DomainPrice::query()->create([
        'extension' => '.xyz',
        'base_cost' => 257000,
        'renewal_cost' => 257000,
        'selling_price' => 290000,
        'renewal_price_with_tax' => 290000,
        'promo_price' => 33000,
        'promo_base_cost' => 36630,
        'promo_selling_price' => 55000,
        'promo_starts_at' => now('UTC')->subDay(),
        'promo_ends_at' => now('UTC')->addDays(3),
        'is_active' => true,
    ]);

    expect($cheaper->promoIsActive())->toBeTrue()
        ->and($cheaper->promo_muted)->toBeFalse()
        ->and(DomainPrice::query()->withActivePromo()->pluck('extension')->all())->toContain('.xyz');
});

it('opsi --no-promo tidak menyimpan data promo dari RDash', function () {
    $local = DomainPrice::query()->create([
        'extension' => '.my.id',
        'base_cost' => 25000,
        'renewal_cost' => 25000,
        'selling_price' => 35000,
        'renewal_price_with_tax' => 35000,
        'is_active' => true,
    ]);

    rdashFakeApi([], [[
        'id' => 71,
        'domain_extension' => ['id' => 2, 'extension' => '.my.id'],
        'currency' => 'IDR',
        'registration' => ['1' => 22000],
        'renewal' => ['1' => 22000],
        'transfer' => '22000.00',
        'promo_registration' => [
            'registration' => ['1' => '9500'],
            'start_date' => '2025-06-30T17:00:00.000000Z',
            'end_date' => '2026-09-30T16:59:00.000000Z',
            'description' => 'promo',
        ],
    ]]);

    $this->artisan('rdash:sync-prices --apply --no-promo')->assertExitCode(0);

    $local->refresh();

    expect($local->promo_selling_price)->toBeNull()
        ->and($local->promo_price)->toBeNull()
        ->and($local->promo_ends_at)->toBeNull();
});

it('cek ketersediaan domain memakai Basic Auth dan hasil asli RDash', function () {
    rdashFakeApi([rdashDomainRow('websweetstudio.my.id')]);

    $result = app(DomainAvailabilityService::class)->checkAvailability('websweetstudio.my.id');

    expect($result['available'])->toBeTrue()
        ->and($result['fallback'])->toBeFalse()
        ->and($result['status'])->toBe('available');

    expect(rdashCallsTo('/domains/availability')[0]['auth'])->toBe('Basic '.base64_encode('123:rahasia-api-key'));
});

it('jatuh ke fallback (ditandai) ketika kredensial belum diisi', function () {
    config()->set('services.rdash.reseller_id', null);
    config()->set('services.rdash.api_key', null);

    $result = app(DomainAvailabilityService::class)->checkAvailability('websweetstudio-panjang123.my.id');

    expect($result['fallback'])->toBeTrue()
        ->and($result['success'])->toBeFalse()
        ->and($result['message'])->toContain('RDASH_RESELLER_ID');
});
