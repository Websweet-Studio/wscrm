<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Penjadwalan tugas berkala
|--------------------------------------------------------------------------
| PENTING: Laravel scheduler (`php artisan schedule:run`) TIDAK dipasang di
| server produksi — tugas tidak dijalankan oleh `schedule:run` tiap menit.
|
| Penjadwalan nyata = cron harian sekali per tugas:
|   /etc/cron.d/wscrm-jobs  →  /usr/local/bin/wscrm-job.sh
|   - websites:auto-update         01:30 WIB
|   - websites:check-uptime        02:00 WIB
|   - invoice:generate-renewals    02:30 WIB
|   - ai-credits:expire            03:00 WIB
|   - services:expire              04:00 WIB (invoice → overdue, layanan → expired)
|   (harga & status domain RDash: /etc/cron.d/wscrm-rdash, 01:15 WIB)
|
| Log semua tugas: storage/logs/cron-jobs.log
|
| Blok Schedule di bawah DIPERTAHANKAN hanya sebagai rujukan niat jadwal.
| Kalau suatu saat `schedule:run` tiap menit dipasang, RAJIN: matikan dulu
| baris-baris di /etc/cron.d/wscrm-jobs supaya tugas tidak jalan dua kali.
| Catatan: aplikasi berzona waktu UTC, jadi dailyAt('03:00') = 10:00 WIB.
*/

// Auto update plugin/tema website klien yang schedulernya aktif
Schedule::command('websites:auto-update')->dailyAt('03:00');

// Cek uptime website harian → catat ke jurnal + notifikasi ke admin jika down
Schedule::command('websites:check-uptime')->dailyAt('08:00');

// Generate invoice renewal untuk layanan yang hampir habis masa berlakunya
Schedule::command('invoice:generate-renewals')->dailyAt('09:00');

// Potong kredit AI customer yang sudah lewat masa aktif (default 30 hari)
Schedule::command('ai-credits:expire')->dailyAt('09:30');

// Rapikan status menggantung: invoice lewat jatuh tempo → overdue, layanan lewat
// masa aktif → expired (toleransi 7 hari supaya tidak buru-buru mematikan layanan).
Schedule::command('services:expire', ['--grace' => 7])->dailyAt('10:00');
