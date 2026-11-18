<?php

namespace App\Console\Commands;

use App\Models\BlogPost;
use App\Models\User;
use App\Services\AiClient;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Terbitkan artikel blog secara berkala (dipanggil cron /etc/cron.d/wscrm-jobs,
 * cadensi nyata dijaga di sini: minimal sekali per 2 hari).
 *
 * Sumber konten: AI (AiClient; endpoint/model dari env atau /admin/ai/settings).
 * Setiap artikel ditandai meta_data.auto_generated = true supaya cadensi
 * bisa dihitung dari artikel otomatis terakhir.
 *
 * Thumbnail: pixel-art motif sesuai tema (keranjang, globe, server, dollar, ...)
 * digambar GD — pola deterministik dari slug, tanpa teks, tanpa logo.
 */
class AutoPublishBlog extends Command
{
    /** Tema rotasi + motif thumbnail-nya. AI memilih judul & sudut pandang baru tiap kali. */
    private const TEMA = [
        ['theme' => 'memilih hosting untuk website UMKM pemula', 'category' => 'hosting-domain', 'motif' => 'keranjang'],
        ['theme' => 'cara membeli dan mengelola domain .my.id untuk bisnis kecil', 'category' => 'hosting-domain', 'motif' => 'globe'],
        ['theme' => 'perbedaan shared hosting, VPS, dan cloud hosting untuk pemilik website', 'category' => 'hosting-domain', 'motif' => 'server'],
        ['theme' => 'memindahkan website WordPress ke hosting baru tanpa downtime', 'category' => 'tutorial', 'motif' => 'pindah'],
        ['theme' => 'membuat email bisnis dengan nama domain sendiri', 'category' => 'tutorial', 'motif' => 'email'],
        ['theme' => 'memasang SSL gratis Let\'s Encrypt di hosting', 'category' => 'tutorial', 'motif' => 'shield'],
        ['theme' => 'menghubungkan domain ke website: DNS, A record, dan nameserver', 'category' => 'tutorial', 'motif' => 'globe'],
        ['theme' => 'cara cepat website: image, cache, dan hosting', 'category' => 'tips-trik', 'motif' => 'speed'],
        ['theme' => 'checklist sebelum website bisnis online ke publik', 'category' => 'tips-trik', 'motif' => 'checklist'],
        ['theme' => 'memilih nama domain yang baik untuk bisnis Indonesia', 'category' => 'tips-trik', 'motif' => 'globe'],
        ['theme' => 'website bisnis wajib mobile-friendly: cara mengecek dan memperbaiki', 'category' => 'tips-trik', 'motif' => 'ponsel'],
        ['theme' => 'kenapa website bisnis perlu backup rutin dan cara melakukannya', 'category' => 'tips-trik', 'motif' => 'database'],
        ['theme' => 'SSL/HTTPS dan keamanan dasar website untuk pemilik usaha', 'category' => 'teknologi', 'motif' => 'shield'],
        ['theme' => 'apa itu uptime dan kenapa penting untuk toko online', 'category' => 'teknologi', 'motif' => 'speed'],
        ['theme' => 'domain vs hosting: analogi sederhana untuk pemula', 'category' => 'hosting-domain', 'motif' => 'server'],
        ['theme' => 'perpanjangan domain: biaya, tenggat, dan cara menghindari domain hangus', 'category' => 'hosting-domain', 'motif' => 'kalender'],
        ['theme' => 'membuat landing page promo yang konversi untuk usaha kecil', 'category' => 'tips-trik', 'motif' => 'grafik'],
        ['theme' => 'memanfaatkan WhatsApp Business bersama website untuk menerima pesanan', 'category' => 'tips-trik', 'motif' => 'chat'],
        ['theme' => 'perbedaan .com, .id, dan .co.id untuk bisnis Indonesia', 'category' => 'hosting-domain', 'motif' => 'globe'],
        ['theme' => 'cara membaca statistik pengunjung website tanpa pusing', 'category' => 'tips-trik', 'motif' => 'grafik'],
        ['theme' => 'persiapan website sebelum musim ramai (Ramadhan, tahun baru, tanggal kembar)', 'category' => 'tips-trik', 'motif' => 'kalender'],
        ['theme' => 'apa yang terjadi pada website saat hosting penuh: resource limits dijelaskan', 'category' => 'teknologi', 'motif' => 'server'],
        ['theme' => 'membuat halaman about us yang dipercaya pembeli', 'category' => 'tips-trik', 'motif' => 'chat'],
        ['theme' => 'mengapa ada hosting yang sangat murah: apa yang perlu dicek sebelum membeli', 'category' => 'hosting-domain', 'motif' => 'dollar'],
    ];

    /**
     * Bitmap motif pixel-art. Karakter: 'G' hijau, 'D' hijau tua, '.' kosong.
     * Semua baris harus sama panjang. Digambar besar di tengah thumbnail.
     */
    private const MOTIF = [
        'keranjang' => [
            '.......GGGGGGGG.....',
            '......G........G....',
            '......G........G....',
            '...GGGGGGGGGGGGGGG..',
            '...GDDDDDDDDDDDDDG..',
            '...GGGDDGGGDDGGGGG..',
            '...GGGGDDGGGDDGGGG..',
            '...GGGDDGGGDDGGGGG..',
            '...GGGGDDGGGDDGGGG..',
            '...GGGDDGGGDDGGGGG..',
            '....GGGGGGGGGGGGG...',
            '.....GGGGGGGGGGG....',
            '......GGGGGGGGG.....',
            '.....GG........GG...',
            '....GGGG......GGGG..',
        ],
        'server' => [
            '.GGGGGGGGGGGGGGGGGG.',
            '.G................G.',
            '.G..GG...........GG.',
            '.G................G.',
            '.GGGGGGGGGGGGGGGGGG.',
            '.G................G.',
            '.G..GG...........GG.',
            '.G................G.',
            '.GGGGGGGGGGGGGGGGGG.',
            '.G................G.',
            '.G..GG...........GG.',
            '.G................G.',
            '.GGGGGGGGGGGGGGGGGG.',
        ],
                'dollar' => [
            '.........GG.........',
            '.........GG.........',
            '......GGGGGGGGG.....',
            '....GGGGGGGGGGGGG...',
            '...GGGG.....GGGGG...',
            '...GG........GGGG...',
            '....GGGGGGGGGGG.....',
            '.......GGGGGGGG.....',
            '..........GGGGGG....',
            '.....GG.......GGG...',
            '....GGGGGGGGGGGGG...',
            '......GGGGGGGGG.....',
            '.........GG.........',
            '.........GG.........',
        ],
        'shield' => [
            '...GGGGGGGGGGG......',
            '..GGGGGGGGGGGGG.....',
            '.GGGGGGGGGGGGGGG....',
            '.GGGGGGGGGGGGGGG....',
            '.GGGG.GGGG.GGGGG....',
            '.GGGGG.GG.GGGGGG....',
            '..GGGGG.GGGGGGG.....',
            '..GGGGGG.GGGGG......',
            '...GGGGGGGGGG.......',
            '....GGGGGGGG........',
            '.....GGGGGG.........',
            '......GGGG..........',
            '.......GG...........',
        ],
        'speed' => [
            '....GGGGGGGGGGGG....',
            '..GG..........GG....',
            '.G..............G...',
            '.G............DD....',
            'G............DD.....',
            'G...........DD......',
            'G.........DD........',
            'G........DD.........',
            '.G.....DD...........',
            '.GGGGGGGGGGGGGG.....',
            '..GGGGGGGGGGGG......',
        ],
        'database' => [
            '...GGGGGGGGGGGGG....',
            '..GGGGGGGGGGGGGGG...',
            '.GGGGGGGGGGGGGGGGG..',
            '.GG.............GG..',
            '.GG.............GG..',
            '.GGGGGGGGGGGGGGGGG..',
            '.GG.............GG..',
            '.GG.............GG..',
            '.GGGGGGGGGGGGGGGGG..',
            '.GG.............GG..',
            '.GG.............GG..',
            '..GGGGGGGGGGGGGGG...',
            '...GGGGGGGGGGGGG....',
        ],
        'email' => [
            '.GGGGGGGGGGGGGGGGGG.',
            '.GG............GG...',
            '.GGG..........GGG...',
            '.GGGG........GGGG...',
            '.GGGGG......GGGGG...',
            '.GGGGGG....GGGGGG...',
            '.GGGGGGG..GGGGGGG...',
            '.GGGGGGGGGGGGGGGGG..',
            '.G...............G..',
            '.G...............G..',
            '.GGGGGGGGGGGGGGGGGG.',
        ],
        'chat' => [
            '..GGGGGGGGGGGGGG....',
            '.GGGGGGGGGGGGGGGG...',
            '.GGGDGDGDGDGGGGGG...',
            '.GGGGGGGGGGGGGGGG...',
            '.GGGGGGGGGGGGGGGG...',
            '.GGGGGGGGGGGGGGGG...',
            '.GGGGGGGGGGGGGGGG...',
            '..GGGGGGGGGGGGGG....',
            '...GGGGGGGGGGGG.....',
            '....GGG.............',
            '...GG...............',
            '..GG................',
        ],
        'checklist' => [
            '..GGGGGGGGGGGGGG....',
            '..GGGGGGGGGGGGGG....',
            '..G............G....',
            '..G.GG..........G...',
            '..G..GGGGGG....G....',
            '..G...GGGGG....G....',
            '..G............G....',
            '..G.GG..........G...',
            '..G..GGGGGGG...G....',
            '..G...GGGGG....G....',
            '..G............G....',
            '..GGGGGGGGGGGGGG....',
        ],
        'ponsel' => [
            '.....GGGGGGGGGG.....',
            '.....GGGGGGGGGG.....',
            '.....G........G.....',
            '.....G........G.....',
            '.....G..DDDD..G.....',
            '.....G..DDDD..G.....',
            '.....G..DDDD..G.....',
            '.....G..DDDD..G.....',
            '.....G........G.....',
            '.....G........G.....',
            '.....GGGGGGGGGG.....',
        ],
        'kalender' => [
            '..GGGGGGGGGGGGGG....',
            '..GGGGGGGGGGGGGG....',
            '..G............G....',
            '..G............G....',
            '..GGGGGGGGGGGGGG....',
            '..G............G....',
            '..G..GG..GG....G....',
            '..G............G....',
            '..G..GG..GG....G....',
            '..G............G....',
            '..G..GG........G....',
            '..GGGGGGGGGGGGGG....',
        ],
        'grafik' => [
            '..........GG........',
            '..........GG...GGG..',
            '.....GG...GG...GGG..',
            '.....GG...GG...GGG..',
            '.GG..GG...GG...GGG..',
            '.GG..GG...GG...GGG..',
            '.GG..GG...GG...GGG..',
            '.GG..GG...GG...GGG..',
            '.GGGGGGGGGGGGGGGGG..',
            '.GGGGGGGGGGGGGGGGG..',
        ],
        'pindah' => [
            '..GGGGG.............',
            '..GGGGG.............',
            '..GGGGG..GGG........',
            '..GGGGG...GGG.......',
            '..GGGGG....GGG......',
            '..GGGGG..GGGGGGGGG..',
            '..GGGGG....GGG......',
            '..GGGGG...GGG.......',
            '..GGGGG..GGG........',
            '..GGGGG.............',
        ],
    ];

    /** Interval minimum antar eksekusi otomatis (jam). Cron jalan tiap 2 hari. */
    private const INTERVAL_JAM = 47;

    protected $signature = 'blog:auto-publish
        {--count=2 : Jumlah artikel per eksekusi}
        {--force : Abaikan cek interval 2 hari}
        {--backfill : Buatkan thumbnail untuk artikel auto yang belum punya gambar}';

    protected $description = 'Terbitkan artikel blog otomatis (AI), sekali per 2 hari';

    public function handle(): int
    {
        if ($this->option('backfill')) {
            return $this->backfillThumbnail();
        }

        if (! $this->option('force') && ! $this->jatuhTempo()) {
            $this->line('SKIP: artikel otomatis terakhir belum berumur ' . self::INTERVAL_JAM . ' jam.');

            return self::SUCCESS;
        }

        $client = new AiClient();
        if (! $client->hasApiKey()) {
            $this->error('AiClient tidak punya API key (atur AI_API_KEY di .env atau /admin/ai/settings).');

            return self::FAILURE;
        }

        $count = max(1, (int) $this->option('count'));
        $penulisId = $this->idPenulis();
        $judulAda = BlogPost::query()->latest('published_at')->limit(40)->pluck('title')->all();
        $offset = (int) BlogPost::where('meta_data->auto_generated', true)->count();

        $berhasil = 0;
        for ($i = 0; $i < $count; $i++) {
            $tema = self::TEMA[($offset + $i) % count(self::TEMA)];

            try {
                $data = $this->generateArtikel($client, $tema, $judulAda);
                $post = $this->simpan($data, $tema, $penulisId);
                $this->tempelThumbnail($post, $tema['motif']);
            } catch (\Throwable $e) {
                $this->error("Gagal membuat artikel (tema: {$tema['theme']}): " . $e->getMessage());

                continue;
            }

            $judulAda[] = $data['title'];
            $berhasil++;
            $this->info("Terbit: {$post->title} → /blog/{$post->slug}");
        }

        return $berhasil > 0 ? self::SUCCESS : self::FAILURE;
    }

    /** Cadensi nyata: artikel otomatis terakhir harus lebih tua dari interval. */
    private function jatuhTempo(): bool
    {
        $terakhir = BlogPost::where('meta_data->auto_generated', true)
            ->max('published_at');

        if ($terakhir === null) {
            return true;
        }

        return Carbon::parse($terakhir)
            ->addHours(self::INTERVAL_JAM)
            ->isPast();
    }

    private function idPenulis(): int
    {
        return User::whereIn('role', ['super_admin', 'admin'])->orderBy('id')->value('id')
            ?? User::orderBy('id')->value('id')
            ?? 1;
    }

    /**
     * @return array{title: string, excerpt: string, content: string, category: string, meta: array<string, string>}
     */
    private function generateArtikel(AiClient $client, array $tema, array $judulAda): array
    {
        $system = <<<'TXT'
        Kamu penulis konten blog Websweet Studio (websweetstudio.com), penyedia hosting, domain,
        dan jasa pembuatan website untuk bisnis di Indonesia. Gaya tulisan: bahasa Indonesia yang
        praktis dan spesifik, tidak lebay, tidak penuh jargon, seperti praktisi menjelaskan ke
        pemilik usaha. Hindari klise pemasaran ("dunia digital yang serba cepat", dsb.).
        TXT;

        $daftarJudul = implode("\n- ", array_slice($judulAda, -20));
        $prompt = <<<TXT
        Tulis satu artikel blog dengan tema: "{$tema['theme']}".

        Aturan konten:
        - Panjang 500-800 kata.
        - "content" berisi Markdown saja: gunakan ## untuk subjudul, - untuk daftar, **tebal**, *miring*. Tanpa HTML, tanpa tabel, tanpa code fence.
        - Buka dengan langsung ke inti (tanpa basa-basi pembuka), tutup dengan satu paragraf ajakan singkat yang wajar terkait layanan Websweet Studio.
        - Contoh konkret dan angka bila relevan; konteks pembaca: pemilik usaha kecil-menengah di Indonesia.
        - Judul artikel TIDAK boleh mirip dengan judul yang sudah ada berikut ini.

        Judul yang sudah ada (jangan dipakai / variasikan):
        - {$daftarJudul}

        Jawab HANYA JSON valid tanpa teks lain:
        {
          "title": "judul artikel (max 70 karakter)",
          "excerpt": "ringkasan 1 kalimat menarik (max 180 karakter)",
          "meta_title": "judul SEO (max 60 karakter)",
          "meta_description": "deskripsi SEO (max 155 karakter)",
          "meta_keywords": "3-6 kata kunci dipisah koma",
          "content": "isi artikel dalam Markdown"
        }
        TXT;

        $isi = $client->chat(
            [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $prompt],
            ],
            0.75,
            8000,
        );

        $data = $this->decodeJson($isi);
        if ($data === null) {
            // Coba sekali lagi: minta JSON ulang yang valid dan lengkap.
            $isi = $client->chat(
                [
                    ['role' => 'system', 'content' => $system],
                    ['role' => 'user', 'content' => $prompt],
                    ['role' => 'assistant', 'content' => $isi],
                    ['role' => 'user', 'content' => 'Jawabanmu tadi bukan JSON valid (kemungkinan terpotong atau ada baris baru di dalam nilai string). Ulangi jawaban lengkap sebagai SATU JSON valid tanpa baris baru di dalam nilai string (pakai spasi saja).'],
                ],
                0.4,
                8000,
            );
            $data = $this->decodeJson($isi);
        }

        if (! is_array($data)) {
            throw new \RuntimeException('Respons AI bukan JSON valid: ' . Str::limit($isi, 200));
        }

        foreach (['title', 'content'] as $wajib) {
            if (empty($data[$wajib]) || ! is_string($data[$wajib])) {
                throw new \RuntimeException("Field '{$wajib}' hilang/kosong dari respons AI");
            }
        }

        $html = (new \League\CommonMark\GithubFlavoredMarkdownConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]))->convert($data['content'])->getContent();

        return [
            'title' => trim($data['title']),
            'excerpt' => isset($data['excerpt']) ? mb_substr(trim($data['excerpt']), 0, 300) : '',
            'content' => $this->bersihkanHtml($html),
            'category' => $tema['category'],
            'meta' => array_filter([
                'meta_title' => isset($data['meta_title']) ? mb_substr(trim($data['meta_title']), 0, 255) : null,
                'meta_description' => isset($data['meta_description']) ? mb_substr(trim($data['meta_description']), 0, 255) : null,
                'meta_keywords' => isset($data['meta_keywords']) ? mb_substr(trim($data['meta_keywords']), 0, 255) : null,
            ]),
        ];
    }

    /** Decoding JSON respons AI yang tahan code fence dan JSON terpotong. */
    private function decodeJson(string $isi): ?array
    {
        $isi = trim(preg_replace('/^```(?:json)?\s*|\s*```$/s', '', trim($isi)) ?? $isi);

        $data = json_decode($isi, true);
        if (is_array($data)) {
            return $data;
        }

        // JSON terpotong di tengah: tutup string/objek yang menggantung.
        $perbaikan = rtrim($isi);
        if (substr_count($perbaikan, '"') % 2 === 1) {
            $perbaikan .= '"';
        }
        $perbaikan = rtrim($perbaikan, ", \n\r\t");
        $kurawal = substr_count($perbaikan, '{') - substr_count($perbaikan, '}');
        $siku = substr_count($perbaikan, '[') - substr_count($perbaikan, ']');
        $perbaikan .= str_repeat(']', max(0, $siku)) . str_repeat('}', max(0, $kurawal));

        $data = json_decode($perbaikan, true);

        return is_array($data) ? $data : null;
    }

    /** Buang tag/atribut berbahaya dari HTML hasil AI sebelum disimpan untuk v-html. */
    private function bersihkanHtml(string $html): string
    {
        $html = preg_replace('#<\s*(script|style|iframe|object|embed|form|input)[^>]*>.*?<\s*/\s*\1\s*>#is', '', $html) ?? '';
        $html = preg_replace('#<\s*(script|style|iframe|object|embed|form|input)[^>]*/?>#i', '', $html) ?? '';
        $html = preg_replace('#\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)#i', '', $html) ?? '';
        $html = preg_replace('#javascript\s*:#i', '', $html) ?? '';

        return trim($html);
    }

    private function simpan(array $data, array $tema, int $penulisId): BlogPost
    {
        $slug = Str::slug($data['title']);
        $asli = $slug;
        $n = 1;
        while (BlogPost::where('slug', $slug)->exists()) {
            $slug = $asli . '-' . ++$n;
        }

        return BlogPost::create([
            'title' => $data['title'],
            'slug' => $slug,
            'excerpt' => $data['excerpt'],
            'content' => $data['content'],
            'blog_category_id' => $this->idKategori($data['category']),
            'user_id' => $penulisId,
            'type' => 'article',
            'status' => 'published',
            'is_featured' => false,
            'is_pinned' => false,
            'allow_comments' => true,
            'meta_data' => array_merge($data['meta'], [
                'auto_generated' => true,
                'generator' => 'blog:auto-publish',
                'tema' => $tema['theme'],
            ]),
            'published_at' => now(),
        ]);
    }

    private function idKategori(string $slug): int
    {
        $id = \Illuminate\Support\Facades\DB::table('blog_categories')->where('slug', $slug)->value('id');

        return $id ?: 4; // fallback: Hosting & Domain
    }

    /** Buatkan thumbnail untuk semua artikel auto yang belum punya gambar. */
    private function backfillThumbnail(): int
    {
        $posts = BlogPost::where('meta_data->auto_generated', true)
            ->where(fn ($q) => $q->whereNull('featured_image')->orWhere('featured_image', ''))
            ->get();

        if ($posts->isEmpty()) {
            $this->line('Semua artikel otomatis sudah punya thumbnail.');

            return self::SUCCESS;
        }

        foreach ($posts as $post) {
            $motif = $this->motifUntukArtikel($post);
            try {
                $path = $this->tempelThumbnail($post, $motif);
                $this->info("Thumbnail: {$post->slug} → {$path}");
            } catch (\Throwable $e) {
                $this->error("Gagal thumbnail {$post->slug}: " . $e->getMessage());
            }
        }

        return self::SUCCESS;
    }

    /** Motif dari tema tersimpan di meta_data; fallback server. */
    private function motifUntukArtikel(BlogPost $post): string
    {
        $tema = (string) ($post->meta_data['tema'] ?? '');
        foreach (self::TEMA as $entri) {
            if ($entri['theme'] === $tema) {
                return $entri['motif'];
            }
        }

        return 'server';
    }

    /** Lampirkan thumbnail pixel-art ke artikel; kembalikan path relatif di disk public. */
    private function tempelThumbnail(BlogPost $post, string $motif): string
    {
        $path = $this->buatThumbnail($post->slug, $motif);

        // Hapus thumbnail lama bila diganti (path lokal disk public saja).
        $lama = (string) $post->featured_image;
        if ($lama !== '' && $lama !== $path && ! Str::startsWith($lama, ['http://', 'https://'])) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($lama);
        }

        $post->update(['featured_image' => $path]);

        return $path;
    }

    /**
     * Thumbnail 1200x675: motif pixel-art sesuai tema di tengah, ambient piksel
     * renggang di belakang. Pola deterministik dari slug + nama file ber-hash
     * konten (aman untuk cache immutable nginx).
     */
    private function buatThumbnail(string $slug, string $motif): string
    {
        $lebar = 1200;
        $tinggi = 675;
        $img = imagecreatetruecolor($lebar, $tinggi);

        $putih = imagecolorallocate($img, 255, 255, 255);
        $hijau = imagecolorallocate($img, 10, 108, 77);
        $hijauTua = imagecolorallocate($img, 12, 42, 31);
        imagefill($img, 0, 0, $putih);

        // Pita hijau di atas, seperti aksen halaman publik.
        imagefilledrectangle($img, 0, 0, $lebar, 14, $hijau);

        mt_srand(crc32($slug));

        // Ambient piksel renggang & pucat di seluruh bidang.
        $sel = 15;
        for ($y = $sel * 2; $y < $tinggi - $sel; $y += $sel) {
            for ($x = 0; $x < $lebar; $x += $sel) {
                $undian = mt_rand(1, 100);
                if ($undian <= 88) {
                    continue;
                }
                $alpha = mt_rand(30, 70);
                $warna = $undian <= 96
                    ? imagecolorallocatealpha($img, 10, 108, 77, 127 - (int) round($alpha * 0.14))
                    : imagecolorallocatealpha($img, 12, 42, 31, 127 - (int) round($alpha * 0.22));
                imagefilledrectangle($img, $x, $y, min($x + $sel - 1, $lebar - 1), min($y + $sel - 1, $tinggi - 1), $warna);
            }
        }

        // Motif pixel-art besar di tengah, sedikit di atas garis tengah.
        $baris = self::MOTIF[$motif] ?? self::MOTIF['server'];
        $kolom = strlen($baris[0]);
        $selMotif = 26;
        $motifLebar = $kolom * $selMotif;
        $motifTinggi = count($baris) * $selMotif;
        $awalX = (int) round(($lebar - $motifLebar) / 2);
        $awalY = (int) round(($tinggi - $motifTinggi) / 2) - 20;

        foreach ($baris as $r => $barisTeks) {
            for ($c = 0; $c < $kolom; $c++) {
                $karakter = $barisTeks[$c];
                if ($karakter === '.') {
                    continue;
                }
                // ~12% sel motif memakai hijau tua sebagai tekstur.
                $warna = $karakter === 'D' || mt_rand(1, 100) <= 12 ? $hijauTua : $hijau;
                $x = $awalX + $c * $selMotif;
                $y = $awalY + $r * $selMotif;
                imagefilledrectangle($img, $x + 1, $y + 1, $x + $selMotif - 2, $y + $selMotif - 2, $warna);
            }
        }

        ob_start();
        imagepng($img, null, 6);
        $biner = (string) ob_get_clean();
        imagedestroy($img);

        // Nama file ber-hash konten: aman untuk cache immutable 30 hari di /storage/.
        $path = 'blog/images/' . $slug . '-' . substr(md5($biner), 0, 8) . '.png';
        \Illuminate\Support\Facades\Storage::disk('public')->put($path, $biner);

        return $path;
    }
}
