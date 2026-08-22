<?php

namespace App\Console\Commands;

use App\Models\AiCredit;
use App\Models\AiTransaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Potong saldo kredit AI yang sudah lewat masa aktifnya (FIFO).
 * Dijadwalkan harian di routes/console.php.
 */
class ExpireAiCredits extends Command
{
    protected $signature = 'ai-credits:expire';

    protected $description = 'Potong saldo kredit AI yang sudah melewati masa aktif (default 30 hari)';

    public function handle(): int
    {
        $now = now();
        $total = 0;

        AiTransaction::query()
            ->where('type', 'in')
            ->where('remaining', '>', 0)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', $now)
            ->select('customer_id')
            ->distinct()
            ->orderBy('customer_id')
            ->each(function (AiTransaction $row) use (&$total) {
                $expired = DB::transaction(function () use ($row) {
                    $credit = AiCredit::where('customer_id', $row->customer_id)->lockForUpdate()->first();

                    if (! $credit || $credit->balance <= 0) {
                        return 0;
                    }

                    // Jumlah kredit masuk yang sudah kedaluwarsa dan masih punya sisa.
                    $toExpire = (int) AiTransaction::where('customer_id', $row->customer_id)
                        ->where('type', 'in')
                        ->where('remaining', '>', 0)
                        ->whereNotNull('expires_at')
                        ->where('expires_at', '<=', now())
                        ->sum('remaining');

                    $toExpire = min($toExpire, (int) $credit->balance);

                    if ($toExpire <= 0) {
                        return 0;
                    }

                    // Tandai sisa yang kedaluwarsa sebagai hangus.
                    $left = $toExpire;
                    AiTransaction::where('customer_id', $row->customer_id)
                        ->where('type', 'in')
                        ->where('remaining', '>', 0)
                        ->whereNotNull('expires_at')
                        ->where('expires_at', '<=', now())
                        ->orderBy('expires_at')
                        ->orderBy('id')
                        ->get()
                        ->each(function (AiTransaction $t) use (&$left) {
                            if ($left <= 0) {
                                return;
                            }
                            $take = min((int) $t->remaining, $left);
                            $t->decrement('remaining', $take);
                            $left -= $take;
                        });

                    // Catat transaksi keluar "expired" lalu kurangi saldo.
                    AiTransaction::create([
                        'customer_id' => $row->customer_id,
                        'type' => 'out',
                        'source' => 'expired',
                        'credits' => -$toExpire,
                        'description' => 'Kredit hangus (masa aktif berakhir)',
                    ]);

                    $credit->balance = max(0, (int) $credit->balance - $toExpire);
                    $credit->save();

                    return $toExpire;
                });

                $total += $expired;
            });

        if ($total > 0) {
            Log::info("ExpireAiCredits: {$total} kredit hangus karena masa aktif berakhir.");
            $this->info("{$total} kredit hangus.");
        } else {
            $this->info('Tidak ada kredit yang hangus.');
        }

        return self::SUCCESS;
    }
}
