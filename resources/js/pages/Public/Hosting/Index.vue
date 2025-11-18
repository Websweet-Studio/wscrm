<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import CustomerPublicLayout from '@/layouts/CustomerPublicLayout.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { Check, Cpu, Filter, HardDrive, MemoryStick, Search, Server, ShoppingCart, Wifi } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface HostingPlan {
    id: number;
    plan_name: string;
    storage_gb: number;
    cpu_cores: number;
    ram_gb: number;
    bandwidth: string;
    selling_price: number;
    discount_percent: number;
    features: string[];
    is_active: boolean;
}

interface Props {
    hostingPlans: HostingPlan[];
    filters: {
        search?: string;
    };
}

const props = defineProps<Props>();

const page = usePage();
const isCustomer = page.props.auth?.customer !== null;
const isAdmin = page.props.auth?.user !== null;

const search = ref(props.filters.search || '');
const activeTab = ref('basic');

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(price);
};

const getDiscountedPrice = (price: number, discount: number) => {
    return price * (1 - discount / 100);
};

const getActionUrl = () => {
    if (isCustomer) {
        return '/customer/hosting'; // Customer dashboard hosting
    } else if (isAdmin) {
        return '/dashboard'; // Admin dashboard
    } else {
        return '/customer/register'; // Registration for guests
    }
};

const getSecondaryActionUrl = () => {
    if (isCustomer || isAdmin) {
        return '/customer/hosting'; // Same as primary for logged-in users
    } else {
        return '/customer/login'; // Login for guests
    }
};

const handleSearch = () => {
    router.get(
        '/hosting',
        { search: search.value },
        {
            preserveState: true,
            replace: true,
        },
    );
};

const filteredPlans = computed(() => {
    let plans = props.hostingPlans;

    // Filter by search
    if (search.value) {
        plans = plans.filter((plan) => plan.plan_name.toLowerCase().includes(search.value.toLowerCase()));
    }

    // Filter by tab (category)
    if (activeTab.value === 'basic') {
        plans = plans.filter(
            (plan) =>
                plan.plan_name.toLowerCase().includes('basic') ||
                plan.plan_name.toLowerCase().includes('starter') ||
                plan.plan_name.toLowerCase().includes('standard'),
        );
    } else if (activeTab.value === 'lite') {
        plans = plans.filter(
            (plan) =>
                plan.plan_name.toLowerCase().includes('lite') ||
                plan.plan_name.toLowerCase().includes('light') ||
                plan.plan_name.toLowerCase().includes('minimal'),
        );
    } else if (activeTab.value === 'premium') {
        plans = plans.filter(
            (plan) =>
                plan.plan_name.toLowerCase().includes('premium') ||
                plan.plan_name.toLowerCase().includes('pro') ||
                plan.plan_name.toLowerCase().includes('advanced'),
        );
    }
    // If activeTab is 'all', no filtering by category

    return plans.sort((a, b) => a.selling_price - b.selling_price);
});
</script>

<template>
    <CustomerPublicLayout title="Paket Hosting Web Profesional - WebSweetStudio">
        <!-- Hero Section -->
        <section class="mx-auto max-w-6xl px-4 py-12 sm:px-6 sm:py-16">
            <div class="mb-8 space-y-4 text-center sm:mb-12 sm:space-y-6">
                <h1 class="text-3xl font-bold tracking-tight sm:text-4xl lg:text-5xl">Paket Hosting Web Profesional</h1>
                <p class="mx-auto max-w-3xl text-base text-muted-foreground sm:text-lg lg:text-xl">
                    Pilih paket hosting yang sempurna untuk website Anda. Hosting yang cepat, terpercaya, dan aman.
                </p>
            </div>

            <!-- Search -->
            <Card class="mx-auto mb-8 max-w-2xl sm:mb-12">
                <CardContent class="pt-6">
                    <div class="flex flex-col space-y-3 sm:flex-row sm:items-center sm:space-y-0 sm:space-x-2">
                        <div class="relative flex-1">
                            <Search class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 transform text-muted-foreground" />
                            <Input v-model="search" placeholder="Cari paket hosting..." class="pl-10" @keyup.enter="handleSearch" />
                        </div>
                        <Button @click="handleSearch" class="w-full sm:w-auto">Cari</Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Plan Category Tabs -->
            <div class="mx-auto mb-8 max-w-6xl">
                <Card>
                    <CardContent class="pt-6">
                        <div class="flex items-center justify-center">
                            <div class="grid w-full max-w-2xl grid-cols-2 gap-1 rounded-lg bg-muted p-1 md:flex md:items-center md:space-x-1">
                                <Button
                                    @click="activeTab = 'basic'"
                                    :variant="activeTab === 'basic' ? 'default' : 'ghost'"
                                    size="sm"
                                    class="flex-1 px-2 text-xs md:px-4 md:text-sm"
                                >
                                    <Filter class="mr-1 h-3 w-3 md:mr-2 md:h-4 md:w-4" />
                                    <span class="hidden md:inline">Paket </span>Dasar
                                </Button>
                                <Button
                                    @click="activeTab = 'lite'"
                                    :variant="activeTab === 'lite' ? 'default' : 'ghost'"
                                    size="sm"
                                    class="flex-1 px-2 text-xs md:px-4 md:text-sm"
                                >
                                    <Server class="mr-1 h-3 w-3 md:mr-2 md:h-4 md:w-4" />
                                    <span class="hidden md:inline">Paket </span>Lite
                                </Button>
                                <Button
                                    @click="activeTab = 'premium'"
                                    :variant="activeTab === 'premium' ? 'default' : 'ghost'"
                                    size="sm"
                                    class="flex-1 px-2 text-xs md:px-4 md:text-sm"
                                >
                                    <Check class="mr-1 h-3 w-3 md:mr-2 md:h-4 md:w-4" />
                                    <span class="hidden md:inline">Paket </span>Premium
                                </Button>
                                <Button
                                    @click="activeTab = 'all'"
                                    :variant="activeTab === 'all' ? 'default' : 'ghost'"
                                    size="sm"
                                    class="flex-1 px-2 text-xs md:px-4 md:text-sm"
                                >
                                    <span class="hidden sm:inline">Semua </span>Paket
                                </Button>
                            </div>
                        </div>

                        <!-- Active Filter Info -->
                        <div class="mt-4 space-y-2 text-center">
                            <p class="px-4 text-xs text-muted-foreground sm:text-sm">
                                <span v-if="activeTab === 'basic'">
                                    <span class="hidden sm:inline">Menampilkan paket hosting Dasar & Standar - sempurna untuk website pribadi</span>
                                    <span class="sm:hidden">Paket Dasar untuk website pribadi</span>
                                </span>
                                <span v-else-if="activeTab === 'lite'">
                                    <span class="hidden sm:inline">Menampilkan paket hosting Lite & Minimal - bagus untuk proyek kecil</span>
                                    <span class="sm:hidden">Paket Lite untuk proyek kecil</span>
                                </span>
                                <span v-else-if="activeTab === 'premium'">
                                    <span class="hidden sm:inline">Menampilkan paket hosting Premium & Pro - ideal untuk website bisnis</span>
                                    <span class="sm:hidden">Paket Premium untuk bisnis</span>
                                </span>
                                <span v-else>
                                    <span class="hidden sm:inline">Menampilkan semua paket hosting yang tersedia</span>
                                    <span class="sm:hidden">Semua paket tersedia</span>
                                </span>
                            </p>
                            <p class="text-xs text-muted-foreground">{{ filteredPlans.length }} paket tersedia</p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Hosting Plans List -->
            <div class="mx-auto max-w-6xl">
                <Card>
                    <CardHeader>
                        <CardTitle class="text-center">Bandingkan Paket Hosting</CardTitle>
                        <CardDescription class="text-center">Pilih paket yang sempurna untuk kebutuhan Anda</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <!-- Table Header -->
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[800px]">
                                <thead>
                                    <tr class="border-b">
                                        <th class="px-2 py-4 text-left font-semibold sm:px-4">Detail Paket</th>
                                        <th class="px-2 py-4 text-center font-semibold sm:px-4">
                                            <HardDrive class="mx-auto mb-1 h-4 w-4" />
                                            <span class="hidden sm:inline">Penyimpanan</span>
                                            <span class="sm:hidden">Storage</span>
                                        </th>
                                        <th class="px-2 py-4 text-center font-semibold sm:px-4">
                                            <Cpu class="mx-auto mb-1 h-4 w-4" />
                                            <span class="hidden sm:inline">CPU Core</span>
                                            <span class="sm:hidden">CPU</span>
                                        </th>
                                        <th class="px-2 py-4 text-center font-semibold sm:px-4">
                                            <MemoryStick class="mx-auto mb-1 h-4 w-4" />
                                            <span class="hidden sm:inline">Memori</span>
                                            <span class="sm:hidden">RAM</span>
                                        </th>
                                        <th class="px-2 py-4 text-center font-semibold sm:px-4">
                                            <Wifi class="mx-auto mb-1 h-4 w-4" />
                                            <span class="hidden sm:inline">Bandwidth</span>
                                            <span class="sm:hidden">BW</span>
                                        </th>
                                        <th class="px-2 py-4 text-center font-semibold sm:px-4">Harga</th>
                                        <th class="px-2 py-4 text-center font-semibold sm:px-4">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="plan in filteredPlans" :key="plan.id" class="border-b transition-colors hover:bg-muted/50">
                                        <!-- Plan Details -->
                                        <td class="px-2 py-4 sm:px-4 sm:py-6">
                                            <div class="space-y-2">
                                                <div class="flex items-center space-x-2">
                                                    <div class="rounded bg-blue-100 p-2">
                                                        <Server class="h-4 w-4 text-blue-600" />
                                                    </div>
                                                    <div>
                                                        <h3 class="text-lg font-semibold">{{ plan.plan_name }}</h3>
                                                        <div v-if="plan.discount_percent > 0" class="flex items-center space-x-2">
                                                            <Badge class="bg-red-500 text-xs text-white">{{ plan.discount_percent }}% OFF</Badge>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Features Preview -->
                                                <div v-if="plan.features && plan.features.length > 0" class="text-xs text-muted-foreground">
                                                    <div class="flex flex-wrap gap-1">
                                                        <span
                                                            v-for="(feature, index) in plan.features.slice(0, 2)"
                                                            :key="index"
                                                            class="inline-flex items-center"
                                                        >
                                                            <Check class="mr-1 h-3 w-3 text-green-500" />
                                                            {{ feature }}
                                                            <span v-if="index < 1 && plan.features.length > 1" class="mx-1">•</span>
                                                        </span>
                                                        <span v-if="plan.features.length > 2" class="text-blue-600">
                                                            +{{ plan.features.length - 2 }} more
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Storage -->
                                        <td class="px-2 py-4 text-center sm:px-4 sm:py-6">
                                            <div class="text-base font-semibold sm:text-lg">{{ plan.storage_gb }}GB</div>
                                            <div class="text-xs text-muted-foreground">SSD Storage</div>
                                        </td>

                                        <!-- CPU -->
                                        <td class="px-2 py-4 text-center sm:px-4 sm:py-6">
                                            <div class="text-base font-semibold sm:text-lg">{{ plan.cpu_cores }}</div>
                                            <div class="text-xs text-muted-foreground">vCPU Core</div>
                                        </td>

                                        <!-- RAM -->
                                        <td class="px-2 py-4 text-center sm:px-4 sm:py-6">
                                            <div class="text-base font-semibold sm:text-lg">{{ plan.ram_gb }}GB</div>
                                            <div class="text-xs text-muted-foreground">Memori</div>
                                        </td>

                                        <!-- Bandwidth -->
                                        <td class="px-2 py-4 text-center sm:px-4 sm:py-6">
                                            <div class="text-base font-semibold sm:text-lg">{{ plan.bandwidth }}</div>
                                            <div class="text-xs text-muted-foreground">Transfer</div>
                                        </td>

                                        <!-- Pricing -->
                                        <td class="px-2 py-4 text-center sm:px-4 sm:py-6">
                                            <div class="space-y-1">
                                                <div v-if="plan.discount_percent > 0" class="text-sm text-muted-foreground line-through">
                                                    {{ formatPrice(plan.selling_price) }}
                                                </div>
                                                <div class="text-lg font-bold text-blue-600 sm:text-2xl">
                                                    {{ formatPrice(getDiscountedPrice(plan.selling_price, plan.discount_percent)) }}
                                                </div>
                                                <div class="text-xs text-muted-foreground">per tahun</div>
                                                <div v-if="plan.discount_percent > 0" class="text-xs font-semibold text-green-600">
                                                    Hemat
                                                    {{
                                                        formatPrice(
                                                            plan.selling_price - getDiscountedPrice(plan.selling_price, plan.discount_percent),
                                                        )
                                                    }}/tahun
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Action -->
                                        <td class="px-2 py-4 text-center sm:px-4 sm:py-6">
                                            <div class="space-y-2">
                                                <Button asChild size="sm" class="w-full">
                                                    <Link :href="getActionUrl()">
                                                        <ShoppingCart class="mr-1 h-4 w-4 sm:mr-2" />
                                                        <span class="hidden sm:inline">{{ isCustomer || isAdmin ? 'Kelola' : 'Mulai' }}</span>
                                                        <span class="sm:hidden">{{ isCustomer || isAdmin ? 'Kelola' : 'Daftar' }}</span>
                                                    </Link>
                                                </Button>
                                                <Button v-if="!isCustomer && !isAdmin" variant="outline" asChild size="sm" class="w-full">
                                                    <Link :href="getSecondaryActionUrl()">Pesan Sekarang</Link>
                                                </Button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Empty State -->
            <div v-if="filteredPlans.length === 0" class="py-8 text-center sm:py-12">
                <Server class="mx-auto mb-4 h-12 w-12 text-muted-foreground" />
                <h3 class="mb-2 text-lg font-semibold">Tidak ada paket hosting ditemukan</h3>
                <p class="text-muted-foreground">
                    {{
                        search
                            ? 'Coba sesuaikan kriteria pencarian Anda atau beralih ke kategori lain.'
                            : `Tidak ada paket hosting ${activeTab === 'all' ? '' : activeTab + ' '}yang tersedia saat ini.`
                    }}
                </p>
                <div class="mt-4">
                    <Button @click="activeTab = 'all'" variant="outline" v-if="activeTab !== 'all'">Lihat Semua Paket</Button>
                </div>
            </div>

            <!-- CTA Section -->
            <div v-if="!isCustomer && !isAdmin" class="mt-12 pt-16 text-center sm:mt-16 sm:pt-24">
                <h2 class="mb-4 text-2xl font-bold sm:mb-6 sm:text-3xl">Siap untuk Memulai?</h2>
                <p class="mx-auto mb-6 max-w-2xl text-muted-foreground sm:mb-8">
                    Bergabunglah dengan ribuan pelanggan yang puas yang mempercayai kebutuhan hosting mereka kepada kami.
                </p>
                <div class="flex flex-col gap-3 sm:flex-row sm:justify-center sm:gap-4">
                    <Button asChild size="lg" class="w-full sm:w-auto">
                        <Link href="/customer/register">Buat Akun</Link>
                    </Button>
                    <Button variant="outline" asChild size="lg" class="w-full sm:w-auto">
                        <Link href="/domains">Jelajahi Domain</Link>
                    </Button>
                </div>
            </div>
        </section>
    </CustomerPublicLayout>
</template>
