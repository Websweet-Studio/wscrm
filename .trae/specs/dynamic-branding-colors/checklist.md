# Checklist

- [x] CSS variables `--primary`, `--secondary`, `--accent` dan turunannya di-generate dinamis dari branding_settings
- [x] Foreground color kontras otomatis berdasarkan luminance warna background
- [x] Warna branding tetap apply di dark mode
- [x] Fallback ke hardcoded colors jika branding_settings kosong/tidak tersedia
- [x] Perubahan warna branding langsung terlihat di seluruh UI tanpa perlu rebuild CSS
- [x] Tidak ada error/warning di browser console terkait CSS variables
- [x] Semua hardcoded `#c96442` di Public pages (Hosting, Domains, Demos) diganti `var(--primary)`
- [x] Semua hardcoded `#c96442` di Customer pages (CustomerWelcome, Auth) diganti `var(--primary)`
- [x] Semua hardcoded `#c96442` di Admin pages (Tasks, TaskCategories, AuthCardLayout) diganti
- [x] Embed Blade templates (embed-single, embed-listing) — CSS variables + `:root` fallback
