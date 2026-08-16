# Plan: Improvement Customer Dashboard (`/customer/dashboard`)

## Ringkasan

Audit halaman `resources/js/pages/Customer/Dashboard.vue` + `app/Http/Controllers/Customer/DashboardController.php`. Halaman sudah fungsional (hero, 3 stat card, metode pembayaran, tagihan), tapi banyak data sudah diambil backend tapi **tidak ditampilkan**, dan ada fitur existing (AI kredit, maintenance jurnal, perpanjangan layanan) yang belum disinggung di dashboard.

## Audit Kondisi Saat Ini

| Item | Status |
|---|---|
| `recentOrders` prop dikirim controller | **Tidak dipakai** di template (dead data) |
| `services` prop (orders limit 5) | Hanya dipakai untuk hitung jumlah/aktif, detail tidak ditampilkan |
| AI kredit (`AiCredit.balance`) | Tidak muncul di dashboard |
| Jurnal maintenance (`JournalEntry`) | Tidak muncul; fitur ada di `/customer/maintenance` |
| Notifikasi customer | Tabel `notifications` ada, tapi controller cuma utk admin (`Admin\NotificationController`) |
| `order_items.expires_at` / `orders.expires_at` | Ada kolom + helper Order (`isExpired`, `daysUntilExpiry`, `scopeExpiringSoon`) tapi tidak dipakai dashboard |
| Warna | Banyak hex hardcoded (`#fbfbf9`, `#f6f6f3`, `#62625b`) — tidak ikut tema branding dinamis |
| Stat "Tagihan Belum Bayar" | Hanya jumlah, tidak ada total nominal / keterlambatan |
| Bahasa | Campur Indonesia/Inggris ("Customer Dashboard", "Layanan", "Bayar Tagihan") |
| Logout | Tombol "Logout" di hero — posisi kurang ergonomis, duplikasi dengan menu pengaturan |

## Daftar Improvement (urut prioritas)

### P1 — Quick win (perbaikan cepat, risiko rendah)

1. **Hapus / pakai `recentOrders`**
   - Masalah: data terkirim tapi tak dirender.
   - Solusi A (minimal): hapus dari controller + prop.
   - Solusi B (lebih baik): tampilkan kartu "Pesanan Terbaru" (5 item: nomor, total, status, tanggal) + link ke `/customer/orders`.

2. **Total nominal tagihan + keterlambatan**
   - Masalah: stat card cuma angka.
   - Solusi: tambah `unpaidTotal` di controller (sum amount unpaid); tampilkan di stat card. Warna badge tagihan jadi merah bila `due_date` lewat (overdue).

3. **Empty states**
   - Masalah: tidak ada empty state untuk pesanan/layanan.
   - Solusi: teks "Belum ada pesanan" + CTA link ke halaman layanan/hosting.

4. **Konsistensi bahasa**
   - Seragamkan ke Indonesia: "Dashboard Pelanggan", "Metode Pembayaran", "Pesanan Terbaru", dst. Ganti `Head title` ke "Dashboard".

5. **Konsistensi tema**
   - Ganti hex hardcoded dengan token tema (`bg-background`, `border-border`, `text-muted-foreground`) atau CSS variables. Ikuti pola halaman lain (mis. `Customer/Ai/Index.vue` yang sudah pakai token).

### P2 — Manfaatkan fitur existing

6. **Saldo kredit AI di dashboard**
   - Tambah stat card "Saldo Token AI" (`AiCredit::currentBalance($customer->id)`) + link shortcut ke `/customer/ai` dan `/customer/ai/packages`. Nudging pembelian.

7. **Layanan & perpanjangan (renewal reminder)**
   - Tampilkan daftar layanan aktif dengan `expires_at` (helper `Order::daysUntilExpiry` sudah ada).
   - Peringatan banner: "Layanan X berakhir dalam N hari" untuk `< 30 hari` (pakai `scopeExpiringSoon`), dan badge "Kadaluarsa" untuk yang lewat (`isExpired`).

8. **Ringkasan maintenance / jurnal terbaru**
   - Kartu "Aktivitas Terakhir" (3 jurnal terakhir dari `journal_entries` customer) + link ke `/customer/maintenance`.

9. **Notifikasi customer**
   - Implementasi notifikasi untuk guard `customer` (tabel `notifications` siap): pemberitahuan invoice baru, layanan hampir habis, kredit menipis. Tampilkan di dashboard (atau reuse `NotificationBell`).
   - Ini item terbesar di P2 — bisa dipisah jadi fokus tersendiri.

### P3 — Penyetelan UX & teknis

10. **Tombol logout dipindah**
    - Pindahkan ke area pengaturan/avatar (pola `NavUser`), kurangi clutter hero. Simpan CTA utama: Bayar Tagihan.

11. **Urgensi due date**
    - Tampilkan relatif: "Jatuh tempo hari ini", "Terlambat 3 hari" — bukan hanya tanggal.

12. **Caching query dashboard**
    - Counts (tagihan belum bayar, layanan aktif) jarang berubah; cache ringkas (`Cache::remember`) atau dedup query di controller. Opsional, hanya bila halaman terasa lambat.

13. **Data yang sudah disiapkan backend tapi belum dipakai** — rapikan:
    - `recentOrders` dipakai atau dihapus (no. 1).
    - `services` hanya untuk count — bila dipakai no. 7, pertahankan.

## Rekomendasi Prioritas Eksekusi

1. Batch P1 (1–5): sekali sentuh dashboard, langsung terlihat hasil.
2. P2 no. 6 + 7: nilai bisnis tinggi (monetisasi AI + mencegah churn layanan).
3. P2 no. 8, lalu no. 9 (notifikasi = proyek terpisah bila perlu).
4. P3 dijalankan sambil menyentuh file yang sama.

## File yang Terlibat

- `resources/js/pages/Customer/Dashboard.vue`
- `app/Http/Controllers/Customer/DashboardController.php`
- (no. 9) `app/Http/Controllers/Customer/*`, `resources/js/components/NotificationBell.vue` atau komponen baru
- (no. 7) `app/Models/Order.php` (helper sudah ada, tinggal pakai)
