<?php

namespace App\Console\Commands;

use App\Models\JournalEntry;
use App\Models\User;
use App\Models\WebsiteClient;
use App\Notifications\WebsiteDownNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

class CheckWebsiteUptime extends Command
{
  protected $signature = 'websites:check-uptime';

  protected $description = 'Cek status up/down semua website aktif, catat hasilnya ke jurnal, kirim notifikasi jika ada website down';

  public function handle(): int
  {
    $websites = WebsiteClient::where('is_active', true)
      ->whereNotNull('url')
      ->where('url', '!=', '')
      ->get();

    if ($websites->isEmpty()) {
      $this->info('Tidak ada website aktif yang perlu dicek.');
      return self::SUCCESS;
    }

    $downCount = 0;
    $today = now()->toDateString();

    foreach ($websites as $website) {
      $status = $this->check($website);

      $this->recordJournal($website, $today, $status);
      $this->info("{$website->name} ({$website->url}): {$status['label']} (HTTP {$status['http_code']})");

      if ($status['up'] === false) {
        $downCount++;
        $this->notifyAdmins($website, $status);
      }
    }

    $this->info("Selesai. {$websites->count()} website dicek, {$downCount} down.");
    return self::SUCCESS;
  }

  /**
   * Cek status website via HTTP GET. Mengembalikan ['up' => bool, 'http_code' => int, 'label' => string, 'detail' => string].
   */
  private function check(WebsiteClient $website): array
  {
    try {
      $response = Http::timeout(15)
        ->withoutRedirecting()
        ->get($website->url);

      $code = $response->status();
      $up = $code >= 200 && $code < 400;

      return [
        'up' => $up,
        'http_code' => $code,
        'label' => $up ? 'UP' : 'DOWN',
        'detail' => $up ? 'Website dapat diakses.' : 'Website merespons dengan kode ' . $code . '.',
      ];
    } catch (\Exception $e) {
      return [
        'up' => false,
        'http_code' => 0,
        'label' => 'DOWN',
        'detail' => 'Tidak dapat terhubung: ' . substr($e->getMessage(), 0, 200),
      ];
    }
  }

  /**
   * Catat hasil cek ke jurnal: satu entry per website per tanggal (updateOrCreate),
   * aktivitas ditambahkan ke daftar yang sudah ada.
   */
  private function recordJournal(WebsiteClient $website, string $today, array $status): void
  {
    $entry = JournalEntry::firstOrNew([
      'website_client_id' => $website->id,
      'entry_date' => $today,
    ]);

    $activities = $entry->activities ?? [];
    $activities[] = [
      'type' => 'other',
      'description' => 'Cek uptime: ' . $status['label'] . ' (HTTP ' . $status['http_code'] . ') — ' . $status['detail'],
    ];

    $entry->activities = $activities;
    if (!$entry->exists) {
      $entry->user_id = null;
      $entry->summary = 'Cek uptime otomatis.';
    }
    $entry->save();
  }

  /**
   * Kirim notifikasi database ke semua admin & super admin.
   */
  private function notifyAdmins(WebsiteClient $website, array $status): void
  {
    $admins = User::query()
      ->whereIn('role', ['admin', 'super_admin'])
      ->get();

    if ($admins->isEmpty()) {
      return;
    }

    $message = 'Website ' . $website->name . ' DOWN — ' . $status['detail'];

    Notification::send($admins, new WebsiteDownNotification(
      $website,
      $message,
      $status['http_code'],
      $status['detail'],
    ));
  }
}
