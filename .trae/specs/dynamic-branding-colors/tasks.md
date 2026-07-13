# Tasks

- [x] Task 1: Inject dynamic CSS variables from branding settings in Blade layout
  - Buat helper PHP untuk hitung luminance dan tentukan foreground color (gelap/terang)
  - Inject CSS variable map via JS `document.documentElement.style.setProperty()` di `app.blade.php`
  - Fallback ke hardcoded values jika branding_settings tidak tersedia

- [x] Task 2: Refactor CSS to use override-friendly variables
  - Tidak perlu perubahan — `app.css` sudah pakai CSS variables yang di-refer oleh Tailwind theme

- [x] Task 3: Clean up duplicate branding queries
  - Tidak perlu perubahan — Blade view share dan Inertia share beda fungsi

- [x] Task 4: Add CSS variable override for dark mode
  - Inline style via JS punya prioritas CSS tertinggi, tetap aktif saat `.dark` class di-toggle
  - Background/card/muted/dark-specific colors tetap dari `app.css`

- [x] Task 5: Replace hardcoded `#c96442` with `var(--primary)` di semua Vue components
  - Public pages: Hosting/Index.vue, Domains/Index.vue, Demos/Index.vue
  - Customer pages: CustomerWelcome.vue, Auth/Login.vue, Auth/Register.vue
  - Admin pages: Tasks/Index.vue, TaskCategories/Index.vue, AuthCardLayout.vue
  - Embed Blade templates: embed-single.blade.php, embed-listing.blade.php
  - Fallback values (`|| '#c96442'`) dipertahankan untuk JS default
  - Color palette array di Demos page dipertahankan sebagai preset opsi warna

# Task Dependencies
- Task 1 → Task 2 (inject dulu, CSS menyesuaikan)
- Task 3 independent dari Task 1 & 2
- Task 4 bagian dari Task 1 (di-handle di Blade)
- Task 5 setelah Task 1 selesai (CSS variables harus ready dulu)
