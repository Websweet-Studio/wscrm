# Plan: Improvement Aplikasi (WSCRM / WebSweetStudio)

Audit menyeluruh: keamanan, arsitektur/kualitas, performa & operasional. Prioritas eksekusi diurutkan — perbaiki yang **berbahaya dulu**, baru yang rapi/performa.

---

## P0 — KEAMANAN KRITIS (perbaiki segera)

1. **Celah otorisasi admin: pendaftar publik = admin penuh**
   - `routes/admin.php:40` pakai `admin.auth` → `AdminAuthorization` (`app/Http/Middleware/AdminAuthorization.php:17-42`) cuma memblokir guard `customer`, **tidak cek role**.
   - `EnsureAdmin.php` sudah ada & ter-alias `admin`, tapi **tidak dipakai di route manapun**.
   - `User.php:5` `MustVerifyEmail` di-comment → akun belum verified lolos `'verified'`.
   - Akibat: siapa pun daftar di `/register` bisa buka semua halaman admin: export DB, import DB, impersonate customer, gratis kredit AI, kontrol DirectAdmin.
   - **Fix:** pasang `admin` middleware di grup admin + aktifkan verifikasi email + batasi registrasi (invite/allowlist).

2. **RCE via self-update (hanya butuh akun terdaftar)**
   - `routes/web-update.php:13` grup update cuma `middleware(['auth'])` — bukan super-admin.
   - `UpdateController.php:145-156` terima `download_url` dari GitHub repo bebas → unduh zip → ganti seluruh kode app (`:306-383`).
   - **Fix:** super-admin-only + verifikasi checksum/release signed + validasi struktur zip.

3. **Web installer publik di `public/install/` ikut ter-bundle**
   - `public/install/index.php` (2000+ baris): `shell_exec` artisan, tulis `.env`, drop/migrate DB, hapus folder — **tanpa auth & tanpa kunci installer**.
   - `BuildPackage.php:613-621` menyalinnya ke paket produksi; `public/index.php` redirect ke `/install/` saat `installer.lock` absen.
   - **Fix:** hapus dari build, blok `/install` di `.htaccess`, wajib delete manual pasca-install.

## P1 — KEAMANAN (High)

4. **Password DirectAdmin & WordPress app-password plaintext di DB** — `DirectAdminSetting::setValue('password', ...)`, `WebsiteClient.wp_app_password`. Ekspos via export DB. → enkripsi `Crypt` saat simpan/decrypt saat baca.
5. **API key AI plaintext fallback** — `AiClient.php:52-59` pakai nilai DB mentah saat decrypt gagal; legacy loop decrypt-all di `ChatCompletionsController.php:309-320`. → satu kali migrasi ke encrypted+hash, hapus fallback.
6. **Password customer masuk log** — `CustomerController.php:149-153` log `$request->all()` (termasuk `password`). → log field allowlist.
7. **`db_seed_users` (reset password super admin `password`) bisa dijalankan karyawan** — `AdminToolsController.php:89`. → hapus dari tools / super-admin only.
8. **AI chat publik tanpa biaya batas** — `PublicAiChatController` (throttle 30/min, no auth/domain allowlist) bakar `AI_API_KEY` + prompt injection via history. → IP + budget harian global + kunci interaksi + system prompt keras.
9. **Chat completions tanpa cap ukuran** — `ChatCompletionsController.php:30-31` ambil `$request->all()`, tanpa limit jumlah pesan/panjang konten. → cap pesan (~100), panjang per pesan, ukuran payload.
10. **Error upstream bocor ke client** — `AiClient.php:356-363` embed cuplikan body gateway; `ChatCompletionsController.php:63` balas raw exception. → pesan umum ke client, detail di log.
11. **Race double-consume kredit** — `AiGateway.php:149-182` lock dilepas sebelum panggilan provider. → reserve kredit dalam 1 transaksi mencakup panggilan provider.
12. **CSRF exempt wildcard `/api/*`** — `VerifyCsrfToken.php:14-17`. → daftar URI eksplisit.
13. **Beacon `POST /api/demo-embed/track` tanpa throttle** → throttle + dedupe + cap referer.

## P1 — PERFORMA / OPS (High)

14. **Admin dashboard ~55+ query per load** — `DashboardController.php:101-130` loop per-hari/per-bulan. → 1 query `GROUP BY` per chart.
15. **Email sinkron (bukan antrian)** — semua `Mail` (`InvoiceEmail`, `CustomerWelcomeMail`, dll) tidak `implements ShouldQueue`. → tambah `ShouldQueue` + dokumentasikan worker (`queue:work` + cron `schedule:run`).
16. **Renewal invoice di-generate tiap GET list invoice** — `InvoiceController.php:24` → `generateRenewalInvoices(30)` side-effect. → pindah ke `Schedule::command('invoice:generate-renewals')->daily()` (command sudah ada, belum dijadwalkan).
17. **Streaming dipalsukan** — `AiClient::streamChat` (SSE sungguhan) dead code; `ChatCompletionsController:261-289` buffer penuh lalu kirim 1 chunk + `[DONE]`. → sambungkan `streamChat` via `AiGateway`, pipe SSE.
18. **Index hilang di `ai_transactions`** — tanpa index `source`, `ai_model_id`, `invoice_id`, `ai_package_id`; analytics (`TransactionController:46-101`) full-table scan + aggregasi di PHP. → tambah index + `GROUP BY` di SQL.
19. **Branding/AiSettings tak di-cache** — `BrandingSetting::getValue()` 1 query/key; 4+ query per halaman. → `Cache::rememberForever`, invalidate saat save.

## P2 — ARSITEKTUR & KUALITAS

20. **Fat controller** — `Admin\OrderController` 624 baris, logika harga duplikat 3x (`store:184-242` vs `getDefaultPrice:354-378`, fallback tidak konsisten 500000 vs 2500000). → ekstrak `OrderPricingService`.
21. **`checkAdmin()` diulang 5 controller** — duplikasi `EnsureAdmin`. → hapus, pakai middleware.
22. **Nomor invoice upgrade beda format** — `OrderController.php:579` `INV-UPG-...` vs `InvoiceGeneratorService::generateInvoiceNumber()`. → satu helper.
23. **`Auth::guard('customer')` diulang 8 controller** → `CustomerBaseController`.
24. **Dua flow login** + hardcode `admin@example.com` (`Customer\Auth\LoginController.php:42`) → satukan.
25. **2 pasang migrasi bentrok timestamp** (`2026_08_01_000003` ×2, `2026_01_24_023335` ×2) + duplikat `users.username` 2 migrasi → rename/squash.
26. **Format error JSON tidak konsisten** (`{success}`, `{error}`, `{error:{...}}`) → envelope seragam.
27. **`AiAgentController` error → HTTP 200** (`:88-97`) → 4xx/5xx.
28. **Bahasa campur EN/ID** di flash & UI → normalisasi (pilih ID).
29. **Hex hardcoded vs token tema** (dashboard AI sudah konsisten, halaman lain belum) → migrasi bertahap.

## P3 — KEBERSIHAN / DEAD CODE / TEST

30. Hapus file mati: `_requestedExtension_`, `fix_negative_discounts.php`, `index.php` + `deployment-index.php` (root), `public/index-original.php`, `resources/js/lib/*-stub.ts`, `Blog/Index.vue.backup`, komponen tak terpakai (`OrderChart.vue`, `MonthlyStatsStrips.vue`, `DomainBundleModal.vue`, `HostingOrderModal.vue`, `Icon.vue`), `routes/whmcs.php` (rename).
31. **Tingkatkan test** — area bernilai tinggi tanpa test perilaku: flow pembayaran invoice customer (upload bukti → status → kredit AI), CRUD Blog/Task/Expense/Journal/HostingPlan/DomainPrice/ServicePlan/Employee/Demo*.
32. **N+1 kecil** — `InvoiceGeneratorService.php:25` + `with('orderItems')`; `TaskController.php:71` `with('customer:id,name')`.
33. **2 TODO yang mengubur fitur** — email pemberitahuan renewal (`InvoiceGeneratorService.php:68-69`) & cek ketersediaan domain di list (`DomainPriceController.php:35` — `DomainAvailabilityService` sudah ada, belum dipakai).
34. **`LOG_LEVEL=debug` / `APP_DEBUG=true` default** → pastikan `.env` produksi `APP_DEBUG=false`, turunkan log level.

## YANG SUDAH BAIK (jangan diubah)

- Timeout & retry external HTTP konsisten (DirectAdmin, WordPress, AiClient, update).
- Index DB mayoritas lengkap (orders, invoices, tasks, notifications).
- Halaman customer dashboard & AI sudah memakai token tema + fitur lengkap.
- Error handling layanan luar (WordPressService, DirectAdminService, AiGateway circuit breaker) rapi.

---

## Rekomendasi eksekusi

1. **Sprint 1 (P0):** item 1-3 — otorisasi admin, kunci update, hapus installer. Dampak terbesar.
2. **Sprint 2 (P1 keamanan):** item 4-13.
3. **Sprint 3 (P1 performa):** item 14-19 (dashboard query, antrian email, renewal schedule, index DB).
4. **Sprint 4 (P2+P3):** refactor bertahap + pembersihan + test.
