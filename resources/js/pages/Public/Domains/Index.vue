 <script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import CustomerPublicLayout from '@/layouts/CustomerPublicLayout.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { Crown, Globe, Search, ShoppingCart, Star, TrendingUp } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const branding = computed(() => (page.props.brandingSettings as Record<string, string>) || {});
const brandPrimary = computed(() => branding.value.primary_color || '#e60023');
const brandSecondary = computed(() => branding.value.secondary_color || '#e5e5e0');

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

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(price);
};

const filteredDomainPrices = computed(() => {
    let domains = props.domainPrices.filter(d => d.is_active);
    if (search.value) {
        domains = domains.filter((d) =>
            d.extension.toLowerCase().includes(search.value.toLowerCase())
        );
    }
    return domains;
});

const handleSearch = () => {
    router.get(
        '/domains',
        { search: search.value },
        {
            preserveState: true,
            replace: true,
        },
    );
};

const searchDomain = () => {
    if (domainSearch.value.trim()) {
        router.get('/domains/search', { domain: domainSearch.value });
    }
};

const isPremium = (extension: string) => {
    const cleanExt = extension.replace('.', '');
    return ['com'].includes(cleanExt);
};

const companyWhatsapp = computed(() => {
    return (page.props.brandingSettings as any)?.company_whatsapp || '';
});

const getWhatsappLink = (text: string) => {
    const raw = companyWhatsapp.value;
    if (!raw) return '';

    const phone = String(raw).replace(/[^\d]/g, '');
    const message = encodeURIComponent(text);
    return `https://wa.me/${phone}?text=${message}`;
};
</script>

<template>
    <CustomerPublicLayout title="Registrasi Domain - Temukan Domain Sempurna Anda">
        <section class="mx-auto max-w-6xl px-4 py-12 sm:px-6 sm:py-16 lg:py-20">
            <!-- Hero -->
            <div class="mb-12 text-center sm:mb-16">
                <h1 class="mb-4 text-4xl leading-tight font-medium sm:text-5xl md:text-6xl" style="color: #141413; line-height: 1.1; font-family: Georgia, serif;">
                    Temukan Domain Sempurna Anda
                </h1>
                <p class="mx-auto mb-6 max-w-2xl text-base sm:text-lg lg:text-xl" style="color: #5e5d59; line-height: 1.6;">
                    Cari domain yang tersedia dan daftarkan secara instan. Kehadiran online Anda dimulai di sini dengan nama domain yang sempurna.
                </p>
            </div>

            <!-- Search Domain -->
            <div class="mb-12 mx-auto max-w-2xl">
                <div class="rounded-2xl bg-white p-6" style="border: 1px solid #dadad3; box-shadow: rgba(0,0,0,0.04) 0px 4px 16px;">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="rounded-full p-2" style="background-color: var(--secondary);">
                                <Globe class="h-5 w-5" style="color: var(--primary);" />
                        </div>
                        <div>
                            <div class="text-base font-semibold" style="color: #000000;">Cari Domain</div>
                            <div class="text-sm" style="color: #62625b;">Masukkan nama domain yang Anda inginkan</div>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <div class="relative flex-1">
                            <Globe class="absolute top-2.5 left-3 h-5 w-5" style="color: #91918c;" />
                            <Input
                                v-model="domainSearch"
                                placeholder="Masukkan nama domain Anda"
                                class="pl-10"
                                style="background-color: #ffffff; border: 1px solid #c8c8c1; color: #000000; border-radius: 16px;"
                                @keyup.enter="searchDomain"
                            />
                        </div>
                        <Button @click="searchDomain" style="background-color: var(--primary); color: #ffffff; border-radius: 16px;">
                            <Search class="mr-2 h-4 w-4" />
                            Cari
                        </Button>
                    </div>
                    <div class="mt-3 flex flex-wrap justify-center gap-2 text-sm" style="color: #62625b;">
                        <span>Pencarian populer:</span>
                        <button class="font-bold" style="color: var(--primary);">.com</button>
                        <span>&bull;</span>
                        <button class="font-bold" style="color: var(--primary);">.id</button>
                        <span>&bull;</span>
                        <button class="font-bold" style="color: var(--primary);">.my.id</button>
                    </div>
                </div>
            </div>

            <!-- Table: Daftar Harga Domain -->
            <div class="mb-12">
                <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="text-[22px] font-semibold" style="color: #000000;">Daftar Harga Domain</h2>
                        <p class="text-sm" style="color: #62625b;">Bandingkan harga dan temukan ekstensi yang sempurna</p>
                    </div>
                    <div class="flex gap-2">
                        <div class="relative">
                            <Search class="absolute top-2.5 left-3 h-4 w-4" style="color: #91918c;" />
                            <Input
                                v-model="search"
                                placeholder="Cari ekstensi..."
                                class="pl-8"
                                style="background-color: #ffffff; border: 1px solid #c8c8c1; color: #000000; border-radius: 16px;"
                                @keyup.enter="handleSearch"
                            />
                        </div>
                        <Button @click="handleSearch" style="background-color: var(--secondary); color: #000000; border-radius: 16px;">Filter</Button>
                    </div>
                </div>

                <div class="overflow-hidden rounded-2xl" style="border: 1px solid #dadad3;">
                    <div class="overflow-x-auto">
                        <table class="w-full" style="background-color: #ffffff;">
                            <thead>
                                <tr style="background-color: var(--secondary);">
                                    <th class="px-4 py-3 text-left text-sm font-bold" style="color: #33332e;">No</th>
                                    <th class="px-4 py-3 text-left text-sm font-bold" style="color: #33332e;">Ekstensi</th>
                                    <th class="px-4 py-3 text-left text-sm font-bold" style="color: #33332e;">Status</th>
                                    <th class="px-4 py-3 text-right text-sm font-bold" style="color: #33332e;">Registrasi</th>
                                    <th class="px-4 py-3 text-right text-sm font-bold" style="color: #33332e;">Perpanjangan</th>
                                    <th class="px-4 py-3 text-center text-sm font-bold" style="color: #33332e;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="(domain, index) in filteredDomainPrices"
                                    :key="domain.id"
                                    class="transition-colors hover:bg-[#f6f6f3]"
                                    style="border-top: 1px solid #e5e5e0;"
                                >
                                    <td class="px-4 py-4 text-sm" style="color: #62625b;">{{ index + 1 }}</td>
                                    <td class="px-4 py-4">
                                        <span class="text-base font-semibold" style="color: #000000;">.{{ domain.extension }}</span>
                                    </td>
                                    <td class="px-4 py-4">
                                        <span
                                            v-if="isPremium(domain.extension)"
                                            class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-bold"
                                            style="background-color: var(--primary); color: #ffffff;"
                                        >
                                            <Crown class="h-3 w-3" />
                                            Premium
                                        </span>
                                        <span v-else class="text-sm" style="color: #91918c;">&mdash;</span>
                                    </td>
                                    <td class="px-4 py-4 text-right text-base font-bold" style="color: var(--primary);">
                                        {{ formatPrice(domain.selling_price) }}
                                    </td>
                                    <td class="px-4 py-4 text-right text-sm font-medium" style="color: #33332e;">
                                        {{ formatPrice(domain.renewal_price_with_tax) }}
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <Button
                                            asChild
                                            size="sm"
                                            style="background-color: var(--primary); color: #ffffff; border-radius: 16px;"
                                        >
                                            <a
                                                :href="getWhatsappLink(`Halo, saya ingin beli Domain .${domain.extension}.`)"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                            >
                                                <ShoppingCart class="mr-1 h-3 w-3" />
                                                Beli
                                            </a>
                                        </Button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Why Choose Us -->
            <div class="mb-12 rounded-2xl bg-white p-8" style="border: 1px solid #dadad3;">
                <h2 class="mb-8 text-center text-[22px] font-semibold" style="color: #000000;">Mengapa Pilih Registrasi Domain Kami?</h2>
                <div class="grid gap-6 md:grid-cols-3">
                    <div class="text-center">
                        <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full" style="background-color: var(--secondary);">
                            <Globe class="h-6 w-6" style="color: var(--primary);" />
                        </div>
                        <h3 class="mb-2 text-base font-semibold" style="color: #000000;">Pengelolaan Mudah</h3>
                        <p class="text-sm" style="color: #62625b;">
                            Panel kontrol intuitif untuk mengelola semua domain Anda di satu tempat
                        </p>
                    </div>
                    <div class="text-center">
                        <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full" style="background-color: var(--secondary);">
                            <TrendingUp class="h-6 w-6" style="color: var(--primary);" />
                        </div>
                        <h3 class="mb-2 text-base font-semibold" style="color: #000000;">Harga Kompetitif</h3>
                        <p class="text-sm" style="color: #62625b;">
                            Harga terbaik di pasar dengan tarif perpanjangan yang transparan
                        </p>
                    </div>
                    <div class="text-center">
                        <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full" style="background-color: var(--secondary);">
                            <Star class="h-6 w-6" style="color: var(--primary);" />
                        </div>
                        <h3 class="mb-2 text-base font-semibold" style="color: #000000;">Dukungan 24/7</h3>
                        <p class="text-sm" style="color: #62625b;">Tim dukungan ahli siap membantu dengan masalah domain</p>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="filteredDomainPrices.length === 0" class="py-12 text-center">
                <Globe class="mx-auto mb-4 h-12 w-12" style="color: #91918c;" />
                <h3 class="mb-2 text-xl font-semibold" style="color: #000000;">Tidak ada ekstensi domain ditemukan</h3>
                <p class="text-sm" style="color: #62625b;">
                    {{ search ? 'Coba sesuaikan kriteria pencarian Anda.' : 'Tidak ada ekstensi domain yang tersedia saat ini.' }}
                </p>
            </div>

            <!-- CTA -->
            <div class="mt-12 pt-16 text-center">
                <div class="rounded-2xl bg-[#262622] px-8 py-12" style="border: 1px solid #30302e;">
                    <h2 class="mb-4 text-3xl font-semibold" style="color: #ffffff; line-height: 1.2;">
                        Siap untuk Memulai?
                    </h2>
                    <p class="mx-auto mb-8 max-w-2xl text-base sm:text-lg" style="color: #b0aea5; line-height: 1.6;">
                        Bergabunglah dengan ribuan pelanggan yang puas yang mempercayai kebutuhan domain dan hosting mereka kepada kami.
                    </p>
                    <div class="flex flex-col gap-3 sm:flex-row sm:justify-center sm:gap-4">
                        <Button asChild size="lg" class="text-lg px-6 py-4" style="background-color: var(--primary); color: #ffffff; border-radius: 16px;">
                            <Link href="/customer/register">Buat Akun</Link>
                        </Button>
                        <Button asChild variant="outline" size="lg" class="text-lg px-6 py-4" style="background-color: #ffffff; color: #000000; border: 1px solid #30302e; border-radius: 16px;">
                            <Link href="/hosting">Lihat Paket Hosting</Link>
                        </Button>
                    </div>
                </div>
            </div>
        </section>
    </CustomerPublicLayout>
</template>
