# Plan: Perluas Fitur AI Agent + Konfirmasi Aksi Berisiko

## Ringkasan

AI Agent admin (`/admin/websites/ai`) sekarang hanya menangani domain website WP + order (7 aksi). Tujuan:
1. Perluas ke area baru: **Tugas (Tasks)**, **Customer & Order**, **Konten & Marketing**, **Data & Laporan**.
2. **Konfirmasi sebelum eksekusi** — hanya untuk aksi **berisiko tinggi** (sulit di-rollback / menyentuh produksi & uang). Aksi baca & tulis ringan jalan otomatis.
3. Konteks AI diperluas dengan **ringkasan agregat** (hemat token), bukan semua record penuh.

Keputusan user:
- Scope: Tasks, Customer & Order, Konten & Marketing, Data & Laporan (semua).
- Konfirmasi: **hanya aksi berisiko tinggi**.
- Konteks: **kirim ringkasan saja**.

## Analisis State Saat Ini

### Yang sudah bisa AI (7 aksi, `AiAgentService::executeActions`)
- `check_updates` — cek website perlu update WP core/plugin (WebsiteAgent).
- `update_wp` — update/sync WP core (WebsiteAgent, sebenarnya sync data).
- `update_plugins` — update/sync plugin (WebsiteAgent).
- `create_article` — workflow artikel SEO lengkap: judul → konten → 2 gambar (Unsplash) → featured image → kategori → audit (min 80) → revisi → publish ke WP (ArticleAgent).
- `audit_seo` — audit SEO halaman (title, meta, H1, img alt, size, link).
- `check_expiring_orders` — order aktif berakhir bulan ini (OrderAgent).
- `renew_order` — perpanjang order + tandai invoice lunas (OrderAgent).

### Arsitektur
- Controller: `app/Http/Controllers/Admin/AiAgentController.php` — `index()`, `show()`, `destroy()`, `chat()` (JSON), `streamChat()` (SSE `start/progress/done`).
- Service: `app/Services/AiAgentService.php` — `process(message, onEvent)`: build context → `callAI()` (system prompt + JSON parse) → `executeActions()` → append hasil `[OK]/[GAGAL]` ke pesan → log jurnal.
- Sub-agent: `app/Services/AiAgents/{WebsiteAgent,ArticleAgent,OrderAgent}.php`.
- Client: `app/Services/AiClient.php` (env `AI_ENDPOINT/AI_API_KEY/AI_MODEL`).
- Frontend: `resources/js/pages/Admin/Websites/AiAgent.vue` — chat bubble, SSE streaming, card hasil per aksi (`check_updates`, `audit_seo`, `check_expiring_orders`, `create_article`), `ConfirmModal.vue` existing (hanya dipakai hapus percakapan).
- Context: `buildContext()` hanya memuat `websites` (penuh) + `orders` aktif/suspended (penuh).
- Routes: `routes/admin.php` L102-106 (`websites/ai`, `websites/ai/chat`, `websites/ai/chat/stream`, `websites/ai/conversations/...`).

### Yang BELUM bisa AI
- **Tasks**: tidak ada aksi tugas; data tugas tidak ada di context.
- **Customer**: tidak ada aksi customer; tidak bisa buat/lihat/ubah status.
- **Invoice**: hanya lewat `renew_order` (mark paid); tidak bisa daftar invoice belum bayar/overdue.
- **Blog internal** (`BlogPost`): tidak ada aksi; `create_article` hanya ke WP klien.
- **Data & Laporan**: tidak ada ringkasan penjualan/invoice/tugas/hosting/demo/expense.
- **Konfirmasi**: SEMUA aksi langsung dieksekusi tanpa konfirmasi — termasuk `update_wp`, `update_plugins`, `create_article` (publish), `renew_order` (ubah masa aktif + tandai lunas).

### Model & controller yang relevan (sudah diverifikasi)
- `Task`: title, task_category_id, order_id, description, status(todo/in_progress/done/cancelled), priority, due_date, assigned_user_id, assigned_department, created_by_user_id, qc_results. `TaskController@store/update` = pola validasi.
- `Customer`: name, email, username, password, phone, address, city, country, postal_code, status(active/inactive/suspended). `CustomerController@store/update/sendWelcomeEmail/resendPassword`.
- `Invoice`: customer_id, order_id, invoice_number, invoice_type, amount, discount, status, issue_date, due_date, paid_at. `markAsPaid()`, scope `unpaid`, `overdue`. `InvoiceGeneratorService@generateInvoiceNumber()`.
- `Employee`: user_id, nik, position, department, phone, hire_date, salary, status.
- `BlogPost`, `HostingPlan`, `DemoWebsite`, `Expense` — model ada, dipakai untuk summary.
- `InvoiceObserver` — kredit AI otomatis saat invoice `topup` lunas (tidak disentuh).

## Perubahan yang Diusulkan

### 1. Perluas konteks — `AiAgentService::buildContext()`
Tambahkan ringkasan agregat (bukan record penuh) ke array context:
- customers: `total`, `active`, `inactive`, `suspended`.
- tasks: `total`, `todo`, `in_progress`, `done`, `cancelled`.
- invoices: `unpaid_total`, `unpaid_sum`, `overdue_total`, `overdue_sum`, `pending_topup` (jika ada).
- employees: `total`, `active`.
- blog_posts: `total`, `published`.
- hosting_plans: `total`, `active`.
- demo_websites: `total`.
- expenses (bulan ini, hanya bila user super_admin): `month_total`.
- `websites` + `orders` tetap penuh (dibutuhkan untuk pick id pada aksi).

### 2. Sub-agent baru — `app/Services/AiAgents/TaskAgent.php` & `CustomerAgent.php`
**TaskAgent** (pola sama WebsiteAgent, `$onEvent`):
- `listTasks(?status, ?categoryId, ?assignedUserId)` — read-only, return array task ringkas + count.
- `createTask(title, description, priority, due_date, task_category_id, assigned_user_id, assigned_department)` — validasi sama `TaskController@store`, `created_by_user_id = auth()->id()`, default status `todo`.
- `updateTaskStatus(id, status)` — validasi enum, pastikan QC rule (bila status `done` & kategori punya qc_checklist → wajib ≥70% seperti `TaskController@update`).

**CustomerAgent**:
- `listCustomers(?search, ?status)` — read-only, return array customer ringkas.
- `createCustomer(name, email, username, phone, city, ...)` — validasi sama `CustomerController@store` (password di-generate random, hash).
- `updateCustomerStatus(id, status)` — ubah status customer (active/inactive/suspended).
- `listUnpaidInvoices()` — invoice status `sent`/`overdue` ringkas (no, customer, no_invoice, amount, due_date, days_late).
- `markInvoicePaid(id, payment_method?)` — panggil `Invoice::markAsPaid()`.

### 3. Aksi baru & risk map — `AiAgentService`
- Tambah `private const HIGH_RISK_ACTIONS = ['update_wp','update_plugins','create_article','renew_order','mark_invoice_paid','update_customer_status','create_customer']`.
- Refactor `executeActions()` → hasil dibagi 2:
  - aksi **bukan** di HIGH_RISK → langsung eksekusi (read-only + tulis ringan: `create_task`, `update_task_status`).
  - aksi di HIGH_RISK → **tidak dieksekusi**, dikumpulkan ke `pending_actions` (dengan `action`, `params`, `description` ringkas).
- `process()` return: `['ai_response', 'executed_actions', 'pending_actions', 'success']`.
- Aksi baru di `match`:
  - `list_tasks` → TaskAgent::listTasks
  - `create_task` → TaskAgent::createTask
  - `update_task_status` → TaskAgent::updateTaskStatus
  - `list_customers` → CustomerAgent::listCustomers
  - `create_customer` → CustomerAgent::createCustomer
  - `update_customer_status` → CustomerAgent::updateCustomerStatus
  - `list_unpaid_invoices` → CustomerAgent::listUnpaidInvoices
  - `mark_invoice_paid` → CustomerAgent::markInvoicePaid
  - `business_summary` → method baru `businessSummary()` (query agregat penjualan bulan ini, invoice, tugas, dst — read-only)
- Update system prompt: daftar aksi baru + instruksi "untuk pertanyaan laporan pakai `business_summary`", "aksi berisiko akan dikonfirmasi user dulu".

### 4. Konfirmasi dua-fase — Controller + session
**Alur:**
1. User kirim pesan → `streamChat()` → `process()` → aksi aman dieksekusi (progress via SSE), aksi berisiko masuk `pending_actions`.
2. Controller simpan `pending_actions` ke **session** key `ai_pending_actions_{conversation_id}` (server-side, tidak bisa dimanipulasi client).
3. Event `done` mengirim `pending_actions` (untuk render kartu konfirmasi) + `executed_actions`.
4. Frontend tampil kartu: "AI ingin: [aksi 1], [aksi 2]..." tombol **Jalankan** / **Batal**.
5. **Jalankan** → POST baru `POST /admin/websites/ai/chat/confirm` → controller baca pending dari session → `AiAgentService::executePendingActions($pending, $onEvent)` (method baru, SSE progress sama) → simpan pesan agent (final + results) → hapus session → event `done`.
6. **Batal** → `POST /admin/websites/ai/chat/cancel` → hapus session → simpan pesan agent "Aksi dibatalkan".

**Detail controller (`AiAgentController`):**
- `confirmActions(Request)` — validasi `conversation_id`; load session pending; `response()->stream()` sama pola `streamChat` (start → progress → save message → done → `[DONE]`); error handling sama.
- `cancelActions(Request)` — validasi `conversation_id`; hapus session; simpan pesan agent "Aksi dibatalkan."; return JSON.
- `streamChat()` — setelah `process()`, simpan `pending_actions` ke session bila tidak kosong; kirim di event `done`.
- Keamanan: pastikan ownership conversation (`user_id === auth()->id()`), pending hanya dieksekusi dari session (bukan dari body request).

**Routes (`routes/admin.php`, dekat L102-106):**
```php
Route::post('websites/ai/chat/confirm', [AiAgentController::class, 'confirmActions'])->name('websites.ai.chat.confirm');
Route::post('websites/ai/chat/cancel', [AiAgentController::class, 'cancelActions'])->name('websites.ai.chat.cancel');
```

### 5. Frontend — `AiAgent.vue`
- `Message.actions` tetap untuk executed; tambah render `pending_actions` (dari event `done`):
  - State `pendingActions = ref<any[]>(null)` + `pendingConversationId`.
  - Kartu konfirmasi di bawah pesan agent: daftar aksi (pakai `formatActionName` + badge) + tombol "Jalankan" (POST confirm, parse SSE lagi) & "Batal" (POST cancel, hapus kartu).
  - Reuse parser SSE existing (`handleEvent` → buat fungsi `streamRequest(url, body, onEvent)` yang bisa dipakai ulang untuk confirm).
- Tambah card render hasil baru:
  - `list_tasks` / `update_task_status` — daftar tugas (title, status badge, priority, due_date, assignee).
  - `list_customers` — daftar customer (name, email, status badge).
  - `list_unpaid_invoices` — daftar invoice (no, customer, amount, due, days_late).
  - `business_summary` — kartu angka agregat (reuse pola grid).
- Tambah nama aksi ke `formatActionName()`: list_tasks, create_task, update_task_status, list_customers, create_customer, update_customer_status, list_unpaid_invoices, mark_invoice_paid, business_summary.
- Update welcome text + suggestions (contoh: "Berapa invoice yang belum dibayar?", "Buatkan tugas untuk hari ini", "Ringkas penjualan bulan ini").

### 6. `CreateArticle` — tetap high-risk (publish ke WP)
Tidak diubah perilakunya, hanya masuk HIGH_RISK sehingga butuh konfirmasi sebelum publish. Opsi: tambah param `draft_only` bila user minta "draft dulu" (kecil, optional). **Skip** — tetap publish flow sekarang.

## Asumsi & Keputusan
- Konfirmasi = **hanya aksi HIGH_RISK** (per pilihan user). Aksi baca + `create_task`/`update_task_status` jalan langsung.
- Pending actions disimpan di **session** server, bukan body request (anti-manipulasi). Session expire → user diminta kirim ulang.
- Konteks = **ringkasan agregat** + website/orders penuh (kebutuhan pick id). Hemat token.
- `create_customer` masuk HIGH_RISK (buat akun customer asli). `create_task` LOW (reversible).
- `business_summary` = read-only query agregat, tanpa aksi tulis.
- Tidak menambah depedensi baru; pola mengikuti file existing (sub-agent + `$onEvent` + SSE).
- Blog internal (`create_blog_post`) **diluar scope** iterasi ini — baru artikel WP klien (existing). Ditambah saat AI Content diperluas.

## Langkah Implementasi
1. `AiAgentService`: perluas `buildContext()` (ringkasan) + update system prompt.
2. Buat `TaskAgent.php` (list/create/update status) + `CustomerAgent.php` (list/create/status/invoice) + method `businessSummary()`.
3. `AiAgentService`: risk map + refactor `executeActions` (executed vs pending) + `executePendingActions()`.
4. `AiAgentController`: simpan/load pending session; tambah `confirmActions()` (SSE) + `cancelActions()`; ubah `streamChat()` kirim `pending_actions`.
5. `routes/admin.php`: tambah 2 route.
6. `AiAgent.vue`: render pending + kartu konfirmasi; card hasil baru; `formatActionName`; welcome/suggestions.
7. Verifikasi.

## Verifikasi
1. `php -l` semua file PHP yang diubah.
2. Test existing tetap hijau: `php artisan test --filter="AdminAiControllerTest|CustomerAiControllerTest"` (pastikan tidak ada regresi).
3. Manual (`php artisan serve` + `npm run dev`, login admin):
   - **Aksi aman**: "Berapa tugas yang belum selesai?" → jawab langsung, tanpa konfirmasi. "Cek website yang perlu update" → langsung jalan.
   - **Aksi berisiko**: "Update semua website yang perlu update" → muncul kartu konfirmasi (daftar aksi), belum dieksekusi. Klik **Jalankan** → progress SSE → hasil. Klik **Batal** (percobaan lain) → pesan "Aksi dibatalkan", tidak ada perubahan.
   - **Renew order**: "Perpanjang order si X 3 bulan dan tandai lunas" → konfirmasi dulu.
   - **Business summary**: "Ringkas kondisi penjualan bulan ini" → kartu angka.
   - **Tugas**: "Buatkan tugas 'cek hosting' untuk [karyawan] priority tinggi" → langsung dibuat (tanpa konfirmasi), muncul di `/admin/tasks`.
4. Refresh halaman → riwayat conversation tetap ada, kartu hasil tampil.
