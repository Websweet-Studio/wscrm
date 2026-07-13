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
