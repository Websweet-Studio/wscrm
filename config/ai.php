<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Kredit AI Customer
    |--------------------------------------------------------------------------
    |
    | Masa aktif (dalam hari) kredit yang masuk ke saldo customer. Berlaku per
    | top-up/pemberian kredit (pembelian paket maupun penyesuaian manual admin).
    | Kredit yang lewat masa aktifnya dipotong otomatis oleh command
    | `ai-credits:expire` (dijadwalkan harian).
    |
    */

    'credit_ttl_days' => (int) env('AI_CREDIT_TTL_DAYS', 30),

];
