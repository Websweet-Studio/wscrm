<script setup lang="ts">
import CustomerPublicLayout from '@/layouts/CustomerPublicLayout.vue';
import { formatPrice } from '@/lib/utils';
import { Link, router, usePage } from '@inertiajs/vue3';
import { Search } from 'lucide-vue-next';
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
    domainPrices: DomainPrice[];
    filters: {
        search?: string;
    };
}

const props = defineProps<Props>();
const page = usePage();

const search = ref(props.filters.search || '');
const domainSearch = ref('');

const filteredDomainPrices = computed(() => {
    let domains = props.domainPrices.filter((d) => d.is_active);
    if (search.value) {
        domains = domains.filter((d) => d.extension.toLowerCase().includes(search.value.toLowerCase()));
    }
    return domains;
});

const searchDomain = () => {
    if (domainSearch.value.trim()) {
        router.get('/domains/search', { domain: domainSearch.value.trim() });
    }
};

/** Ekstensi selalu satu titik di depan, apa pun format tersimpannya. */
const extLabel = (extension?: string) => {
    const ext = (extension || '').trim();
    if (ext === '') return '';
    return ext.startsWith('.') ? ext.toLowerCase() : `.${ext.toLowerCase()}`;
};

const contohPopuler = ['.com', '.id', '.my.id'];

const companyWhatsapp = computed(() => {
    return (page.props.brandingSettings as any)?.company_whatsapp || '';
});

const getWhatsappLink = (text: string) => {
    const raw = companyWhatsapp.value;
    if (!raw) return '';
    const phone = String(raw).replace(/[^\d]/g, '');
    return `https://wa.me/${phone}?text=${encodeURIComponent(text)}`;
};
</script>

<template>
    <CustomerPublicLayout title="Registrasi Domain & Cek Harga">
        <section class="border-b" style="border-color: var(--kpub-line);">
            <div class="kcontainer pt-16 pb-12 md:pt-20 md:pb-16">
                <div class="max-w-2xl">
                    <h1 class="text-4xl sm:text-5xl md:text-[3.25rem] md:leading-[1.08]">Cari nama, kami urus sisanya.</h1>
                    <p class="mt-4 text-base sm:text-lg" style="color: var(--kpub-muted);">
                        Ketik nama yang Anda inginkan, kami cek ketersediaannya di semua ekstensi sekaligus. Harga registrasi dan perpanjangan sama-sama tertera.
                    </p>
                </div>

                <form class="mt-8 flex max-w-2xl flex-col gap-2 sm:flex-row" @submit.prevent="searchDomain">
                    <label for="domain-search" class="sr-only">Nama domain</label>
                    <div class="relative flex-1">
                        <Search class="pointer-events-none absolute top-1/2 left-4 h-4 w-4 -translate-y-1/2" style="color: var(--kpub-faint);" />
                        <input
                            id="domain-search"
                            v-model="domainSearch"
                            type="text"
                            inputmode="url"
                            autocapitalize="none"
                            spellcheck="false"
                            placeholder="namawebsiteanda.com"
                            class="kfield kfield-icon"
                        />
                    </div>
                    <button type="submit" class="kbtn kbtn-primary shrink-0">Cek ketersediaan</button>
                </form>

                <div class="mt-3 flex flex-wrap items-center gap-2 text-sm" style="color: var(--kpub-muted);">
                    <span>Contoh:</span>
                    <button
                        v-for="ext in contohPopuler"
                        :key="ext"
                        type="button"
                        class="klink"
                        @click="domainSearch = `namabisnis${ext}`; searchDomain()"
                    >{{ ext }}</button>
                </div>
            </div>
        </section>

        <section class="kcontainer py-12 md:py-16">
            <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <h2 class="text-2xl sm:text-3xl">Daftar harga domain</h2>
                    <p class="knum mt-1 text-sm" style="color: var(--kpub-muted);">{{ filteredDomainPrices.length }} ekstensi · harga per tahun</p>
                </div>
                <div class="flex items-center gap-2">
                    <label for="ext-search" class="sr-only">Cari ekstensi</label>
                    <div class="relative">
                        <Search class="pointer-events-none absolute top-1/2 left-4 h-4 w-4 -translate-y-1/2" style="color: var(--kpub-faint);" />
                        <input id="ext-search" v-model="search" type="search" placeholder="Saring ekstensi" class="kfield kfield-icon w-56" />
                    </div>
                </div>
            </div>

            <div v-if="filteredDomainPrices.length" class="kcard overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[560px] border-collapse text-left">
                        <thead>
                            <tr class="border-b" style="border-color: var(--kpub-line);">
                                <th class="px-6 py-3.5 text-xs font-medium tracking-wide uppercase" style="color: var(--kpub-muted);">Ekstensi</th>
                                <th class="px-4 py-3.5 text-right text-xs font-medium tracking-wide uppercase" style="color: var(--kpub-muted);">Registrasi</th>
                                <th class="px-4 py-3.5 text-right text-xs font-medium tracking-wide uppercase" style="color: var(--kpub-muted);">Perpanjangan</th>
                                <th class="px-6 py-3.5" />
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="domain in filteredDomainPrices"
                                :key="domain.id"
                                class="border-b last:border-b-0"
                                style="border-color: var(--kpub-line);"
                            >
                                <td class="px-6 py-4">
                                    <div class="kdisplay text-lg">{{ extLabel(domain.extension) }}</div>
                                </td>
                                <td class="knum px-4 py-4 text-right text-sm font-medium">{{ formatPrice(domain.selling_price) }}</td>
                                <td class="knum px-4 py-4 text-right text-sm" style="color: var(--kpub-muted);">
                                    {{ formatPrice(domain.renewal_price_with_tax) }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a
                                        :href="getWhatsappLink(`Halo, saya ingin beli Domain ${extLabel(domain.extension)}.`)"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="kbtn kbtn-outline !px-4 !py-2 !text-xs"
                                    >Pesan</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-else class="kcard px-6 py-16 text-center">
                <h3 class="text-xl">Ekstensi tidak ditemukan</h3>
                <p class="mt-2 text-sm" style="color: var(--kpub-muted);">
                    {{ search ? 'Coba kata kunci lain, atau lihat seluruh daftar.' : 'Belum ada ekstensi domain yang aktif.' }}
                </p>
            </div>

            <!-- Catatan kebijakan: hal yang biasanya baru ketahuan setelah bayar -->
            <div class="mt-10 grid gap-x-8 gap-y-4 border-t pt-8 text-sm sm:grid-cols-3" style="border-color: var(--kpub-line); color: var(--kpub-muted);">
                <div>
                    <p class="font-medium" style="color: var(--kpub-ink);">Registrasi satu tahun</p>
                    <p class="mt-1">Harga registrasi berlaku untuk tahun pertama. Perpanjangan ditagih dengan tarif yang tertera di kolom kanan.</p>
                </div>
                <div>
                    <p class="font-medium" style="color: var(--kpub-ink);">Transfer &amp; perpanjangan</p>
                    <p class="mt-1">Domain dari registrar lain bisa dipindahkan tanpa downtime; masa aktif yang tersisa ikut berpindah.</p>
                </div>
                <div>
                    <p class="font-medium" style="color: var(--kpub-ink);">Tanpa biaya tersembunyi</p>
                    <p class="mt-1">Harga perpanjangan sudah termasuk pajak. Tidak ada biaya aktivasi atau pengaturan DNS.</p>
                </div>
            </div>
        </section>

        <section class="kcontainer pb-16 md:pb-24">
            <div class="overflow-hidden rounded-[25px] px-6 py-12 sm:px-12" style="background-color: var(--kpub-deep);">
                <div class="flex flex-col items-start justify-between gap-6 sm:flex-row sm:items-end">
                    <div class="max-w-lg">
                        <h2 class="text-2xl sm:text-3xl" style="color: #ffffff;">Sudah punya nama? Kami pasang hostingnya.</h2>
                        <p class="mt-3 text-sm" style="color: var(--kpub-deep-ink);">
                            Arahkan domain ke hosting kami dalam satu langkah, atau minta kami yang urus.
                        </p>
                    </div>
                    <div class="flex flex-col gap-2 sm:flex-row">
                        <Link href="/hosting" class="kbtn" style="background-color: #ffffff; color: var(--kpub-deep);">Lihat paket hosting</Link>
                        <Link href="/customer/register" class="kbtn" style="border: 1px solid rgba(255, 255, 255, 0.28); color: #ffffff;">Buat akun</Link>
                    </div>
                </div>
            </div>
        </section>
    </CustomerPublicLayout>
</template>
