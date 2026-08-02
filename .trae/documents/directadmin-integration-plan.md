# Plan: Integrasi DirectAdmin (Suspend / Unsuspend / List Akun Sinkron Order)

## Ringkasan

Tambah fitur koneksi ke panel DirectAdmin dari aplikasi admin:

1. Simpan kredensial server DA (host, port, username, password/login key) via **form di admin**, tersimpan di **DB** (pola `branding_settings`).
2. Halaman baru **DirectAdmin** yang menampilkan list akun DA + akun order lokal yang **sinkron via domain** (`orders.domain_name` == domain akun DA).
3. Tombol **Suspend** / **Unsuspend** per akun → panggil API DA, lalu status order lokal ikut berubah (`suspended` / `active`).

Tanpa auto-provision, tanpa auto-suspend otomatis — semua manual.

## Analisis Kondisi Saat Ini

- Laravel 12 + Inertia (Vue 3) + Tailwind. Pola service HTTP ada di `app/Services/` (contoh: [WordPressService.php](file:///g:/wscrm/app/Services/WordPressService.php), [DomainAvailabilityService.php](file:///g:/wscrm/app/Services/DomainAvailabilityService.php) pakai `Http` facade + `config('services.rna...')`).
- Order punya kolom: `domain_name`, `status` enum (`pending,processing,active,suspended,expired,cancelled,terminated`), `service_type` enum (`hosting,domain`, nullable — order buatan admin via `store()` tidak mengisi `service_type`). Lihat [Order.php](file:///g:/wscrm/app/Models/Order.php).
- Settings berbasis DB sudah ada polanya: tabel `branding_settings` key-value + `BrandingSetting::getValue/setValue` ([BrandingSetting.php](file:///g:/wscrm/app/Models/BrandingSetting.php)).
- Sidebar admin: [AppSidebar.vue](file:///g:/wscrm/resources/js/components/AppSidebar.vue), grup **Hosting**.
- Smoke test URL admin: [AdminPagesSmokeTest.php](file:///g:/wscrm/tests/Feature/AdminPagesSmokeTest.php).
- Belum ada config/kolom/servis DirectAdmin sama sekali.

## API DirectAdmin yang Dipakai (Legacy API, port default 2222, auth HTTP Basic)

- List user: `CMD_API_SELECT_USERS` (GET) → `list[]` berisi username.
- Detail per user (email, domain, package, status suspend): `CMD_API_SHOW_USER_CONFIG?user=X` (GET) → respons urlencoded, berisi field `suspended=yes/no`.
- Suspend: `CMD_API_MODIFY_USER` (POST) `action=suspend&user=X`.
- Unsuspend: `CMD_API_MODIFY_USER` (POST) `action=unsuspend&user=X`.
- Test koneksi: `CMD_API_SHOW_USER_CONFIG` (user sendiri) → `error=0` berarti sukses.

Pola ini sama dengan modul WHMCS-DirectAdmin. Respons legacy berupa urlencoded; parsing dengan `parse_str()`, dukung juga bila server balas JSON.

## Perubahan yang Diusulkan

### 1. Migration — `database/migrations/2026_08_02_000001_create_directadmin_settings_table.php`
Tabel key-value sederhana (pola `branding_settings`):
- `id`, `key` (unique string), `value` (text nullable), `timestamps`.

### 2. Model — `app/Models/DirectAdminSetting.php`
- `$fillable = ['key','value']`.
- Static helper: `getValue(string $key, mixed $default = null)`, `setValue(string $key, string $value): void`, `all(): array` (map `key => value`).

### 3. Servis — `app/Services/DirectAdminService.php`
Metode:
- `settings(): array` — gabung default + nilai DB: `scheme` (default `https`), `host`, `port` (default `2222`), `username`, `password`, `verify_ssl` (default `false`).
- `isConfigured(): bool` — `host`, `username`, `password` terisi.
- `request(string $command, array $params = [], string $method = 'GET'): array` — build URL `{scheme}://{host}:{port}/{command}`, `Http::withBasicAuth(username, password)`, `withoutVerifying()` bila `verify_ssl=false`, timeout 30. Parsing: coba `json_decode`, fallback `parse_str`. Throw `RuntimeException` bila HTTP gagal/401.
- `testConnection(): array` — `request('CMD_API_SHOW_USER_CONFIG')` → `['ok' => error==0, 'message' => text]`.
- `listAccounts(): array` — `request('CMD_API_SELECT_USERS')` → ambil `list`/`list[]` username; untuk tiap user `request('CMD_API_SHOW_USER_CONFIG', ['user' => $u])` → `['username','email','domain','package','suspended' => bool]`.
- `suspend(string $username): array` / `unsuspend(string $username): array` — `request('CMD_API_MODIFY_USER', ['action' => 'suspend'|'unsuspend', 'user' => $username], 'POST')`.
- `successful(array $result): bool` — `(int)($result['error'] ?? 1) === 0`.

### 4. Controller — `app/Http/Controllers/Admin/DirectAdminController.php`
- `index()` — ambil settings; bila terkonfigurasi, panggil `listAccounts()` dalam try/catch (gagal → `$error`). Sinkronkan tiap akun DA ke order lokal via helper `findOrderByDomain()` (normalisasi: `strtolower`, buang prefix `www.`, trim). Return Inertia `Admin/DirectAdmin/Index` dengan: `settings` (password di-mask), `accounts` (tiap item + `linked_order`), `connection` (ok/error), `error`.
- `saveSettings(Request)` — validasi `host` required, `port` integer, `username` required, `password` nullable, `scheme in:https,http`, `verify_ssl` boolean. Password kosong → pertahankan lama. Simpan via `DirectAdminSetting::setValue`, lalu `testConnection()`; redirect back dengan flash `success`/`error` berisi hasil tes koneksi.
- `suspend(string $username)` — panggil `suspend()`; sukses → cari `findOrderByDomain()` dari akun tsb, update `status='suspended'`; flash.
- `unsuspend(string $username)` — panggil `unsuspend()`; sukses → update order `status='active'`; flash.
- Helper privat `findOrderByDomain(string $domain): ?Order` — query `Order::where('domain_name', normalisasi)`, prefer `service_type='hosting'`, lalu urut `active` duluan.

### 5. Routes — `routes/admin.php` (di dalam grup admin yang sudah ada)
```php
Route::get('directadmin', [DirectAdminController::class, 'index'])->name('directadmin.index');
Route::post('directadmin/settings', [DirectAdminController::class, 'saveSettings'])->name('directadmin.settings');
Route::post('directadmin/accounts/{username}/suspend', [DirectAdminController::class, 'suspend'])->name('directadmin.suspend');
Route::post('directadmin/accounts/{username}/unsuspend', [DirectAdminController::class, 'unsuspend'])->name('directadmin.unsuspend');
```

### 6. Halaman Frontend — `resources/js/pages/Admin/DirectAdmin/Index.vue`
- **Kartu Pengaturan**: form host, port, username, password (placeholder `********`, opsional saat edit), pilihan scheme (`https`/`http`), checkbox verify SSL, tombol **Simpan & Tes Koneksi** → `router.post('/admin/directadmin/settings', ...)`.
- **Status koneksi**: badge hijau "Terhubung" / merah "Gagal" + pesan error.
- **Tabel Akun**: kolom Username | Domain | Email | Status DA (badge Aktif/Ditangguhkan) | Order Terhubung (`#id` + nama customer + status order) | Aksi (tombol **Suspend** / **Unsuspend**, sesuai status DA, konfirmasi via `confirm()`).
- **Panel "Order tanpa akun DA"**: daftar order hosting yang domainnya tidak cocok dengan akun mana pun (bantuan link).
- Tampilkan flash (`usePage().props.flash.success/error`).
- Pakai komponen UI yang sudah ada: `Card`, `Button`, `Input`, `Label`, `Badge`, `Table`, `AppLayout`. Icon dari `lucide-vue-next` (`Server`, `RefreshCw`, `Ban`, `Play`/`CheckCircle`).

### 7. Sidebar — `resources/js/components/AppSidebar.vue`
Tambah item di grup **Hosting**:
```ts
{ title: 'DirectAdmin', href: '/admin/directadmin', icon: Server },
```

### 8. Smoke test — `tests/Feature/AdminPagesSmokeTest.php`
Tambah baris `['/admin/directadmin', 'Admin/DirectAdmin/Index']`.

### 9. Test baru — `tests/Feature/DirectAdminIntegrationTest.php`
Pakai `Http::fake()`:
- Suspend berhasil → API dipanggil `CMD_API_MODIFY_USER` + order ter-link berubah ke `suspended`.
- Unsuspend berhasil → order kembali `active`.
- Index page render 200 saat belum terkonfigurasi.
- (Membutuhkan `DirectAdminSetting` terisi + order + Http fake; koneksi DA asli tidak dipanggil.)

## Asumsi & Keputusan

- Auth DA pakai HTTP Basic (username + password, atau **Login Key** DA sebagai password).
- Login DA yang dipakai sebaiknya admin/reseller (butuh izin `CMD_API_SELECT_USERS`).
- Sinkron akun **hanya via domain** (sesuai pilihan user) — tidak ada kolom baru di `orders`.
- Verify SSL default **off** (server DA sering self-signed), bisa diubah dari form.
- Single server DA (tanpa multi-server / server id). Tambah saat butuh.
- Tidak ada auto-provision akun saat order aktif, dan tidak ada auto-suspend dari invoice.

## Verifikasi

1. `php artisan migrate` — tabel `directadmin_settings` dibuat.
2. `php artisan test --filter=AdminPagesSmokeTest` — halaman baru lolos.
3. `php artisan test --filter=DirectAdminIntegrationTest` — suspend/unsuspend + index ter-fake.
4. Manual: buka `/admin/directadmin` → isi settings → Simpan & Tes Koneksi → list akun muncul & tersinkron dgn order → klik Suspend (order jadi `suspended`) → Unsuspend (order jadi `active`).
