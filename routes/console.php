<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Auto update & pengecekan website terjadwal
Schedule::command('websites:auto-update')->dailyAt('03:00');
// Cek uptime website harian → catat ke jurnal + notifikasi jika down
Schedule::command('websites:check-uptime')->dailyAt('08:00');
