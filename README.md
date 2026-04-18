# WSCRM - Web Service CRM

Aplikasi manajemen pelanggan dan layanan domain & hosting berbasis Laravel + Inertia + Vue.

## Teknologi

- Backend: Laravel 12.x
- Frontend: Vue 3 + Inertia.js
- UI: Tailwind CSS
- Database: MySQL / SQLite
- Testing: Pest PHP
- MCP Server: Laravel Boost (via .mcp.json)

## Catatan Penting untuk AI Assistant

- **MCP Server**: Digunakan Laravel Boost MCP server untuk integrasi Laravel
- **419 Page Expired**: Setelah login, selalu jalankan `session()->regenerate()` dan `session()->regenerateToken()` untuk menghindari error CSRF
- **Build System**: Lihat `BUILD.md` untuk dokumentasi build dan deployment production

## Instalasi

### Prasyarat

- PHP 8.2+
- Composer
- Node.js & npm / Bun
- Database (MySQL atau SQLite)

### Langkah Instalasi

1. Clone repositori

```bash
git clone <repo-url>
cd wscrm
```

2. Instal dependensi PHP

```bash
composer install
```

3. Instal dependensi Node.js

```bash
npm install
# atau
bun install
```

4. Salin file environment

```bash
cp .env.example .env
```

5. Generate aplikasi key

```bash
php artisan key:generate
```

6. Konfigurasi database di `.env` file

7. Jalankan migrasi & seeder

```bash
php artisan migrate --seed
```

8. Build assets

```bash
npm run build
# atau
bun run build
```

## Pengembangan

Jalankan server pengembangan:

```bash
# Jalankan semua layanan sekaligus
composer dev

# Atau secara terpisah
php artisan serve
npm run dev
```

## Build & Deployment

Lihat **BUILD.md** untuk dokumentasi lengkap tentang:

- Production build structure (laravel + public_html)
- Package-style deployment dengan installer
- Auto-update system

## Fitur

- Manajemen Pelanggan
- Pencarian & Pendaftaran Domain
- Manajemen Paket Hosting
- Penagihan & Invoice
- Dasbor Admin
- Dasbor Pelanggan

## Lisensi

MIT License
