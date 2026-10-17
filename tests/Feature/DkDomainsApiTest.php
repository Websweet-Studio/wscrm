<?php

use App\Services\DkClientDataService;

beforeEach(function () {
    config([
        'app.key' => 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=',
        'services.dk.token' => 'uji-token-domain',
    ]);
});

it('mengembalikan daftar domain lewat endpoint mesin dengan cache no-store', function () {
    $payload = [
        'data' => [[
            'id' => 123,
            'nama' => 'contoh.id',
            'status' => 1,
            'status_label' => 'Active',
            'expired_at' => '2027-01-01',
            'match_status' => 'matched',
            'order_id' => 45,
            'klien' => ['id' => 39, 'nama' => 'Hidayat Rohman'],
        ]],
        'meta' => ['jumlah' => 1, 'dihitung_pada' => now()->toIso8601String()],
    ];

    $service = Mockery::mock(DkClientDataService::class);
    $service->shouldReceive('domainsRdash')->once()->andReturn($payload);
    $this->app->instance(DkClientDataService::class, $service);

    $this->withHeader('Authorization', 'Bearer uji-token-domain')
        ->getJson('/api/dk/domains')
        ->assertOk()
        ->assertHeader('Cache-Control', 'no-store, private')
        ->assertJsonPath('data.0.nama', 'contoh.id')
        ->assertJsonPath('data.0.klien.nama', 'Hidayat Rohman')
        ->assertJsonPath('meta.jumlah', 1);
});

it('menolak endpoint domain tanpa token mesin yang benar', function () {
    $this->getJson('/api/dk/domains')->assertUnauthorized();
});
