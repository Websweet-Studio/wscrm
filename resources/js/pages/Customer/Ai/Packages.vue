<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import ConfirmModal from '@/components/ConfirmModal.vue';
import CustomerLayout from '@/layouts/CustomerLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowRight, Bot, Coins, Sparkles, ShoppingCart, Zap } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Package {
    id: number;
    name: string;
    credits: number;
    price: string;
    discount_amount: string | null;
    is_active: boolean;
    final_price: number;
}

const props = defineProps<{
    balance: number;
    packages: Package[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/customer/dashboard' },
    { title: 'Paket Kredit AI', href: '/customer/ai/packages' },
];

const formatPrice = (price: number): string =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(price);

const showBuyModal = ref(false);
const selectedPkg = ref<Package | null>(null);

const openBuy = (pkg: Package) => { selectedPkg.value = pkg; showBuyModal.value = true; };

const confirmBuy = () => {
    showBuyModal.value = false;
    if (selectedPkg.value) {
        router.post(`/customer/ai/packages/${selectedPkg.value.id}/buy`, undefined, { preserveScroll: true });
    }
};

const buyMessage = computed(() =>
    `Beli paket "${selectedPkg.value?.name || ''}" senilai ${selectedPkg.value ? formatPrice(selectedPkg.value.final_price) : ''}?`
);

const lowestPrice = computed(() =>
    props.packages.length ? Math.min(...props.packages.map((p) => p.final_price)) : 0
);

const hasDiscount = (pkg: Package): boolean => pkg.discount_amount !== null && Number(pkg.discount_amount) > 0;
</script>

<template>
    <Head title="Paket Kredit AI" />

    <CustomerLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-4 sm:space-y-6 sm:p-6">
            <!-- Hero Card -->
            <Card class="relative overflow-hidden border-border/60 bg-card/70 shadow-sm backdrop-blur">
                <div class="pointer-events-none absolute inset-0 opacity-60 dark:opacity-80">
                    <div class="absolute -inset-24 bg-[radial-gradient(closest-side,rgba(245,158,11,0.16),transparent_65%)]"></div>
                    <div class="absolute -right-24 -top-32 h-96 w-96 bg-[radial-gradient(closest-side,rgba(34,211,238,0.14),transparent_60%)]"></div>
                    <div class="absolute inset-0 bg-[linear-gradient(to_right,transparent_0,rgba(245,158,11,0.05)_50%,transparent_100%)]"></div>
                </div>
                <CardContent class="relative p-4 sm:p-6">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <div class="mb-2 inline-flex items-center gap-2 rounded-full border border-border/60 bg-background/60 px-2.5 py-1 text-xs text-muted-foreground">
                                <Sparkles class="h-3.5 w-3.5 text-amber-600 dark:text-amber-400" />
                                <span>AI &amp; LLM</span>
                            </div>
                            <h1 class="font-heading text-2xl font-medium tracking-tight sm:text-3xl">Paket Kredit AI</h1>
                            <p class="mt-1 text-sm text-muted-foreground sm:text-base">Beli kredit untuk memakai AI Assistant. Saldo otomatis masuk setelah invoice lunas.</p>
                        </div>
                        <div class="flex w-full flex-col gap-2 sm:w-auto sm:min-w-[240px]">
                            <Button asChild size="sm" class="w-full justify-between">
                                <Link href="/customer/ai">
                                    <span class="inline-flex items-center gap-2">
                                        <Bot class="h-4 w-4" />
                                        Buka Chat
                                    </span>
                                    <ArrowRight class="h-4 w-4 opacity-80" />
                                </Link>
                            </Button>
                            <Button asChild variant="outline" size="sm" class="w-full justify-between">
                                <Link href="/customer/ai">
                                    <span class="inline-flex items-center gap-2">
                                        <Coins class="h-4 w-4 text-amber-600 dark:text-amber-400" />
                                        Saldo: {{ balance.toLocaleString('id-ID') }}
                                    </span>
                                    <ArrowRight class="h-4 w-4 opacity-70" />
                                </Link>
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Stat Cards -->
            <div class="grid gap-3 sm:grid-cols-2 sm:gap-4 lg:grid-cols-3">
                <Card class="rounded-lg border-border/60 shadow-sm">
                    <CardHeader class="pb-2">
                        <CardDescription>Saldo Anda</CardDescription>
                        <CardTitle class="text-2xl font-semibold">{{ balance.toLocaleString('id-ID') }}</CardTitle>
                    </CardHeader>
                </Card>
                <Card class="rounded-lg border-border/60 shadow-sm">
                    <CardHeader class="pb-2">
                        <CardDescription>Paket Tersedia</CardDescription>
                        <CardTitle class="text-2xl font-semibold">{{ packages.length }}</CardTitle>
                    </CardHeader>
                </Card>
                <Card class="rounded-lg border-border/60 shadow-sm">
                    <CardHeader class="pb-2">
                        <CardDescription>Harga Mulai</CardDescription>
                        <CardTitle class="text-2xl font-semibold">{{ lowestPrice ? formatPrice(lowestPrice) : '-' }}</CardTitle>
                    </CardHeader>
                </Card>
            </div>

            <!-- Packages Grid -->
            <div v-if="packages.length === 0" class="rounded-lg border border-border/60 bg-card p-10 text-center text-muted-foreground">
                Belum ada paket kredit tersedia. Silakan hubungi admin.
            </div>

            <div v-else class="grid gap-4 sm:grid-cols-2 sm:gap-6 lg:grid-cols-3">
                <Card
                    v-for="pkg in packages"
                    :key="pkg.id"
                    class="relative flex flex-col overflow-hidden rounded-lg border-border/60 shadow-sm transition-shadow hover:shadow-md"
                >
                    <div v-if="hasDiscount(pkg)" class="absolute right-0 top-0">
                        <Badge class="rounded-none rounded-bl-lg bg-amber-600 text-white hover:bg-amber-600">Diskon</Badge>
                    </div>
                    <CardHeader>
                        <CardTitle class="text-lg font-semibold">{{ pkg.name }}</CardTitle>
                        <CardDescription class="flex items-center gap-1.5">
                            <Coins class="h-4 w-4 text-amber-500" /> {{ pkg.credits.toLocaleString('id-ID') }} kredit
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="flex flex-1 flex-col justify-between gap-4">
                        <div>
                            <div v-if="hasDiscount(pkg)" class="text-sm text-muted-foreground line-through">{{ formatPrice(Number(pkg.price)) }}</div>
                            <div class="text-3xl font-bold">{{ formatPrice(pkg.final_price) }}</div>
                            <div v-if="hasDiscount(pkg)" class="text-xs text-muted-foreground">Hemat {{ formatPrice(Number(pkg.discount_amount)) }}</div>
                            <div class="mt-3 inline-flex items-center gap-1.5 rounded-full border border-border/60 bg-muted/40 px-2.5 py-1 text-xs text-muted-foreground">
                                <Zap class="h-3.5 w-3.5 text-amber-600 dark:text-amber-400" />
                                {{ formatPrice(pkg.final_price / pkg.credits) }} / kredit
                            </div>
                        </div>
                        <Button class="w-full cursor-pointer" @click="openBuy(pkg)">
                            <ShoppingCart class="mr-2 h-4 w-4" /> Beli Paket
                        </Button>
                    </CardContent>
                </Card>
            </div>
        </div>

        <ConfirmModal
            :show="showBuyModal"
            title="Beli Paket"
            :message="buyMessage"
            confirmText="Beli"
            @confirm="confirmBuy"
            @cancel="showBuyModal = false"
        />
    </CustomerLayout>
</template>
