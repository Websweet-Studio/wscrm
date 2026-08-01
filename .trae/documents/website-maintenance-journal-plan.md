# Plan: Modul Website Klien & Jurnal Maintenance Harian

## Ringkasan

Membuat 2 modul baru:
1. **Website Klien** — entitas untuk mendata 6 website klien beserta info teknis WP.
2. **Jurnal Harian** — logbook per website per hari untuk mencatat aktivitas maintenance (update WP, tema, plugin, artikel, optimasi, dll). Dilengkapi laporan harian/mingguan/bulanan.

Jurnal **terpisah** dari Task (sesuai pilihan user). Format: **satu entry per website per hari**.

---

## Analisis Kondisi Saat Ini

| Aspek | Status |
|-------|--------|
| Framework | Laravel 13 + Inertia.js + Vue 3 + TypeScript |
| Task system | Sudah ada (model Task, TaskCategory, CRUD, calendar view, QC) |
| Customer | Sudah ada (model Customer, CRUD admin & area customer) |
| Order | Sudah ada (untuk transaksi hosting/domain) |
| Journal/Activity | **Belum ada** |
| Website client | **Belum ada** |
| Laporan | **Belum ada** |
| UI pattern | shadcn-vue components, Tailwind CSS, DataTable, dialog modal |

---

## Detail Perubahan

### 1. Modul Website Klien

#### Migration: `create_website_clients_table`

```php
// database/migrations/xxxx_xx_xx_xxxxxx_create_website_clients_table.php
Schema::create('website_clients', function (Blueprint $table) {
    $table->id();
    $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
    $table->string('name');                    // nama website / brand
    $table->string('url');                     // URL website
    $table->string('wp_version')->nullable();  // versi WordPress
    $table->string('theme_name')->nullable();  // nama tema aktif
    $table->string('theme_version')->nullable();
    $table->json('plugins')->nullable();       // [{name, version}] — daftar plugin
    $table->text('notes')->nullable();         // catatan umum
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    $table->softDeletes();
});
```

#### Model: `app/Models/WebsiteClient.php`

```php
class WebsiteClient extends Model {
    use SoftDeletes;
    
    protected $fillable = ['customer_id', 'name', 'url', 'wp_version', 'theme_name', 'theme_version', 'plugins', 'notes', 'is_active'];
    protected $casts = ['plugins' => 'array', 'is_active' => 'boolean'];
    
    public function customer(): BelongsTo { ... }
    public function journals(): HasMany { ... }
}
```

#### Controller: `app/Http/Controllers/Admin/WebsiteClientController.php`

CRUD standar mengikuti pola yang sudah ada (lihat `TaskController`, `BlogPostController`):
- `index()` — DataTable listing, filter by customer, is_active
- `store()`, `update()`, `destroy()`, `bulkDelete()`
- `show()` — detail website + list jurnal terkait

#### Routes: tambahkan ke `routes/admin.php`

```php
Route::resource('websites', WebsiteClientController::class);
Route::post('websites/bulk-delete', [WebsiteClientController::class, 'bulkDelete']);
```

#### Frontend

- `resources/js/pages/Admin/WebsiteClients/Index.vue` — DataTable CRUD (ikuti pola Task/Index.vue)
- `resources/js/pages/Admin/WebsiteClients/Show.vue` — detail + daftar jurnal website ini

---

### 2. Modul Jurnal Harian

#### Migration: `create_journal_entries_table`

```php
Schema::create('journal_entries', function (Blueprint $table) {
    $table->id();
    $table->foreignId('website_client_id')->constrained('website_clients')->cascadeOnDelete();
    $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
    $table->date('entry_date');                // tanggal aktivitas
    $table->json('activities');                // daftar aktivitas hari itu
    // activities structure:
    // [
    //   { type: 'wp_update', from_version: '6.5', to_version: '6.6', note: '...' },
    //   { type: 'plugin_update', plugin: 'Yoast SEO', from_version: '...', to_version: '...' },
    //   { type: 'theme_update', theme: 'Divi', from_version: '...', to_version: '...' },
    //   { type: 'article', title: 'Judul Artikel', url: '...', word_count: 800 },
    //   { type: 'page_optimization', page: 'Landing Page', detail: '...' },
    //   { type: 'other', description: '...' }
    // ]
    $table->text('summary')->nullable();       // ringkasan / catatan tambahan
    $table->timestamps();

    $table->unique(['website_client_id', 'entry_date']);  // satu entry per website per hari
});
```

#### Model: `app/Models/JournalEntry.php`

```php
class JournalEntry extends Model {
    protected $fillable = ['website_client_id', 'user_id', 'entry_date', 'activities', 'summary'];
    protected $casts = ['activities' => 'array', 'entry_date' => 'date'];
    
    public function websiteClient(): BelongsTo { ... }
    public function user(): BelongsTo { ... }
}
```

#### Controller: `app/Http/Controllers/Admin/JournalEntryController.php`

- `index()` — DataTable, filter by website_client_id, date range, user_id
- `store()` — buat/update entry harian (upsert by date + website)
- `update()` — edit entry
- `destroy()` — hapus entry
- `report()` — generate laporan (harian/mingguan/bulanan)
- `export()` — export CSV/PDF

#### Service: `app/Services/JournalReportService.php`

Logic untuk generate data laporan:
- **Harian**: list entry per website untuk tanggal tertentu
- **Mingguan**: aggregasi aktivitas per website dalam 7 hari
- **Bulanan**: aggregasi per website dalam 1 bulan
- Statistik: total artikel, total update WP, total plugin diupdate, dll.

#### Routes: tambahkan ke `routes/admin.php`

```php
Route::resource('journals', JournalEntryController::class)->except(['show']);
Route::get('journals/report', [JournalEntryController::class, 'report']);
Route::get('journals/export', [JournalEntryController::class, 'export']);
```

#### Frontend

- `resources/js/pages/Admin/Journals/Index.vue` — DataTable entry jurnal + filter
- `resources/js/pages/Admin/Journals/CreateEdit.vue` — form entry jurnal (dynamic activities input)
- `resources/js/pages/Admin/Journals/Report.vue` — halaman laporan

---

### 3. Desain Form Entry Jurnal Harian

Form satu entry per website per hari dengan dynamic activities:

```
Pilih Website:  [Dropdown 6 website]
Tanggal:        [Date picker, default hari ini]

--- Aktivitas ---
[+] Tambah Aktivitas

Aktivitas 1:
  Tipe: [Dropdown: WP Update | Plugin Update | Theme Update | Artikel | Optimasi Halaman | Lainnya]
  
  Jika WP Update:
    Versi Sebelum: [text]  Versi Sesudah: [text]  Catatan: [text]
  
  Jika Plugin Update:
    Nama Plugin: [text]   Versi Sebelum: [text]  Versi Sesudah: [text]
  
  Jika Theme Update:
    Nama Theme: [text]    Versi Sebelum: [text]  Versi Sesudah: [text]
  
  Jika Artikel:
    Judul: [text]  URL: [text]  Jumlah Kata: [number]
  
  Jika Optimasi Halaman:
    Nama Halaman: [text]  Detail: [textarea]
  
  Jika Lainnya:
    Deskripsi: [textarea]

Ringkasan: [textarea opsional]
```

### 4. Laporan (Reports)

Halaman `/admin/journals/report`:
- Tiga tab: Harian / Mingguan / Bulanan
- Filter: rentang tanggal, website
- Tabel aggregasi per website
- Statistik total per tipe aktivitas
- Tombol export (CSV)

Statistik yang ditampilkan di laporan:
- Jumlah update WP
- Jumlah plugin diupdate
- Jumlah tema diupdate
- Jumlah artikel dibuat
- Jumlah halaman dioptimasi
- Total aktivitas per website

### 5. Sidebar Navigasi

Tambahkan ke komponen sidebar (`resources/js/components/layout/AdminSidebar.vue` atau sejenisnya):
- Menu "Website" (ikon Globe) → `/admin/websites`
- Menu "Jurnal" (ikon BookOpen) → `/admin/journals`
- Sub-menu "Laporan" (ikon BarChart) → `/admin/journals/report`

---

## Jenis Aktivitas (Enum)

```php
// app/Enums/ActivityType.php
enum ActivityType: string {
    case WP_UPDATE = 'wp_update';
    case PLUGIN_UPDATE = 'plugin_update';
    case THEME_UPDATE = 'theme_update';
    case ARTICLE = 'article';
    case PAGE_OPTIMIZATION = 'page_optimization';
    case OTHER = 'other';
}
```

---

## Struktur File yang Dibuat/Diubah

### File Baru

| File | Keterangan |
|------|------------|
| `app/Models/WebsiteClient.php` | Model Website Klien |
| `app/Models/JournalEntry.php` | Model Jurnal |
| `app/Enums/ActivityType.php` | Enum tipe aktivitas |
| `app/Http/Controllers/Admin/WebsiteClientController.php` | CRUD Website Klien |
| `app/Http/Controllers/Admin/JournalEntryController.php` | CRUD & Report Jurnal |
| `app/Services/JournalReportService.php` | Service laporan |
| `app/Http/Requests/WebsiteClientRequest.php` | Form request validation |
| `app/Http/Requests/JournalEntryRequest.php` | Form request validation |
| `database/migrations/xxxx_create_website_clients_table.php` | Migration |
| `database/migrations/xxxx_create_journal_entries_table.php` | Migration |
| `resources/js/pages/Admin/WebsiteClients/Index.vue` | Halaman daftar website |
| `resources/js/pages/Admin/WebsiteClients/Show.vue` | Halaman detail website |
| `resources/js/pages/Admin/Journals/Index.vue` | Halaman daftar jurnal |
| `resources/js/pages/Admin/Journals/CreateEdit.vue` | Form entry jurnal |
| `resources/js/pages/Admin/Journals/Report.vue` | Halaman laporan |

### File Diubah

| File | Perubahan |
|------|-----------|
| `routes/admin.php` | Tambah route website + journal |
| `resources/js/components/layout/AdminSidebar.vue` (atau sejenisnya) | Tambah menu sidebar |

---

## Asumsi & Keputusan

1. Website klien tidak terkait Order (independen). Bisa di-link ke Customer jika klien terdaftar.
2. Satu website hanya boleh satu entry jurnal per hari (`unique` constraint pada `website_client_id + entry_date`).
3. Jurnal terpisah dari Task — tidak ada relasi ke Task.
4. Aktivitas disimpan sebagai JSON di field `activities`, bukan sebagai tabel terpisah. Ini menyederhanakan query dan form.
5. Laporan tidak disimpan di DB (real-time generate dari data jurnal).
6. Untuk artikel 3 hari sekali: ini jadi reminder / panduan pengguna. Bisa ditambahkan alert di dashboard nanti.

---

## Verifikasi

1. Jalankan `php artisan migrate` — pastikan tabel baru terbuat.
2. Buat 6 website klien via `/admin/websites`.
3. Buat beberapa entry jurnal via `/admin/journals` untuk tanggal berbeda.
4. Cek constraint unik: entry kedua untuk website+tanggal sama harus ditolak.
5. Buka `/admin/journals/report` — verifikasi data aggregasi benar.
6. Test export CSV — file terdownload dengan format benar.

---

## Urutan Pengerjaan

1. Buat migration + model WebsiteClient
2. Buat controller + request + route WebsiteClient  
3. Buat frontend WebsiteClient (Index + Show)
4. Buat migration + model JournalEntry + ActivityType enum
5. Buat controller + request + route JournalEntry (CRUD)
6. Buat frontend JournalEntry (Index + CreateEdit)
7. Buat JournalReportService
8. Buat frontend Report
9. Tambah sidebar navigation
10. Test end-to-end
