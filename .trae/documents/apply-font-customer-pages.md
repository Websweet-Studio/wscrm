# Plan: Terapkan Font Heading Space Grotesk ke Semua Halaman Customer

## Ringkasan

Global font sudah di-set (Inter untuk body/sans, Space Grotesk untuk heading). Namun hanya Dashboard.vue yang sudah menggunakan class `font-heading`. Semua halaman customer lain masih pakai `font-serif` (Georgia) untuk heading. Plan ini mengganti semua heading di halaman customer dengan `font-heading` (Space Grotesk).

## Current State

- `--font-sans: 'Inter', ...` dan `--font-heading: 'Space Grotesk', ...` sudah di [app.css](file:///d:/dev/wscrm/resources/css/app.css)
- Google Fonts link sudah di [app.blade.php](file:///d:/dev/wscrm/resources/views/app.blade.php)
- Hanya [Dashboard.vue](file:///d:/dev/wscrm/resources/js/pages/Customer/Dashboard.vue) yang sudah pakai `font-heading`
- Semua halaman lain masih pakai `font-serif` untuk page title / heading utama di hero card
- Auth pages (Login, Register) pakai inline `font-family: Georgia, serif` untuk heading

## Perubahan yang Akan Dilakukan

### 1. [Orders/Index.vue](file:///d:/dev/wscrm/resources/js/pages/Orders/Index.vue)
- **Line 198**: `font-serif` → `font-heading` di `<h1>Pesanan Saya</h1>`

### 2. [Orders/Show.vue](file:///d:/dev/wscrm/resources/js/pages/Orders/Show.vue)
- **Line 130**: `font-serif` → `font-heading` di `<h1>Order #{{ order.id }}</h1>`

### 3. [Customer/Invoices/Index.vue](file:///d:/dev/wscrm/resources/js/pages/Customer/Invoices/Index.vue)
- **Line 100**: `font-serif` → `font-heading` di `<h1>Invoice Saya</h1>`

### 4. [Customer/Invoices/Show.vue](file:///d:/dev/wscrm/resources/js/pages/Customer/Invoices/Show.vue)
- **Line 106**: `font-serif` → `font-heading` di `<h1>Invoice ...</h1>`

### 5. [Customer/Invoices/Payment.vue](file:///d:/dev/wscrm/resources/js/pages/Customer/Invoices/Payment.vue)
- **Line 159**: `font-serif` → `font-heading` di `<h1>Invoice ...</h1>`

### 6. [Customer/Settings/Index.vue](file:///d:/dev/wscrm/resources/js/pages/Customer/Settings/Index.vue)
- **Line 73**: `font-serif` → `font-heading` di `<h1>Settings</h1>`
- **Line 95**: `font-serif` → `font-heading` di `<CardTitle>Informasi Profil</CardTitle>`
- **Line 202**: `font-serif` → `font-heading` di `<CardTitle>Ubah Password</CardTitle>`

### 7. [Customer/Auth/Login.vue](file:///d:/dev/wscrm/resources/js/pages/Customer/Auth/Login.vue)
- **Line 35**: Hapus `font-family: Georgia, serif`, tambah `font-heading` → `<h1 class="font-heading ...">Selamat datang kembali</h1>`

### 8. [Customer/Auth/Register.vue](file:///d:/dev/wscrm/resources/js/pages/Customer/Auth/Register.vue)
- **Line 34**: Hapus `font-family: Georgia, serif`, tambah `font-heading` → `<h2 class="font-heading ...">Buat Akun</h2>`

### 9. Font di app.css — tidak ada perubahan
`font-sans` (Inter) dan `font-heading` (Space Grotesk) sudah terdefinisi dan siap pakai.

## Asumsi & Keputusan

- Hanya heading (h1, h2, CardTitle) yang diganti — bukan price/total display yang pakai `font-serif`, karena itu bagian dari data presentation style, bukan heading
- `font-serif` di Badge status dan elemen non-heading tetap tidak disentuh
- Auth pages (Login, Register) juga ikut diubah karena termasuk customer pages

## Verifikasi

1. `npm run build` — pastikan tidak ada error kompilasi
2. Buka setiap halaman customer untuk verifikasi visual:
   - `/customer/orders`
   - `/customer/orders/{id}`
   - `/customer/invoices`
   - `/customer/invoices/{id}`
   - `/customer/invoices/{id}/payment`
   - `/customer/settings`
   - `/customer/login`
   - `/customer/register`
