<script setup lang="ts">
import CustomerPublicLayout from '@/layouts/CustomerPublicLayout.vue';
import { router, usePage } from '@inertiajs/vue3';
import { Check, ChevronLeft, ChevronRight, Copy, ExternalLink, LayoutGrid, Monitor, Palette, Search, Terminal } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

interface DemoItem {
    id: number;
    title: string;
    url: string;
    category: string | null;
    category_slug: string | null;
    packages: { id: number; name: string; slug: string }[];
    featured_image: string | null;
    description: string | null;
}

interface CategoryItem {
    id: number;
    name: string;
    slug: string;
}

interface PackageItem {
    id: number;
    name: string;
    slug: string;
}

interface Props {
    demos: {
        data: DemoItem[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number;
        to: number;
        prev_page_url: string | null;
        next_page_url: string | null;
    };
    categories: CategoryItem[];
    packages: PackageItem[];
    filters: {
        search?: string;
        category?: string;
        package?: string;
    };
}

const props = defineProps<Props>();

const search = ref(props.filters?.search || '');
const selectedCategory = ref(props.filters?.category || null);
const selectedPackage = ref(props.filters?.package || null);

const hasActiveFilters = () => Boolean(search.value || selectedCategory.value || selectedPackage.value);

const applyFilters = (page?: number) => {
    router.get(
        '/demo-web',
        {
            search: search.value || undefined,
            category: selectedCategory.value || undefined,
            package: selectedPackage.value || undefined,
            page: page || 1,
        },
        { preserveState: true, preserveScroll: true },
    );
};

const clearFilters = () => {
    search.value = '';
    selectedCategory.value = null;
    selectedPackage.value = null;
    router.get('/demo-web', {}, { preserveState: true, preserveScroll: true });
};

const goToPage = (url: string | null) => {
    if (!url) return;
    router.visit(url, { preserveState: true, preserveScroll: true });
};

// Debounced search
let searchTimeout: ReturnType<typeof setTimeout> | null = null;
watch(search, () => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters(1), 400);
});

watch(selectedCategory, () => applyFilters(1));
watch(selectedPackage, () => applyFilters(1));

const page = usePage();
const companyWhatsapp = computed(() => (page.props.brandingSettings as any)?.company_whatsapp || '6281234567890');

const appUrl = window.location.origin;
const copiedId = ref<string | null>(null);
const activeTab = ref<'embed' | 'api'>('embed');

// Embed configurator
const embedCategory = ref<string | null>(null);
const embedPerPage = ref(6);
const embedPrimary = ref(document.documentElement.style.getPropertyValue('--primary').trim() || '#0a6c4d');
const embedWhatsapp = ref('');

const jsEmbedCode = computed(() => {
    const categoryAttr = embedCategory.value ? `\n  data-category="${embedCategory.value}"` : '';
    const whatsappAttr = embedWhatsapp.value ? `\n  data-whatsapp="${embedWhatsapp.value}"` : '';
    return `<div id="wss-demo-widget"${categoryAttr}${whatsappAttr}
  data-per-page="${embedPerPage.value}"
  data-primary="${embedPrimary.value}">
</div>
<script src="${appUrl}/demo-web/embed.js"><\/script>`;
});

const singleEmbedIframeCode = (demo: DemoItem) =>
    `<iframe src="${appUrl}/demo-web/embed/${demo.id}" width="800" height="600" frameborder="0" allowfullscreen style="max-width:100%;border:1px solid #e6e8e6;border-radius:14px;overflow:hidden;"></iframe>`;

const previewPage = ref(1);

const previewFilteredDemos = computed(() => {
    const all = props.demos?.data || [];
    if (!embedCategory.value) return all;
    return all.filter((d) => d.category_slug === embedCategory.value);
});

const previewTotalPages = computed(() => Math.ceil(previewFilteredDemos.value.length / embedPerPage.value));

const previewDemos = computed(() => {
    const start = (previewPage.value - 1) * embedPerPage.value;
    return previewFilteredDemos.value.slice(start, start + embedPerPage.value);
});

watch([embedCategory, embedPerPage], () => {
    previewPage.value = 1;
});

const copyToClipboard = async (text: string, id: string) => {
    try {
        await navigator.clipboard.writeText(text);
    } catch {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
    }
    copiedId.value = id;
    setTimeout(() => (copiedId.value = null), 2000);
};

/** Nomor halaman yang ditampilkan: ujung + sekitar halaman aktif. */
const nomorHalaman = computed(() =>
    Array.from({ length: props.demos.last_page }, (_, i) => i + 1).filter(
        (p) => props.demos.last_page <= 7 || p === 1 || p === props.demos.last_page || Math.abs(p - props.demos.current_page) <= 1,
    ),
);

const warnaAksen = ['#0a6c4d', '#2563eb', '#059669', '#7c3aed', '#db2777', '#ea580c', '#0891b2', '#4d4c48'];
</script>

<template>
    <CustomerPublicLayout title="Demo Website & Template">
        <section class="kcontainer pt-16 pb-12 md:pt-20">
            <div class="max-w-2xl">
                <h1 class="text-4xl sm:text-5xl md:text-[3.25rem] md:leading-[1.08]">Contoh nyata, bukan tangkapan layar.</h1>
                <p class="mt-4 text-base sm:text-lg" style="color: var(--kpub-muted);">
                    Setiap demo bisa dibuka langsung. Kalau ada yang cocok, kami pasang versi Anda dengan nama dan isi sendiri.
                </p>
            </div>

            <!-- Filter: pencarian tunggal, sisanya dropdown -->
            <div class="mt-8 flex flex-col gap-3 border-y py-4 md:flex-row md:items-center" style="border-color: var(--kpub-line);">
                <div class="relative flex-1">
                    <Search class="pointer-events-none absolute top-1/2 left-4 h-4 w-4 -translate-y-1/2" style="color: var(--kpub-faint);" />
                    <label for="demo-search" class="sr-only">Cari demo</label>
                    <input id="demo-search" v-model="search" type="search" placeholder="Cari demo website" class="kfield kfield-icon" />
                </div>

                <div v-if="categories.length" class="flex items-center gap-2">
                    <label for="demo-category" class="sr-only">Kategori</label>
                    <select id="demo-category" v-model="selectedCategory" class="kfield !rounded-full !py-2.5 md:w-48">
                        <option :value="null">Semua kategori</option>
                        <option v-for="cat in categories" :key="cat.id" :value="cat.slug">{{ cat.name }}</option>
                    </select>
                </div>

                <div v-if="packages.length" class="flex items-center gap-2">
                    <label for="demo-package" class="sr-only">Paket</label>
                    <select id="demo-package" v-model="selectedPackage" class="kfield !rounded-full !py-2.5 md:w-40">
                        <option :value="null">Semua paket</option>
                        <option v-for="pkg in packages" :key="pkg.id" :value="pkg.slug">{{ pkg.name }}</option>
                    </select>
                </div>

                <button v-if="hasActiveFilters()" type="button" class="kbtn kbtn-ghost !px-3" @click="clearFilters">Reset</button>
            </div>

            <p v-if="demos.total > 0" class="knum mt-4 text-sm" style="color: var(--kpub-muted);">
                Menampilkan {{ demos.from }}–{{ demos.to }} dari {{ demos.total }} demo
            </p>

            <!-- Grid demo -->
            <div v-if="demos.data.length > 0" class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                <article v-for="demo in demos.data" :key="demo.id" class="kcard group flex flex-col overflow-hidden">
                    <div class="aspect-video overflow-hidden" style="background-color: var(--kpub-surface);">
                        <img v-if="demo.featured_image" :src="demo.featured_image" :alt="demo.title" class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-[1.02]" loading="lazy" />
                        <div v-else class="flex h-full w-full items-center justify-center">
                            <Monitor class="h-8 w-8" style="color: var(--kpub-faint);" />
                        </div>
                    </div>
                    <div class="flex flex-1 flex-col p-5">
                        <div class="flex items-center justify-between gap-2">
                            <span v-if="demo.category" class="text-xs" style="color: var(--kpub-muted);">{{ demo.category }}</span>
                            <div class="flex flex-wrap gap-1">
                                <span
                                    v-for="pkg in demo.packages"
                                    :key="pkg.id"
                                    class="rounded-full px-2 py-0.5 text-[10px]"
                                    style="border: 1px solid var(--kpub-line); color: var(--kpub-muted);"
                                >{{ pkg.name }}</span>
                            </div>
                        </div>
                        <h2 class="mt-1.5 text-lg">
                            <a :href="demo.url" target="_blank" rel="noopener noreferrer" class="transition-colors hover:opacity-70">{{ demo.title }}</a>
                        </h2>
                        <p v-if="demo.description" class="mt-1 line-clamp-2 flex-1 text-sm" style="color: var(--kpub-muted);">{{ demo.description }}</p>
                        <div class="mt-4 flex items-center gap-2">
                            <a :href="demo.url" target="_blank" rel="noopener noreferrer" class="kbtn kbtn-primary !px-4 !py-2 !text-xs">
                                Buka demo
                                <ExternalLink class="h-3 w-3" />
                            </a>
                            <button
                                type="button"
                                class="kbtn kbtn-outline !px-3 !py-2"
                                :aria-label="`Salin kode embed ${demo.title}`"
                                @click="copyToClipboard(singleEmbedIframeCode(demo), 'card-' + demo.id)"
                            >
                                <Check v-if="copiedId === 'card-' + demo.id" class="h-3.5 w-3.5" style="color: var(--kpub-green);" />
                                <Copy v-else class="h-3.5 w-3.5" />
                            </button>
                        </div>
                    </div>
                </article>
            </div>

            <div v-else class="kcard mt-6 px-6 py-16 text-center">
                <h2 class="text-xl">Tidak ada demo yang cocok</h2>
                <p class="mt-2 text-sm" style="color: var(--kpub-muted);">
                    {{ hasActiveFilters() ? 'Coba longgarkan filter, atau lihat semuanya.' : 'Belum ada demo yang tayang.' }}
                </p>
                <button v-if="hasActiveFilters()" type="button" class="kbtn kbtn-outline mt-5" @click="clearFilters">Reset filter</button>
            </div>

            <!-- Paginasi -->
            <nav v-if="demos.last_page > 1" class="mt-8 flex items-center justify-center gap-1.5" aria-label="Halaman demo">
                <button type="button" class="kbtn kbtn-outline !px-3 !py-2" :disabled="!demos.prev_page_url" :style="!demos.prev_page_url ? 'opacity:.4' : ''" @click="goToPage(demos.prev_page_url)">
                    <ChevronLeft class="h-4 w-4" />
                    <span class="sr-only">Sebelumnya</span>
                </button>
                <template v-for="(p, i) in nomorHalaman" :key="p">
                    <span v-if="i > 0 && p - nomorHalaman[i - 1] > 1" class="px-1" style="color: var(--kpub-faint);">…</span>
                    <button
                        type="button"
                        class="knum inline-flex min-w-9 items-center justify-center rounded-full px-3 py-2 text-sm"
                        :style="
                            p === demos.current_page
                                ? 'background-color: var(--kpub-ink); color: #ffffff;'
                                : 'border: 1px solid var(--kpub-line); color: var(--kpub-ink);'
                        "
                        @click="applyFilters(p)"
                    >{{ p }}</button>
                </template>
                <button type="button" class="kbtn kbtn-outline !px-3 !py-2" :disabled="!demos.next_page_url" :style="!demos.next_page_url ? 'opacity:.4' : ''" @click="goToPage(demos.next_page_url)">
                    <ChevronRight class="h-4 w-4" />
                    <span class="sr-only">Selanjutnya</span>
                </button>
            </nav>
        </section>

        <!-- Embed & API: alat untuk developer yang ingin menampilkan galeri ini di situs sendiri -->
        <section class="border-t py-16 md:py-20" style="border-color: var(--kpub-line); background-color: var(--kpub-surface);">
            <div class="kcontainer">
                <div class="max-w-2xl">
                    <h2 class="text-2xl sm:text-3xl">Pasang galeri ini di website Anda</h2>
                    <p class="mt-2 text-sm" style="color: var(--kpub-muted);">
                        Satu baris skrip untuk embed, atau REST API kalau Anda ingin tampilan sendiri.
                    </p>
                </div>

                <div class="mt-6 inline-flex rounded-full p-1" style="background-color: #ffffff; border: 1px solid var(--kpub-line);">
                    <button
                        v-for="tab in [{ key: 'embed', label: 'Embed', icon: Monitor }, { key: 'api', label: 'REST API', icon: Terminal }]"
                        :key="tab.key"
                        type="button"
                        class="inline-flex items-center gap-1.5 rounded-full px-4 py-1.5 text-sm transition-colors"
                        :style="activeTab === tab.key ? 'background-color: var(--kpub-ink); color: #ffffff;' : 'color: var(--kpub-muted);'"
                        @click="activeTab = tab.key as 'embed' | 'api'"
                    >
                        <component :is="tab.icon" class="h-4 w-4" />
                        {{ tab.label }}
                    </button>
                </div>

                <!-- Embed -->
                <div v-if="activeTab === 'embed'" class="mt-6 grid gap-4 lg:grid-cols-2">
                    <div class="space-y-3">
                        <div class="kcard bg-white p-5" style="background-color: #ffffff;">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="embed-category" class="text-xs font-medium" style="color: var(--kpub-muted);">Kategori</label>
                                    <select id="embed-category" v-model="embedCategory" class="kfield mt-1.5 !rounded-[10px]">
                                        <option :value="null">Semua kategori</option>
                                        <option v-for="cat in categories" :key="cat.id" :value="cat.slug">{{ cat.name }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="embed-per-page" class="text-xs font-medium" style="color: var(--kpub-muted);">Demo per halaman</label>
                                    <input id="embed-per-page" v-model.number="embedPerPage" type="range" min="3" max="12" step="3" class="mt-3 w-full" style="accent-color: var(--kpub-green);" />
                                    <p class="knum text-xs" style="color: var(--kpub-faint);">{{ embedPerPage }} per halaman</p>
                                </div>
                            </div>

                            <div class="mt-4">
                                <span class="text-xs font-medium" style="color: var(--kpub-muted);">Warna aksen</span>
                                <div class="mt-2 flex flex-wrap items-center gap-2">
                                    <button
                                        v-for="color in warnaAksen"
                                        :key="color"
                                        type="button"
                                        class="h-6 w-6 rounded-full transition-transform hover:scale-110"
                                        :style="{
                                            backgroundColor: color,
                                            boxShadow: embedPrimary === color ? '0 0 0 2px #ffffff, 0 0 0 4px var(--kpub-ink)' : 'none',
                                        }"
                                        :aria-label="`Pilih warna ${color}`"
                                        @click="embedPrimary = color"
                                    />
                                    <input v-model="embedPrimary" type="text" class="kfield !w-24 !rounded-[10px] !py-1 !text-xs" aria-label="Kode warna heksadesimal" />
                                </div>
                            </div>

                            <div class="mt-4">
                                <label for="embed-wa" class="text-xs font-medium" style="color: var(--kpub-muted);">Nomor WhatsApp tombol pesan</label>
                                <input id="embed-wa" v-model="embedWhatsapp" type="text" placeholder="081234567890" class="kfield mt-1.5 !rounded-[10px]" />
                            </div>
                        </div>

                        <div class="kcard overflow-hidden" style="background-color: #ffffff;">
                            <div class="flex items-center justify-between border-b px-5 py-3" style="border-color: var(--kpub-line);">
                                <h3 class="text-sm font-medium">Kode embed</h3>
                                <div class="flex gap-2">
                                    <button type="button" class="kbtn kbtn-primary !px-3 !py-1.5 !text-xs" @click="copyToClipboard(jsEmbedCode, 'js-embed')">
                                        <component :is="copiedId === 'js-embed' ? Check : Copy" class="h-3 w-3" />
                                        {{ copiedId === 'js-embed' ? 'Tersalin' : 'Salin' }}
                                    </button>
                                    <button type="button" class="kbtn kbtn-outline !px-3 !py-1.5 !text-xs" @click="copyToClipboard(appUrl + '/demo-web/embed.js', 'js-url')">
                                        <component :is="copiedId === 'js-url' ? Check : Copy" class="h-3 w-3" />
                                        URL skrip
                                    </button>
                                </div>
                            </div>
                            <pre class="overflow-x-auto px-5 py-4 text-xs leading-relaxed" style="background-color: #0d1210; color: #cfd6d2;"><code>{{ jsEmbedCode }}</code></pre>
                        </div>

                        <div class="kcard p-5" style="background-color: #ffffff;">
                            <h3 class="text-sm font-medium">Opsi data-attribute</h3>
                            <dl class="mt-3 space-y-1.5 text-xs" style="color: var(--kpub-muted);">
                                <div v-for="opt in [
                                    ['data-category', 'Filter kategori (slug)'],
                                    ['data-per-page', 'Demo per halaman (default 6)'],
                                    ['data-primary', 'Warna aksen (hex)'],
                                    ['data-bg', 'Warna latar'],
                                    ['data-card-bg', 'Warna latar kartu'],
                                    ['data-text', 'Warna teks utama'],
                                    ['data-text-secondary', 'Warna teks sekunder'],
                                    ['data-border', 'Warna garis'],
                                    ['data-whatsapp', 'Nomor WhatsApp tombol pesan'],
                                ]" :key="opt[0]" class="flex gap-2">
                                    <dt><code class="rounded px-1 py-0.5" style="background-color: var(--kpub-surface); color: var(--kpub-ink);">{{ opt[0] }}</code></dt>
                                    <dd>{{ opt[1] }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Preview -->
                    <div class="lg:sticky lg:top-20 lg:self-start">
                        <div class="kcard overflow-hidden" style="background-color: #ffffff;">
                            <div class="flex items-center justify-between border-b px-4 py-2.5" style="border-color: var(--kpub-line);">
                                <h3 class="text-sm font-medium">Pratinjau</h3>
                                <div class="flex gap-1.5">
                                    <span class="h-2.5 w-2.5 rounded-full" style="background-color: #f87171"></span>
                                    <span class="h-2.5 w-2.5 rounded-full" style="background-color: #fbbf24"></span>
                                    <span class="h-2.5 w-2.5 rounded-full" style="background-color: #34d399"></span>
                                </div>
                            </div>
                            <div class="max-h-[520px] overflow-y-auto p-4">
                                <div class="grid gap-3" style="grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));">
                                    <div
                                        v-for="demo in previewDemos"
                                        :key="'preview-' + demo.id"
                                        class="overflow-hidden rounded-[10px]"
                                        style="border: 1px solid var(--kpub-line);"
                                    >
                                        <div class="aspect-video" style="background-color: var(--kpub-surface);">
                                            <img v-if="demo.featured_image" :src="demo.featured_image" :alt="demo.title" class="h-full w-full object-cover" loading="lazy" />
                                            <div v-else class="flex h-full w-full items-center justify-center">
                                                <Monitor class="h-6 w-6" style="color: var(--kpub-faint);" />
                                            </div>
                                        </div>
                                        <div class="p-2.5">
                                            <h4 class="truncate text-xs font-medium">{{ demo.title }}</h4>
                                            <a
                                                :href="demo.url"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="mt-1.5 flex w-full items-center justify-center rounded-md px-2 py-1.5 text-[10px] font-medium text-white"
                                                :style="{ backgroundColor: embedPrimary }"
                                            >Buka demo</a>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="previewTotalPages > 1" class="mt-4 flex items-center justify-center gap-1">
                                    <button type="button" class="rounded-md px-2 py-1 text-[11px]" style="border: 1px solid var(--kpub-line);" :disabled="previewPage <= 1" :class="{ 'opacity-40': previewPage <= 1 }" @click="previewPage--">‹</button>
                                    <button
                                        v-for="p in previewTotalPages"
                                        :key="'pp-' + p"
                                        type="button"
                                        class="knum rounded-md px-2 py-1 text-[11px]"
                                        :style="p === previewPage ? `background-color: ${embedPrimary}; color: #ffffff;` : 'border: 1px solid var(--kpub-line);'"
                                        @click="previewPage = p"
                                    >{{ p }}</button>
                                    <button type="button" class="rounded-md px-2 py-1 text-[11px]" style="border: 1px solid var(--kpub-line);" :disabled="previewPage >= previewTotalPages" :class="{ 'opacity-40': previewPage >= previewTotalPages }" @click="previewPage++">›</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- REST API -->
                <div v-else class="mt-6 grid gap-4 lg:grid-cols-2">
                    <div class="kcard p-5" style="background-color: #ffffff;">
                        <div class="flex items-center gap-2">
                            <span class="rounded px-2 py-0.5 text-[10px] font-medium text-white" style="background-color: #0a6c4d;">GET</span>
                            <code class="text-sm" style="color: var(--kpub-ink);">{{ appUrl }}/api/demos</code>
                        </div>
                        <p class="mt-3 text-xs" style="color: var(--kpub-muted);">Semua demo aktif beserta kategori dan paketnya.</p>
                        <p class="mt-3 text-xs font-medium">Parameter</p>
                        <div class="mt-1.5 space-y-1 text-xs" style="color: var(--kpub-muted);">
                            <p><code style="color: var(--kpub-ink);">category</code> — slug kategori</p>
                            <p><code style="color: var(--kpub-ink);">package</code> — slug paket</p>
                        </div>
                        <pre class="mt-3 overflow-x-auto rounded-[10px] p-3 text-xs leading-relaxed" style="background-color: #0d1210; color: #cfd6d2;"><code>curl {{ appUrl }}/api/demos

curl "{{ appUrl }}/api/demos?category=profil-perusahaan"

curl "{{ appUrl }}/api/demos?package=instant"</code></pre>
                    </div>

                    <div class="kcard p-5" style="background-color: #ffffff;">
                        <div class="flex items-center gap-2">
                            <span class="rounded px-2 py-0.5 text-[10px] font-medium text-white" style="background-color: #0a6c4d;">GET</span>
                            <code class="text-sm" style="color: var(--kpub-ink);">{{ appUrl }}/api/demos/{id}</code>
                        </div>
                        <p class="mt-3 text-xs" style="color: var(--kpub-muted);">Detail satu demo berdasarkan ID.</p>
                        <p class="mt-3 text-xs font-medium">Contoh respons</p>
                        <pre class="mt-1.5 max-h-[280px] overflow-y-auto rounded-[10px] p-3 text-[11px] leading-relaxed" style="background-color: #0d1210; color: #cfd6d2;"><code>{{ JSON.stringify({
    id: 1,
    title: 'Nama Demo',
    url: 'https://demo.sweet.web.id',
    category: 'Profil Perusahaan',
    category_slug: 'profil-perusahaan',
    packages: [{ id: 1, name: 'Instant', slug: 'instant' }],
    featured_image: 'https://...',
    description: 'Deskripsi demo...'
}, null, 2) }}</code></pre>
                    </div>
                </div>
            </div>
        </section>

        <section class="kcontainer py-16 md:py-24">
            <div class="overflow-hidden rounded-[25px] px-6 py-12 sm:px-12" style="background-color: var(--kpub-deep);">
                <div class="flex flex-col items-start justify-between gap-6 sm:flex-row sm:items-end">
                    <div class="max-w-lg">
                        <h2 class="text-2xl sm:text-3xl" style="color: #ffffff;">Suka salah satu demo?</h2>
                        <p class="mt-3 text-sm" style="color: var(--kpub-deep-ink);">
                            Sebutkan nama demonya, kami buat versi Anda dengan logo, warna, dan isi sendiri.
                        </p>
                    </div>
                    <div class="flex flex-col gap-2 sm:flex-row">
                        <a
                            :href="`https://wa.me/${companyWhatsapp}?text=${encodeURIComponent('Halo, saya tertarik dengan demo website di WebSweetStudio!')}`"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="kbtn"
                            style="background-color: #ffffff; color: var(--kpub-deep);"
                        >Chat WhatsApp</a>
                        <a href="/hosting" class="kbtn" style="border: 1px solid rgba(255, 255, 255, 0.28); color: #ffffff;">Lihat paket hosting</a>
                    </div>
                </div>
            </div>
        </section>
    </CustomerPublicLayout>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
