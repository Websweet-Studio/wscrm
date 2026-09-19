<?php

namespace App\Console\Commands;

use App\Services\ServiceRenewalService;
use Illuminate\Console\Command;

class RenewService extends Command
{
    protected $signature = 'service:renew
        {target : Domain atau ID order (beberapa dipisah koma)}
        {--years=1 : Jumlah tahun perpanjangan}
        {--paid : Tandai invoice renewal sebagai lunas}
        {--paid-at= : Tanggal bayar (Y-m-d atau Y-m-d H:i:s), default hari ini}
        {--invoice : Buat invoice renewal kalau belum ada (tanpa kirim email)}
        {--amount= : Override nominal invoice (default: subtotal item)}
        {--no-extend : Jangan ubah tanggal jatuh tempo (hanya invoice/pembayaran/kerapian item)}
        {--activate-items : Set status item jadi active mengikuti order}
        {--force : Izinkan untuk layanan berstatus cancelled/terminated}
        {--dry-run : Tampilkan rencana tanpa mengubah data}';

    protected $description = 'Perpanjang layanan (order + item + invoice + pembayaran) dalam satu perintah';

    public function handle(ServiceRenewalService $renewal): int
    {
        $targets = array_values(array_filter(array_map('trim', explode(',', (string) $this->argument('target')))));
        $dryRun = (bool) $this->option('dry-run');

        if ($targets === []) {
            $this->error('Sebutkan minimal satu domain atau ID order.');

            return self::FAILURE;
        }

        if ($dryRun) {
            $this->warn('MODE DRY-RUN — tidak ada data yang diubah.');
        }

        $failed = 0;

        foreach ($targets as $target) {
            $order = $renewal->resolve($target);

            if (! $order) {
                $this->error("✗ {$target}: layanan tidak ditemukan (cek domain/ID).");
                $failed++;

                continue;
            }

            if (in_array($order->status, ['cancelled', 'terminated'], true) && ! $this->option('force')) {
                $this->error("✗ {$target}: status layanan `{$order->status}` — perpanjangan dilewati (pakai --force kalau memang disengaja).");
                $failed++;

                continue;
            }

            $result = $renewal->renew($order, [
                'years' => (int) $this->option('years'),
                'mark_paid' => (bool) $this->option('paid'),
                'paid_at' => $this->option('paid-at'),
                'create_invoice' => (bool) $this->option('invoice'),
                'amount' => $this->option('amount'),
                'extend' => ! (bool) $this->option('no-extend'),
                'activate_items' => (bool) $this->option('activate-items'),
                'dry_run' => $dryRun,
            ]);

            $this->line('');
            $this->info(sprintf('%s (#%d · %s)', $result['domain'] ?? '-', $result['order_id'], $result['customer'] ?? '-'));
            $this->line(sprintf('  status layanan : %s', $result['status']));
            $this->line($result['extended']
                ? sprintf('  jatuh tempo    : %s → %s (+%d tahun)', $result['old_expiry'] ?? '-', $result['new_expiry'], $result['years'])
                : sprintf('  jatuh tempo    : %s (TIDAK diubah, --no-extend)', $result['new_expiry'] ?? '-'));
            $this->line(sprintf('  item           : %d/%d diperbarui', $result['items_updated'], $result['items_total']));

            if ($result['invoice_number']) {
                $status = strtoupper((string) $result['invoice_status']);
                $paidInfo = '';
                if ($result['paid_at']) {
                    $paidInfo = $result['dry_run']
                        ? ' · akan ditandai lunas '.$result['paid_at']
                        : ' · dibayar '.$result['paid_at'];
                }
                if ($result['dry_run'] && $result['invoice_created']) {
                    $paidInfo .= ' (invoice baru)';
                }
                $this->line(sprintf(
                    '  invoice        : %s (#%d) — %s%s',
                    $result['invoice_number'],
                    $result['invoice_id'],
                    $status,
                    $paidInfo
                ));
                $this->line(sprintf(
                    '  nominal        : Rp%s − diskon Rp%s = Rp%s',
                    number_format((float) $result['amount'], 0, ',', '.'),
                    number_format((float) $result['discount'], 0, ',', '.'),
                    number_format((float) $result['net'], 0, ',', '.')
                ));
            } else {
                $this->line('  invoice        : belum ada (tambah --invoice untuk membuat)');
            }

            if ($result['invoice_created']) {
                $this->line('  catatan        : invoice baru dibuat tanpa email ke customer.');
            }

            foreach ($result['warnings'] as $warning) {
                $this->warn('  ! '.$warning);
            }

            if ($result['items_stale_status'] > 0) {
                $this->warn(sprintf('  ! %d item masih berstatus pending (data lama) — pakai --activate-items untuk merapikan.', $result['items_stale_status']));
            }
        }

        $this->line('');

        if ($failed > 0) {
            $this->error("Selesai dengan {$failed} kegagalan.");

            return self::FAILURE;
        }

        $this->info('Selesai.');

        return self::SUCCESS;
    }
}
