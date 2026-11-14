<script setup lang="ts">
import CustomerPublicLayout from '@/layouts/CustomerPublicLayout.vue';
import { formatPrice } from '@/lib/utils';
import { Link, router } from '@inertiajs/vue3';
import { Check, Search, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface DomainPrice {
    id: number;
    extension: string;
    base_cost: number;
    renewal_cost: number;
    selling_price: number;
    renewal_price_with_tax: number;
    is_active: boolean;
}

interface Props {
    domain: string;
    requestedDomain?: string;
    requestedExtension: string;
    domainPrices: DomainPrice[];
    availabilityResults: Record<string, any>;
}

const props = defineProps<Props>();

const newSearch = ref(props.domain);

const searchAgain = () => {
    if (newSearch.value.trim()) {
        router.get('/domains/search', { domain: newSearch.value.trim() });
    }
};

/** Harga per ekstensi; data bisa tersimpan dengan atau tanpa titik. */
const priceFor = (extension: string): DomainPrice | undefined =>
    props.domainPrices.find((d) => d.extension === extension) ??
    props.domainPrices.find((d) => d.extension === `.${extension}`);

/** Ekstensi tanpa titik terdepan: "com", "co.id". */
const bareExtension = (fullDomain: string) => {
    const parts = fullDomain.split('.');
    return parts.length > 1 ? parts.slice(1).join('.') : fullDomain;
};

/**
 * Satu baris per domain yang dicek. API mengembalikan kunci domain penuh
 * ("namabisnis.com"), jadi ekstensinya diambil dari kunci itu.
 */
const hasil = computed(() =>
    Object.entries(props.availabilityResults).map(([fullDomain, result]) => {
        const extension = bareExtension(fullDomain);
        const price = priceFor(extension);
        return {
            fullDomain,
            extension,
            tersedia: Boolean(result?.success && result?.available),
            belumDiketahui: !result?.success,
            perkiraan: Boolean(result?.fallback),
            harga: price?.selling_price ?? 0,
            perpanjangan: price?.renewal_price_with_tax ?? 0,
        };
    }),
);

const jumlahTersedia = computed(() => hasil.value.filter((h) => h.tersedia).length);
const punyaHarga = (extension: string) => priceFor(extension) !== undefined;

const waLink = (domain: string) => {
    const branding = (window as any).brandingSettings || {};
    const raw = branding.company_whatsapp || '';
    const phone = String(raw).replace(/[^\d]/g, '');
    if (!phone) return '';
    return `https://wa.me/${phone}?text=${encodeURIComponent(`Halo, saya ingin daftarkan domain ${domain}.`)}`;
};
</script>

<template>
    <CustomerPublicLayout :title="`Hasil pencarian ${requestedDomain || domain}`">
        <section class="kcontainer pt-16 pb-16 md:pt-20 md:pb-24">
            <div class="max-w-2xl">
                <h1 class="text-3xl sm:text-4xl md:text-[2.75rem] md:leading-[1.1]">
                    <template v-if="jumlahTersedia > 0">{{ domain }} masih bisa dipakai.</template>
                    <template v-else>Belum ada yang tersedia untuk {{ domain }}.</template>
                </h1>
                <p class="knum mt-3 text-base" style="color: var(--kpub-muted);">
                    {{ hasil.length }} ekstensi dicek · {{ jumlahTersedia }} tersedia
                </p>
            </div>

            <form class="mt-8 flex max-w-2xl flex-col gap-2 sm:flex-row" @submit.prevent="searchAgain">
                <label for="search-again" class="sr-only">Cari domain lain</label>
                <div class="relative flex-1">
                    <Search class="pointer-events-none absolute top-1/2 left-4 h-4 w-4 -translate-y-1/2" style="color: var(--kpub-faint);" />
                    <input id="search-again" v-model="newSearch" type="text" inputmode="url" autocapitalize="none" spellcheck="false" placeholder="Coba nama lain" class="kfield kfield-icon" />
                </div>
                <button type="submit" class="kbtn kbtn-primary shrink-0">Cari lagi</button>
            </form>

            <!-- Hasil: satu baris per ekstensi, harga hanya untuk yang tersedia -->
            <div v-if="hasil.length" class="kcard mt-10 overflow-hidden">
                <div
                    v-for="h in hasil"
                    :key="h.fullDomain"
                    class="flex flex-col gap-3 border-b px-6 py-5 last:border-b-0 sm:flex-row sm:items-center sm:justify-between"
                    style="border-color: var(--kpub-line);"
                >
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="truncate text-base font-medium">{{ h.fullDomain }}</span>
                            <span
                                v-if="h.tersedia"
                                class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-medium"
                                style="background-color: var(--kpub-green-soft); color: var(--kpub-green);"
                            >
                                <Check class="h-3 w-3" />
                                Tersedia
                            </span>
                            <span
                                v-else-if="h.belumDiketahui"
                                class="rounded-full px-2 py-0.5 text-[11px] font-medium"
                                style="background-color: #f2f1ec; color: #6b6257;"
                            >Gagal dicek</span>
                            <span
                                v-else
                                class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-medium"
                                style="background-color: #f4f4f4; color: #585856;"
                            >
                                <X class="h-3 w-3" />
                                Sudah terdaftar
                            </span>
                        </div>
                        <p v-if="h.perkiraan" class="mt-1 text-xs" style="color: var(--kpub-amber);">
                            Hasil perkiraan — ketersediaan belum dikonfirmasi registrar.
                        </p>
                        <p v-else-if="!punyaHarga(h.extension)" class="mt-1 text-xs" style="color: var(--kpub-muted);">
                            Ekstensi ini belum kami jual — tanyakan lewat WhatsApp.
                        </p>
                    </div>

                    <div class="flex items-center justify-between gap-6 sm:justify-end">
                        <div v-if="h.tersedia && punyaHarga(h.extension)" class="knum text-right">
                            <div class="text-base font-medium">{{ formatPrice(h.harga) }}</div>
                            <div class="text-xs" style="color: var(--kpub-muted);">perpanjangan {{ formatPrice(h.perpanjangan) }}</div>
                        </div>
                        <a
                            v-if="h.tersedia && waLink(h.fullDomain)"
                            :href="waLink(h.fullDomain)"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="kbtn kbtn-primary !px-4 !py-2 !text-xs"
                        >Pesan</a>
                        <span v-else-if="!h.tersedia" class="text-xs" style="color: var(--kpub-faint);">tidak bisa dipesan</span>
                    </div>
                </div>
            </div>

            <div v-else class="kcard mt-10 p-10 text-center">
                <h2 class="text-xl">Pengecekan tidak tersedia</h2>
                <p class="mt-2 text-sm" style="color: var(--kpub-muted);">
                    Layanan cek domain sedang tidak merespons. Coba lagi sebentar, atau hubungi kami lewat WhatsApp.
                </p>
            </div>

            <div class="mt-10 grid gap-x-8 gap-y-4 border-t pt-8 text-sm sm:grid-cols-2" style="border-color: var(--kpub-line); color: var(--kpub-muted);">
                <div>
                    <p class="font-medium" style="color: var(--kpub-ink);">Nama sudah dipakai?</p>
                    <p class="mt-1">Coba tambahkan kata di depan atau belakang, ubah akhiran, atau pakai ekstensi lain seperti .id dan .my.id.</p>
                </div>
                <div>
                    <p class="font-medium" style="color: var(--kpub-ink);">Butuh nama yang persis?</p>
                    <p class="mt-1">Domain premium bisa dibeli dari pemiliknya lewat jalur khusus. Hubungi kami kalau namanya penting untuk merek Anda.</p>
                </div>
            </div>

            <div class="mt-8 flex flex-col gap-2 sm:flex-row">
                <Link href="/domains" class="kbtn kbtn-outline">Lihat semua ekstensi</Link>
                <Link href="/hosting" class="kbtn kbtn-ghost">Lihat paket hosting</Link>
            </div>
        </section>
    </CustomerPublicLayout>
</template>
