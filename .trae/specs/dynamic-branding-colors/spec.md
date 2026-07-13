# Dynamic Branding Colors Spec

## Why
Warna aksen UI (`--primary`, `--secondary`, `--accent`) saat ini hardcoded di `app.css` (warna Claude). Branding settings di `/admin/branding` sudah menyimpan `primary_color`, `secondary_color`, `accent_color` tapi tidak dipakai untuk CSS variables, sehingga perubahan warna di halaman branding tidak berdampak ke seluruh UI.

## What Changes
- Generate CSS custom properties dinamis dari branding settings di `app.blade.php`
- Hitung foreground color (teks) otomatis berdasarkan luminance warna background
- Update `app.css` — referensi warna pindah ke variable custom yang bisa di-override
- Update `HandleInertiaRequests.php` — muat branding settings sekali saja, tidak duplikat dengan `AppServiceProvider`
- Hapus duplikasi query branding settings di `AppServiceProvider` (Inertia share + Blade share redundant dengan HandleInertiaRequests)

## Impact
- Affected specs: branding, UI theming
- Affected code: `resources/views/app.blade.php`, `resources/css/app.css`, `app/Providers/AppServiceProvider.php`, `app/Http/Middleware/HandleInertiaRequests.php`

## ADDED Requirements
### Requirement: Dynamic CSS Variables from Branding
The system SHALL inject CSS custom properties from branding_settings into every page load.

#### Scenario: Branding colors applied globally
- **WHEN** admin updates `primary_color` to `#ff0000` via `/admin/branding`
- **THEN** all UI elements using `--primary` CSS variable (buttons, badges, links, sidebar) show red

#### Scenario: Contrast text color computed
- **WHEN** `primary_color` is set
- **THEN** the system SHALL compute `--primary-foreground` as white (`#faf9f5`) if luminance < 0.5, or dark (`#141413`) if luminance ≥ 0.5

#### Scenario: Dark mode support
- **WHEN** dark mode is active
- **THEN** branding colors tetap dipakai untuk `--primary`, `--secondary`, `--accent` (dan turunannya)
- **AND** background/dark-mode specific colors tetap dari `app.css`

## MODIFIED Requirements
### Requirement: Branding settings query
**Before**: `AppServiceProvider` dan `HandleInertiaRequests` masing-masing query branding_settings.
**After**: Hanya `HandleInertiaRequests` yang query. `AppServiceProvider` cukup share via Blade view sekali.

## REMOVED Requirements
None.
