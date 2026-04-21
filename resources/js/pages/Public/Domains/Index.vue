<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import CustomerPublicLayout from '@/layouts/CustomerPublicLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { Crown, Globe, Search, ShoppingCart, Star, TrendingUp } from 'lucide-vue-next';
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

const getPopularExtensions = () => {
    return filteredDomainPrices.value
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
        <section class="mx-auto max-w-6xl px-4 py-12 sm:px-6 sm:py-16 lg:py-20">
            <div class="mb-12 text-center sm:mb-16">
                <h1 class="mb-4 text-4xl leading-tight font-medium sm:text-5xl md:text-6xl" style="color: #141413; line-height: 1.1; font-family: Georgia, serif;">
                    Temukan Domain Sempurna Anda
                </h1>
                <p class="mx-auto mb-6 max-w-2xl text-base sm:text-lg lg:text-xl" style="color: #5e5d59; line-height: 1.6;">
                    Cari domain yang tersedia dan daftarkan secara instan. Kehadiran online Anda dimulai di sini dengan nama domain yang sempurna.
                </p>
            </div>

            <div class="mb-12">
                <Card style="background-color: #faf9f5; border: 1px solid #f0eee6; border-radius: 16px; box-shadow: rgba(0,0,0,0.05) 0px 4px 24px;">
                    <CardHeader>
                        <div class="flex items-center gap-3">
                            <div class="rounded-full p-2" style="background-color: #e8e6dc;">
                                <Globe class="h-6 w-6" style="color: #c96442;" />
                            </div>
                            <div>
                                <CardTitle class="text-xl font-medium" style="color: #141413; font-family: Georgia, serif;">Cari Domain</CardTitle>
                                <CardDescription style="color: #5e5d59;">Masukkan nama domain yang Anda inginkan</CardDescription>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="flex gap-2">
                            <div class="relative flex-1">
                                <Globe class="absolute top-2.5 left-3 h-5 w-5" style="color: #87867f;" />
                                <Input
                                    v-model="domainSearch"
                                    placeholder="Masukkan nama domain Anda"
                                    class="pl-10"
                                    style="background-color: #ffffff; border: 1px solid #e8e6dc; color: #141413; border-radius: 12px;"
                                    @keyup.enter="searchDomain"
                                />
                            </div>
                            <Button @click="searchDomain" style="background-color: #c96442; color: #faf9f5; border-radius: 12px;">
                                <Search class="mr-2 h-4 w-4" />
                                Cari
                            </Button>
                        </div>
                        <div class="mt-3 flex flex-wrap justify-center gap-2 text-sm" style="color: #5e5d59;">
                            <span>Pencarian populer:</span>
                            <button class="font-medium" style="color: #c96442;">.com</button>
                            <span>•</span>
                            <button class="font-medium" style="color: #c96442;">.id</button>
                            <span>•</span>
                            <button class="font-medium" style="color: #c96442;">.my.id</button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div class="mb-12">
                <Card style="background-color: #faf9f5; border: 1px solid #f0eee6; border-radius: 16px; box-shadow: rgba(0,0,0,0.05) 0px 4px 24px;">
                    <CardHeader>
                        <div class="flex items-center gap-3">
                            <div class="rounded-full p-2" style="background-color: #e8e6dc;">
                                <Star class="h-6 w-6" style="color: #c96442;" />
                            </div>
                            <div>
                                <CardTitle class="text-xl font-medium" style="color: #141413; font-family: Georgia, serif;">Ekstensi Domain Populer</CardTitle>
                                <CardDescription style="color: #5e5d59;">Mulai kehadiran online Anda dengan ekstensi domain paling terpercaya</CardDescription>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-6 md:grid-cols-3">
                            <Card
                                v-for="domain in getPopularExtensions()"
                                :key="domain.id"
                                class="relative transition-all hover:shadow-lg"
                                style="background-color: #ffffff; border: 1px solid #f0eee6; border-radius: 16px;"
                            >
                                <div v-if="isPremium(domain.extension)" class="absolute top-3 right-3 z-10">
                                    <Badge style="background-color: #c96442; color: #faf9f5; border-radius: 8px;">
                                        <Crown class="mr-1 h-3 w-3" />
                                        Premium
                                    </Badge>
                                </div>

                                <CardContent class="space-y-4 pt-6 text-center">
                                    <div class="space-y-1">
                                        <div class="text-3xl font-bold" style="color: #c96442;">.{{ domain.extension }}</div>
                                        <div class="text-xs tracking-wider uppercase" style="color: #87867f;">Ekstensi Domain</div>
                                    </div>

                                    <div class="space-y-2 rounded-lg p-4" style="background-color: #faf9f5;">
                                        <div class="space-y-1">
                                            <div class="text-xs font-medium" style="color: #5e5d59;">Tahun Pertama</div>
                                            <div class="text-2xl font-bold" style="color: #c96442;">{{ formatPrice(domain.selling_price) }}</div>
                                        </div>

                                        <Separator style="background-color: #f0eee6;" />

                                        <div class="space-y-1 pt-2">
                                            <div class="text-xs" style="color: #87867f;">
                                                Perpanjangan: {{ formatPrice(domain.renewal_price_with_tax) }}/tahun
                                            </div>
                                        </div>
                                    </div>

                                    <Button
                                        asChild
                                        class="w-full"
                                        size="lg"
                                        style="background-color: #c96442; color: #faf9f5; border-radius: 12px;"
                                    >
                                        <Link href="/customer/register">
                                            <ShoppingCart class="mr-2 h-4 w-4" />
                                            Mulai Sekarang
                                        </Link>
                                    </Button>
                                </CardContent>
                            </Card>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div class="mb-12">
                <Card style="background-color: #faf9f5; border: 1px solid #f0eee6; border-radius: 16px; box-shadow: rgba(0,0,0,0.05) 0px 4px 24px;">
                    <CardHeader>
                        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                            <div>
                                <CardTitle class="text-xl font-medium" style="color: #141413; font-family: Georgia, serif;">Harga Domain Lengkap</CardTitle>
                                <CardDescription style="color: #5e5d59;">Bandingkan harga dan temukan ekstensi yang sempurna</CardDescription>
                            </div>

                            <div class="flex gap-2">
                                <div class="relative flex-1">
                                    <Search class="absolute top-2.5 left-3 h-4 w-4" style="color: #87867f;" />
                                    <Input
                                        v-model="search"
                                        placeholder="Cari ekstensi..."
                                        class="pl-8"
                                        style="background-color: #ffffff; border: 1px solid #e8e6dc; color: #141413; border-radius: 12px;"
                                        @keyup.enter="handleSearch"
                                    />
                                </div>
                                <Button @click="handleSearch" style="background-color: #e8e6dc; color: #4d4c48; border-radius: 12px;">Filter</Button>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                            <Card
                                v-for="domain in filteredDomainPrices"
                                :key="domain.id"
                                class="transition-all hover:shadow-lg"
                                style="background-color: #ffffff; border: 1px solid #f0eee6; border-radius: 12px;"
                            >
                                <CardContent class="pt-6">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <div class="text-xl font-bold" style="color: #141413;">.{{ domain.extension }}</div>
                                            <Badge
                                                v-if="isPremium(domain.extension)"
                                                style="background-color: #c96442; color: #faf9f5; border-radius: 6px;"
                                            >
                                                <Crown class="mr-1 h-3 w-3" />
                                                Premium
                                            </Badge>
                                        </div>
                                    </div>

                                    <Separator class="my-4" style="background-color: #f0eee6;" />

                                    <div class="space-y-3">
                                        <div class="flex justify-between items-center">
                                            <span style="color: #5e5d59;">Registrasi</span>
                                            <span class="font-bold text-lg" style="color: #c96442;">{{ formatPrice(domain.selling_price) }}</span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span style="color: #5e5d59;">Perpanjangan</span>
                                            <span class="font-medium" style="color: #4d4c48;">{{ formatPrice(domain.renewal_price_with_tax) }}</span>
                                        </div>
                                    </div>

                                    <Separator class="my-4" style="background-color: #f0eee6;" />

                                    <Button
                                        asChild
                                        class="w-full"
                                        style="background-color: #c96442; color: #faf9f5; border-radius: 12px;"
                                    >
                                        <Link href="/customer/register">
                                            <ShoppingCart class="mr-2 h-4 w-4" />
                                            Daftar
                                        </Link>
                                    </Button>
                                </CardContent>
                            </Card>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div class="mb-12">
                <Card style="background-color: #faf9f5; border: 1px solid #f0eee6; border-radius: 16px; box-shadow: rgba(0,0,0,0.05) 0px 4px 24px;">
                    <CardHeader>
                        <div class="text-center">
                            <CardTitle class="text-xl font-medium" style="color: #141413; font-family: Georgia, serif;">Mengapa Pilih Registrasi Domain Kami?</CardTitle>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-6 md:grid-cols-3">
                            <div class="text-center">
                                <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full" style="background-color: #e8e6dc;">
                                    <Globe class="h-6 w-6" style="color: #c96442;" />
                                </div>
                                <h3 class="mb-2 text-base font-medium" style="color: #141413; font-family: Georgia, serif;">Pengelolaan Mudah</h3>
                                <p style="color: #5e5d59;">
                                    Panel kontrol intuitif untuk mengelola semua domain Anda di satu tempat
                                </p>
                            </div>

                            <div class="text-center">
                                <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full" style="background-color: #e8e6dc;">
                                    <TrendingUp class="h-6 w-6" style="color: #c96442;" />
                                </div>
                                <h3 class="mb-2 text-base font-medium" style="color: #141413; font-family: Georgia, serif;">Harga Kompetitif</h3>
                                <p style="color: #5e5d59;">
                                    Harga terbaik di pasar dengan tarif perpanjangan yang transparan
                                </p>
                            </div>

                            <div class="text-center">
                                <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full" style="background-color: #e8e6dc;">
                                    <Star class="h-6 w-6" style="color: #c96442;" />
                                </div>
                                <h3 class="mb-2 text-base font-medium" style="color: #141413; font-family: Georgia, serif;">Dukungan 24/7</h3>
                                <p style="color: #5e5d59;">Tim dukungan ahli siap membantu dengan masalah domain</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div v-if="filteredDomainPrices.length === 0" class="py-12 text-center">
                <Globe class="mx-auto mb-4 h-12 w-12" style="color: #87867f;" />
                <h3 class="mb-2 text-xl font-medium" style="color: #141413; font-family: Georgia, serif;">Tidak ada ekstensi domain ditemukan</h3>
                <p style="color: #5e5d59;">
                    {{ search ? 'Coba sesuaikan kriteria pencarian Anda.' : 'Tidak ada ekstensi domain yang tersedia saat ini.' }}
                </p>
            </div>

            <div class="mt-12 pt-16 text-center">
                <Card style="background-color: #141413; border: 1px solid #30302e; border-radius: 16px; box-shadow: rgba(0,0,0,0.05) 0px 4px 24px;">
                    <CardContent class="py-12">
                        <h2 class="mb-4 text-3xl font-medium" style="color: #faf9f5; line-height: 1.2; font-family: Georgia, serif;">
                            Siap untuk Memulai?
                        </h2>
                        <p class="mx-auto mb-8 max-w-2xl text-base sm:text-lg" style="color: #b0aea5; line-height: 1.6;">
                            Bergabunglah dengan ribuan pelanggan yang puas yang mempercayai kebutuhan domain dan hosting mereka kepada kami.
                        </p>
                        <div class="flex flex-col gap-3 sm:flex-row sm:justify-center sm:gap-4">
                            <Button asChild size="lg" class="text-lg px-6 py-4" style="background-color: #c96442; color: #faf9f5; border-radius: 12px;">
                                <Link href="/customer/register">Buat Akun</Link>
                            </Button>
                            <Button asChild variant="outline" size="lg" class="text-lg px-6 py-4" style="background-color: #ffffff; color: #141413; border: 1px solid #30302e; border-radius: 12px;">
                                <Link href="/hosting">Lihat Paket Hosting</Link>
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </section>
    </CustomerPublicLayout>
</template>
