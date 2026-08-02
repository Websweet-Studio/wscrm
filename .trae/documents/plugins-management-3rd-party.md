# Plan: Manajemen Plugin Pihak Ketiga (Sub Menu "Plugins")

## Summary

Tambah sub menu **"Plugins"** di bawah **Manage Website** pada sidebar admin. Halaman berisi list plugin pihak ketiga (nama plugin + file zip yang di-upload admin). Dari halaman ini plugin bisa di-*push*/update ke website WordPress target. Karena plugin premium/custom tidak ada di wordpress.org, mekanisme install/update zip ditanam di theme **wsbase** (endpoint REST kustom) yang sudah punya pola `Plugin_Upgrader->install($package)`.

Alur deploy: admin klik **"Update ke Website"** → pilih website → WSCRM kirim URL zip (disk public) ke `{wp}/wp-json/wsbase/v1/install-plugin` dengan Application Password → WP unduh zip, install via `Plugin_Upgrader`, lalu aktifkan otomatis.

## Current State

- WP REST API native TIDAK mendukung upload zip plugin — hanya install dari slug wordpress.org.
- Theme `wsbase` (`G:\DEV\app\public\wp-content\themes\wsbase\inc\updater.php`) sudah punya pola install plugin dari URL: `Plugin_Upgrader->install($package)` + `Automatic_Upgrader_Skin` (dipakai untuk SweetAddons & WP Store dari GitHub release).
- `WordPressService::syncSiteInfo()` hanya re-sync data plugin (baca), tidak pernah install/update.
- `WebsiteClient` punya `url`, `wp_username`, `wp_app_password` → auth Basic untuk REST API WP.
- Belum ada storage/disk khusus plugin; disk `public` ada (`storage/app/public`, URL `{APP_URL}/storage`), tapi symlink `public/storage` belum dibuat.
- Sidebar admin = array hardcoded di `AppSidebar.vue`; menu Manage Website ada di baris 132-158.
- Route `websites/ai/*` harus didaftarkan SEBELUM `Route::resource('websites')` (pola yang sama untuk route baru).
- Halaman admin = Inertia + Vue, layout `AppLayout`, `checkAdmin()` (guard `isAdmin()`).

## Proposed Changes

### A. WSCRM (Laravel)

#### A1. Migration `database/migrations/2026_08_01_000003_create_third_party_plugins_table.php`

Tabel `third_party_plugins`:

| kolom | tipe | catatan |
|---|---|---|
| id | bigint PK | |
| name | string | nama plugin |
| slug | string unique | folder plugin di WP (`alpha_dash`) |
| description | text nullable | |
| version | string nullable | versi zip saat ini |
| file_path | string | relative path di disk public, `plugins/{slug}.zip` |
| file_name | string nullable | nama file zip asli saat upload |
| file_size | bigint nullable | ukuran bytes |
| is_active | boolean default true | |
| timestamps | | |

#### A2. Model `app/Models/ThirdPartyPlugin.php`

`$fillable` = semua kolom di atas; `$casts` = `['is_active' => 'boolean']`.

#### A3. Storage

- Upload zip ke disk `public`, folder `plugins/`, nama file `{slug}.zip` (satu zip per plugin; upload baru menimpa).
- Jalankan `php artisan storage:link` (symlink `public/storage` belum ada).

#### A4. Service `app/Services/PluginDeployService.php`

Method `deploy(ThirdPartyPlugin $plugin, WebsiteClient $website): array`:
1. Validasi `wp_username` + `wp_app_password` ada → error bila kosong.
2. `$packageUrl = Storage::disk('public')->url($plugin->file_path);`
3. `POST {website->url}/wp-json/wsbase/v1/install-plugin`:
   - Header `Authorization: Basic base64(wp_username:wp_app_password)`
   - Body JSON: `['package_url' => $packageUrl, 'activate' => true]`
   - `timeout(180)` (install zip bisa lama)
4. Respon sukses → `['success' => true, 'message' => ...]`; selain itu parse `WP_Error` dari JSON body → `['success' => false, 'message' => ...]`. Wrap `try/catch`, exception → error.

#### A5. Controller `app/Http/Controllers/Admin/ThirdPartyPluginController.php`

- `checkAdmin()` (pola controller admin existing).
- `index()`: `ThirdPartyPlugin::orderBy('name')->get()` + `WebsiteClient::orderBy('name')->get(['id','name','url'])` → `Inertia::render('Admin/Websites/Plugins', ['plugins'=>..., 'websites'=>...])`.
- `store(Request)`:
  - validasi: `name` required, `slug` required `alpha_dash` unique, `version` nullable, `file` required `mimes:zip` max 50MB.
  - simpan file → `$request->file('file')->storeAs('plugins', $slug.'.zip', 'public')`.
  - redirect back + flash success.
- `update(Request, ThirdPartyPlugin $plugin)`:
  - update `name`/`version`/`description`; bila ada `file` baru → hapus file lama, simpan baru (namanya tetap `{slug}.zip`).
  - redirect back + flash success.
- `destroy(ThirdPartyPlugin $plugin)`: hapus file zip + record → redirect back.
- `deploy(Request, ThirdPartyPlugin $plugin)`:
  - validasi `website_id` required exists.
  - panggil `PluginDeployService::deploy()` → `response()->json(['success'=>bool, 'message'=>...])` (422 bila gagal).

#### A6. Routes `routes/admin.php`

Di blok atas (sebelum `Route::resource('websites')`, mengikuti komentar existing):

```php
Route::get('websites/plugins', [ThirdPartyPluginController::class, 'index'])->name('websites.plugins');
Route::post('websites/plugins', [ThirdPartyPluginController::class, 'store'])->name('websites.plugins.store');
Route::post('websites/plugins/{plugin}', [ThirdPartyPluginController::class, 'update'])->name('websites.plugins.update');
Route::post('websites/plugins/{plugin}/deploy', [ThirdPartyPluginController::class, 'deploy'])->name('websites.plugins.deploy');
Route::delete('websites/plugins/{plugin}', [ThirdPartyPluginController::class, 'destroy'])->name('websites.plugins.destroy');
```

#### A7. Vue `resources/js/pages/Admin/Websites/Plugins.vue`

- Layout `AppLayout`, breadcrumb: `Dashboard > Manage Website > Plugins` (pola AiAgent.vue).
- Props: `plugins: array`, `websites: array`.
- UI (komponen ui local yang sudah dipakai: Card, Button, Badge, Input, Label, Textarea, Table, Alert):
  - Header + tombol **"Tambah Plugin"** → form (name, slug, version, description, file zip) via `useForm` multipart → `router.post(route('admin.websites.plugins.store'), ...)`.
  - Tabel list plugin: Nama, Slug, Versi, File ZIP (nama asli + ukuran KB), Status aktif, Aksi.
  - Aksi per baris: **Edit** (ganti zip/versi), **Update ke Website** (dropdown pilih website target → POST deploy → loading state → tampilkan hasil sukses/error inline), **Hapus** (konfirmasi).
- Wayfinder akan auto-generate route helper setelah dev server jalan ulang.

#### A8. Sidebar `resources/js/components/AppSidebar.vue`

Tambahkan di `children` Manage Website (baris 132-158):

```js
{ title: 'Plugins', href: '/admin/websites/plugins', icon: Package },
```

Import `Package` dari `lucide-vue-next`.

### B. WP side (theme wsbase) — `G:\DEV\app\public\wp-content\themes\wsbase`

#### B1. File baru `inc/plugin-installer-api.php`

Endpoint REST kustom (ditanam di theme sekalian, sesuai keputusan user):

```php
<?php
defined('ABSPATH') || exit;

// Endpoint: POST /wp-json/wsbase/v1/install-plugin
// Auth: Application Password (capability install_plugins dipakai dari user)
add_action('rest_api_init', function () {
    register_rest_route('wsbase/v1', '/install-plugin', [
        'methods'  => WP_REST_Server::CREATABLE,
        'permission_callback' => function () {
            return current_user_can('install_plugins');
        },
        'callback' => 'wsbase_rest_install_plugin',
    ]);
});

function wsbase_rest_install_plugin(WP_REST_Request $request) {
    $packageUrl = esc_url_raw($request->get_param('package_url'));
    $activate   = (bool) $request->get_param('activate');

    // Validasi: URL https + ekstensi .zip
    if (!$packageUrl || !preg_match('/^https:\/\//i', $packageUrl) || substr(strtolower($packageUrl), -4) !== '.zip') {
        return new WP_Error('wsbase_invalid_package', 'URL paket zip tidak valid.', ['status' => 400]);
    }

    // Snapshot plugin sebelum install untuk deteksi file plugin baru
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
    $before = array_keys(get_plugins());
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

    $skin     = new Automatic_Upgrader_Skin();
    $upgrader = new Plugin_Upgrader($skin);
    $result   = $upgrader->install($packageUrl);

    if (is_wp_error($result)) {
        return $result;
    }
    if ($result === false) {
        return new WP_Error('wsbase_install_failed', 'Install plugin gagal.', ['status' => 500]);
    }

    // Deteksi plugin baru (folder zip ≠ slug plugin)
    $after   = array_keys(get_plugins());
    $newFile = null;
    foreach ($after as $f) {
        if (!in_array($f, $before, true)) { $newFile = $f; break; }
    }

    if ($activate && $newFile) {
        $act = activate_plugin($newFile);
        if (is_wp_error($act)) {
            return $act;
        }
    }

    wp_clean_plugins_cache();
    return new WP_REST_Response([
        'success'     => true,
        'message'     => 'Plugin berhasil diinstall' . ($activate ? ' & diaktifkan' : ''),
        'plugin_file' => $newFile,
    ], 200);
}
```

#### B2. `functions.php`

Tambahkan `/plugin-installer-api.php` ke blok require (di sekitar baris 33 tempat `/updater.php`).

## Assumptions & Decisions

- **Push dari WSCRM**: trigger update ada di halaman Plugins WSCRM (keputusan user).
- **Aktifkan otomatis** setelah install/update (`activate=true`) (keputusan user).
- Zip disimpan di disk `public` → URL `{APP_URL}/storage/plugins/{slug}.zip`. WP side yang mengunduh URL ini; **APP_URL di .env produksi harus URL publik WSCRM** yang bisa diakses WP.
- Nama file zip tetap `{slug}.zip` — satu versi tersimpan, upload baru menimpa.
- Application Password WP harus dibuat dari user yang punya capability `install_plugins` (user admin).
- Theme wsbase versi baru (dengan endpoint ini) harus di-deploy ke website WP yang mau di-manage — di luar kontrol WSCRM.
- Tidak dicatat ke `JournalEntry` (fitur admin manual, bukan aktivitas AI).
- Deploy memakai Application Password yang sama dengan fitur lain (tidak ada kredensial baru).

## Verification

1. `php artisan storage:link` → `public/storage` muncul.
2. `php artisan migrate` → tabel `third_party_plugins` terbuat.
3. Buka `/admin/websites/plugins` → menu aktif di sidebar → upload zip contoh → muncul di tabel.
4. WP side: daftarkan website WP yang reachable (mis. WP lokal `G:\DEV\app`) sebagai `WebsiteClient` dengan Application Password user admin → klik "Update ke Website" → cek hasil sukses; verifikasi plugin terinstall + aktif di `wp-admin/plugins.php`.
5. Tes langsung endpoint WP: `POST {wp}/wp-json/wsbase/v1/install-plugin` dengan header Basic app password + body `{package_url, activate:true}` → respon `{success:true, plugin_file}`.
6. Uji kasus gagal: website tanpa kredensial → pesan error; URL zip tidak valid → WP `WP_Error`.
