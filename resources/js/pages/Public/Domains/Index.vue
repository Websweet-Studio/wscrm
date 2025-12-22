<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import CustomerPublicLayout from '@/layouts/CustomerPublicLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { Crown, Globe, Search, ShoppingCart, Star, TrendingUp } from 'lucide-vue-next';
import { ref } from 'vue';

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

const search = ref(props.filters.search || '');
const domainSearch = ref('');

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(price);
};

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

const getPopularExtensions = () => {
    return props.domainPrices
        .filter((domain) => {
            const cleanExt = domain.extension.replace('.', '');
            return ['com', 'id', 'my.id'].includes(cleanExt);
        })
        .sort((a, b) => a.selling_price - b.selling_price);
};

const isPremium = (extension: string) => {
    const cleanExt = extension.replace('.', '');
    return ['com'].includes(cleanExt);
};

const isPopular = (extension: string) => {
    const cleanExt = extension.replace('.', '');
    return ['com', 'id', 'my.id'].includes(cleanExt);
};
</script>

<template>
    <CustomerPublicLayout title="Registrasi Domain - Temukan Domain Sempurna Anda">
        <section class="mx-auto max-w-6xl px-4 py-12 sm:px-6 sm:py-16">
            <!-- Hero Section -->
            <div
                class="mb-12 space-y-6 rounded-3xl border border-blue-100 bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 py-12 text-center sm:mb-16 sm:space-y-8 sm:py-20"
            >
                <div class="space-y-4 sm:space-y-6">
                    <div
                        class="inline-flex items-center rounded-full border border-blue-200 bg-white/80 px-3 py-1 text-xs font-medium text-blue-700 sm:px-4 sm:py-2 sm:text-sm"
                    >
                        <Star class="mr-1 h-3 w-3 text-yellow-500 sm:mr-2 sm:h-4 sm:w-4" />
                        Mulai perjalanan online Anda hari ini
                    </div>
                    <h1
                        class="bg-gradient-to-br from-blue-900 via-blue-700 to-purple-700 bg-clip-text text-3xl font-bold tracking-tight text-transparent sm:text-4xl lg:text-5xl"
                    >
                        Temukan Domain Sempurna Anda
                    </h1>
                    <p class="mx-auto max-w-3xl text-base leading-relaxed text-muted-foreground sm:text-lg lg:text-xl">
                        Cari domain yang tersedia dan daftarkan secara instan. Kehadiran online Anda dimulai di sini dengan nama domain yang sempurna.
                    </p>
                </div>

                <!-- Enhanced Domain Search -->
                <Card class="mx-auto max-w-3xl border-0 bg-white/90 shadow-xl backdrop-blur-sm">
                    <CardContent class="px-4 pt-6 pb-6 sm:px-8 sm:pt-8 sm:pb-8">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                            <div class="relative w-full flex-1">
                                <Globe class="absolute top-1/2 left-3 h-5 w-5 -translate-y-1/2 transform text-blue-500 sm:left-4 sm:h-6 sm:w-6" />
                                <Input
                                    v-model="domainSearch"
                                    placeholder="Masukkan nama domain Anda (contoh: websayaawesome.com)"
                                    class="h-12 rounded-xl border-2 border-blue-200 pr-3 pl-10 text-base focus:border-blue-500 sm:h-14 sm:pr-4 sm:pl-12 sm:text-lg"
                                    @keyup.enter="searchDomain"
                                />
                            </div>
                            <Button
                                @click="searchDomain"
                                size="lg"
                                class="h-12 w-full rounded-xl bg-gradient-to-r from-blue-600 to-blue-700 px-6 font-semibold text-white shadow-lg transition-all hover:from-blue-700 hover:to-blue-800 hover:shadow-xl sm:h-14 sm:w-auto sm:px-8"
                            >
                                <Search class="mr-1 h-4 w-4 sm:mr-2 sm:h-5 sm:w-5" />
                                <span class="hidden sm:inline">Cari Domain</span>
                                <span class="sm:hidden">Cari</span>
                            </Button>
                        </div>
                        <div class="mt-3 flex flex-wrap justify-center gap-1 text-xs text-muted-foreground sm:mt-4 sm:gap-2 sm:text-sm">
                            <span>Pencarian populer:</span>
                            <button class="font-medium text-blue-600 hover:text-blue-800">.com</button>
                            <span>•</span>
                            <button class="font-medium text-blue-600 hover:text-blue-800">.id</button>
                            <span>•</span>
                            <button class="font-medium text-blue-600 hover:text-blue-800">.my.id</button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Popular Extensions -->
            <div class="mb-12 space-y-6 sm:mb-16 sm:space-y-8">
                <div class="space-y-2 text-center sm:space-y-3">
                    <h2 class="text-2xl font-bold sm:text-3xl">Ekstensi Domain Populer</h2>
                    <p class="mx-auto max-w-2xl text-sm text-muted-foreground sm:text-base">
                        Mulai kehadiran online Anda dengan ekstensi domain paling terpercaya dan banyak digunakan
                    </p>
                </div>

                <div class="mx-auto grid max-w-4xl grid-cols-1 gap-4 sm:gap-6 lg:grid-cols-3">
                    <Card
                        v-for="domain in getPopularExtensions()"
                        :key="domain.id"
                        class="group relative mx-auto max-w-sm cursor-pointer overflow-hidden border-2 transition-all hover:scale-105 hover:border-blue-300 hover:shadow-xl lg:max-w-none"
                    >
                        <!-- Premium Badge -->
                        <div v-if="isPremium(domain.extension)" class="absolute top-2 right-2 z-10 sm:top-3 sm:right-3">
                            <Badge class="bg-gradient-to-r from-yellow-400 to-orange-500 text-xs font-semibold text-white">
                                <Crown class="mr-1 h-3 w-3" />
                                Premium
                            </Badge>
                        </div>

                        <CardContent class="space-y-4 pt-6 pb-4 text-center sm:space-y-6 sm:pt-8 sm:pb-6">
                            <!-- Extension Name -->
                            <div class="space-y-1 sm:space-y-2">
                                <div class="text-3xl font-bold tracking-tight text-blue-600 sm:text-4xl">.{{ domain.extension }}</div>
                                <div class="text-xs tracking-wider text-muted-foreground uppercase">Ekstensi Domain</div>
                            </div>

                            <!-- Pricing -->
                            <div class="space-y-2 rounded-lg bg-gradient-to-br from-blue-50 to-indigo-50 p-3 sm:space-y-3 sm:p-4">
                                <div class="space-y-1">
                                    <div class="text-xs font-medium text-muted-foreground sm:text-sm">Tahun Pertama</div>
                                    <div class="text-2xl font-bold text-blue-600 sm:text-3xl">{{ formatPrice(domain.selling_price) }}</div>
                                </div>

                                <div class="space-y-1 border-t pt-2">
                                    <div class="text-xs text-muted-foreground">
                                        Perpanjangan: {{ formatPrice(domain.renewal_price_with_tax) }}/tahun
                                    </div>
                                </div>
                            </div>

                            <!-- CTA Button -->
                            <Button
                                asChild
                                class="w-full bg-gradient-to-r from-blue-600 to-blue-700 py-2 font-semibold text-white transition-all group-hover:shadow-lg hover:from-blue-700 hover:to-blue-800 sm:py-3"
                            >
                                <Link href="/customer/register">
                                    <ShoppingCart class="mr-1 h-3 w-3 sm:mr-2 sm:h-4 sm:w-4" />
                                    <span class="hidden sm:inline">Mulai Sekarang</span>
                                    <span class="sm:hidden">Daftar</span>
                                </Link>
                            </Button>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <!-- All Extensions -->
            <div class="space-y-6 sm:space-y-8">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="space-y-1">
                        <h2 class="text-xl font-bold sm:text-2xl">Harga Domain Lengkap</h2>
                        <p class="text-sm text-muted-foreground sm:text-base">
                            Bandingkan harga dan temukan ekstensi yang sempurna untuk kebutuhan Anda
                        </p>
                    </div>

                    <!-- Extension Search -->
                    <Card class="w-full lg:w-80">
                        <CardContent class="pt-4">
                            <div class="flex items-center space-x-2">
                                <div class="relative flex-1">
                                    <Search class="absolute top-2.5 left-2.5 h-4 w-4 text-muted-foreground" />
                                    <Input
                                        v-model="search"
                                        placeholder="Cari ekstensi (contoh: com, id, org)..."
                                        class="pl-8"
                                        @keyup.enter="handleSearch"
                                    />
                                </div>
                                <Button @click="handleSearch" size="sm" class="shrink-0">Filter</Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Table View for Better Comparison -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center text-base sm:text-lg">
                            <Globe class="mr-2 h-4 w-4 text-blue-600 sm:h-5 sm:w-5" />
                            Harga Ekstensi Domain
                        </CardTitle>
                        <CardDescription class="text-sm">
                            Semua harga ditampilkan per tahun. Registrasi untuk tahun pertama, perpanjangan berlaku mulai tahun kedua.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="p-0">
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[600px]">
                                <thead class="border-b bg-muted/50">
                                    <tr>
                                        <th class="px-3 py-3 text-left font-semibold sm:px-6 sm:py-4">Ekstensi</th>
                                        <th class="px-3 py-3 text-right font-semibold sm:px-6 sm:py-4">
                                            <span class="hidden sm:inline">Registrasi</span>
                                            <span class="sm:hidden">Reg</span>
                                        </th>
                                        <th class="px-3 py-3 text-right font-semibold sm:px-6 sm:py-4">
                                            <span class="hidden sm:inline">Perpanjangan</span>
                                            <span class="sm:hidden">Renewal</span>
                                        </th>
                                        <th class="px-3 py-3 text-center font-semibold sm:px-6 sm:py-4">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="domain in domainPrices" :key="domain.id" class="border-b transition-colors hover:bg-muted/20">
                                        <td class="px-3 py-3 sm:px-6 sm:py-4">
                                            <div class="flex items-center space-x-2 sm:space-x-3">
                                                <div class="text-lg font-bold text-blue-600 sm:text-xl">.{{ domain.extension }}</div>
                                                <Badge
                                                    v-if="isPremium(domain.extension)"
                                                    class="bg-gradient-to-r from-yellow-400 to-orange-500 text-xs text-white"
                                                >
                                                    <Crown class="mr-1 h-3 w-3" />
                                                    Premium
                                                </Badge>
                                            </div>
                                        </td>
                                        <td class="px-3 py-3 text-right sm:px-6 sm:py-4">
                                            <div class="space-y-1">
                                                <div class="text-base font-bold text-green-600 sm:text-lg">
                                                    {{ formatPrice(domain.selling_price) }}
                                                </div>
                                                <div class="text-xs text-muted-foreground">tahun pertama</div>
                                            </div>
                                        </td>
                                        <td class="px-3 py-3 text-right sm:px-6 sm:py-4">
                                            <div class="space-y-1">
                                                <div class="text-base font-semibold sm:text-lg">{{ formatPrice(domain.renewal_price_with_tax) }}</div>
                                                <div class="text-xs text-muted-foreground">per tahun</div>
                                            </div>
                                        </td>
                                        <td class="px-3 py-3 text-center sm:px-6 sm:py-4">
                                            <Button asChild size="sm" class="w-full bg-blue-600 text-white hover:bg-blue-700 sm:w-auto">
                                                <Link href="/customer/register">
                                                    <ShoppingCart class="mr-1 h-3 w-3 sm:h-4 sm:w-4" />
                                                    <span class="hidden sm:inline">Daftar</span>
                                                    <span class="sm:hidden">Reg</span>
                                                </Link>
                                            </Button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Why Choose Our Domains -->
            <Card class="mt-12 bg-gradient-to-br from-blue-50 to-indigo-100 sm:mt-16">
                <CardContent class="px-4 pt-6 pb-6 sm:px-8 sm:pt-8 sm:pb-8">
                    <div class="space-y-4 text-center sm:space-y-6">
                        <h2 class="text-xl font-bold sm:text-2xl">Mengapa Pilih Registrasi Domain Kami?</h2>

                        <div class="mx-auto grid max-w-4xl gap-4 sm:gap-6 md:grid-cols-3">
                            <div class="space-y-2 text-center sm:space-y-3">
                                <div class="mx-auto w-fit rounded-full bg-blue-100 p-2 sm:p-3">
                                    <Globe class="h-5 w-5 text-blue-600 sm:h-6 sm:w-6" />
                                </div>
                                <h3 class="text-sm font-semibold sm:text-base">Pengelolaan Mudah</h3>
                                <p class="text-xs text-muted-foreground sm:text-sm">
                                    Panel kontrol intuitif untuk mengelola semua domain Anda di satu tempat
                                </p>
                            </div>

                            <div class="space-y-2 text-center sm:space-y-3">
                                <div class="mx-auto w-fit rounded-full bg-green-100 p-2 sm:p-3">
                                    <TrendingUp class="h-5 w-5 text-green-600 sm:h-6 sm:w-6" />
                                </div>
                                <h3 class="text-sm font-semibold sm:text-base">Harga Kompetitif</h3>
                                <p class="text-xs text-muted-foreground sm:text-sm">
                                    Harga terbaik di pasar dengan tarif perpanjangan yang transparan
                                </p>
                            </div>

                            <div class="space-y-2 text-center sm:space-y-3">
                                <div class="mx-auto w-fit rounded-full bg-purple-100 p-2 sm:p-3">
                                    <Star class="h-5 w-5 text-purple-600 sm:h-6 sm:w-6" />
                                </div>
                                <h3 class="text-sm font-semibold sm:text-base">Dukungan 24/7</h3>
                                <p class="text-xs text-muted-foreground sm:text-sm">Tim dukungan ahli siap membantu dengan masalah domain</p>
                            </div>
                        </div>

                        <div class="flex flex-col gap-3 pt-4 sm:flex-row sm:justify-center sm:gap-4 sm:pt-6">
                            <Button asChild size="lg" class="w-full sm:w-auto">
                                <Link href="/customer/register">Mulai Hari Ini</Link>
                            </Button>
                            <Button variant="outline" asChild size="lg" class="w-full sm:w-auto">
                                <Link href="/hosting">Lihat Paket Hosting</Link>
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Empty State -->
            <div v-if="domainPrices.length === 0" class="py-8 text-center sm:py-12">
                <Globe class="mx-auto mb-4 h-12 w-12 text-muted-foreground" />
                <h3 class="mb-2 text-lg font-semibold">Tidak ada ekstensi domain ditemukan</h3>
                <p class="text-muted-foreground">
                    {{ search ? 'Coba sesuaikan kriteria pencarian Anda.' : 'Tidak ada ekstensi domain yang tersedia saat ini.' }}
                </p>
            </div>
        </section>
    </CustomerPublicLayout>
</template>
