<?php

namespace App\Services;

use App\Enums\ActivityType;
use App\Models\BlogPost;
use App\Models\Customer;
use App\Models\DemoWebsite;
use App\Models\DomainPrice;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\HostingPlan;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\Order;
use App\Models\Task;
use App\Models\WebsiteClient;
use App\Services\AiAgents\ArticleAgent;
use App\Services\AiAgents\CustomerAgent;
use App\Services\AiAgents\JournalAgent;
use App\Services\AiAgents\OrderAgent;
use App\Services\AiAgents\PricelistAgent;
use App\Services\AiAgents\TaskAgent;
use App\Services\AiAgents\WebsiteAgent;
use Illuminate\Support\Facades\Log;

/**
 * Orchestrator AI Agent: pahami intent user, bangun konteks, panggil sub-agent per domain.
 */
class AiAgentService
{
    /**
     * Aksi berisiko tinggi — TIDAK dieksekusi langsung, menunggu konfirmasi user.
     * Aksi di luar daftar ini (baca & tulis ringan) dieksekusi otomatis.
     */
    private const HIGH_RISK_ACTIONS = [
        'update_wp',
        'update_plugins',
        'create_article',
        'renew_order',
        'mark_invoice_paid',
        'update_customer_status',
        'create_customer',
        'update_journal',
        'delete_journal',
        'update_domain_price',
        'update_hosting_price',
    ];

    /**
     * Semua nama aksi valid yang dikenali sistem. Dipakai untuk validasi respons AI
     * dan menyusun pesan koreksi bila AI mengarang aksi yang tidak ada.
     */
    private const KNOWN_ACTIONS = [
        'check_updates',
        'update_wp',
        'update_plugins',
        'create_article',
        'audit_seo',
        'check_expiring_orders',
        'renew_order',
        'list_tasks',
        'create_task',
        'update_task_status',
        'list_customers',
        'create_customer',
        'update_customer_status',
        'list_unpaid_invoices',
        'mark_invoice_paid',
        'business_summary',
        'list_journals',
        'create_journal',
        'update_journal',
        'delete_journal',
        'list_domain_prices',
        'update_domain_price',
        'list_hosting_prices',
        'update_hosting_price',
    ];

    public function __construct(
        private AiClient $aiClient,
        private WebsiteAgent $websiteAgent,
        private ArticleAgent $articleAgent,
        private OrderAgent $orderAgent,
        private TaskAgent $taskAgent,
        private CustomerAgent $customerAgent,
        private JournalAgent $journalAgent,
        private PricelistAgent $pricelistAgent,
    ) {}

    /**
     * Process a user command and return the AI response + actions taken.
     * Aksi aman dieksekusi; aksi berisiko tinggi dikembalikan sebagai pending_actions.
     * $onEvent dipanggil tiap tahap workflow: fn(string $message, string $status, string $agent).
     */
    public function process(string $userMessage, ?callable $onEvent = null, array $history = [], array $pageContext = []): array
    {
        // 1. Gather context: all websites & orders with their current state + ringkasan bisnis
        $context = $this->buildContext();

        // Sertakan konteks halaman aktif agar AI paham "sedang di halaman apa".
        if (!empty($pageContext['url'])) {
            $context['current_page'] = [
                'url' => $pageContext['url'],
                'label' => $pageContext['label'] ?? '',
            ];
        }

        if ($onEvent) {
            $onEvent('Menganalisis permintaan dan menyusun konteks data...', 'loading', 'Orchestrator');
        }

        // 1b. /jurnal = catat jurnal maintenance. Tulis ulang jadi instruksi eksplisit
        // agar AI tidak salah tafsir jadi check_updates / create_article.
        $userMessage = $this->normalizeJournalCommand($userMessage);

        // 2. Send to AI to determine intent and required actions
        $aiResponse = $this->callAI($userMessage, $context, $history);

        // 3. Execute safe actions & collect high-risk as pending
        [$executed, $pending] = $this->executeActions($aiResponse, $onEvent);

        // 4. Lampirkan hasil eksekusi nyata ke pesan agar AI tidak mengklaim sukses sebelum aksi dijalankan
        $message = $aiResponse['message'] ?? '';
        if ($executed) {
            $lines = [];
            foreach ($executed as $r) {
                $res = $r['result'];
                if (isset($res['error'])) {
                    $lines[] = '[GAGAL] ' . $r['action'] . ': ' . $res['error'];
                } else {
                    $lines[] = '[OK] ' . ($res['message'] ?? 'Aksi ' . $r['action'] . ' selesai');
                }
            }
            $message = trim($message) . "\n\n" . implode("\n", $lines);
        }

        if ($pending) {
            $pendings = array_map(fn($p) => $p['action'] . ($p['description'] ? " ({$p['description']})" : ''), $pending);
            $message = trim($message) . "\n\nMenunggu konfirmasi: " . implode(', ', $pendings) . '.';
        }

        return [
            'ai_response' => $message,
            'actions' => $executed,
            'pending_actions' => $pending,
            'success' => true,
        ];
    }

    /**
     * Eksekusi aksi berisiko tinggi yang sudah dikonfirmasi user (dari session).
     */
    public function executePendingActions(array $pendingActions, ?callable $onEvent = null): array
    {
        $executed = [];

        foreach ($pendingActions as $action) {
            $actionName = $action['action'] ?? '';
            $params = $action['params'] ?? [];

            $result = $this->runAction($actionName, $params, $onEvent);

            if (!isset($result['error'])) {
                $this->logJournal($actionName, $params, $result);
            }

            $executed[] = [
                'action' => $actionName,
                'params' => $params,
                'result' => $result,
            ];
        }

        return $executed;
    }

    /**
     * Perintah /jurnal (mis. "/jurnal cek update plugin, tema, core") adalah permintaan
     * MENCATAT jurnal maintenance, bukan menjalankan aksi. Rewrite jadi instruksi
     * menulis jurnal agar AI tidak salah mengeksekusi check_updates / create_article.
     */
    private function normalizeJournalCommand(string $message): string
    {
        $trimmed = trim($message);

        if (str_starts_with($trimmed, '/jurnal')) {
            $detail = trim(substr($trimmed, strlen('/jurnal')));
            $today = now()->toDateString();
            $instr = 'CATAT JURNAL MAINTENANCE. Ini permintaan menulis catatan jurnal harian, BUKAN perintah menjalankan aksi. '
                . 'Periksa dulu data "journals" pada konteks: jika sudah ada entry dengan website_id DAN entry_date yang sama, gunakan aksi update_journal (sertakan id jurnal itu + activities baru). Jika belum ada, gunakan create_journal. '
                . "entry_date otomatis hari ini ($today) — jangan tanya tanggal. "
                . 'Identifikasi website yang dimaksud dari detail user (cari nama/domain di data websites, wajib sertakan website_id atau website_client_id). '
                . 'Susun activities[] dari detail yang disebut. Contoh petunjuk tipe: "cek update plugin/tema/core" bisa berarti aktivitas wp_update/plugin_update/theme_update TANPA dari/to versi bila tidak disebutkan; buat satu aktivitas per hal yang disebut. '
                . 'WAJIB: setiap aktivitas harus menyertakan field "description" berupa kalimat deskriptif jelas dalam bahasa Indonesia yang menjelaskan apa yang dikerjakan (misal "Melakukan pengecekan dan pembaruan plugin ke versi terbaru"). Untuk tipe "other", isi description dengan penjelasan detail kegiatannya. Jangan biarkan description kosong. '
                . 'Jika detail terlalu kabur sehingga tidak bisa menyusun activities, tanyakan HAL SPESIFIK yang kurang saja (misal "website mana?"), tetap dalam konteks jurnal. '
                . 'Jangan gunakan check_updates, update_wp, update_plugins, atau create_article. Jangan tawarkan menu lain (tugas/order/dll).';
            return $instr . "\n\nDetail dari user: " . ($detail !== '' ? $detail : '(belum ada detail)');
        }

        return $message;
    }

    private function buildContext(): array
    {
        $websites = WebsiteClient::with('customer')->get();

        $websiteData = [];
        foreach ($websites as $w) {
            $websiteData[] = [
                'id' => $w->id,
                'name' => $w->name,
                'url' => $w->url,
                'wp_version' => $w->wp_version,
                'theme_name' => $w->theme_name,
                'theme_version' => $w->theme_version,
                'plugins' => $w->plugins,
                'has_wp_credentials' => !empty($w->wp_username) && !empty($w->wp_app_password),
            ];
        }

        $orders = Order::with('customer')
            ->whereIn('status', ['active', 'suspended'])
            ->orderBy('expires_at')
            ->get();

        $orderData = $orders->map(fn(Order $o) => [
            'id' => $o->id,
            'customer' => $o->customer?->name ?? 'Tanpa customer',
            'service_type' => $o->service_type,
            'domain' => $o->domain_name,
            'expires_at' => $o->expires_at?->format('Y-m-d'),
            'auto_renew' => (bool) $o->auto_renew,
            'status' => $o->status,
            'billing_cycle' => $o->billing_cycle,
            'total_amount' => round((float) ($o->total_amount ?? 0), 2),
        ])->values()->all();

        return [
            'summary' => $this->buildBusinessSummary(),
            'websites' => $websiteData,
            'orders' => $orderData,
            'journals' => JournalEntry::orderBy('entry_date', 'desc')
                ->limit(30)
                ->get(['id', 'website_client_id', 'entry_date'])
                ->map(fn(JournalEntry $j) => [
                    'id' => $j->id,
                    'website_id' => $j->website_client_id,
                    'entry_date' => $j->entry_date?->format('Y-m-d'),
                ])->values()->all(),
            'domain_prices' => DomainPrice::orderBy('extension')->get()->map(fn(DomainPrice $d) => [
                'id' => $d->id,
                'extension' => $d->extension,
                'selling_price' => (float) $d->selling_price,
                'renewal_price_with_tax' => (float) $d->renewal_price_with_tax,
                'is_active' => (bool) $d->is_active,
            ])->values()->all(),
            'hosting_plans' => HostingPlan::orderBy('plan_name')->get()->map(fn(HostingPlan $h) => [
                'id' => $h->id,
                'plan_name' => $h->plan_name,
                'service_type' => $h->service_type,
                'selling_price' => (float) $h->selling_price,
                'final_price' => round($h->finalPrice(), 2),
                'is_active' => (bool) $h->is_active,
            ])->values()->all(),
            'task_categories' => TaskAgent::categoriesBrief(),
            'users' => TaskAgent::usersBrief(),
        ];
    }

    /**
     * Ringkasan agregat bisnis (hemat token vs kirim semua record).
     */
    private function buildBusinessSummary(): array
    {
        $customers = Customer::selectRaw("COUNT(*) total, SUM(status='active') active, SUM(status='inactive') inactive, SUM(status='suspended') suspended")->first();
        $tasks = Task::selectRaw("COUNT(*) total, SUM(status='todo') todo, SUM(status='in_progress') in_progress, SUM(status='done') done, SUM(status='cancelled') cancelled")->first();
        $invoices = Invoice::whereIn('status', ['sent', 'overdue'])->get();
        $unpaidSum = $invoices->sum(fn($i) => $i->getFinalAmountAttribute());
        $overdueInvoices = $invoices->where('status', 'overdue');
        $overdueSum = $overdueInvoices->sum(fn($i) => $i->getFinalAmountAttribute());
        $employees = Employee::selectRaw("COUNT(*) total, SUM(status='active') active")->first();
        $blog = BlogPost::selectRaw("COUNT(*) total, SUM(status='published') published")->first();
        $hosting = HostingPlan::selectRaw("COUNT(*) total, SUM(is_active) active")->first();
        $demoCount = DemoWebsite::count();
        $sales = Order::where('status', 'active')->sum('total_amount');

        $summary = [
            'customers' => [
                'total' => (int) $customers->total,
                'active' => (int) ($customers->active ?? 0),
                'inactive' => (int) ($customers->inactive ?? 0),
                'suspended' => (int) ($customers->suspended ?? 0),
            ],
            'tasks' => [
                'total' => (int) $tasks->total,
                'todo' => (int) ($tasks->todo ?? 0),
                'in_progress' => (int) ($tasks->in_progress ?? 0),
                'done' => (int) ($tasks->done ?? 0),
                'cancelled' => (int) ($tasks->cancelled ?? 0),
            ],
            'invoices' => [
                'unpaid_total' => $invoices->count(),
                'unpaid_sum' => round($unpaidSum, 2),
                'overdue_total' => $overdueInvoices->count(),
                'overdue_sum' => round($overdueSum, 2),
            ],
            'employees' => [
                'total' => (int) $employees->total,
                'active' => (int) ($employees->active ?? 0),
            ],
            'blog_posts' => [
                'total' => (int) $blog->total,
                'published' => (int) ($blog->published ?? 0),
            ],
            'hosting_plans' => [
                'total' => (int) $hosting->total,
                'active' => (int) ($hosting->active ?? 0),
            ],
            'demo_websites' => $demoCount,
            'active_order_revenue' => round((float) $sales, 2),
            'journals_today' => JournalEntry::where('entry_date', now()->toDateString())->count(),
        ];

        // Expense bulan ini — hanya untuk super admin (data keuangan sensitif)
        if (auth()->user()?->isSuperAdmin()) {
            $summary['expenses_this_month'] = round(Expense::where(function ($q) {
                // Tagihan berulang jatuh tempo bulan ini (next_billing) atau
                // pengeluaran one-time yang dibayar bulan ini (paid_date).
                $q->whereYear('next_billing', now()->year)
                    ->whereMonth('next_billing', now()->month)
                    ->orWhere(fn($q2) => $q2
                        ->whereYear('paid_date', now()->year)
                        ->whereMonth('paid_date', now()->month));
            })->sum('amount'), 2);
        }

        return $summary;
    }

    private function callAI(string $userMessage, array $context, array $history = []): array
    {
        if (!$this->aiClient->hasApiKey()) {
            return ['message' => "AI tidak dikonfigurasi. Tambahkan AI_API_KEY di .env"];
        }

        try {
            $messages = [
                ['role' => 'system', 'content' => $this->getSystemPrompt($context)],
            ];

            // Riwayat percakapan (riwayat_chat) supaya AI ingat konteks multi-langkah,
            // misal alur "/jurnal ..." lalu user jawab "hari ini".
            foreach ($history as $h) {
                $messages[] = [
                    'role' => in_array($h['role'] ?? '', ['user', 'assistant'], true) ? ($h['role'] === 'assistant' ? 'assistant' : 'user') : 'user',
                    'content' => (string) ($h['content'] ?? ''),
                ];
            }
            $messages[] = ['role' => 'user', 'content' => $userMessage];

            $content = $this->aiClient->chat($messages, 0.3, 2000);
            $response = $this->parseAiResponse($content);

            // Koreksi diri: bila AI mengarang aksi yang tidak dikenal / respons tak ter-parse,
            // kirim umpan balik dan minta sekali lagi. Membuat agen lebih andal tanpa nambah fitur.
            $issues = $this->validateResponse($response);
            if ($issues) {
                $messages[] = ['role' => 'assistant', 'content' => mb_strimwidth($content, 0, 500)];
                $messages[] = ['role' => 'user', 'content' => $this->correctionPrompt($issues)];
                $retryContent = $this->aiClient->chat($messages, 0.2, 2000);
                $retryResponse = $this->parseAiResponse($retryContent);

                // Pakai hasil retry bila valid; kalau tetap bermasalah, pertahankan pesan asli AI
                // (jangan buang konteks jawaban hanya karena aksi yang dihasilkan kurang tepat).
                $retryIssues = $this->validateResponse($retryResponse);
                $response = $retryIssues ? $response : $retryResponse;
            }

            return $response;
        } catch (\Exception $e) {
            Log::error('AI call failed: ' . $e->getMessage());
            return ['message' => "Gagal menghubungi AI: " . $e->getMessage()];
        }
    }

    /**
     * Cek respons AI: wajib punya message (kecuali memang tanpa aksi) dan semua aksi
     * harus dikenal sistem. Mengembalikan daftar masalah (kosong = valid).
     */
    private function validateResponse(array $response): array
    {
        $issues = [];

        $message = trim((string) ($response['message'] ?? ''));
        $actions = $response['actions'] ?? [];

        if ($message === '' && empty($actions)) {
            $issues[] = 'Respons tidak berisi message maupun actions.';
        }

        if (!is_array($actions)) {
            $issues[] = 'Field "actions" harus berupa array.';
        }

        foreach ((array) $actions as $action) {
            $name = $action['action'] ?? '';
            if ($name === '' || !in_array($name, self::KNOWN_ACTIONS, true)) {
                $issues[] = "Aksi \"{$name}\" tidak dikenal.";
            }
        }

        return $issues;
    }

    private function correctionPrompt(array $issues): string
    {
        $valid = implode(', ', self::KNOWN_ACTIONS);

        return "Respons JSON kamu sebelumnya bermasalah:\n- " . implode("\n- ", $issues) .
            "\n\nPerbaiki dan ulangi. Hanya gunakan salah satu aksi ini: {$valid}. " .
            "Kembalikan JSON valid dengan format: {\"message\": \"...\", \"actions\": [{\"action\": \"...\", \"params\": {...}}]}";
    }

    private function getSystemPrompt(array $context): string
    {
        $contextJson = json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return <<<PROMPT
Kamu adalah AI Agent untuk mengelola aplikasi WSCRM (website WordPress, layanan hosting/domain, order klien, tugas tim, customer, dan invoice). Kamu BISA menjalankan aksi nyata di sistem.

## Data Saat Ini (termasuk ringkasan bisnis di blok "summary"):
```json
{$contextJson}
```

Blok "current_page" (jika ada) menunjukkan halaman yang sedang dibuka user. Gunakan untuk menyesuaikan jawaban/aksi dengan konteks halaman itu (misal user sedang di /admin/orders lalu bilang "perpanjang yang ini", rujuk ke data orders).

## Aksi yang Bisa Kamu Lakukan:
**Website & Konten:**
1. **check_updates** - Cek website mana yang perlu update WP core/plugin/tema (sertakan website_id jika user menyebut website/domain tertentu, cari id-nya di data websites)
2. **update_wp** - Update WordPress core untuk website tertentu (perlu id) — membutuhkan konfirmasi user
3. **update_plugins** - Update plugin spesifik di website tertentu (perlu id, plugin_slugs[]) — membutuhkan konfirmasi user
4. **create_article** - Buat artikel SEO lengkap otomatis ke WP klien: generate konten, sisipkan 2 gambar, audit SEO, publish jika skor >= 80 (perlu website_id, title/topik, opsional keyword) — membutuhkan konfirmasi user
5. **audit_seo** - Audit SEO halaman website (perlu id, url)

**Order:**
6. **check_expiring_orders** - Cek order aktif yang akan berakhir (kadaluarsa) bulan ini + yang SUDAH lewat jatuh tempo (overdue). Hasil mencakup estimasi nilai perpanjangan & siklus billing tiap order
7. **renew_order** - Perpanjang masa aktif order/layanan dari tanggal jatuh tempo saat ini dan/atau tandai sudah dibayar (perlu id dari data orders, months (jumlah bulan, default 3), mark_paid (true/false)) — membutuhkan konfirmasi user

**Tugas (Tasks):**
8. **list_tasks** - Daftar tugas (opsional status, task_category_id, assigned_user_id). Cari id user di data "users", id kategori di "task_categories"
9. **create_task** - Buat tugas baru (perlu title; opsional description, priority low/medium/high, due_date YYYY-MM-DD, task_category_id, assigned_user_id, assigned_department). Eksekusi langsung tanpa konfirmasi
10. **update_task_status** - Ubah status tugas (perlu id, status todo/in_progress/done/cancelled). Eksekusi langsung

**Customer & Invoice:**
11. **list_customers** - Daftar customer (opsional search, status active/inactive/suspended)
12. **create_customer** - Buat customer baru (perlu name, email; opsional username, phone, address, city, country, postal_code) — membutuhkan konfirmasi user
13. **update_customer_status** - Ubah status customer active/inactive/suspended (perlu id, status) — membutuhkan konfirmasi user
14. **list_unpaid_invoices** - Daftar invoice belum dibayar/terlambat
15. **mark_invoice_paid** - Tandai invoice lunas (perlu id) — membutuhkan konfirmasi user

**Laporan:**
16. **business_summary** - Ringkasan agregat bisnis: penjualan aktif, invoice belum bayar, tugas, customer, karyawan, hosting plan, blog, demo website

**Jurnal Maintenance (catatan aktivitas harian per website di halaman /admin/journals):**
17. **list_journals** - Daftar jurnal maintenance (opsional website_id, date_from, date_to). Eksekusi langsung tanpa konfirmasi
18. **create_journal** - Catat jurnal maintenance harian untuk website (perlu website_client_id + entry_date + activities). Satu entry per website per tanggal. Tipe aktivitas: wp_update, plugin_update, theme_update, article, page_optimization, other — sesuaikan field detailnya. SETIAP activity WAJIB punya field "description" (kalimat jelas apa yang dikerjakan). Contoh activity: {"type":"article","title":"...","url":"...","word_count":N,"description":"..."}; {"type":"wp_update","from_version":"6.5","to_version":"6.6","description":"..."}; {"type":"plugin_update","plugin":"...","from_version":"...","to_version":"...","description":"..."}; {"type":"page_optimization","page":"...","detail":"...","description":"..."}; {"type":"other","description":"..."}. Eksekusi langsung tanpa konfirmasi
19. **update_journal** - Update jurnal maintenance yang sudah ada (perlu id + data baru). Gunakan ini UNTUK MENAMBAH aktivitas baru ke jurnal yang SUDAH ADA (cukup isi activities baru, entry_date & website_client_id opsional) ATAU MEMPERBAIKI aktivitas yang sudah ada (kirim ulang aktivitas dengan identitas sama — title/plugin/page — beserta description/field baru; akan menimpa, bukan menduplikasi). SETIAP activity WAJIB punya field "description". — membutuhkan konfirmasi user
20. **delete_journal** - Hapus jurnal maintenance (perlu id) — membutuhkan konfirmasi user

**Pricelist (harga domain & hosting):**
21. **list_domain_prices** - Daftar harga domain (opsional extension untuk filter, misal ".com"). Eksekusi langsung tanpa konfirmasi
22. **update_domain_price** - Ubah harga domain (perlu id dari data list_domain_prices; isi field yang berubah saja: selling_price, base_cost, renewal_cost, renewal_price_with_tax, is_active). Gunakan UNTUK EDIT HARGA — membutuhkan konfirmasi user
23. **list_hosting_prices** - Daftar paket hosting & harganya (opsional plan_name untuk filter). Eksekusi langsung tanpa konfirmasi
24. **update_hosting_price** - Ubah harga paket hosting (perlu id dari data list_hosting_prices; isi field yang berubah saja: selling_price, modal_cost, maintenance_cost, discount_percent, is_active). Gunakan UNTUK EDIT HARGA — membutuhkan konfirmasi user

## Penting: Perbedaan "JURNAL" vs "ARTIKEL"
- Jika user bilang **"tulis jurnal"**, **"catat jurnal"**, **"jurnal maintenance"**, **"jurnal harian"**, atau menyebut aktivitas maintenance harian (update WP, update plugin, buat artikel, optimasi halaman) UNTUK DICATAT — itu artinya **jurnal maintenance** → gunakan aksi **create_journal** (atau **update_journal** jika jurnal untuk website & tanggal itu SUDAH ADA) atau list_journals untuk melihat
- Jika user bilang **"buat artikel"**, **"tulis artikel"**, **"publish artikel"**, atau **"artikel SEO"** ke website WP klien — itu artinya **artikel blog** → gunakan aksi **create_article**
- Jangan tertukar: "tulis jurnal" = catat catatan harian, bukan membuat artikel blog

## Aturan Khusus Jurnal (Wajib):
- Sebelum create_journal, cek data "journals" pada konteks di atas: jika sudah ada entry dengan **website_id DAN entry_date yang sama**, jangan pakai create_journal (akan gagal), gunakan **update_journal** dengan id dari data itu dan activities baru — aktivitas baru akan DITAMBAHKAN ke entry yang ada. update_journal butuh konfirmasi user
- entry_date default hari ini (tanggal server), jangan tanya ke user kecuali user menyebut tanggal lain
- Jika user menyebut "update WP/plugin/tema" dalam konteks jurnal, itu aktivitas yang DICATAT (create_journal/update_journal), bukan aksi update_wp/update_plugins yang benar-benar mengupdate — kecuali user secara eksplisit minta menjalankan update

## Aturan:
- Selalu analisis data (summary + website/order/domain_prices/hosting_plans/task_categories/users) terlebih dahulu
- Jika user minta "cek update", gunakan aksi **check_updates** dan sebutkan website mana saja
- Jika user menyebut website/domain tertentu (misal "cek demo1.sweet.web.id" atau "website 3"), cari id website itu di data websites lalu SERTAKAN **website_id** (atau **id**) pada SEMUA aksi yang membutuhkan website — jangan jalankan aksi website tanpa website_id
- Jika user minta "update", jalankan aksi update
- Jika user minta "buat artikel", jalankan aksi **create_article** — sistem otomatis generate konten, sisipkan gambar, audit, dan publish jika skor lolos
- Jika user tanya order yang akan mati/berakhir/kadaluarsa bulan ini, gunakan aksi **check_expiring_orders**
- Jika user bilang order sudah bayar / minta perpanjang / tandai lunas, cari order di data orders lalu gunakan aksi **renew_order** dengan id sesuai, months sesuai permintaan (default 3), dan mark_paid true jika user menyebut sudah bayar
- Jika user tanya tugas/PR/pekerjaan tim, gunakan **list_tasks** (untuk laporan) atau **create_task** (untuk membuat tugas). Assignee: cari id user di data "users" berdasarkan nama, isi assigned_user_id; bila user tidak menyebut, jangan isi
- Jika user tanya customer, gunakan **list_customers**; buat customer baru pakai **create_customer**
- Jika user tanya invoice belum bayar/terlambat/tunggakan, gunakan **list_unpaid_invoices**
- Jika user minta ringkasan/condition penjualan/laporan kondisi bisnis, gunakan **business_summary** — jawab langsung dari datanya
- Jika user minta lihat harga domain, pakai **list_domain_prices**; ubah harga domain pakai **update_domain_price** (cari id dari hasil list, dan tanyakan/ikuti angka yang diminta user)
- Jika user minta lihat harga hosting/paket, pakai **list_hosting_prices**; ubah harga hosting pakai **update_hosting_price** (cari id dari hasil list)
- Saat mengubah harga, hanya sertakan field yang benar-benar berubah, dan SERTAKAN id — jangan ubah harga tanpa id yang jelas
- Harga domain & hosting juga tersedia langsung di konteks (data "domain_prices" dan "hosting_plans") — kamu bisa menjawab pertanyaan harga TANPA aksi list, tapi untuk MENGUBAH harga tetap wajib pakai update_domain_price / update_hosting_price dengan id dari konteks
- Aksi yang bertanda "membutuhkan konfirmasi user" TIDAK langsung jalan; sistem akan menampilkan konfirmasi ke user. Kamu tetap kirim aksi tersebut di JSON, sistem yang menangani konfirmasi
- Balas dalam bahasa Indonesia yang natural dan informatif
- Di akhir respons, sertakan JSON aksi yang perlu dijalankan dalam format:
```json
{"message": "respons kamu ke user", "actions": [{"action": "nama_aksi", "params": {...}}]}
```
- Jika tidak ada aksi yang perlu dijalankan, actions bisa kosong []
- Jika user hanya tanya/minta informasi, jawab saja tanpa actions

PROMPT;
    }

    private function parseAiResponse(string $content): array
    {
        // 1. Seluruh konten adalah JSON murni (objek atau array aksi)
        $parsed = $this->decodeJsonBlock($content);
        if ($parsed !== null) {
            return $this->normalizeParsed($parsed, $content);
        }

        // 2. Blok JSON dalam code fence (```json ... ``` atau ``` ... ```)
        if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/', $content, $m)) {
            $parsed = $this->decodeJsonBlock($m[1]);
            if ($parsed !== null) {
                return $this->normalizeParsed($parsed, $content);
            }
        }

        // 3. Array aksi mentah: coba dulu agar tidak tertelan objek bagian dalam
        if (preg_match('/\[[\s\S]*\]/', $content, $m)) {
            $parsed = $this->decodeJsonBlock($m[0]);
            if ($parsed !== null) {
                return $this->normalizeParsed($parsed, $content);
            }
        }

        // 4. Objek JSON mentah di mana pun dalam respons
        if (preg_match('/\{[\s\S]*\}/', $content, $m)) {
            $parsed = $this->decodeJsonBlock($m[0]);
            if ($parsed !== null) {
                return $this->normalizeParsed($parsed, $content);
            }
        }

        // 5. Gagal di-parse: anggap seluruh respons sebagai message tanpa aksi
        return ['message' => $content, 'actions' => []];
    }

    private function decodeJsonBlock(string $raw): ?array
    {
        // Bila ada trailing comma atau teks sisa, coba potong dari karakter `{`/`[` pertama
        $candidates = [$raw];
        if (preg_match('/[\{\[][\s\S]*[\}\]]/', $raw, $m)) {
            $candidates[] = $m[0];
        }

        foreach (array_unique($candidates) as $candidate) {
            $decoded = json_decode(trim($candidate), true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }

    /**
     * Normalisasi hasil parse: dukung {message, actions}, {actions}, atau array aksi polos.
     * Saat AI hanya mengirim aksi tanpa message, pesan asli AI tetap dipertahankan.
     */
    private function normalizeParsed(array $parsed, string $originalContent): array
    {
        if (isset($parsed['message'])) {
            return [
                'message' => (string) $parsed['message'],
                'actions' => $parsed['actions'] ?? [],
            ];
        }

        if (isset($parsed['actions']) && is_array($parsed['actions'])) {
            return [
                'message' => $originalContent,
                'actions' => $parsed['actions'],
            ];
        }

        // Array polos berisi aksi: [{action, params}, ...]
        if (array_is_list($parsed)) {
            return [
                'message' => $originalContent,
                'actions' => $parsed,
            ];
        }

        return ['message' => $originalContent, 'actions' => []];
    }

    /**
     * Pisahkan aksi: aman dieksekusi sekarang, berisiko tinggi jadi pending.
     */
    private function executeActions(array $aiResponse, ?callable $onEvent = null): array
    {
        $actions = $aiResponse['actions'] ?? [];
        $executed = [];
        $pending = [];
        $seen = [];

        foreach ($actions as $action) {
            $actionName = $action['action'] ?? '';

            // Skip duplicate actions: dedupe berdasarkan nama + params, sehingga AI yang
            // mengirim 2x create_journal untuk website berbeda tetap dieksekusi keduanya.
            $params = $action['params'] ?? [];
            $dedupeKey = $actionName . '|' . md5(json_encode($params));
            if (in_array($dedupeKey, $seen, true)) {
                continue;
            }
            $seen[] = $dedupeKey;

            if (in_array($actionName, self::HIGH_RISK_ACTIONS, true)) {
                $pending[] = [
                    'action' => $actionName,
                    'params' => $params,
                    'description' => $this->describeAction($actionName, $params),
                ];
                if ($onEvent) {
                    $onEvent("Aksi {$actionName} menunggu konfirmasi user", 'pending', 'Orchestrator');
                }
                continue;
            }

            $result = $this->runAction($actionName, $params, $onEvent);

            if (!isset($result['error'])) {
                $this->logJournal($actionName, $params, $result);
            }

            $executed[] = [
                'action' => $actionName,
                'params' => $params,
                'result' => $result,
            ];
        }

        return [$executed, $pending];
    }

    private function runAction(string $actionName, array $params, ?callable $onEvent = null): array
    {
        try {
            if ($invalid = $this->validateActionParams($actionName, $params)) {
                return ['error' => $invalid];
            }

            return match ($actionName) {
                'check_updates' => $this->websiteAgent->checkUpdates($params['website_id'] ?? $params['id'] ?? null, $onEvent),
                'update_wp' => $this->websiteAgent->updateWp($params['website_id'] ?? $params['id'] ?? null, $onEvent),
                'update_plugins' => $this->websiteAgent->updatePlugins($params['website_id'] ?? $params['id'] ?? null, $params['plugin_slugs'] ?? [], $onEvent),
                'audit_seo' => $this->websiteAgent->auditSeo($params['website_id'] ?? $params['id'] ?? null, $params['url'] ?? '', $onEvent),
                'create_article' => $this->articleAgent->createArticle($params['website_id'] ?? $params['id'] ?? null, $params['title'] ?? '', $params['content'] ?? '', $params['keyword'] ?? '', $onEvent),
                'check_expiring_orders' => $this->orderAgent->checkExpiringOrders($onEvent),
                'renew_order' => $this->orderAgent->renewOrder($params['id'] ?? null, (int) ($params['months'] ?? 3), (bool) ($params['mark_paid'] ?? false), $onEvent),
                'list_tasks' => $this->taskAgent->listTasks($params['status'] ?? null, $params['task_category_id'] ?? null, $params['assigned_user_id'] ?? null, $onEvent),
                'create_task' => $this->taskAgent->createTask($params, $onEvent),
                'update_task_status' => $this->taskAgent->updateTaskStatus((int) ($params['id'] ?? 0), $params['status'] ?? '', $onEvent),
                'list_customers' => $this->customerAgent->listCustomers($params['search'] ?? null, $params['status'] ?? null, $onEvent),
                'create_customer' => $this->customerAgent->createCustomer($params, $onEvent),
                'update_customer_status' => $this->customerAgent->updateCustomerStatus((int) ($params['id'] ?? 0), $params['status'] ?? '', $onEvent),
                'list_unpaid_invoices' => $this->customerAgent->listUnpaidInvoices($onEvent),
                'mark_invoice_paid' => $this->customerAgent->markInvoicePaid((int) ($params['id'] ?? 0), $params['payment_method'] ?? null, $onEvent),
                'list_journals' => $this->journalAgent->listJournals($params['website_id'] ?? $params['id'] ?? null, $params['date_from'] ?? null, $params['date_to'] ?? null, $onEvent),
                'create_journal' => $this->journalAgent->createJournal($this->journalParams($params), $onEvent),
                'update_journal' => $this->journalAgent->updateJournal((int) ($params['id'] ?? 0), $this->journalParams($params, useIdAsWebsite: false), $onEvent),
                'delete_journal' => $this->journalAgent->deleteJournal((int) ($params['id'] ?? 0), $onEvent),
                'list_domain_prices' => $this->pricelistAgent->listDomainPrices($params['extension'] ?? null, $onEvent),
                'update_domain_price' => $this->pricelistAgent->updateDomainPrice((int) ($params['id'] ?? 0), $params, $onEvent),
                'list_hosting_prices' => $this->pricelistAgent->listHostingPlans($params['plan_name'] ?? null, $onEvent),
                'update_hosting_price' => $this->pricelistAgent->updateHostingPrice((int) ($params['id'] ?? 0), $params, $onEvent),
                'business_summary' => $this->buildBusinessSummary(),
                default => ['error' => "Aksi tidak dikenal: {$actionName}"],
            };
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Validasi minimal params tiap aksi agar AI tidak mengirim aksi rusak
     * (mis. update/delete tanpa id). Mengembalikan pesan error atau null bila valid.
     */
    private function validateActionParams(string $action, array $params): ?string
    {
        $needsId = ['update_wp', 'update_plugins', 'audit_seo', 'create_article', 'renew_order', 'update_task_status', 'update_customer_status', 'mark_invoice_paid', 'update_journal', 'delete_journal', 'update_domain_price', 'update_hosting_price'];

        if (in_array($action, $needsId, true)) {
            $id = $params['id'] ?? $params['website_id'] ?? $params['website_client_id'] ?? null;
            if ($id === null || $id === '' || $id === 0) {
                return "Aksi {$action} membutuhkan id.";
            }
        }

        return null;
    }

    /**
     * Normalisasi params jurnal: AI biasanya kirim "website_id"/"id", sedangkan
     * JournalAgent butuh "website_client_id". Kunci lain (entry_date, activities) diteruskan apa adanya.
     * Untuk update/delete, "id" adalah ID JURNAL, bukan website id — jadi jangan dipakai sebagai website.
     */
    private function journalParams(array $params, bool $useIdAsWebsite = true): array
    {
        $normalized = $params;
        unset($normalized['website_id'], $normalized['id']);

        if (!isset($normalized['website_client_id'])) {
            $normalized['website_client_id'] = $params['website_id'] ?? ($useIdAsWebsite ? ($params['id'] ?? null) : null);
        }

        return $normalized;
    }

    /**
     * Deskripsi ringkas aksi untuk kartu konfirmasi user.
     */
    private function describeAction(string $action, array $params): string
    {
        return match ($action) {
            'update_wp' => 'Update WP core',
            'update_plugins' => 'Update plugin: ' . implode(', ', $params['plugin_slugs'] ?? ['semua']),
            'create_article' => 'Publikasi artikel: ' . ($params['title'] ?? $params['keyword'] ?? 'topik'),
            'renew_order' => 'Perpanjang order #' . ($params['id'] ?? '?') . ' selama ' . ($params['months'] ?? 3) . ' bulan' . (!empty($params['mark_paid']) ? ' + tandai lunas' : ''),
            'mark_invoice_paid' => 'Tandai invoice #' . ($params['id'] ?? '?') . ' lunas',
            'update_customer_status' => 'Ubah status customer #' . ($params['id'] ?? '?') . ' → ' . ($params['status'] ?? '?'),
            'create_customer' => 'Buat customer baru: ' . ($params['name'] ?? '?'),
            'update_journal' => 'Update jurnal #' . ($params['id'] ?? '?') . ' (' . ($params['entry_date'] ?? 'tanggal lama') . ')',
            'delete_journal' => 'Hapus jurnal #' . ($params['id'] ?? '?'),
            'update_domain_price' => 'Ubah harga domain #' . ($params['id'] ?? '?') . ' (harga jual: Rp ' . ($params['selling_price'] ?? '?') . ')',
            'update_hosting_price' => 'Ubah harga hosting #' . ($params['id'] ?? '?') . ' (harga jual: Rp ' . ($params['selling_price'] ?? '?') . ')',
            default => '',
        };
    }

    /**
     * Catat aktivitas AI ke jurnal maintenance (1 entry per website per hari, activity di-append).
     * Hanya aksi yang berhubungan dengan website — aksi order (tanpa website) tidak masuk report.
     */
    private function logJournal(string $action, array $params, array $result): void
    {
        try {
            $websiteActions = ['check_updates', 'update_wp', 'update_plugins', 'audit_seo', 'create_article'];
            if (!in_array($action, $websiteActions, true)) {
                return;
            }

            $websiteId = $params['website_id'] ?? $params['id'] ?? null;
            if (!$websiteId || !WebsiteClient::whereKey($websiteId)->exists()) {
                return;
            }

            $activity = $this->buildActivity($action, $params, $result);
            if (!$activity) {
                return;
            }

            $entry = JournalEntry::firstOrNew([
                'website_client_id' => $websiteId,
                'entry_date' => now()->toDateString(),
            ]);
            $entry->user_id = auth()->id();

            // Hindari duplikat aktivitas identik di hari yang sama (mis. create_article diulang)
            $activityKey = $activity['type'] . '|' . ($activity['title'] ?? $activity['plugin'] ?? $activity['description'] ?? '');
            $activities = $entry->activities ?? [];
            $exists = collect($activities)->contains(function ($a) use ($activityKey) {
                return ($a['type'] ?? '') . '|' . ($a['title'] ?? $a['plugin'] ?? $a['description'] ?? '') === $activityKey;
            });

            if (!$exists) {
                $activities[] = $activity;
                $entry->activities = $activities;
                $entry->summary = $activity['description'] ?? null;
                $entry->save();
            }
        } catch (\Throwable $e) {
            // Jangan gagalkan workflow hanya karena gagal mencatat jurnal
            Log::warning('Gagal mencatat aktivitas AI ke jurnal: ' . $e->getMessage());
        }
    }

    private function buildActivity(string $action, array $params, array $result): array
    {
        $common = ['source' => 'AI'];

        return match ($action) {
            'create_article' => [
                'type' => ActivityType::ARTICLE->value,
                'title' => $result['title'] ?? ($params['title'] ?? 'Artikel'),
                'word_count' => $result['word_count'] ?? 0,
                'url' => $result['post_url'] ?? null,
                'description' => 'AI: artikel dipublikasikan (skor ' . ($result['score'] ?? '-') . '/100, '
                    . ($result['word_count'] ?? 0) . ' kata)',
            ] + $common,
            'update_wp' => [
                'type' => ActivityType::WP_UPDATE->value,
                'description' => 'AI: ' . ($result['message'] ?? 'update WP core selesai'),
            ] + $common,
            'update_plugins' => [
                'type' => ActivityType::PLUGIN_UPDATE->value,
                'plugin' => implode(', ', $result['plugins'] ?? []),
                'description' => 'AI: ' . ($result['message'] ?? 'update plugin selesai'),
            ] + $common,
            'audit_seo' => [
                'type' => ActivityType::PAGE_OPTIMIZATION->value,
                'page' => $result['url'] ?? ($result['website'] ?? '-'),
                'detail' => 'Audit SEO skor ' . ($result['analysis']['score'] ?? '-') . '/100',
                'description' => 'AI: audit SEO ' . ($result['url'] ?? '') . ' skor '
                    . ($result['analysis']['score'] ?? '-') . '/100',
            ] + $common,
            'check_updates' => [
                'type' => ActivityType::OTHER->value,
                'description' => 'AI: cek update — '
                    . count($result['websites_need_update'] ?? []) . ' website perlu update',
            ] + $common,
            default => [
                'type' => ActivityType::OTHER->value,
                'description' => 'AI: ' . $action . ' — ' . ($result['message'] ?? 'selesai'),
            ] + $common,
        };
    }
}
