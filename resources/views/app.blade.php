<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "system" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        <title inertia>{{ $brandingSettings['app_name'] ?? config('app.name', 'WSCRM') }}</title>

        <script>
            window.brandingSettings = @json($brandingSettings ?? []);
        </script>

        {{-- Favicon from branding settings with fallback to default --}}
        <link rel="icon" href="{{ $brandingSettings['app_favicon'] ?? '/1.png' }}" sizes="any">
        <link rel="icon" href="{{ $brandingSettings['app_favicon'] ?? '/1.png' }}" type="image/png">
        <link rel="apple-touch-icon" href="{{ $brandingSettings['app_favicon'] ?? '/1.png' }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300..700&display=swap" rel="stylesheet">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400..700&display=swap" rel="stylesheet">

        @vite(['resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        @inertiaHead

        {{-- Dynamic CSS variables from branding settings — overrides hardcoded colors in app.css --}}
        <style>
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }
        </style>

        {{-- Tipografi konten artikel dari editor (v-html).
             Aturan .prose di komponen Vue (Blog/Show.vue, Admin/Blog/Show.vue) memakai
             <style scoped>, sehingga TIDAK berlaku untuk elemen hasil v-html karena
             elemen tersebut tidak punya atribut data-v-*. Tipografi efektif didefinisikan
             di sini supaya berlaku di pratinjau admin maupun halaman publik.
             Ruang lingkup dibatasi ke wadah .prose-lg (blog publik) & .prose-gray (admin). --}}
        <style>
            .prose-lg h1, .prose-gray h1,
            .prose-lg h2, .prose-gray h2,
            .prose-lg h3, .prose-gray h3 {
                font-weight: 700;
                line-height: 1.3;
                margin: 1.75rem 0 0.75rem;
            }
            .prose-lg h1, .prose-gray h1 { font-size: 1.875rem; }
            .prose-lg h2, .prose-gray h2 { font-size: 1.5rem; }
            .prose-lg h3, .prose-gray h3 { font-size: 1.25rem; }
            .prose-lg p, .prose-gray p { margin: 0 0 1rem; line-height: 1.75; }
            .prose-lg ul, .prose-gray ul { list-style: disc; margin: 0 0 1.25rem; padding-left: 1.5rem; }
            .prose-lg ol, .prose-gray ol { list-style: decimal; margin: 0 0 1.25rem; padding-left: 1.5rem; }
            .prose-lg li, .prose-gray li { margin-bottom: 0.5rem; line-height: 1.7; }
            .prose-lg li::marker, .prose-gray li::marker { color: var(--primary, #c96442); }
            .prose-lg strong, .prose-gray strong { font-weight: 700; }
            .prose-lg a, .prose-gray a {
                color: var(--primary, #c96442);
                font-weight: 500;
                text-decoration: underline;
                text-underline-offset: 2px;
            }
            .prose-lg a:hover, .prose-gray a:hover { opacity: 0.85; }
            .prose-lg blockquote, .prose-gray blockquote {
                border-left: 4px solid var(--primary, #c96442);
                margin: 1.5rem 0;
                padding-left: 1rem;
                font-style: italic;
                color: #4b5563;
            }
            .prose-lg code, .prose-gray code {
                background: #f3f4f6;
                border-radius: 0.25rem;
                padding: 0.125rem 0.35rem;
                font-size: 0.875em;
            }
            .prose-lg pre, .prose-gray pre {
                background: #1f2937;
                color: #f9fafb;
                border-radius: 0.5rem;
                margin: 0 0 1.25rem;
                padding: 1rem;
                overflow-x: auto;
            }
            .prose-lg pre code, .prose-gray pre code { background: transparent; color: inherit; padding: 0; }
            .prose-lg img, .prose-gray img { max-width: 100%; height: auto; border-radius: 0.75rem; margin: 1.5rem 0; }
            .prose-lg hr, .prose-gray hr { border: 0; border-top: 1px solid #e5e7eb; margin: 2rem 0; }
            .dark .prose-lg blockquote, .dark .prose-gray blockquote { border-color: #6b7280; color: #d1d5db; }
            .dark .prose-lg code, .dark .prose-gray code { background: #374151; color: #f3f4f6; }

            /* Polesan tipografi halaman artikel publik (.prose-lg) — 19 Sep 2026.
               Ukuran baca lebih besar, jarak antar-seksi lebih lega, aksen warna brand
               untuk marker/kutipan. (Admin tetap memakai .prose-gray apa adanya.) */
            .prose-lg { font-size: 1.0625rem; line-height: 1.8; color: var(--foreground, #141413); }
            .prose-lg h1:first-child, .prose-lg h2:first-child, .prose-lg h3:first-child { margin-top: 0; }
            .prose-lg h2 { margin: 2.5rem 0 0.85rem; font-size: 1.5rem; letter-spacing: -0.01em; }
            .prose-lg h3 { margin: 2rem 0 0.6rem; font-size: 1.2rem; letter-spacing: -0.01em; }
            .prose-lg p { margin: 0 0 1.25rem; line-height: 1.8; }
            .prose-lg ul, .prose-lg ol { margin: 0 0 1.4rem; padding-left: 1.35rem; }
            .prose-lg li { margin-bottom: 0.6rem; line-height: 1.75; }
            .prose-lg strong { font-weight: 600; color: var(--foreground, #141413); }
            .prose-lg a { text-underline-offset: 3px; text-decoration-thickness: 1px; }
            .prose-lg a:hover { opacity: 1; text-decoration-thickness: 2px; }
            .prose-lg blockquote {
                border-left-width: 3px;
                border-radius: 0 0.75rem 0.75rem 0;
                background: rgba(201, 100, 66, 0.06);
                margin: 1.75rem 0;
                padding: 0.9rem 1.25rem;
                color: var(--muted-foreground, #5e5d59);
            }
            .prose-lg img { border-radius: 1rem; margin: 2rem 0; }
            .prose-lg hr { border-top-color: var(--border, #f0eee6); margin: 2.5rem 0; }
            .prose-lg > :last-child { margin-bottom: 0; }
        </style>

        <script>
            (function() {
                var vars = @json(\App\Models\BrandingSetting::getCssVariableMap());
                var root = document.documentElement;
                for (var key in vars) {
                    if (vars.hasOwnProperty(key)) {
                        root.style.setProperty(key, vars[key]);
                    }
                }
            })();
        </script>
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
