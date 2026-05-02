# Panduan Deploy (Baru & Update)

Dokumen ini mengikuti struktur build produksi yang menghasilkan 2 folder:

- `wscrm/` (source Laravel, vendor, storage, bootstrap, dll) — taruh di luar web root
- `public_html/` (web root) — taruh sebagai document root domain

Artefak build ada di `dist/production.zip` (dibuat dari lokal).

## 1) Build Release (di lokal)

1. Pastikan dependency frontend sudah terinstall:
   - `npm ci` (atau `npm install`)
2. Jalankan build produksi:
   - `composer run build:production`
3. Ambil file:
   - `dist/production.zip`

Catatan:
- Build ini tidak memasukkan `.env` secara default (lebih aman). `.env` dibuat/diatur di server.

## 2) Deploy Baru (Shared Hosting / cPanel / public_html)

### A. Upload & Extract

1. Upload `dist/production.zip` ke server (disarankan ke folder non-public, mis. `~/releases/<versi>/`).
2. Extract zip. Hasilnya ada 2 folder: `wscrm/` dan `public_html/`.

### B. Pasang Struktur Folder

1. Pastikan folder `wscrm/` berada di luar document root.
2. Pindahkan isi `public_html/` hasil extract ke document root domain (biasanya folder `public_html` milik hosting).

Struktur akhir (contoh):

- `~/public_html/` (web root)
  - `index.php`
  - `.htaccess`
  - `build/`
- `~/wscrm/` (di luar web root)
  - `artisan`
  - `vendor/`
  - `bootstrap/`
  - `storage/`

### C. Buat `.env` di Server

Buat file `~/wscrm/.env` (jangan taruh di web root). Minimal pastikan:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://domainkamu.tld`
- Konfigurasi database (`DB_*`)
- Konfigurasi mail / queue sesuai kebutuhan

Lalu generate app key:

- `cd ~/wscrm`
- `php artisan key:generate --force`

### D. Permission

Pastikan write permission untuk:

- `~/wscrm/storage`
- `~/wscrm/bootstrap/cache`

Pada Linux umumnya:

- `chmod -R 775 storage bootstrap/cache`

### E. Link Upload (`/storage`)

Agar file upload bisa diakses dari web, buat link dari web root ke storage:

- Target: `~/wscrm/storage/app/public`
- Link di web root: `~/public_html/storage`

Contoh (Linux):

- `cd ~/public_html`
- `ln -s ../wscrm/storage/app/public storage`

Jika hosting tidak mengizinkan symlink, gunakan fitur “Symlink” di file manager (jika ada), atau fallback terakhir dengan copy (kurang direkomendasikan).

### F. Migrasi & Cache (setelah `.env` benar)

- `cd ~/wscrm`
- `php artisan migrate --force`
- `php artisan optimize`

## 3) Update Deploy (tanpa reset data)

Tujuan update: ganti kode & asset, tapi tetap mempertahankan `.env` dan `storage` (termasuk upload).

### A. Persiapan

1. Backup database.
2. Backup folder:
   - `~/wscrm/storage/app` (minimal `storage/app/public`)
   - `~/wscrm/.env`

### B. Maintenance Mode (opsional tapi disarankan)

- `cd ~/wscrm`
- `php artisan down`

### C. Replace Kode & Asset

1. Upload `production.zip` versi baru ke folder sementara, extract.
2. Replace:
   - Update `public_html/` (terutama `build/` dan `index.php`).
   - Update `wscrm/` (kode aplikasi).
3. Pastikan tetap mempertahankan:
   - `~/wscrm/.env` (jangan tertimpa)
   - `~/wscrm/storage/` (jangan tertimpa)
4. Pastikan `~/public_html/storage` masih mengarah ke `../wscrm/storage/app/public`.

### D. Post-update

- `cd ~/wscrm`
- `php artisan migrate --force`
- `php artisan optimize`
- `php artisan up`

Jika pakai queue worker:

- restart worker (Supervisor / systemd / panel hosting) atau jalankan ulang `php artisan queue:work`.

## 4) Troubleshooting Cepat

- **CSS/JS tidak update / 404 build assets**
  - pastikan `public_html/build/` ada dan ter-upload
  - pastikan cache dibersihkan: `php artisan optimize`
- **500 / blank page**
  - cek permission `storage` dan `bootstrap/cache`
  - cek nilai `APP_URL` dan koneksi `DB_*` di `.env`
- **Upload tidak bisa diakses (404 /storage/...)**
  - pastikan link `public_html/storage -> ../wscrm/storage/app/public` benar
