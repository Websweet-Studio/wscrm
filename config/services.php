<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    | RDASH — Domain Reseller Open API (https://api.rdash.id/v1).
    | Auth: Basic Auth (resellerId:apiKey). IP server wajib di-whitelist di panel RDash.
    */
    'rdash' => [
        'reseller_id' => env('RDASH_RESELLER_ID', env('RNA_RESELLER_ID')),
        'api_key' => env('RDASH_API_KEY', env('RNA_API')),
        'base_url' => env('RDASH_BASE_URL', 'https://api.rdash.id/v1'),
        'timeout' => env('RDASH_TIMEOUT', 25),

        // TLD yang harga jualnya DIPERTAHANKAN manual (harga promo), tidak ikut
        // dihitung ulang oleh `rdash:sync-prices --fix-selling`. Pisahkan dengan koma.
        'keep_selling' => env('RDASH_KEEP_SELLING', '.com'),
    ],

    // Alias lama (kompatibilitas) — config('services.rna.*')
    'rna' => [
        'reseller_id' => env('RDASH_RESELLER_ID', env('RNA_RESELLER_ID')),
        'api_key' => env('RDASH_API_KEY', env('RNA_API')),
        'base_url' => env('RDASH_BASE_URL', 'https://api.rdash.id/v1'),
    ],

    'agent' => [
        'token' => env('AGENT_API_TOKEN'),
    ],

    /*
    | Data klien untuk aplikasi mitra (app.digitalkonsultan.com).
    | Token mesin TERPISAH dari token agent — hanya boleh membaca data klien.
    */
    'dk' => [
        'token' => env('DK_DATA_TOKEN'),
        // Lingkup klien yang boleh dibaca aplikasi DK (ID customer WSCRM, dipisah koma).
        // Kosong = semua klien. Contoh: DK_KLIEN_IDS=39 (hanya akun Hidayat).
        'klien_ids' => array_values(array_filter(array_map(
            'intval',
            explode(',', (string) env('DK_KLIEN_IDS', ''))
        ))),
    ],

    /*
    | WSD Shield — pengaman server (dashboard dash.wsd.my.id).
    | Dipakai halaman pelanggan "IP Block": cek blokir pada domain pelanggan +
    | pengajuan whitelist sementara yang langsung diterima.
    | Token mesin TERPISAH dari token agent (WSD_CUSTOMER_TOKEN di /etc/wsd-shield.env).
    */
    'shield' => [
        'url' => env('WSD_SHIELD_URL', 'http://127.0.0.1:3110'),
        'token' => env('WSD_SHIELD_CUSTOMER_TOKEN'),
        'timeout' => env('WSD_SHIELD_TIMEOUT', 6),
        // Masa berlaku whitelist sementara (jam). Jumlah pengajuan tidak dibatasi.
        'jam' => env('WSD_SHIELD_JAM', 24),
    ],

];
