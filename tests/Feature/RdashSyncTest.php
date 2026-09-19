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
