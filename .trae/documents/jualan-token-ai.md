# Rencana: Jualan Token AI (Kredit AI in-app, Multi-Provider, Customer Existing)

## Ringkasan

Pelanggan (yang punya akun portal customer) bisa **beli paket kredit AI** → saldo kredit per customer bertambah saat invoice lunas → pelanggan pakai **chat AI di portal customer** yang memotong kredit sesuai model yang dipakai. Backend mendukung **multi-provider dinamis** (tabel provider + model, pilih/fallback per request, harga per model).

Keputusan dari user:
- **Model jualan**: kredit AI in-app (pakai flow invoice existing).
- **Provider**: multi-provider dinamis (pilih provider/model per request, harga per model, fallback).
- **Pembeli**: customer existing (guard `customer`).

## Analisis State Saat Ini

- **Billing**: `Order` → `OrderItem` → `Invoice`; `InvoiceGeneratorService`; `PaymentAccount`; `Invoice::markAsPaid()`. **Tidak ada** wallet/balance/prepaid di seluruh codebase.
- **AI existing**:
  - `app/Services/AiClient.php` — 1 endpoint OpenAI-compatible dari env (`AI_ENDPOINT`/`AI_API_KEY`/`AI_MODEL`), **tidak menangkap `usage`** dari respons provider.
  - `app/Services/AiAgentService.php` + sub-agents — agent admin (artikel, update WP, dsb). Context memuat SEMUA WebsiteClient/Order — memang tool admin.
  - `AiAgentController` — chat + SSE `streamChat` (pola `response()->stream()` + event start/progress/done). Route `/admin/websites/ai`.
  - `AiConversation.user_id` → admin `User`. **Tidak ada** fitur AI untuk customer, tidak ada token counting, tidak ada pengaturan AI di UI.
- **Enum MySQL**: `invoices.invoice_type` = `ENUM('setup','renewal','upgrade','downgrade')` — menambah `'topup'` butuh `ALTER TABLE MODIFY COLUMN` (pola sudah ada: `2025_09_26_112813_modify_invoice_type_enum_in_invoices_table.php`). `order_items.item_type` = `ENUM('hosting','domain','service','app','web','maintenance')`.
- **Sidebar**: admin `resources/js/components/AppSidebar.vue`, customer `resources/js/components/CustomerSidebar.vue`.
- **Catatan penting (konteks kemarin)**: DB production lama punya `users.id` non-bigint → kolom FK baru **jangan** referensi `users` (errno 150). Kolom `user_id` baru cukup `unsignedBigInteger` tanpa FK.

## Perubahan yang Diusulkan

### 1. Database — 7 migrasi baru (tanpa FK ke `users`)

Urutan timestamp `2026_08_03_000001..000007`:

1. **`create_ai_providers_table`**: `id`, `name`, `endpoint` (base URL, mis. `https://openrouter.ai/api/v1`), `api_key` (disimpan `Crypt::encryptString`), `is_active` bool, `sort_order` int, timestamps.
2. **`create_ai_models_table`**: `id`, `provider_id` (FK), `model_key` (mis. `gpt-4o-mini`), `display_name`, `input_rate` decimal(10,4) (kredit per 1K token input), `output_rate` decimal(10,4), `is_active`, `sort_order`, timestamps.
3. **`create_ai_packages_table`**: `id`, `name`, `credits` int, `price` decimal(12,2), `discount_amount` decimal(12,2) nullable, `is_active`, `sort_order`, timestamps.
4. **`create_ai_credits_table`**: `id`, `customer_id` unique, `balance` int default 0, timestamps. (saldo per customer; 1 baris per customer)
5. **`create_ai_transactions_table`**: `id`, `customer_id`, `type` enum(`in`,`out`), `source` enum(`purchase`,`usage`,`manual_adjust`), `credits` int (positif utk in, negatif utk out), `ai_package_id` nullable, `invoice_id` nullable, `ai_model_id` nullable, `tokens_input` int nullable, `tokens_output` int nullable, `description` string nullable, timestamps. Index `(customer_id, created_at)`. (ledger audit — semua perubahan saldo tercatat)
6. **`add_customer_id_to_ai_conversations_table`**: `customer_id` unsignedBigInteger nullable + index. (admin tetap pakai `user_id`)
7. **`add_ai_package_id_to_invoices_table`**: `ai_package_id` unsignedBigInteger nullable + FK ke `ai_packages`, **plus** `DB::statement("ALTER TABLE invoices MODIFY COLUMN invoice_type ENUM('setup','renewal','upgrade','downgrade','topup')")`.

### 2. Model (app/Models)

- `AiProvider`, `AiModel` (`provider` relasi), `AiPackage`, `AiCredit` (`customer`, scope `forCustomer`, helper `currentBalance`), `AiTransaction` (relasi `customer`, `package`, `invoice`, `model`).
- `AiConversation`: tambah `customer_id` fillable + relasi `customer()`.
- `Invoice`: tambah relasi `aiPackage()`.
- Daftarkan observer `InvoiceObserver` di `AppServiceProvider::boot()` (pola `Invoice::observe(...)`).

### 3. Service — `AiGateway` baru + perluasan `AiClient`

**`AiClient`** (perluasan minimal, tetap dipakai admin agent):
- Tambah method `chatWithUsage(...)` mengembalikan `['content' => ..., 'usage' => ['prompt_tokens','completion_tokens','total_tokens']]` dari field `usage` respons provider (OpenAI-compatible). `chat()` existing tetap ada (wrapper).

**`app/Services/AiGateway.php`** (inti — multi-provider + kredit):
- `chat(int $customerId, ?string $modelKey, array $messages): array`:
  1. Ambil `AiCredit::forCustomer($customerId)`; `balance <= 0` → `RuntimeException` "Saldo AI tidak mencukupi. Silakan beli paket kredit."
  2. Pilih model: `$modelKey` yang dicari, atau model aktif pertama urut `sort_order`. Ambil provider-nya.
  3. Panggil `AiClient` (provider baru di-instansiasi per-provider: endpoint+key dari DB, decrypt).
  4. **Fallback**: HTTP/network error → coba model aktif berikutnya (urut `sort_order`), catat ke `Log::warning`. Semua gagal → exception.
  5. **Deduksi**: `input_tokens = usage.prompt_tokens ?? estimasi`, `output_tokens = usage.completion_tokens ?? estimasi`; `kredit = ceil(in/1000)*input_rate + ceil(out/1000)*output_rate` (min 1 bila ada pemakaian). Estimasi fallback: `ceil(mb_strlen(JSON input)/4)` & `ceil(mb_strlen(content)/4)`.
  6. Transaksi DB: `lockForUpdate` baris `ai_credits` → cek balance → insert `ai_transactions` (out/usage) → `decrement` balance. Commit.
  7. Return `['content', 'usage', 'credits_used', 'balance_after', 'model_key', 'provider_name']`.

### 4. Billing hook — kredit otomatis saat invoice lunas

`app/Observers/InvoiceObserver.php`:
- Hook `saved(Invoice $invoice)`; bila `status === 'paid'` **dan** `ai_package_id` terisi **dan** belum ada `AiTransaction` source=purchase utk invoice tsb → insert transaksi `in/purchase` (credits = package.credits) + `increment` balance. **Idempoten** (cek transaksi existing) sehingga jalur lunas mana pun (admin `markAsPaid`, `bulkMarkAsPaid`, customer `confirmPayment`) hanya menambah sekali.

### 5. Admin — CRUD provider/model/paket + manajemen kredit

Controller baru (namespace `App\Http\Controllers\Admin\Ai\`, pola mengikuti `Admin\ServicePlansController`/`Admin\DomainPricesController`):
- `ProviderController` — CRUD `ai_providers` (api_key pakai `Crypt::encryptString` saat store/update).
- `ModelController` — CRUD `ai_models` (pilih provider, rate input/output).
- `PackageController` — CRUD `ai_packages`.
- `CreditController` — daftar customer + saldo (`search`), aksi **top-up / set / adjust** manual (masuk transaksi `manual_adjust`).
- `TransactionController` — daftar transaksi + filter (customer, type, source, range tanggal).

Routes (`routes/admin.php`, group `admin.auth`):
- `Resource` `admin/ai/providers`, `admin/ai/models`, `admin/ai/packages`.
- `GET/POST admin/ai/credits`, `POST admin/ai/credits/adjust`.
- `GET admin/ai/transactions`.

Vue pages (pola CRUD tabel + modal seperti `Admin/ServicePlans/Index.vue`):
- `Admin/Ai/Providers/Index.vue`, `Admin/Ai/Models/Index.vue`, `Admin/Ai/Packages/Index.vue`, `Admin/Ai/Credits/Index.vue`, `Admin/Ai/Transactions/Index.vue`.
- Menu baru grup "AI" di `AppSidebar.vue`.

### 6. Customer — beli paket & chat AI

`app/Http/Controllers/Customer/AiController.php` (guard `auth:customer`):
- `index()` — render `Customer/Ai/Index.vue`: saldo + daftar conversation customer + chat.
- `packages()` — render `Customer/Ai/Packages.vue`: daftar paket aktif + saldo.
- `buy(AiPackage $package)` — validasi aktif; buat `Invoice` (`customer_id`, `invoice_type='topup'`, `amount = price - discount`, `ai_package_id`, `due_date = now+7`); redirect ke `customer.invoices.payment` (flow payment existing).
- `chat()` / `streamChat()` — pola sama persis `AiAgentController` tapi: conversation scope `customer_id`, dan proses pakai `AiGateway::chat()`; error saldo → balas pesan "beli paket" + link. SSE events `start/progress/done` untuk konsistensi UI.

Routes (`routes/customer.php`, group `auth:customer`):
- `GET customer/ai` → index
- `GET customer/ai/packages` → packages
- `POST customer/ai/packages/{package}/buy` → buy
- `POST customer/ai/chat/stream` → streamChat
- `GET/DELETE customer/ai/conversations/{conversation}` → show/destroy

Vue:
- `Customer/Ai/Index.vue` — bubble chat (reuse pola `Admin/Websites/AiAgent.vue`), header saldo kredit, history per customer.
- `Customer/Ai/Packages.vue` — kartu paket (kredit, harga, diskon) + tombol "Beli" → lanjut halaman payment invoice.
- Menu "AI" di `CustomerSidebar.vue`.

### 7. Keamanan & config

- `api_key` provider di DB: encrypt saat simpan, decrypt saat request.
- Setiap request chat: validasi `message` max 2000 (pola existing), ownership conversation (`customer_id === auth()->id()`).
- `.env.example`: tambah baris komentar opsional (tidak wajib) untuk provider default.

### 8. Seed default (opsional, dijalankan manual)

`AiDefaultSeeder` — provider default (OpenAI / OpenRouter) + beberapa model (mis. `gpt-4o-mini`, `gpt-4o`, `claude-sonnet-4`) dengan rate awal. Dipakai agar langsung bisa dicoba; admin bisa ubah.

## Asumsi & Keputusan

- Satuan saldo = **kredit (integer)**. Rate model = kredit per 1K token input/output. Paket = jumlah kredit.
- Pembelian kredit = **invoice langsung tanpa Order** (kredit bukan layanan berlangganan; `service_type`/`item_type` tidak disentuh). Deduksi pakai `usage` respons provider; estimasi char/4 bila tidak tersedia.
- Kolom `customer_id`/`user_id` baru **tanpa FK ke `users`** (konsisten solusi errno 150).
- MVP: chat asisten generik multi-provider untuk customer. Agent (artikel/update WP) di portal customer = **fase 2** (butuh akses WP credentials per WebsiteClient + konteks terbatas per customer).
- Admin agent existing (`AiAgentService`) tetap pakai env `AI_*` — tidak berubah. Migrasi ke DB provider = fase 2.
- Rate model + paket konfigurasi admin; tidak ada harga otomatis dari provider.

## Fase Implementasi

- **Fase 1 (MVP)**: migrasi (7) → model → `AiClient` usage → `AiGateway` → `InvoiceObserver` → admin CRUD + routes + Vue → customer beli/chat + routes + Vue → sidebar → verifikasi.
- **Fase 2 (opsional, nanti)**: agent AI untuk customer, top-up otomatis (auto-renew kredit), tier harga per model, reseller API key (OpenAI-compatible) untuk eksternal.

## Verifikasi

- Local: `php artisan migrate` (atau `migrate:repair` bila konflik 1050 di production).
- Pest tests (pola test existing, `wscrm_test` DB):
  1. `AiGatewayTest` — deduksi kredit benar dari `usage`; balance habis → blokir; fallback provider saat HTTP error; estimasi saat usage kosong.
  2. `InvoiceObserverTest` — invoice `topup` lunas → saldo bertambah; panggilan `saved` berulang tidak dobel (idempoten).
  3. `CustomerAiControllerTest` — beli paket → invoice dibuat (`invoice_type=topup`); konfirmasi bayar → saldo bertambah; chat potong kredit.
  4. `AdminAiControllerTest` — CRUD provider/model/paket; top-up manual → transaksi `manual_adjust`.
- Manual end-to-end: admin set provider+model+paket → customer buka `/customer/ai/packages` → beli → bayar (confirm) → saldo bertambah → chat → kredit berkurang → saldo 0 → blokir + ajakan beli.
