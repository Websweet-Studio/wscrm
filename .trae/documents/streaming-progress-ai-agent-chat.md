# Plan: Streaming Progress Real-time Chat AI Agent

## Ringkasan

Saat ini workflow AI (mis. buat artikel) dijalankan sinkron dalam satu request HTTP — pengguna menunggu loading lama tanpa laporan bertahap, baru semua hasil muncul sekaligus. Tujuan: alur interaktif real-time di chat, tiap tahap melapor ke chat saat berjalan (generate judul → lapor, generate konten → lapor, cari gambar → lapor, featured image → lapor, pilih kategori → lapor, audit → lapor, publish → lapor).

Solusi: **SSE streaming via fetch-streaming (POST)**. Backend mengirim event bertahap (`StreamedResponse`), frontend memparse event dan menampilkan timeline progress live di bubble chat. Pilihan ini paling sederhana untuk Laravel dev server, tanpa job queue.

## Analisis Kondisi Saat Ini

- `AiAgentController@chat` (POST `/admin/websites/ai/chat`) — validasi, simpan pesan user, panggil `$agent->process()`, simpan pesan agent, return JSON. Sinkron; response hanya dikirim setelah seluruh workflow selesai.
- `AiAgentService@process` — callAI (intent) lalu `executeActions()` sinkron; hasil aksi di-append ke pesan (`[OK]`/`[GAGAL]`).
- `ArticleAgent@createArticle` — alur sekaligus: generate konten → embed 2 gambar → audit → revisi → publish. Build array `$logs` tapi hanya terlihat setelah selesai.
- `WebsiteAgent`, `OrderAgent` — tidak ada laporan bertahap.
- Frontend `AiAgent.vue` — `sendMessage()` fetch biasa + `res.json()`, tampil setelah selesai. Interface `Message` punya `content`, `actions`.
- `AiClient@chat` — synchronous, `max_tokens` besar (8000) utk konten → butuh puluhan detik (ini sumber "proses lama").

## Perubahan yang Diusulkan

### 1. `app/Services/AiAgentService.php`
- Ubah signature: `process(string $userMessage, ?callable $onEvent = null): array`.
- Callback format: `function (string $message, string $status = 'done', string $agent = '')` dengan `$status` ∈ `loading|done`.
- Emit event awal ("Menganalisis permintaan..."), teruskan `$onEvent` ke `executeActions` → ke tiap sub-agent.
- `executeActions(array $aiResponse, ?callable $onEvent)`: teruskan ke `websiteAgent` / `articleAgent` / `orderAgent`.
- Tidak mengubah return (`ai_response` + `actions`) — tetap kompatibel dengan response lama.

### 2. `app/Services/AiAgents/ArticleAgent.php` — alur bertahap + featured image + kategori
- Signature: `createArticle(?int $websiteId, string $title, string $content, string $keyword = '', ?callable $onEvent = null): array`.
- Helper `emit($onEvent, $msg, $status, $agent)` — tambah ke `$logs` + panggil callback bila ada.
- Alur baru:
  1. **SEO Writer — judul**: `generateArticleTitle()` (call AI kecil, `max_tokens` 300): minta 1 judul 30-65 karakter, keyword-aware → emit "Judul artikel: '...'".
  2. **SEO Writer — konten**: `generateArticleDraft()` pakai judul final, `max_tokens` 8000, retry 1x (logika retry sudah ada) → emit "Konten selesai di-generate (N kata)".
  3. **Media Agent — gambar inline**: embed 2 gambar picsum → emit "2 gambar disisipkan".
  4. **Media Agent — featured image**: upload 1 gambar picsum (seed berbeda), ambil **media id** → emit "Featured image dibuat".
  5. **SEO Writer/Publisher — kategori**: cari kategori slug `artikel`; fallback kategori pertama non-`uncategorized`; simpan `$categoryId` → emit "Kategori dipilih: <nama>".
  6. **Content Auditor — audit**: `auditArticleContent()` → emit "Audit: skor N/100 (LOLOS/BELUM LOLOS)".
  7. **Revisi** (bila gagal): revisi 1x (pakai logika valid-panjang sudah ada), audit ulang → emit hasil revisi.
  8. **Publisher — publish**: payload + `categories: [$categoryId]` + `featured_media: $featuredMediaId` → emit "Artikel dipublikasikan / disimpan draft".
- `uploadImage()` ubah return jadi array media lengkap (`['id'=>..., 'source_url'=>...]`) atau tambah `uploadMedia()`; inline pakai `source_url`, featured pakai `id`. Sesuaikan `embedImages()`.
- `$logs` tetap di-return untuk card render (konsisten dengan UI sekarang).

### 3. `app/Services/AiAgents/WebsiteAgent.php` & `OrderAgent.php`
- Tambah param optional `?callable $onEvent = null` di tiap method publik; emit 1-2 event progress ("Mengecek update website...", "Memperpanjang order...", dst). Backward compatible (param optional).

### 4. `app/Http/Controllers/Admin/AiAgentController.php`
- Tambah `streamChat(Request, AiAgentService)`:
  - Validasi sama dengan `chat()`.
  - Simpan pesan user ke DB (seperti sekarang).
  - `return response()->stream(function () use (...) { ... })` headers: `Content-Type: text/event-stream`, `Cache-Control: no-cache`, `X-Accel-Buffering: no`, `Connection: keep-alive`.
  - `set_time_limit(300)`.
  - Emit: `data: {"type":"start","conversation_id":N}\n\n` → `$agent->process($msg, fn($m,$s,$a) => emit progress)` → simpan pesan agent (content final + actions) ke DB → emit `data: {"type":"done","ai_response":...,"actions":[...],"conversation_id":N}\n\n` → `data: [DONE]\n\n`.
  - Error: emit `{"type":"error","message":...}` + simpan pesan error.
  - `ob_flush() + flush()` setelah tiap event.
- Metode `chat()` lama dibiarkan (fallback), tidak dihapus.

### 5. `routes/admin.php`
- Tambah: `Route::post('websites/ai/chat/stream', [AiAgentController::class, 'streamChat'])`.

### 6. `resources/js/pages/Admin/Websites/AiAgent.vue`
- `Message` interface: tambah `progress?: { message: string; status: string; agent?: string }[]` dan `pending?: boolean`.
- Helper `streamRequest()`: `fetch` POST ke `/admin/websites/ai/chat/stream`, `res.body.getReader()`, `TextDecoder`, buffer di-split per `\n\n`, parse `data:` lines.
- `sendMessage()`:
  - Push pesan user; buat objek agent placeholder `{ role:'agent', content:'', pending:true, progress:[] }` dan push ke messages.
  - Event `start` → set `conversation_id` + `upsertConversation`.
  - Event `progress` → `msg.progress.push({ message, status, agent })` (reaktivitas array otomatis), `scrollToBottom()`.
  - Event `done` → set `msg.content`, `msg.actions`, `msg.pending=false`, hapus `msg.progress`.
  - Event `error` → set content error, `pending=false`.
  - `isLoading` tetap lock sampai stream selesai/`[DONE]`.
- Template bubble agent:
  - Jika `msg.pending` → render timeline progress (spinner `Loader2` utk `status='loading'`, `CheckCircle2` utk `done`, chip agent warna — fungsi `agentBadge` sudah ada).
  - Jika final → render konten + action cards seperti sekarang (tidak berubah).

## Asumsi & Keputusan

- **SSE langsung dari request**, bukan job queue (tidak ada Redis/supervisor; artisan serve OK).
- **Progress live tidak disimpan permanen** di DB — yang tersimpan tetap pesan final + actions seperti sekarang (riwayat tetap utuh setelah refresh).
- **Alur bertahap (judul → konten) menambah total durasi** karena 2 panggilan AI untuk generate, tapi memberi feedback tiap tahap (ini yang diminta user).
- Kategori: pilih slug `artikel` bila ada, fallback kategori pertama non-uncategorized.
- Featured image baru (sebelumnya tidak ada).
- Semua aksi (artikel, website, order) bisa emit progress; alur bertahap detail hanya untuk artikel.

## Langkah Verifikasi

1. `php -l` pada file PHP yang diubah.
2. Jalankan server dev (`php artisan serve`) + `npm run dev`.
3. Buka `/admin/websites/ai`, kirim "buatkan artikel seo untuk demo1.sweet.web.id tentang manfaat perusahaan punya website":
   - Progress muncul bertahap di chat: judul → konten → gambar → featured image → kategori → audit → publish (bukan muncul semua setelah selesai).
   - Spinner berganti check di tiap tahap.
4. Setelah selesai: cek post di WP — 2 gambar inline + featured image terpasang, kategori `artikel`, status publish (skor ≥ 80).
5. Refresh halaman → riwayat conversation tetap ada, pesan final + action card tampil.
6. Cek aksi lain masih jalan: "cek website yang perlu update" → progress event muncul.
