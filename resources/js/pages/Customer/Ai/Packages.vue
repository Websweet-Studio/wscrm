<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import ConfirmModal from '@/components/ConfirmModal.vue';
import CustomerLayout from '@/layouts/CustomerLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Bot, Coins, ShoppingCart } from 'lucide-vue-next';
import { ref } from 'vue';

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
</script>

<template>
    <CustomerLayout :breadcrumbs="breadcrumbs">
        <Head title="Paket Kredit AI" />

        <div class="space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold">Paket Kredit AI</h1>
                    <p class="text-muted-foreground">Beli kredit untuk memakai AI Assistant. Saldo otomatis masuk setelah invoice lunas.</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2 rounded-lg border bg-card px-4 py-2">
                        <Coins class="h-5 w-5 text-amber-500" />
                        <div>
                            <div class="text-xs text-muted-foreground">Saldo Anda</div>
                            <div class="text-lg font-bold leading-tight">{{ balance.toLocaleString('id-ID') }}</div>
                        </div>
                    </div>
                    <Link href="/customer/ai">
                        <Button variant="outline" class="cursor-pointer"><Bot class="mr-2 h-4 w-4" /> Buka Chat</Button>
                    </Link>
                </div>
            </div>

            <div v-if="packages.length === 0" class="rounded-lg border bg-card p-10 text-center text-muted-foreground">
                Belum ada paket kredit tersedia. Silakan hubungi admin.
            </div>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <Card v-for="pkg in packages" :key="pkg.id" class="relative overflow-hidden">
                    <CardHeader>
                        <CardTitle class="flex items-center justify-between">
                            {{ pkg.name }}
                            <Badge v-if="pkg.discount_amount !== null" variant="destructive">Diskon</Badge>
                        </CardTitle>
                        <CardDescription class="flex items-center gap-1">
                            <Coins class="h-4 w-4 text-amber-500" /> {{ pkg.credits.toLocaleString('id-ID') }} kredit
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div>
                            <div v-if="pkg.discount_amount !== null" class="text-sm text-muted-foreground line-through">{{ formatPrice(Number(pkg.price)) }}</div>
                            <div class="text-3xl font-bold">{{ formatPrice(pkg.final_price) }}</div>
                            <div v-if="pkg.discount_amount !== null" class="text-xs text-muted-foreground">Hemat {{ formatPrice(Number(pkg.discount_amount)) }}</div>
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
            :message="`Beli paket \"${selectedPkg?.name || ''}\" senilai ${selectedPkg ? formatPrice(selectedPkg.final_price) : ''}?`"
            confirmText="Beli"
            @confirm="confirmBuy"
            @cancel="showBuyModal = false"
        />
    </CustomerLayout>
</template>
