<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import axios from 'axios';
import { AlertTriangle, Calendar, FileText, Mail, MapPin, Package, Phone, ShoppingCart, ArrowUpDown, Loader2 } from 'lucide-vue-next';

interface Customer {
    id: number;
    name: string;
    email: string;
    phone?: string;
    address?: string;
    city?: string;
    country?: string;
}

interface OrderItem {
    id: number;
    item_type: string;
    domain_name?: string;
    quantity: number;
    price: number;
}

interface Invoice {
    id: number;
    invoice_number: string;
    total_amount: number;
    status: string;
    due_date: string;
    created_at: string;
}

interface HostingPlan {
    id: number;
    plan_name: string;
    storage_gb: number;
    cpu_cores: number;
    ram_gb: number;
    selling_price: number;
    features?: string[];
}

interface Order {
    id: number;
    total_amount: number;
    status: 'pending' | 'processing' | 'active' | 'suspended' | 'expired' | 'cancelled' | 'terminated';
    billing_cycle: string;
    domain_name?: string;
    expires_at?: string;
    auto_renew?: boolean;
    discount_amount?: number;
    created_at: string;
    updated_at: string;
    change_status?: 'none' | 'pending' | 'completed';
    pending_plan_id?: number;
    customer: Customer;
    order_items: OrderItem[];
    hosting_plan?: HostingPlan;
    pending_plan?: HostingPlan;
    invoice?: Invoice;
}

interface Props {
    order: Order;
    availablePlans: HostingPlan[];
}

const props = defineProps<Props>();

// Upgrade/Downgrade Modal State
const showUpgradeModal = ref(false);
const selectedPlanId = ref<number | null>(null);
const simulation = ref<any>(null);
const isSimulating = ref(false);
const isProcessing = ref(false);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Orders', href: '/admin/orders' },
    { title: `Order #${props.order.id}`, href: `/admin/orders/${props.order.id}` },
];

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(price);
};

const getStatusClass = (status: string) => {
    switch (status) {
        case 'completed':
        case 'active':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
        case 'processing':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
        case 'pending':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
        case 'suspended':
            return 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300';
        case 'expired':
        case 'cancelled':
        case 'terminated':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
        case 'paid':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
        case 'unpaid':
        case 'overdue':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
    }
};

const getDaysUntilExpiry = (expiresAt: string) => {
    const now = new Date();
    const expiry = new Date(expiresAt);
    const diffTime = expiry.getTime() - now.getTime();
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays;
};

const getExpiryBadgeClass = (daysLeft: number) => {
    if (daysLeft <= 0) {
        return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
    } else if (daysLeft <= 15) {
        return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
    } else if (daysLeft <= 30) {
        return 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300';
    } else if (daysLeft <= 90) {
        return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
    } else {
        return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
    }
};

const getStatusText = (status: string) => {
    const statusMap: Record<string, string> = {
        pending: 'Menunggu',
        processing: 'Diproses',
        active: 'Aktif',
        suspended: 'Ditangguhkan',
        expired: 'Kadaluarsa',
        cancelled: 'Dibatalkan',
        terminated: 'Dihentikan',
    };
    return statusMap[status] || status;
};

const getBillingCycleText = (cycle: string) => {
    const cycleMap: Record<string, string> = {
        onetime: 'Sekali Bayar',
        monthly: 'Bulanan',
        quarterly: 'Triwulan',
        semi_annually: '6 Bulan',
        annually: 'Tahunan',
    };
    return cycleMap[cycle] || cycle;
};

const totalItemsAmount = props.order.order_items.reduce((sum, item) => sum + item.price * item.quantity, 0);

// Upgrade/Downgrade Functions
const canUpgradeDowngrade = computed(() => {
    return props.order.status === 'active' &&
           props.order.hosting_plan &&
           props.order.change_status !== 'pending' &&
           props.order.expires_at &&
           getDaysUntilExpiry(props.order.expires_at) > 0;
});

const openUpgradeModal = () => {
    showUpgradeModal.value = true;
    selectedPlanId.value = null;
    simulation.value = null;
};

const closeUpgradeModal = () => {
    showUpgradeModal.value = false;
    selectedPlanId.value = null;
    simulation.value = null;
    isSimulating.value = false;
    isProcessing.value = false;
};

const simulateUpgradeDowngrade = async () => {
    if (!selectedPlanId.value) return;

    isSimulating.value = true;
    try {
        const response = await axios.post(`/admin/orders/${props.order.id}/simulate-upgrade-downgrade`, {
            new_plan_id: selectedPlanId.value
        });
        simulation.value = response.data;
    } catch (error) {
        console.error('Simulation error:', error);
    } finally {
        isSimulating.value = false;
    }
};

const processUpgradeDowngrade = () => {
    if (!selectedPlanId.value) return;

    isProcessing.value = true;
    router.post(`/admin/orders/${props.order.id}/process-upgrade-downgrade`, {
        new_plan_id: selectedPlanId.value
    }, {
        onFinish: () => {
            isProcessing.value = false;
            closeUpgradeModal();
        }
    });
};
</script>

<template>
    <Head :title="`Order #${order.id}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">{{ order.domain_name || `Order #${order.id}` }}</h1>
                    <p class="text-muted-foreground">Order details and customer information</p>
                </div>
                <div class="flex gap-3">
                    <Button
                        v-if="canUpgradeDowngrade"
                        variant="outline"
                        @click="openUpgradeModal"
                        class="flex items-center gap-2"
                    >
                        <ArrowUpDown class="h-4 w-4" />
                        Upgrade/Downgrade
                    </Button>
                    <Button variant="outline" asChild>
                        <Link href="/admin/orders" class="cursor-pointer"> Back to Orders </Link>
                    </Button>
                </div>
            </div>

            <!-- Expiry Warning for Services -->
            <Card v-if="order.expires_at && ['active', 'suspended'].includes(order.status)" 
                  :class="getDaysUntilExpiry(order.expires_at) <= 30 ? 'border-orange-200 bg-orange-50 dark:border-orange-800 dark:bg-orange-950' : ''">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2" 
                               :class="getDaysUntilExpiry(order.expires_at) <= 30 ? 'text-orange-800 dark:text-orange-200' : ''">
                        <AlertTriangle v-if="getDaysUntilExpiry(order.expires_at) <= 30" class="h-5 w-5" />
                        <Calendar v-else class="h-5 w-5" />
                        Informasi Masa Aktif
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm text-muted-foreground">Berakhir pada</div>
                            <div class="font-medium">{{ formatDate(order.expires_at) }}</div>
                            <div v-if="order.auto_renew" class="text-xs text-green-600 dark:text-green-400 mt-1">
                                ✓ Perpanjangan otomatis aktif
                            </div>
                        </div>
                        <div class="text-right">
                            <Badge 
                                :class="getExpiryBadgeClass(getDaysUntilExpiry(order.expires_at))"
                                class="mb-2"
                            >
                                <template v-if="getDaysUntilExpiry(order.expires_at) <= 0">
                                    Sudah Berakhir
                                </template>
                                <template v-else>
                                    {{ getDaysUntilExpiry(order.expires_at) }} hari lagi
                                </template>
                            </Badge>
                            <div v-if="getDaysUntilExpiry(order.expires_at) <= 15 && getDaysUntilExpiry(order.expires_at) > 0" 
                                 class="text-xs text-red-600 dark:text-red-400">
                                ⚠️ Segera perpanjang
                            </div>
                            <div v-else-if="getDaysUntilExpiry(order.expires_at) <= 30 && getDaysUntilExpiry(order.expires_at) > 15" 
                                 class="text-xs text-orange-600 dark:text-orange-400">
                                🔔 Persiapkan perpanjangan
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Order Overview -->
            <div class="grid gap-6 md:grid-cols-2">
                <!-- Order Information -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <ShoppingCart class="h-5 w-5" />
                            Order Information
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium">Status</span>
                            <Badge :class="getStatusClass(order.status)">
                                {{ getStatusText(order.status) }}
                            </Badge>
                        </div>

                        <div class="space-y-3">
                            <div v-if="order.domain_name" class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">Domain</span>
                                <span class="text-sm font-medium">{{ order.domain_name }}</span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">Siklus Tagihan</span>
                                <span class="text-sm font-medium">{{ getBillingCycleText(order.billing_cycle) }}</span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">Total Harga</span>
                                <div class="text-right">
                                    <template v-if="order.discount_amount && order.discount_amount > 0">
                                        <div class="text-xs text-muted-foreground line-through">
                                            {{ formatPrice(totalItemsAmount) }}
                                        </div>
                                        <div class="text-lg font-bold text-green-600 dark:text-green-400">
                                            {{ formatPrice(totalItemsAmount - Number(order.discount_amount)) }}
                                        </div>
                                        <div class="text-xs text-green-600 dark:text-green-400">
                                            Hemat: {{ formatPrice(order.discount_amount) }}
                                        </div>
                                    </template>
                                    <template v-else>
                                        <div class="text-lg font-bold">{{ formatPrice(order.total_amount) }}</div>
                                    </template>
                                </div>
                            </div>

                            <div v-if="order.expires_at" class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">Berakhir</span>
                                <span class="text-sm">{{ formatDate(order.expires_at) }}</span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">Dibuat</span>
                                <span class="text-sm">{{ formatDate(order.created_at) }}</span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">Terakhir Diperbarui</span>
                                <span class="text-sm">{{ formatDate(order.updated_at) }}</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Customer Information -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Mail class="h-5 w-5" />
                            Informasi Pelanggan
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-3">
                            <div>
                                <span class="text-sm font-medium">{{ order.customer.name }}</span>
                                <div class="text-sm text-muted-foreground">Customer ID: #{{ order.customer.id }}</div>
                            </div>

                            <div class="flex items-center gap-3">
                                <Mail class="h-4 w-4 text-muted-foreground" />
                                <span class="text-sm">{{ order.customer.email }}</span>
                            </div>

                            <div v-if="order.customer.phone" class="flex items-center gap-3">
                                <Phone class="h-4 w-4 text-muted-foreground" />
                                <span class="text-sm">{{ order.customer.phone }}</span>
                            </div>

                            <div v-if="order.customer.address || order.customer.city || order.customer.country" class="flex items-start gap-3">
                                <MapPin class="mt-0.5 h-4 w-4 text-muted-foreground" />
                                <div class="text-sm">
                                    <div v-if="order.customer.address">{{ order.customer.address }}</div>
                                    <div>
                                        {{ [order.customer.city, order.customer.country].filter(Boolean).join(', ') }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="border-t pt-3">
                            <Button size="sm" variant="outline" asChild>
                                <Link :href="`/admin/customers/${order.customer.id}`"> Lihat Detail Pelanggan </Link>
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Order Items -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Package class="h-5 w-5" />
                        Item Pesanan
                    </CardTitle>
                    <CardDescription>Item yang termasuk dalam pesanan ini</CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Tipe Item</TableHead>
                                <TableHead>Detail</TableHead>
                                <TableHead>Jumlah</TableHead>
                                <TableHead>Harga Satuan</TableHead>
                                <TableHead>Total</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="item in order.order_items" :key="item.id">
                                <TableCell class="font-medium capitalize">{{ item.item_type }}</TableCell>
                                <TableCell>
                                    <div v-if="item.domain_name" class="font-medium">{{ item.domain_name }}</div>
                                    <div class="text-sm text-muted-foreground">Item ID: #{{ item.item_id }}</div>
                                </TableCell>
                                <TableCell>{{ item.quantity }}</TableCell>
                                <TableCell>{{ formatPrice(item.price) }}</TableCell>
                                <TableCell class="font-medium">{{ formatPrice(item.price * item.quantity) }}</TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <div class="flex items-center justify-between border-t pt-4">
                        <span class="text-sm text-muted-foreground">Total ({{ order.order_items.length }} item)</span>
                        <span class="text-xl font-bold">{{ formatPrice(totalItemsAmount) }}</span>
                    </div>
                </CardContent>
            </Card>

            <!-- Invoice Information -->
            <Card v-if="order.invoice">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <FileText class="h-5 w-5" />
                        Informasi Faktur
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                        <div>
                            <div class="text-sm text-muted-foreground">Nomor Faktur</div>
                            <div class="font-medium">{{ order.invoice.invoice_number }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-muted-foreground">Jumlah</div>
                            <div class="font-medium">{{ formatPrice(order.invoice.total_amount) }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-muted-foreground">Status</div>
                            <Badge :class="getStatusClass(order.invoice.status)">
                                {{ order.invoice.status }}
                            </Badge>
                        </div>
                        <div>
                            <div class="text-sm text-muted-foreground">Tanggal Jatuh Tempo</div>
                            <div class="font-medium">{{ formatDate(order.invoice.due_date) }}</div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- No Invoice State -->
            <Card v-else>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <FileText class="h-5 w-5" />
                        Informasi Faktur
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="py-8 text-center text-muted-foreground">
                        <FileText class="mx-auto h-12 w-12 text-muted-foreground/40" />
                        <h3 class="mt-2 text-sm font-semibold">Belum Ada Faktur</h3>
                        <p class="mt-1 text-sm">Faktur akan dibuat ketika pesanan diproses.</p>
                    </div>
                </CardContent>
            </Card>

            <!-- Upgrade/Downgrade Modal -->
            <div v-if="showUpgradeModal" class="fixed inset-0 z-50 flex items-center justify-center">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-black/50" @click="closeUpgradeModal"></div>

                <!-- Modal Content -->
                <div class="relative bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 dark:bg-gray-900">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-xl font-semibold">Upgrade/Downgrade Layanan</h2>
                            <Button variant="ghost" size="sm" @click="closeUpgradeModal">
                                ×
                            </Button>
                        </div>

                        <!-- Current Plan Info -->
                        <div v-if="order.hosting_plan" class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg mb-6">
                            <h3 class="font-medium mb-3">Plan Saat Ini</h3>
                            <div class="space-y-2">
                                <div class="flex justify-between items-center">
                                    <span class="font-semibold text-lg">{{ order.hosting_plan.plan_name }}</span>
                                    <span class="font-bold text-blue-600 dark:text-blue-400">{{ formatPrice(order.hosting_plan.selling_price) }}/bulan</span>
                                </div>

                                <!-- Plan Specifications -->
                                <div class="grid grid-cols-3 gap-4 py-3 text-sm">
                                    <div class="text-center">
                                        <div class="font-medium text-gray-900 dark:text-gray-100">{{ order.hosting_plan.storage_gb }}GB</div>
                                        <div class="text-gray-500 text-xs">Storage</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="font-medium text-gray-900 dark:text-gray-100">{{ order.hosting_plan.cpu_cores }} Core</div>
                                        <div class="text-gray-500 text-xs">CPU</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="font-medium text-gray-900 dark:text-gray-100">{{ order.hosting_plan.ram_gb }}GB</div>
                                        <div class="text-gray-500 text-xs">RAM</div>
                                    </div>
                                </div>

                                <!-- Service Info -->
                                <div class="pt-2 border-t border-gray-200 dark:border-gray-600 text-sm space-y-1">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Berakhir:</span>
                                        <span class="font-medium">{{ order.expires_at ? formatDate(order.expires_at) : '-' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Sisa hari:</span>
                                        <span class="font-medium text-orange-600 dark:text-orange-400">{{ order.expires_at ? getDaysUntilExpiry(order.expires_at) : 0 }} hari</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- New Plan Selection -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium mb-2">Pilih Plan Baru:</label>
                            <select
                                v-model="selectedPlanId"
                                @change="simulateUpgradeDowngrade"
                                class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            >
                                <option value="">Pilih plan baru...</option>
                                <option
                                    v-for="plan in availablePlans"
                                    :key="plan.id"
                                    :value="plan.id"
                                >
                                    {{ plan.plan_name }} - {{ formatPrice(plan.selling_price) }}/bulan
                                    ({{ plan.storage_gb }}GB, {{ plan.cpu_cores }} CPU, {{ plan.ram_gb }}GB RAM)
                                </option>
                            </select>
                        </div>

                        <!-- Cost Simulation -->
                        <div v-if="isSimulating" class="flex items-center justify-center py-8">
                            <Loader2 class="h-6 w-6 animate-spin" />
                            <span class="ml-2">Menghitung biaya...</span>
                        </div>

                        <div v-else-if="simulation" class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg mb-6">
                            <h4 class="font-medium mb-4">💰 Simulasi Biaya Upgrade</h4>

                            <!-- Plan Comparison -->
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <!-- Current Plan -->
                                <div class="bg-white dark:bg-gray-800 p-3 rounded-lg">
                                    <h5 class="text-xs text-gray-500 mb-1">PLAN SAAT INI</h5>
                                    <div class="font-semibold text-sm">{{ simulation.current_plan.name }}</div>
                                    <div class="text-lg font-bold text-gray-600">{{ formatPrice(simulation.current_plan.price) }}<span class="text-xs font-normal">/bulan</span></div>
                                    <div class="text-xs text-orange-600 mt-1">Pro-rated {{ simulation.calculation.remaining_days }} hari: {{ formatPrice(simulation.current_plan.prorated_amount) }}</div>
                                </div>

                                <!-- New Plan -->
                                <div class="bg-white dark:bg-gray-800 p-3 rounded-lg border-2 border-blue-200 dark:border-blue-600">
                                    <h5 class="text-xs text-blue-600 mb-1">PLAN BARU</h5>
                                    <div class="font-semibold text-sm">{{ simulation.new_plan.name }}</div>
                                    <div class="text-lg font-bold text-blue-600">{{ formatPrice(simulation.new_plan.price) }}<span class="text-xs font-normal">/bulan</span></div>
                                    <div class="text-xs text-orange-600 mt-1">Pro-rated {{ simulation.calculation.remaining_days }} hari: {{ formatPrice(simulation.new_plan.prorated_amount) }}</div>
                                </div>
                            </div>

                            <!-- Calculation Summary -->
                            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg">
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span>Biaya Plan Lama (sisa {{ simulation.calculation.remaining_days }} hari):</span>
                                        <span class="line-through text-gray-500">{{ formatPrice(simulation.current_plan.prorated_amount) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Biaya Plan Baru ({{ simulation.calculation.remaining_days }} hari):</span>
                                        <span class="font-semibold">{{ formatPrice(simulation.new_plan.prorated_amount) }}</span>
                                    </div>
                                    <hr class="border-gray-300 dark:border-gray-600">
                                    <div class="flex justify-between font-bold text-lg">
                                        <span v-if="simulation.calculation.is_upgrade">💳 Yang Harus Dibayar:</span>
                                        <span v-else-if="simulation.calculation.is_downgrade">💚 Penghematan:</span>
                                        <span v-else>Tidak ada perubahan biaya</span>

                                        <span
                                            class="px-3 py-1 rounded-full text-white font-bold"
                                            :class="simulation.calculation.is_upgrade ? 'bg-red-500' : simulation.calculation.is_downgrade ? 'bg-green-500' : 'bg-gray-500'"
                                        >
                                            {{ simulation.calculation.cost_difference > 0 ? '+' : '' }}{{ formatPrice(Math.abs(simulation.calculation.cost_difference)) }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Warning for Downgrade -->
                            <div v-if="simulation.calculation.is_downgrade" class="mt-3 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-lg">
                                <p class="text-sm text-yellow-800 dark:text-yellow-200">
                                    ⚠️ <strong>Perhatian:</strong> Downgrade tidak ada refund. Penghematan akan diterapkan pada billing period berikutnya.
                                </p>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-3">
                            <Button
                                @click="processUpgradeDowngrade"
                                :disabled="!selectedPlanId || isProcessing"
                                class="flex-1"
                            >
                                <Loader2 v-if="isProcessing" class="h-4 w-4 animate-spin mr-2" />
                                <template v-if="simulation?.calculation.is_upgrade">
                                    Buat Invoice Upgrade
                                </template>
                                <template v-else-if="simulation?.calculation.is_downgrade">
                                    Proses Downgrade
                                </template>
                                <template v-else>
                                    Proses Perubahan
                                </template>
                            </Button>
                            <Button variant="outline" @click="closeUpgradeModal" :disabled="isProcessing">
                                Batal
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
