<script setup lang="ts">
import CustomerFooter from '@/components/CustomerFooter.vue';
import CustomerHeader from '@/components/CustomerHeader.vue';
import PublicFloatingChat from '@/components/PublicFloatingChat.vue';
import { Head } from '@inertiajs/vue3';

interface Props {
    title?: string;
}

withDefaults(defineProps<Props>(), {
    title: 'Layanan Hosting & Domain',
});
</script>

<template>
    <Head :title="title" />

    <div class="kpub min-h-screen" style="background-color: var(--kpub-bg);">
        <CustomerHeader />

        <main class="flex-1">
            <slot />
        </main>

        <CustomerFooter />

        <!-- Chatbot customer service untuk pengunjung -->
        <PublicFloatingChat />
    </div>
</template>

<style>
/* Token halaman publik WebSweetStudio — satu sumber warna untuk header/isi/footer.
   SEMUA warna diturunkan dari branding (Admin → Branding: primary / secondary / accent),
   jadi mengubah branding langsung mengubah tampilan halaman publik tanpa sentuh CSS. */
.kpub {
    /* Brand */
    --kpub-primary: var(--primary, #e11d48);
    --kpub-primary-ink: var(--primary-foreground, #faf9f5);
    --kpub-secondary: var(--secondary, #e7e7e7);

    /* Permukaan terang */
    --kpub-bg: color-mix(in srgb, var(--kpub-secondary) 45%, #ffffff);
    --kpub-card: #ffffff;
    --kpub-ink: color-mix(in srgb, var(--accent, #000000) 90%, #ffffff);
    --kpub-muted: color-mix(in srgb, var(--accent, #000000) 62%, #ffffff);
    --kpub-line: color-mix(in srgb, var(--accent, #000000) 12%, #ffffff);

    /* Permukaan gelap (panel harga, footer, bukti kerja) */
    --kpub-deep: color-mix(in srgb, var(--kpub-primary) 20%, #0b0708);
    --kpub-deep-ink: color-mix(in srgb, var(--kpub-primary) 5%, #ffffff);
    --kpub-deep-text: color-mix(in srgb, var(--kpub-deep-ink) 82%, var(--kpub-deep));
    --kpub-deep-muted: color-mix(in srgb, var(--kpub-deep-ink) 58%, var(--kpub-deep));
    --kpub-deep-line: color-mix(in srgb, var(--kpub-deep-ink) 16%, var(--kpub-deep));

    /* Aksen terang di atas permukaan gelap (harga promo, tautan) */
    --kpub-highlight: color-mix(in srgb, var(--kpub-primary) 45%, #ffffff);

    font-family: 'Bricolage Grotesque', 'Inter', ui-sans-serif, system-ui, sans-serif;
    color: var(--kpub-ink);
}

.kpub .kdisplay {
    font-weight: 600;
    letter-spacing: -0.02em;
    line-height: 1.06;
    text-wrap: balance;
}

.kpub .knum {
    font-variant-numeric: tabular-nums;
}

.kpub h1,
.kpub h2,
.kpub h3 {
    text-wrap: balance;
}

.kpub p {
    text-wrap: pretty;
}

/* Tombol — kelas dipakai bersama supaya hover/focus konsisten di semua section */
.kpub .kbtn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    font-weight: 600;
    transition:
        background-color 0.15s ease,
        border-color 0.15s ease,
        color 0.15s ease;
}

.kpub .kbtn-primary {
    background-color: var(--kpub-primary);
    color: var(--kpub-primary-ink);
    border: 1px solid var(--kpub-primary);
}

.kpub .kbtn-primary:hover {
    background-color: color-mix(in srgb, var(--kpub-primary) 86%, #000000);
    border-color: color-mix(in srgb, var(--kpub-primary) 86%, #000000);
}

.kpub .kbtn-ghost {
    background-color: transparent;
    color: var(--kpub-ink);
    border: 1px solid var(--kpub-line);
}

.kpub .kbtn-ghost:hover {
    background-color: color-mix(in srgb, var(--kpub-ink) 6%, transparent);
    border-color: color-mix(in srgb, var(--kpub-ink) 22%, transparent);
}

.kpub .klink {
    color: var(--kpub-primary);
    font-weight: 500;
    text-decoration: underline;
    text-underline-offset: 4px;
    transition: color 0.15s ease;
}

.kpub .klink:hover {
    color: color-mix(in srgb, var(--kpub-primary) 78%, #000000);
}

.kpub .klink-on-deep {
    color: var(--kpub-highlight);
}

.kpub .klink-on-deep:hover {
    color: var(--kpub-deep-ink);
}

/* Fokus keyboard tetap terlihat & memakai warna brand */
.kpub a:focus-visible,
.kpub button:focus-visible {
    outline: 2px solid var(--kpub-primary);
    outline-offset: 2px;
    border-radius: 0.5rem;
}

@media (prefers-reduced-motion: reduce) {
    .kpub * {
        transition-duration: 0.01ms !important;
        animation-duration: 0.01ms !important;
    }
}
</style>
