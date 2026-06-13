<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import CustomerPublicLayout from '@/layouts/CustomerPublicLayout.vue';
import { ExternalLink, Filter, LayoutGrid, Monitor, Search, Code, Check, Copy, Palette } from 'lucide-vue-next';
import { router, usePage } from '@inertiajs/vue3';
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

const hasActiveFilters = () => {
    return search.value || selectedCategory.value || selectedPackage.value;
};

const applyFilters = (page?: number) => {
    router.get(
        '/demo-web',
        {
            search: search.value || undefined,
            category: selectedCategory.value || undefined,
            package: selectedPackage.value || undefined,
            page: page || 1,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
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
    searchTimeout = setTimeout(() => {
        applyFilters(1);
    }, 400);
});

watch(selectedCategory, () => {
    applyFilters(1);
});

watch(selectedPackage, () => {
    applyFilters(1);
});

const page = usePage();
const companyWhatsapp = computed(() => (page.props.brandingSettings as any)?.company_whatsapp || '6281234567890');

const appUrl = window.location.origin;
const copiedId = ref<string | null>(null);

// JS Embed Configurator
const embedCategory = ref<string | null>(null);
const embedPerPage = ref(6);
const embedPrimary = ref('#c96442');
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

const singleEmbedIframeCode = (demo: DemoItem) => {
    const embedUrl = `${appUrl}/demo-web/embed/${demo.id}`;
    return `<iframe src="${embedUrl}" width="800" height="600" frameborder="0" allowfullscreen style="max-width:100%;border:1px solid #e8e6dc;border-radius:12px;overflow:hidden;"></iframe>`;
};

// Preview pagination
const previewPage = ref(1);

const previewFilteredDemos = computed(() => {
    const all = props.demos?.data || [];
    if (!embedCategory.value) return all;
    return all.filter(d => d.category_slug === embedCategory.value);
});

const previewTotalPages = computed(() => {
    return Math.ceil(previewFilteredDemos.value.length / embedPerPage.value);
});

const previewDemos = computed(() => {
    const start = (previewPage.value - 1) * embedPerPage.value;
    return previewFilteredDemos.value.slice(start, start + embedPerPage.value);
});

// Reset preview page when settings change
watch([embedCategory, embedPerPage], () => {
    previewPage.value = 1;
});

const copyToClipboard = async (text: string, id: string) => {
    try {
        await navigator.clipboard.writeText(text);
        copiedId.value = id;
        setTimeout(() => { copiedId.value = null; }, 2000);
    } catch {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        copiedId.value = id;
        setTimeout(() => { copiedId.value = null; }, 2000);
    }
};
</script>

<template>
    <CustomerPublicLayout title="Demo Website - Template & Contoh Website">
        <section class="mx-auto max-w-6xl px-4 py-12 sm:px-6 sm:py-16 lg:py-20">
            <!-- Hero -->
            <div class="mb-12 text-center sm:mb-16">
                <h1
                    class="mb-4 text-4xl leading-tight font-medium sm:text-5xl md:text-6xl"
                    style="color: #141413; line-height: 1.1; font-family: Georgia, serif"
                >
                    Demo Website
                </h1>
                <p class="mx-auto mb-6 max-w-2xl text-base sm:text-lg lg:text-xl" style="color: #5e5d59; line-height: 1.6">
                    Lihat contoh website yang bisa Anda miliki. Pilih template favorit dan hubungi kami untuk memulai.
                </p>
            </div>

            <!-- Search & Filters -->
            <div class="mb-8">
                <Card style="background-color: #faf9f5; border: 1px solid #f0eee6; border-radius: 16px; box-shadow: rgba(0, 0, 0, 0.05) 0px 4px 24px">
                    <CardContent class="pt-6">
                        <div class="flex flex-col gap-4 md:flex-row md:items-center">
                            <!-- Search -->
                            <div class="relative flex-1">
                                <Search class="absolute top-2.5 left-3 h-5 w-5" style="color: #87867f" />
                                <input
                                    v-model="search"
                                    type="text"
                                    placeholder="Cari demo website..."
                                    class="w-full rounded-xl border py-2.5 pl-10 pr-4 text-sm focus:outline-none focus:ring-2"
                                    style="background-color: #ffffff; border-color: #e8e6dc; color: #141413"
                                />
                            </div>

                            <!-- Category Filter -->
                            <div v-if="categories.length > 0" class="flex items-center gap-2">
                                <Filter class="h-4 w-4" style="color: #87867f" />
                                <select
                                    v-model="selectedCategory"
                                    class="rounded-xl border py-2.5 px-3 text-sm focus:outline-none focus:ring-2"
                                    style="background-color: #ffffff; border-color: #e8e6dc; color: #141413"
                                >
                                    <option :value="null">Semua Kategori</option>
                                    <option v-for="cat in categories" :key="cat.id" :value="cat.slug">{{ cat.name }}</option>
                                </select>
                            </div>

                            <!-- Package Filter -->
                            <div v-if="packages.length > 0" class="flex items-center gap-2">
                                <LayoutGrid class="h-4 w-4" style="color: #87867f" />
                                <select
                                    v-model="selectedPackage"
                                    class="rounded-xl border py-2.5 px-3 text-sm focus:outline-none focus:ring-2"
                                    style="background-color: #ffffff; border-color: #e8e6dc; color: #141413"
                                >
                                    <option :value="null">Semua Paket</option>
                                    <option v-for="pkg in packages" :key="pkg.id" :value="pkg.slug">{{ pkg.name }}</option>
                                </select>
                            </div>

                            <!-- Clear -->
                            <Button v-if="hasActiveFilters()" variant="ghost" size="sm" @click="clearFilters" style="color: #c96442">
                                Reset Filter
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Results info -->
            <div v-if="demos.total > 0" class="mb-6 flex flex-wrap items-center justify-between gap-2 text-sm" style="color: #5e5d59">
                <span>
                    Menampilkan {{ demos.from }}-{{ demos.to }} dari {{ demos.total }} demo
                </span>
            </div>

            <!-- Demo Grid -->
            <div v-if="demos.data.length > 0" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <Card
                    v-for="demo in demos.data"
                    :key="demo.id"
                    class="overflow-hidden transition-all hover:shadow-lg"
                    style="background-color: #ffffff; border: 1px solid #f0eee6; border-radius: 16px"
                >
                    <!-- Image -->
                    <div class="relative aspect-video w-full overflow-hidden" style="background-color: #e8e6dc">
                        <img
                            v-if="demo.featured_image"
                            :src="demo.featured_image"
                            :alt="demo.title"
                            class="h-full w-full object-cover"
                        />
                        <div v-else class="flex h-full w-full items-center justify-center">
                            <Monitor class="h-12 w-12" style="color: #b0aea5" />
                        </div>
                        <!-- Category Badge -->
                        <div v-if="demo.category" class="absolute top-3 left-3">
                            <Badge style="background-color: #c96442; color: #faf9f5; border-radius: 8px">
                                {{ demo.category }}
                            </Badge>
                        </div>
                    </div>

                    <CardContent class="pt-4 space-y-3">
                        <h3 class="text-lg font-medium" style="color: #141413; font-family: Georgia, serif">
                            {{ demo.title }}
                        </h3>

                        <p v-if="demo.description" class="line-clamp-2 text-sm" style="color: #5e5d59">
                            {{ demo.description }}
                        </p>

                        <!-- Packages -->
                        <div v-if="demo.packages.length > 0" class="flex flex-wrap gap-1.5">
                            <Badge
                                v-for="pkg in demo.packages"
                                :key="pkg.id"
                                variant="outline"
                                style="border-color: #e8e6dc; color: #4d4c48; border-radius: 6px"
                            >
                                {{ pkg.name }}
                            </Badge>
                        </div>

                        <!-- Action -->
                        <div class="flex gap-2">
                            <Button
                                asChild
                                class="flex-1"
                                style="background-color: #c96442; color: #faf9f5; border-radius: 12px"
                            >
                                <a :href="demo.url" target="_blank" rel="noopener noreferrer">
                                    <ExternalLink class="mr-2 h-4 w-4" />
                                    Lihat Demo
                                </a>
                            </Button>
                            <Button
                                variant="outline"
                                size="sm"
                                class="flex-shrink-0"
                                style="border-color: #e8e6dc; color: #4d4c48; border-radius: 12px"
                                @click="copyToClipboard(singleEmbedIframeCode(demo), 'card-' + demo.id)"
                            >
                                <component :is="copiedId === 'card-' + demo.id ? Check : Copy" class="h-4 w-4" />
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Empty State -->
            <div v-else class="py-16 text-center">
                <Monitor class="mx-auto mb-4 h-16 w-16" style="color: #b0aea5" />
                <h3 class="mb-2 text-xl font-medium" style="color: #141413; font-family: Georgia, serif">
                    Tidak ada demo ditemukan
                </h3>
                <p class="mb-4" style="color: #5e5d59">
                    {{ hasActiveFilters() ? 'Coba sesuaikan filter pencarian Anda.' : 'Belum ada demo website yang tersedia saat ini.' }}
                </p>
                <Button v-if="hasActiveFilters()" variant="outline" @click="clearFilters" style="border-color: #e8e6dc; color: #4d4c48">
                    Reset Filter
                </Button>
            </div>

            <!-- Pagination -->
            <div v-if="demos.last_page > 1" class="mt-8 flex items-center justify-center gap-2">
                <Button
                    :disabled="!demos.prev_page_url"
                    variant="outline"
                    size="sm"
                    @click="goToPage(demos.prev_page_url)"
                    style="border-color: #e8e6dc; color: #4d4c48"
                >
                    Sebelumnya
                </Button>

                <div class="flex items-center gap-1">
                    <template v-for="page in demos.last_page" :key="page">
                        <Button
                            v-if="demos.last_page <= 7 || page === 1 || page === demos.last_page || Math.abs(page - demos.current_page) <= 1"
                            :variant="page === demos.current_page ? 'default' : 'outline'"
                            size="sm"
                            class="min-w-[36px]"
                            :style="page === demos.current_page ? 'background-color: #c96442; color: #faf9f5; border-radius: 8px' : 'border-color: #e8e6dc; color: #4d4c48'"
                            @click="applyFilters(page)"
                        >
                            {{ page }}
                        </Button>
                        <span
                            v-else-if="Math.abs(page - demos.current_page) === 2"
                            class="px-1"
                            style="color: #87867f"
                        >
                            ...
                        </span>
                    </template>
                </div>

                <Button
                    :disabled="!demos.next_page_url"
                    variant="outline"
                    size="sm"
                    @click="goToPage(demos.next_page_url)"
                    style="border-color: #e8e6dc; color: #4d4c48"
                >
                    Selanjutnya
                </Button>
            </div>

            <!-- Embed Code Section - JS Widget -->
            <div class="mt-12">
                <Card style="background-color: #faf9f5; border: 1px solid #f0eee6; border-radius: 16px; box-shadow: rgba(0, 0, 0, 0.05) 0px 4px 24px">
                    <CardContent class="pt-6">
                        <div class="mb-6 flex items-center gap-3">
                            <div class="rounded-full p-2" style="background-color: #e8e6dc">
                                <Code class="h-5 w-5" style="color: #c96442" />
                            </div>
                            <div>
                                <h3 class="text-lg font-medium" style="color: #141413; font-family: Georgia, serif">Embed di Website Anda</h3>
                            </div>
                        </div>

                        <div class="grid gap-6 lg:grid-cols-2">
                            <!-- Left: Configurator -->
                            <div class="space-y-4">
                                <div class="rounded-xl p-4" style="background-color: #ffffff; border: 1px solid #f0eee6">

                                    <!-- Category Filter -->
                                    <div class="mb-4">
                                        <label class="mb-1.5 flex items-center gap-2 text-xs font-medium" style="color: #4d4c48">
                                            <Filter class="h-3.5 w-3.5" style="color: #87867f" />
                                            Kategori
                                        </label>
                                        <select
                                            v-model="embedCategory"
                                            class="w-full rounded-lg border px-3 py-2 text-sm"
                                            style="background-color: #faf9f5; border-color: #e8e6dc; color: #141413"
                                        >
                                            <option :value="null">Semua Kategori</option>
                                            <option v-for="cat in categories" :key="cat.id" :value="cat.slug">{{ cat.name }}</option>
                                        </select>
                                    </div>

                                    <!-- Per Page -->
                                    <div class="mb-4">
                                        <label class="mb-1.5 flex items-center gap-2 text-xs font-medium" style="color: #4d4c48">
                                            <LayoutGrid class="h-3.5 w-3.5" style="color: #87867f" />
                                            Demo Per Halaman (Pagination)
                                        </label>
                                        <input
                                            v-model.number="embedPerPage"
                                            type="range"
                                            min="3"
                                            max="12"
                                            step="3"
                                            class="w-full accent-[#c96442]"
                                        />
                                        <div class="mt-1 flex justify-between text-xs" style="color: #87867f">
                                            <span>3</span>
                                            <span class="font-medium" style="color: #141413">{{ embedPerPage }} per halaman</span>
                                            <span>12</span>
                                        </div>
                                    </div>

                                    <!-- Color palette -->
                                    <div>
                                        <label class="mb-1.5 flex items-center gap-2 text-xs font-medium" style="color: #4d4c48">
                                            <Palette class="h-3.5 w-3.5" style="color: #87867f" />
                                            Warna Aksen
                                        </label>
                                        <div class="flex items-center gap-3">
                                            <input
                                                v-model="embedPrimary"
                                                type="color"
                                                class="h-8 w-8 cursor-pointer rounded-lg border"
                                                style="border-color: #e8e6dc"
                                            />
                                            <div class="flex flex-wrap gap-2">
                                                <button
                                                    v-for="color in ['#c96442', '#2563eb', '#059669', '#7c3aed', '#db2777', '#ea580c', '#0891b2', '#4d4c48']"
                                                    :key="color"
                                                    class="h-6 w-6 rounded-full border-2 transition-transform hover:scale-110"
                                                    :style="{
                                                        backgroundColor: color,
                                                        borderColor: embedPrimary === color ? '#141413' : 'transparent',
                                                    }"
                                                    @click="embedPrimary = color"
                                                />
                                            </div>
                                        </div>
                                        <div class="mt-2 flex items-center gap-2">
                                            <span class="text-xs" style="color: #87867f">Hex:</span>
                                            <input
                                                v-model="embedPrimary"
                                                type="text"
                                                class="w-24 rounded-lg border px-2 py-1 text-xs font-mono"
                                                style="background-color: #faf9f5; border-color: #e8e6dc; color: #141413"
                                            />
                                        </div>
                                    </div>

                                    <!-- WhatsApp -->
                                    <div class="mt-4">
                                        <label class="mb-1.5 flex items-center gap-2 text-xs font-medium" style="color: #4d4c48">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor" style="color: #25D366"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                            No. WhatsApp
                                        </label>
                                        <input
                                            v-model="embedWhatsapp"
                                            type="text"
                                            placeholder="081234567890"
                                            class="w-full rounded-lg border px-3 py-2 text-sm"
                                            style="background-color: #faf9f5; border-color: #e8e6dc; color: #141413"
                                        />
                                        <p class="mt-1 text-xs" style="color: #87867f">Tombol "Order Desain Ini" akan muncul di overlay demo</p>
                                    </div>
                                </div>

                                <!-- Code output -->
                                <div class="rounded-xl p-4" style="background-color: #ffffff; border: 1px solid #f0eee6">
                                    <h4 class="mb-3 text-sm font-medium" style="color: #141413">Kode Embed</h4>
                                    <p class="mb-3 text-xs" style="color: #5e5d59">
                                        Salin kode di bawah dan tempel di HTML website Anda.
                                    </p>
                                    <div class="overflow-x-auto rounded-lg p-3 text-xs leading-relaxed" style="background-color: #141413; color: #b0aea5; font-family: 'Fira Code', monospace">
                                        <pre class="whitespace-pre-wrap break-all" style="color: #b0aea5; font-size: 12px; line-height: 1.7">{{ jsEmbedCode }}</pre>
                                    </div>
                                    <div class="mt-3 flex gap-2">
                                        <Button
                                            size="sm"
                                            @click="copyToClipboard(jsEmbedCode, 'js-embed')"
                                            style="background-color: #c96442; color: #faf9f5; border-radius: 8px"
                                        >
                                            <component :is="copiedId === 'js-embed' ? Check : Copy" class="mr-1 h-3.5 w-3.5" />
                                            {{ copiedId === 'js-embed' ? 'Tersalin!' : 'Salin Kode' }}
                                        </Button>
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            style="border-color: #e8e6dc; color: #4d4c48; border-radius: 8px"
                                            @click="copyToClipboard(appUrl + '/demo-web/embed.js', 'js-url')"
                                        >
                                            <component :is="copiedId === 'js-url' ? Check : Copy" class="mr-1 h-3.5 w-3.5" />
                                            {{ copiedId === 'js-url' ? 'Tersalin!' : 'Salin URL Script' }}
                                        </Button>
                                    </div>
                                </div>

                                <!-- Attributes guide -->
                                <div class="rounded-xl p-4" style="background-color: #e8e6dc40; border: 1px solid #e8e6dc">
                                    <h4 class="mb-2 text-xs font-medium" style="color: #141413">Opsi Konfigurasi (data-attributes)</h4>
                                    <div class="space-y-1 text-xs" style="color: #5e5d59">
                                        <p><code style="color: #c96442">data-category</code> — Filter kategori (slug)</p>
                                        <p><code style="color: #c96442">data-per-page</code> — Demo per halaman (default: 6)</p>
                                        <p><code style="color: #c96442">data-primary</code> — Warna aksen (hex)</p>
                                        <p><code style="color: #c96442">data-bg</code> — Warna background</p>
                                        <p><code style="color: #c96442">data-card-bg</code> — Warna background kartu</p>
                                        <p><code style="color: #c96442">data-text</code> — Warna teks utama</p>
                                        <p><code style="color: #c96442">data-text-secondary</code> — Warna teks sekunder</p>
                                        <p><code style="color: #c96442">data-border</code> — Warna border</p>
                                        <p><code style="color: #c96442">data-whatsapp</code> — No. WhatsApp untuk tombol order</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Live Preview -->
                            <div>
                                <div class="sticky top-4 rounded-xl overflow-hidden" style="background-color: #ffffff; border: 1px solid #f0eee6">
                                    <div class="px-4 py-3 flex items-center justify-between" style="border-bottom: 1px solid #f0eee6">
                                        <h4 class="text-sm font-medium" style="color: #141413">Live Preview</h4>
                                        <div class="flex gap-1.5">
                                            <span class="h-3 w-3 rounded-full" style="background-color: #f87171"></span>
                                            <span class="h-3 w-3 rounded-full" style="background-color: #fbbf24"></span>
                                            <span class="h-3 w-3 rounded-full" style="background-color: #34d399"></span>
                                        </div>
                                    </div>
                                    <div class="p-4" style="min-height: 400px; max-height: 600px; overflow-y: auto;">
                                        <div class="grid gap-3" style="grid-template-columns: repeat(auto-fill, minmax(200px, 1fr))">
                                            <div
                                                v-for="demo in previewDemos"
                                                :key="'preview-' + demo.id"
                                                class="rounded-lg overflow-hidden transition-shadow hover:shadow-md"
                                                style="background-color: #ffffff; border: 1px solid #f0eee6"
                                            >
                                                <div class="relative aspect-video" style="background-color: #e8e6dc">
                                                    <img v-if="demo.featured_image" :src="demo.featured_image" :alt="demo.title" class="h-full w-full object-cover" loading="lazy" />
                                                    <div v-else class="flex h-full w-full items-center justify-center">
                                                        <Monitor class="h-8 w-8" style="color: #b0aea5" />
                                                    </div>
                                                    <span v-if="demo.category" class="absolute top-1.5 left-1.5 rounded px-1.5 py-0.5 text-[10px] font-semibold text-white" :style="{ backgroundColor: embedPrimary }">
                                                        {{ demo.category }}
                                                    </span>
                                                </div>
                                                <div class="p-2.5">
                                                    <h3 class="text-xs font-semibold truncate" style="color: #141413">{{ demo.title }}</h3>
                                                    <a
                                                        :href="demo.url"
                                                        target="_blank"
                                                        class="mt-1.5 flex w-full items-center justify-center gap-1 rounded-md px-2 py-1.5 text-[10px] font-medium text-white transition-opacity hover:opacity-90"
                                                        :style="{ backgroundColor: embedPrimary }"
                                                    >
                                                        Lihat Demo
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Preview pagination -->
                                        <div v-if="previewTotalPages > 1" class="mt-4 flex items-center justify-center gap-1">
                                            <button
                                                class="rounded-lg border px-2.5 py-1 text-[11px] font-semibold transition-colors"
                                                :style="{ borderColor: '#e8e6dc', color: '#4d4c48', backgroundColor: '#ffffff' }"
                                                :disabled="previewPage <= 1"
                                                :class="{ 'opacity-35': previewPage <= 1 }"
                                                @click="previewPage--"
                                            >
                                                &lsaquo; Prev
                                            </button>
                                            <button
                                                v-for="p in previewTotalPages"
                                                :key="'pp-' + p"
                                                class="rounded-lg border px-2.5 py-1 text-[11px] font-semibold transition-colors"
                                                :style="p === previewPage ? { backgroundColor: embedPrimary, color: '#ffffff', borderColor: embedPrimary } : { borderColor: '#e8e6dc', color: '#4d4c48', backgroundColor: '#ffffff' }"
                                                @click="previewPage = p"
                                            >
                                                {{ p }}
                                            </button>
                                            <button
                                                class="rounded-lg border px-2.5 py-1 text-[11px] font-semibold transition-colors"
                                                :style="{ borderColor: '#e8e6dc', color: '#4d4c48', backgroundColor: '#ffffff' }"
                                                :disabled="previewPage >= previewTotalPages"
                                                :class="{ 'opacity-35': previewPage >= previewTotalPages }"
                                                @click="previewPage++"
                                            >
                                                Next &rsaquo;
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- CTA -->
            <div class="mt-16 text-center">
                <Card style="background-color: #141413; border: 1px solid #30302e; border-radius: 16px; box-shadow: rgba(0, 0, 0, 0.05) 0px 4px 24px">
                    <CardContent class="py-12">
                        <h2 class="mb-4 text-3xl font-medium" style="color: #faf9f5; line-height: 1.2; font-family: Georgia, serif">
                            Tertarik dengan Salah satu Demo?
                        </h2>
                        <p class="mx-auto mb-8 max-w-2xl text-base sm:text-lg" style="color: #b0aea5; line-height: 1.6">
                            Hubungi kami untuk memulai website impian Anda berdasarkan template yang Anda pilih.
                        </p>
                        <div class="flex flex-col gap-3 sm:flex-row sm:justify-center sm:gap-4">
                            <Button asChild size="lg" class="text-lg px-6 py-4" style="background-color: #c96442; color: #faf9f5; border-radius: 12px">
                                <a
                                    :href="`https://wa.me/${companyWhatsapp}?text=${encodeURIComponent('Halo, saya tertarik dengan demo website di WebSweetStudio!')}`"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    Hubungi via WhatsApp
                                </a>
                            </Button>
                            <Button
                                asChild
                                variant="outline"
                                size="lg"
                                class="text-lg px-6 py-4"
                                style="background-color: #ffffff; color: #141413; border: 1px solid #30302e; border-radius: 12px"
                            >
                                <a href="/hosting">Lihat Paket Hosting</a>
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </section>
    </CustomerPublicLayout>
</template>