<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
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
        return '/customer/hosting';
    } else if (isAdmin) {
        return '/dashboard';
    } else {
        return '/customer/register';
    }
};

const getSecondaryActionUrl = () => {
    if (isCustomer || isAdmin) {
        return '/customer/hosting';
    } else {
        return '/customer/login';
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

    if (search.value) {
        plans = plans.filter((plan) => plan.plan_name.toLowerCase().includes(search.value.toLowerCase()));
    }

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

    return plans.sort((a, b) => a.selling_price - b.selling_price);
});
</script>

<template>
    <CustomerPublicLayout title="Paket Hosting Web Profesional">
        <section class="mx-auto max-w-6xl px-4 py-12 sm:px-6 sm:py-16 lg:py-20">
            <div class="mb-12 text-center sm:mb-16">
                <h1 class="mb-4 text-4xl leading-tight font-medium sm:text-5xl md:text-6xl" style="color: #141413; line-height: 1.1; font-family: Georgia, serif;">
                    Paket Hosting Web Profesional
                </h1>
                <p class="mx-auto mb-6 max-w-2xl text-base sm:text-lg lg:text-xl" style="color: #5e5d59; line-height: 1.6;">
                    Pilih paket hosting yang sempurna untuk website Anda. Hosting yang cepat, terpercaya, dan aman.
                </p>
            </div>

            <div class="mb-12">
                <Card style="background-color: #faf9f5; border: 1px solid #f0eee6; border-radius: 16px; box-shadow: rgba(0,0,0,0.05) 0px 4px 24px;">
                    <CardHeader>
                        <div class="flex items-center gap-3">
                            <div class="rounded-full p-2" style="background-color: #e8e6dc;">
                                <Search class="h-6 w-6" style="color: #c96442;" />
                            </div>
                            <div>
                                <CardTitle class="text-xl font-medium" style="color: #141413; font-family: Georgia, serif;">Cari Paket Hosting</CardTitle>
                                <CardDescription style="color: #5e5d59;">Temukan paket yang sesuai dengan kebutuhan Anda</CardDescription>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="flex gap-2">
                            <div class="relative flex-1">
                                <Search class="absolute top-2.5 left-3 h-5 w-5" style="color: #87867f;" />
                                <Input v-model="search" placeholder="Cari paket hosting..." class="pl-10" style="background-color: #ffffff; border: 1px solid #e8e6dc; color: #141413; border-radius: 12px;" @keyup.enter="handleSearch" />
                            </div>
                            <Button @click="handleSearch" style="background-color: #c96442; color: #faf9f5; border-radius: 12px;">Cari</Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div class="mb-12">
                <Card style="background-color: #faf9f5; border: 1px solid #f0eee6; border-radius: 16px; box-shadow: rgba(0,0,0,0.05) 0px 4px 24px;">
                    <CardHeader>
                        <div class="flex items-center gap-3">
                            <div class="rounded-full p-2" style="background-color: #e8e6dc;">
                                <Filter class="h-6 w-6" style="color: #c96442;" />
                            </div>
                            <div>
                                <CardTitle class="text-xl font-medium" style="color: #141413; font-family: Georgia, serif;">Kategori Paket</CardTitle>
                                <CardDescription style="color: #5e5d59;">Pilih kategori paket yang sesuai</CardDescription>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-2 gap-2 sm:flex sm:items-center sm:justify-center sm:gap-2">
                            <Button
                                @click="activeTab = 'basic'"
                                size="sm"
                                class="flex-1 px-3 py-2 text-sm"
                                :style="activeTab === 'basic' ? 'background-color: #c96442; color: #faf9f5; border-radius: 12px;' : 'background-color: #e8e6dc; color: #4d4c48; border-radius: 12px;'"
                            >
                                <Server class="mr-2 h-4 w-4" />
                                Dasar
                            </Button>
                            <Button
                                @click="activeTab = 'lite'"
                                size="sm"
                                class="flex-1 px-3 py-2 text-sm"
                                :style="activeTab === 'lite' ? 'background-color: #c96442; color: #faf9f5; border-radius: 12px;' : 'background-color: #e8e6dc; color: #4d4c48; border-radius: 12px;'"
                            >
                                <Server class="mr-2 h-4 w-4" />
                                Lite
                            </Button>
                            <Button
                                @click="activeTab = 'premium'"
                                size="sm"
                                class="flex-1 px-3 py-2 text-sm"
                                :style="activeTab === 'premium' ? 'background-color: #c96442; color: #faf9f5; border-radius: 12px;' : 'background-color: #e8e6dc; color: #4d4c48; border-radius: 12px;'"
                            >
                                <Check class="mr-2 h-4 w-4" />
                                Premium
                            </Button>
                            <Button
                                @click="activeTab = 'all'"
                                size="sm"
                                class="flex-1 px-3 py-2 text-sm"
                                :style="activeTab === 'all' ? 'background-color: #c96442; color: #faf9f5; border-radius: 12px;' : 'background-color: #e8e6dc; color: #4d4c48; border-radius: 12px;'"
                            >
                                Semua
                            </Button>
                        </div>

                        <div class="mt-4 space-y-2 text-center">
                            <p class="text-sm" style="color: #5e5d59;">
                                <span v-if="activeTab === 'basic'">
                                    Menampilkan paket hosting Dasar & Standar - sempurna untuk website pribadi
                                </span>
                                <span v-else-if="activeTab === 'lite'">
                                    Menampilkan paket hosting Lite & Minimal - bagus untuk proyek kecil
                                </span>
                                <span v-else-if="activeTab === 'premium'">
                                    Menampilkan paket hosting Premium & Pro - ideal untuk website bisnis
                                </span>
                                <span v-else>
                                    Menampilkan semua paket hosting yang tersedia
                                </span>
                            </p>
                            <p class="text-sm" style="color: #87867f;">{{ filteredPlans.length }} paket tersedia</p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div class="mb-12">
                <Card style="background-color: #faf9f5; border: 1px solid #f0eee6; border-radius: 16px; box-shadow: rgba(0,0,0,0.05) 0px 4px 24px;">
                    <CardHeader>
                        <CardTitle class="text-center text-xl font-medium" style="color: #141413; font-family: Georgia, serif;">
                            Bandingkan Paket Hosting
                        </CardTitle>
                        <CardDescription class="text-center" style="color: #5e5d59;">
                            Pilih paket yang sempurna untuk kebutuhan Anda
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                            <Card v-for="plan in filteredPlans" :key="plan.id" class="relative transition-all hover:shadow-lg" style="background-color: #ffffff; border: 1px solid #f0eee6; border-radius: 16px;">
                                <CardHeader>
                                    <div class="flex items-start justify-between">
                                        <div class="flex items-center gap-3">
                                            <div class="rounded-full p-2" style="background-color: #e8e6dc;">
                                                <Server class="h-5 w-5" style="color: #c96442;" />
                                            </div>
                                            <div>
                                                <CardTitle class="text-lg font-medium" style="color: #141413; font-family: Georgia, serif;">{{ plan.plan_name }}</CardTitle>
                                            </div>
                                        </div>
                                        <Badge v-if="plan.discount_percent > 0" style="background-color: #c96442; color: #faf9f5; border-radius: 8px;">
                                            {{ plan.discount_percent }}% OFF
                                        </Badge>
                                    </div>
                                </CardHeader>

                                <CardContent class="space-y-4">
                                    <div class="text-center">
                                        <div v-if="plan.discount_percent > 0" class="text-sm line-through" style="color: #87867f;">
                                            {{ formatPrice(getDiscountedPrice(plan.selling_price, plan.discount_percent)) }}
                                        </div>
                                        <div class="text-3xl font-bold" style="color: #c96442;">
                                            {{ formatPrice(plan.selling_price) }}
                                        </div>
                                        <div class="text-sm" style="color: #5e5d59;">/tahun</div>
                                        <div v-if="plan.discount_percent > 0" class="mt-1 text-xs font-medium" style="color: #4d4c48;">
                                            Hemat {{ formatPrice(plan.selling_price - getDiscountedPrice(plan.selling_price, plan.discount_percent)) }}/tahun
                                        </div>
                                    </div>

                                    <Separator style="background-color: #f0eee6;" />

                                    <div class="space-y-3 text-sm">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <HardDrive class="h-4 w-4" style="color: #87867f;" />
                                                <span style="color: #5e5d59;">Penyimpanan</span>
                                            </div>
                                            <span class="font-medium" style="color: #4d4c48;">{{ plan.storage_gb }}GB SSD</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <Cpu class="h-4 w-4" style="color: #87867f;" />
                                                <span style="color: #5e5d59;">CPU Core</span>
                                            </div>
                                            <span class="font-medium" style="color: #4d4c48;">{{ plan.cpu_cores }} vCPU</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <MemoryStick class="h-4 w-4" style="color: #87867f;" />
                                                <span style="color: #5e5d59;">Memori</span>
                                            </div>
                                            <span class="font-medium" style="color: #4d4c48;">{{ plan.ram_gb }}GB RAM</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <Wifi class="h-4 w-4" style="color: #87867f;" />
                                                <span style="color: #5e5d59;">Bandwidth</span>
                                            </div>
                                            <span class="font-medium" style="color: #4d4c48;">{{ plan.bandwidth }}</span>
                                        </div>
                                    </div>

                                    <Separator style="background-color: #f0eee6;" />

                                    <div v-if="plan.features && plan.features.length > 0" class="space-y-2">
                                        <Label class="text-sm font-medium" style="color: #4d4c48;">Fitur</Label>
                                        <div class="space-y-1 text-sm">
                                            <div v-for="(feature, index) in plan.features" :key="index" class="flex items-center gap-2">
                                                <Check class="h-4 w-4" style="color: #c96442;" />
                                                <span style="color: #5e5d59;">{{ feature }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </CardContent>

                                <div class="px-6 pb-6">
                                    <Button asChild class="w-full" size="lg" style="background-color: #c96442; color: #faf9f5; border-radius: 12px;">
                                        <Link :href="getActionUrl()">
                                            <ShoppingCart class="mr-2 h-4 w-4" />
                                            {{ isCustomer || isAdmin ? 'Kelola' : 'Mulai' }}
                                        </Link>
                                    </Button>
                                    <Button v-if="!isCustomer && !isAdmin" variant="outline" asChild class="w-full mt-2" size="lg" style="background-color: #ffffff; color: #4d4c48; border: 1px solid #e8e6dc; border-radius: 12px;">
                                        <Link :href="getSecondaryActionUrl()">Pesan Sekarang</Link>
                                    </Button>
                                </div>
                            </Card>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div v-if="filteredPlans.length === 0" class="py-12 text-center">
                <Server class="mx-auto mb-4 h-12 w-12" style="color: #87867f;" />
                <h3 class="mb-2 text-xl font-medium" style="color: #141413; font-family: Georgia, serif;">Tidak ada paket hosting ditemukan</h3>
                <p style="color: #5e5d59;">
                    {{
                        search
                            ? 'Coba sesuaikan kriteria pencarian Anda atau beralih ke kategori lain.'
                            : `Tidak ada paket hosting ${activeTab === 'all' ? '' : activeTab + ' '}yang tersedia saat ini.`
                    }}
                </p>
                <div class="mt-4">
                    <Button @click="activeTab = 'all'" v-if="activeTab !== 'all'" style="background-color: #e8e6dc; color: #4d4c48; border-radius: 12px;">Lihat Semua Paket</Button>
                </div>
            </div>

            <div v-if="!isCustomer && !isAdmin" class="mt-12 pt-16 text-center">
                <Card style="background-color: #141413; border: 1px solid #30302e; border-radius: 16px; box-shadow: rgba(0,0,0,0.05) 0px 4px 24px;">
                    <CardContent class="py-12">
                        <h2 class="mb-4 text-3xl font-medium" style="color: #faf9f5; line-height: 1.2; font-family: Georgia, serif;">
                            Siap untuk Memulai?
                        </h2>
                        <p class="mx-auto mb-8 max-w-2xl text-base sm:text-lg" style="color: #b0aea5; line-height: 1.6;">
                            Bergabunglah dengan ribuan pelanggan yang puas yang mempercayai kebutuhan hosting mereka kepada kami.
                        </p>
                        <div class="flex flex-col gap-3 sm:flex-row sm:justify-center sm:gap-4">
                            <Button asChild size="lg" class="text-lg px-6 py-4" style="background-color: #c96442; color: #faf9f5; border-radius: 12px;">
                                <Link href="/customer/register">Buat Akun</Link>
                            </Button>
                            <Button asChild variant="outline" size="lg" class="text-lg px-6 py-4" style="background-color: #ffffff; color: #141413; border: 1px solid #30302e; border-radius: 12px;">
                                <Link href="/domains">Jelajahi Domain</Link>
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </section>
    </CustomerPublicLayout>
</template>
