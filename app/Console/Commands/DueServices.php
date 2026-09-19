<?php

namespace App\Console\Commands;

use App\Services\ServiceRenewalService;
use Illuminate\Console\Command;

class DueServices extends Command
{
    protected $signature = 'service:due
        {--days=60 : Rentang hari ke depan}
        {--only-future : Sembunyikan layanan yang sudah lewat jatuh tempo}
        {--json : Keluarkan JSON (untuk dipakai agen/otomasi)}';

    protected $description = 'Daftar layanan aktif yang akan (atau sudah) jatuh tempo';

    public function handle(ServiceRenewalService $renewal): int
    {
        $rows = $renewal->due((int) $this->option('days'), ! $this->option('only-future'));

        if ($this->option('json')) {
            $this->line($rows->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return self::SUCCESS;
        }

        if ($rows->isEmpty()) {
            $this->info('Tidak ada layanan yang jatuh tempo dalam rentang ini.');

            return self::SUCCESS;
        }

        $this->table(
            ['Order', 'Domain', 'Pelanggan', 'Jatuh tempo', 'Sisa', 'Tagihan', 'Auto-renew'],
            $rows->map(fn (array $row) => [
                '#'.$row['id'],
                $row['domain'] ?? '-',
                $row['customer'] ?? '-',
                $row['expires_at'],
                $row['days_left'] < 0 ? 'LEWAT '.abs($row['days_left']).' hari' : $row['days_left'].' hari',
                'Rp'.number_format($row['amount'], 0, ',', '.'),
                $row['auto_renew'] ? 'ya' : 'tidak',
            ])->all()
        );

        $this->line(sprintf(
            'Total %d layanan · %d sudah lewat jatuh tempo.',
            $rows->count(),
            $rows->where('days_left', '<', 0)->count()
        ));

        return self::SUCCESS;
    }
}
