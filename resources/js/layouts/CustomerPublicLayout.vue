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

    <div class="kpub min-h-screen">
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
/*
  Satu sumber tampilan halaman publik (landing, hosting, domain, demo, blog).
  Netral putih dengan garis rambut; hijau hanya untuk aksi dan satu pita penutup.
*/
.kpub {
    --kpub-bg: #ffffff;
    --kpub-surface: #f7f8f7;
    --kpub-ink: #0d1210;
    --kpub-muted: #5b6661;
    --kpub-faint: #8b948f;
    --kpub-line: #e6e8e6;
    --kpub-line-strong: #d3d9d5;
    --kpub-card: #ffffff;
    --kpub-green: #0a6c4d;
    --kpub-green-hover: #085839;
    --kpub-green-ink: #ffffff;
    --kpub-green-soft: #edf5f1;
    --kpub-deep: #0c2a1f;
    --kpub-deep-ink: #e7efeb;
    --kpub-amber: #96530a;
    --kpub-display: 'Funnel Display', 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
    --kpub-body: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
    --kpub-radius: 14px;
    --kpub-radius-lg: 25px;

    background-color: var(--kpub-bg);
    color: var(--kpub-ink);
    font-family: var(--kpub-body);
    font-size: 16px;
    line-height: 1.6;
    -webkit-font-smoothing: antialiased;
}

.kpub h1,
.kpub h2,
.kpub h3,
.kpub h4,
.kpub .kdisplay {
    font-family: var(--kpub-display);
    font-weight: 400;
    letter-spacing: -0.02em;
    line-height: 1.1;
}

.kpub .knum {
    font-variant-numeric: tabular-nums;
    font-feature-settings: 'tnum' 1;
}

.kpub .kcontainer {
    margin-inline: auto;
    max-width: 1120px;
    padding-inline: 1.25rem;
}

@media (min-width: 640px) {
    .kpub .kcontainer {
        padding-inline: 2rem;
    }
}

/* Tombol: satu bentuk pil, dua bobot. */
.kpub .kbtn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    border-radius: 999px;
    padding: 0.65rem 1.25rem;
    font-size: 0.9375rem;
    font-weight: 500;
    line-height: 1.2;
    transition: background-color 0.15s ease, border-color 0.15s ease, color 0.15s ease;
    white-space: nowrap;
}

.kpub .kbtn-primary {
    background-color: var(--kpub-green);
    color: var(--kpub-green-ink);
}

.kpub .kbtn-primary:hover {
    background-color: var(--kpub-green-hover);
}

.kpub .kbtn-outline {
    border: 1px solid var(--kpub-line-strong);
    color: var(--kpub-ink);
    background-color: transparent;
}

.kpub .kbtn-outline:hover {
    border-color: var(--kpub-ink);
}

.kpub .kbtn-ghost {
    color: var(--kpub-muted);
}

.kpub .kbtn-ghost:hover {
    color: var(--kpub-ink);
}

.kpub .klink {
    color: var(--kpub-green);
    text-decoration: underline;
    text-decoration-thickness: 1px;
    text-underline-offset: 3px;
}

.kpub .klink:hover {
    color: var(--kpub-green-hover);
}

/* Permukaan: kartu bergaris rambut, tanpa bayangan. */
.kpub .kcard {
    background-color: var(--kpub-card);
    border: 1px solid var(--kpub-line);
    border-radius: var(--kpub-radius);
}

.kpub .kfield {
    width: 100%;
    border: 1px solid var(--kpub-line-strong);
    border-radius: 999px;
    background-color: #ffffff;
    padding: 0.7rem 1.15rem;
    font-size: 0.9375rem;
    color: var(--kpub-ink);
    outline: none;
}

/* Bidang dengan ikon di kiri: utility pl-* kalah oleh padding .kfield di atas. */
.kpub .kfield-icon {
    padding-left: 2.75rem;
}


.kpub .kfield::placeholder {
    color: var(--kpub-faint);
}

.kpub .kfield:focus-visible {
    border-color: var(--kpub-green);
    box-shadow: 0 0 0 3px var(--kpub-green-soft);
}

.kpub :focus-visible {
    outline: 2px solid var(--kpub-green);
    outline-offset: 2px;
}

@media (prefers-reduced-motion: reduce) {
    .kpub * {
        transition-duration: 0.01ms !important;
        animation-duration: 0.01ms !important;
    }
}
/*
  app.css mengunci html/body ke height:100% untuk shell aplikasi; halaman publik
  memakai dokumen setinggi konten supaya bisa digulir.
*/
html:has(.kpub),
body:has(.kpub) {
    height: auto;
}
</style>
