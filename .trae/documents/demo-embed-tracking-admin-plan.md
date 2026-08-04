# Plan: Demo Embed Tracking & Admin Blocking

## Summary

Saat ini embed widget (`embed.js`) dan public API (`/api/demos`, `/api/demos/{id}`) sudah ada, tapi belum ada tracking dan blocking. Plan ini menambahkan:
1. Tracking hits untuk semua akses (embed widget, oembed, API)
2. Halaman admin untuk melihat statistik tracking
3. Fitur block domain mencurigakan dari admin — API/embed akan return 403

---

## Current State

- **Migration** `demo_embed_trackings` sudah dibuat tapi belum di-migrate. Kolom: `id`, `referer_url`, `referer_host`, `embed_type` (listing/single/oembed), `demo_website_id`, `hits`, `first_seen_at`, `last_seen_at`, `timestamps`.
- **Model** `DemoEmbedTracking` sudah ada dengan static `recordHit()`.
- **API public**: `GET /api/demos` (`publicIndex`), `GET /api/demos/{id}` (`publicShow`), `GET /api/oembed` (`oembed`).
- **Route tracking**: `POST /api/demo-embed/track` sudah ada di `api.php` tapi method `trackEmbed` belum dibuat di controller.
- **Admin pages**: Belum ada halaman untuk tracking stats.
- **Embed.js**: Belum ada beacon ping ke tracking endpoint.

---

## Proposed Changes

### 1. Migration: Tambah `is_blocked` & `api` enum

**File**: `d:\wscrm\database\migrations\2026_08_04_000001_create_demo_embed_trackings_table.php`

**What**: 
- Tambah `api` ke enum `embed_type`: `['listing', 'single', 'oembed', 'api']`
- Tambah kolom `is_blocked` (boolean, default false)
- Tambah kolom `blocked_at` (timestamp, nullable)

**Why**: `api` type untuk tracking akses API. `is_blocked` untuk block domain. `blocked_at` untuk audit kapan di-block.

---

### 2. Model: Update `DemoEmbedTracking`

**File**: `d:\wscrm\app\Models\DemoEmbedTracking.php`

**What**:
- Tambah `is_blocked`, `blocked_at` ke `$fillable`
- Tambah `blocked_at` ke `casts` (datetime)
- Tambah `is_blocked` ke `casts` (boolean)
- Tambah static method `isBlocked(string $referer): bool` — cek apakah referer host ada yang di-block
- Tambah scope `scopeBlocked($query)` dan `scopeNotBlocked($query)`

**Why**: Model punya semua logic untuk cek blocked status.

---

### 3. Controller: Buat `trackEmbed` method

**File**: `d:\wscrm\app\Http\Controllers\DemoWebsiteController.php`

**What**: Method baru `trackEmbed(Request $request): JsonResponse`
- Ambil `referer` dari `$request->header('Referer')` atau `$request->input('ref')`
- Ambil `type` dari `$request->input('type', 'listing')`
- Ambil `demo_id` dari `$request->input('demo_id')` (nullable)
- Panggil `DemoEmbedTracking::recordHit($referer, $type, $demoId)`
- Return `{ok: true}`

**Why**: Endpoint dipanggil oleh embed.js beacon dan bisa juga oleh API consumer.

---

### 4. Controller: Tambah tracking + blocking di API methods

**File**: `d:\wscrm\app\Http\Controllers\DemoWebsiteController.php`

**What**: Di awal `publicIndex`, `publicShow`, dan `oembed`:
```php
$referer = $request->header('Referer', '');
if ($referer && DemoEmbedTracking::isBlocked($referer)) {
    return response()->json(['error' => 'Access denied'], 403);
}
if ($referer) {
    DemoEmbedTracking::recordHit($referer, 'api', $demoId ?? null);
}
```
- `publicIndex` → type `api`, no demo id
- `publicShow` → type `api`, with demo id
- `oembed` → type `oembed`, no demo id

**Why**: Tracking semua akses API/oembed + blokir domain yang di-block.

---

### 5. Embed.js: Tambah tracking beacon

**File**: `d:\wscrm\app\Http\Controllers\DemoWebsiteController.php` (method `embedJs`)

**What**: Di akhir IIFE embed.js, setelah `render()`:
```javascript
// Tracking beacon
(function() {
    var img = new Image();
    img.src = APP_URL_PLACEHOLDER + '/api/demo-embed/track?ref=' + encodeURIComponent(document.referrer || window.location.href) + '&type=listing';
})();
```

Atau pakai `navigator.sendBeacon`:
```javascript
navigator.sendBeacon(APP_URL_PLACEHOLDER + '/api/demo-embed/track', JSON.stringify({ref: document.referrer, type: 'listing'}));
```

**Why**: Catat siapa yang pakai embed widget. Pakai Image beacon atau sendBeacon supaya tidak blocking.

**Note**: Perlu replace `APP_URL_PLACEHOLDER` dengan `config('app.url')` di PHP seperti `DEMOS_JSON_PLACEHOLDER`.

---

### 6. Admin Controller: `DemoEmbedTrackingController`

**File baru**: `d:\wscrm\app\Http\Controllers\Admin\DemoEmbedTrackingController.php`

**What**: 
- `index(Request $request)`: Query `DemoEmbedTracking` dengan filter search (referer_url, referer_host) dan type. Paginate 20 per halaman. Urut `last_seen_at DESC`. Return `Inertia::render('Admin/DemoEmbedTrackings/Index', [...])`.
- `toggleBlock(DemoEmbedTracking $tracking)`: Toggle `is_blocked`, set `blocked_at = now()` jika block, `null` jika unblock.
- `destroy(DemoEmbedTracking $tracking)`: Hapus record.
- `bulkDestroy(Request $request)`: Hapus multi record by IDs.

**Why**: CRUD sederhana + block toggle, ikutin pattern `DemoCategoryController`.

---

### 7. Admin Vue Page

**File baru**: `d:\wscrm\resources\js\pages\Admin\DemoEmbedTrackings\Index.vue`

**What**: Halaman dengan:
- Stats cards di atas: Total domains, Total hits, Blocked count
- Filter: search (referer host/url), type dropdown (listing/api/oembed/single)
- Table: referer_url, referer_host, embed_type (badge), hits, first_seen, last_seen, is_blocked (badge merah/hijau)
- Actions per row: Block/Unblock toggle, Delete
- Bulk delete untuk multiple rows
- Checkbox multi-select

Ikutin pattern sederhana dari `DemoCategories/Index.vue`.

---

### 8. Routes & Sidebar

**File**: `d:\wscrm\routes\admin.php`

**What**: Tambah di bawah "Demo Website Management" section:
```php
Route::resource('demo-embed-trackings', DemoEmbedTrackingController::class)->only(['index', 'destroy']);
Route::patch('demo-embed-trackings/{demoEmbedTracking}/toggle-block', [DemoEmbedTrackingController::class, 'toggleBlock'])->name('demo-embed-trackings.toggle-block');
Route::delete('demo-embed-trackings-bulk', [DemoEmbedTrackingController::class, 'bulkDestroy'])->name('demo-embed-trackings.bulk-destroy');
```

**File**: `d:\wscrm\resources\js\components\AppSidebar.vue`

**What**: Tambah child di menu "Demo Website":
```js
{
    title: 'Tracking Embed',
    href: '/admin/demo-embed-trackings',
    icon: BarChart3,  // atau Eye
},
```

Import `BarChart3` dari `lucide-vue-next`.

---

### 9. JS route definitions (wayfinder)

**File baru**: `d:\wscrm\resources\js\routes\admin\demo-embed-trackings\index.ts`

**What**: Wayfinder route definitions untuk `/admin/demo-embed-trackings` mengikuti pattern dari `demo-categories/index.ts`.

---

## Verification

1. Jalankan `php artisan migrate` — pastikan tabel `demo_embed_trackings` terbuat
2. Buka `http://localhost:8000/demo-web` — cek tabel ada record baru type `api`
3. Akses `http://localhost:8000/api/demos` — cek tabel ada record
4. Embed widget di halaman lain — cek tabel ada record type `listing`
5. Buka `/admin/demo-embed-trackings` — lihat daftar tracking
6. Klik block pada salah satu domain — cek status berubah
7. Akses lagi dari domain yang di-block — harus return 403
