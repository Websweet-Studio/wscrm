---
name: 'build-production'
description: 'Build aplikasi Laravel menjadi package zip siap deploy (optimized: no-dev, minified assets). Aktifkan saat user ingin build package deployment, menjalankan composer run build:production, atau menanyakan cara build untuk deploy.'
---

# Build Production Package

Membangun aplikasi Laravel menjadi package zip siap extract & install di hosting.

## Prasyarat

- `vendor/` sudah ada (pernah menjalankan `composer install`)
- `node_modules/` sudah ada (pernah menjalankan `npm install`)

## Alur Build

```
composer run build:production
```

Yang dijalankan secara berurutan:

### 1. Safety Checks

- Validasi berada di root project (file `artisan` & `composer.json`)
- Peringatan git uncommitted changes
- Konfirmasi lanjut (skip dengan `--no-interaction` / `--force`)

### 2. Validasi Dependencies

- Cek `vendor/autoload.php` — gagal jika tidak ada
- Cek `node_modules/` — gagal jika tidak ada

### 3. Optimasi Production Dependencies

```
composer install --no-dev --optimize-autoloader --no-interaction
```

- Menghapus dev dependencies (PHPUnit, Larastan, Mockery, dll)
- Optimasi autoloader dengan `--optimize-autoloader`
- Ukuran package turun drastis (contoh: 36MB → 11MB)

### 4. Build Frontend Assets

```
npm run build  # = vite build
```

- Build Vue + Tailwind + Inertia assets
- Output ke `public/build/`

### 5. Copy & Struktur Package

- Copy file aplikasi ke direktori temp
- Flatten `public/` ke root (struktur flat untuk hosting)
- Pindahkan file backend ke folder `warungmember/`
- Buat `warungmember/public/index.php` & `.htaccess`

### 6. Buat Installer

- Copy folder `public/install/` untuk panduan instalasi
- Set `.env.example` ke mode production
- Buat `README.txt`

### 7. Zip Package

- Buat ZIP di `dist/warungmember.{version}.zip`
- Path entry menggunakan forward slash (kompatibel cPanel/DirectAdmin)
- Auto-close & reopen ZIP setiap 1000 file (hindari memory leak)

## Output

```
dist/warungmember.{version}.zip
```

Struktur dalam ZIP:

```
/
├── index.php
├── .env.example
├── warungmember/         # Backend Laravel
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   ├── vendor/
│   ├── public/
│   │   ├── index.php
│   │   └── .htaccess
│   └── artisan
├── build/                # Frontend assets (Vite)
├── install/              # Web installer
└── README.txt
```

## Command Options

```bash
# Default (dengan versi dari composer.json)
php artisan build:package

# Output custom
php artisan build:package --output=dist/warungmember-v2.0.0.zip

# Skip konfirmasi (headless/CI)
php artisan build:package --no-interaction

# Skip konfirmasi (force)
php artisan build:package --force
```

## Cara Install Package

1. Extract ZIP ke folder `public_html` atau domain folder
2. Buka `https://domain.com/install/`
3. Ikuti panduan instalasi (Step 1-2)
4. Klik "Install" — migrasi + optimize + cleanup otomatis

Atau jika sudah ada instalasi sebelumnya (update):

1. Extract package update ke `public_html` sampai muncul folder `warungmember`
2. Buka `https://domain.com/install/`
3. Klik "Update aplikasi" — backup otomatis dibuat
