<script setup lang="ts">
import OrderFormModal from '@/components/OrderFormModal.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import { ArrowUpDown, Edit, FileText, Loader2, Mail, MapPin, Package, Phone, Send, ShoppingCart } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Customer {
    id: number;
    name: string;
    email: string;
    phone?: string;
    address?: string;
    city?: string;
    country?: string;
}

interface DomainPrice {
    id: number;
    extension: string;
    selling_price: number;
}

interface ServicePlan {
    id: number;
    name: string;
    category: string;
    price: number;
}

interface OrderItem {
    id: number;
    item_type: string;
    item_id: number;
    domain_name?: string;
    quantity: number;
    price: number;
    expires_at?: string;
    status?: string;
    hosting_plan?: HostingPlan;
    domain_price?: DomainPrice;
    service_plan?: ServicePlan;
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
    hostingPlans: HostingPlan[];
    customers: Customer[];
    domainPrices: DomainPrice[];
    servicePlans: ServicePlan[];
}

const props = defineProps<Props>();

// Edit Modal State
const showEditModal = ref(false);

const openEditModal = () => {
    showEditModal.value = true;
};

const closeEditModal = () => {
    showEditModal.value = false;
};

const handleEditSubmit = (data: any) => {
    router.put(`/admin/orders/${props.order.id}`, data, {
        preserveState: false,
        preserveScroll: true,
        onSuccess: () => {
            closeEditModal();
        },
    });
};

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

const formatDateOnly = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
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
const currentHostingPlan = computed(() => {
    // First try direct relationship
    if (props.order.hosting_plan) {
        return props.order.hosting_plan;
    }

    // Then check order items for hosting type
    if (props.order.order_items) {
        const hostingItem = props.order.order_items.find((item) => item.item_type === 'hosting');
        if (hostingItem && hostingItem.hosting_plan) {
            return hostingItem.hosting_plan;
        }
    }

    return null;
});

const canUpgradeDowngrade = computed(() => {
    const hasHostingPlan = currentHostingPlan.value !== null;

    return (
        props.order.status === 'active' &&
        hasHostingPlan &&
        props.order.change_status !== 'pending' &&
        props.order.expires_at &&
        getDaysUntilExpiry(props.order.expires_at) > 0
    );
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
            new_plan_id: selectedPlanId.value,
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
    router.post(
        `/admin/orders/${props.order.id}/process-upgrade-downgrade`,
        {
            new_plan_id: selectedPlanId.value,
        },
        {
            onFinish: () => {
                isProcessing.value = false;
                closeUpgradeModal();
            },
        },
    );
};

// Send Invoice Email
const isSendingInvoice = ref(false);

const sendInvoiceToEmail = () => {
    if (!props.order.invoice) return;

    isSendingInvoice.value = true;
    router.post(
        `/admin/invoices/${props.order.invoice.id}/send`,
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                isSendingInvoice.value = false;
            },
        },
    );
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
                    <Button variant="outline" @click="openEditModal" class="flex cursor-pointer items-center gap-2">
                        <Edit class="h-4 w-4" />
                        Edit
                    </Button>
                    <Button v-if="canUpgradeDowngrade" variant="outline" @click="openUpgradeModal" class="flex items-center gap-2">
                        <ArrowUpDown class="h-4 w-4" />
                        Upgrade/Downgrade
                    </Button>
                    <Button variant="outline" asChild>
                        <Link href="/admin/orders" class="cursor-pointer"> Back to Orders </Link>
                    </Button>
                </div>
            </div>

            <!-- Order Overview -->
            <div class="grid gap-6 md:grid-cols-2">
                <!-- Order Information -->
                <Card
                    :class="order.expires_at && ['active', 'suspended'].includes(order.status) && getDaysUntilExpiry(order.expires_at) <= 30 ? 'border-orange-200 dark:border-orange-800' : ''"
                >
                    <CardHeader class="pb-3">
                        <CardTitle class="flex items-center gap-2 text-base">
                            <ShoppingCart class="h-4 w-4" />
                            Informasi Order
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3 text-sm">
                        <!-- Status + Expiry inline -->
                        <div class="flex items-center justify-between">
                            <span class="text-muted-foreground">Status</span>
                            <div class="flex items-center gap-2">
                                <Badge :class="getStatusClass(order.status)" class="text-xs">
                                    {{ getStatusText(order.status) }}
                                </Badge>
                                <Badge
                                    v-if="order.expires_at && ['active', 'suspended'].includes(order.status)"
                                    :class="getExpiryBadgeClass(getDaysUntilExpiry(order.expires_at))"
                                    class="text-xs"
                                >
                                    <template v-if="getDaysUntilExpiry(order.expires_at) <= 0">Berakhir</template>
                                    <template v-else>{{ getDaysUntilExpiry(order.expires_at) }}h</template>
                                </Badge>
                            </div>
                        </div>

                        <!-- Expiry detail (compact) -->
                        <div
                            v-if="order.expires_at && ['active', 'suspended'].includes(order.status)"
                            class="flex items-center justify-between rounded-md px-2 py-1.5"
                            :class="getDaysUntilExpiry(order.expires_at) <= 30 ? 'bg-orange-50 dark:bg-orange-950' : 'bg-muted'"
                        >
                            <span class="text-muted-foreground">Berakhir</span>
                            <div class="text-right">
                                <span class="font-medium">{{ formatDate(order.expires_at) }}</span>
                                <span v-if="order.auto_renew" class="ml-1 text-xs text-green-600 dark:text-green-400">· Auto</span>
                            </div>
                        </div>

                        <div v-if="order.domain_name" class="flex items-center justify-between">
                            <span class="text-muted-foreground">Domain</span>
                            <span class="font-medium">{{ order.domain_name }}</span>
                        </div>

                        <div v-if="currentHostingPlan" class="flex items-center justify-between">
                            <span class="text-muted-foreground">Plan</span>
                            <div class="text-right">
                                <span class="font-medium">{{ currentHostingPlan.plan_name }}</span>
                                <div class="text-xs text-muted-foreground">
                                    {{ currentHostingPlan.storage_gb }}GB SSD | {{ currentHostingPlan.cpu_cores }} Core CPU | {{ currentHostingPlan.ram_gb }}GB RAM
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-muted-foreground">Siklus</span>
                            <span>{{ getBillingCycleText(order.billing_cycle) }}</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-muted-foreground">Total</span>
                            <div class="text-right">
                                <template v-if="order.discount_amount && order.discount_amount > 0">
                                    <span class="text-xs text-muted-foreground line-through">{{ formatPrice(totalItemsAmount) }}</span>
                                    <span class="ml-1 font-bold text-green-600 dark:text-green-400">{{ formatPrice(totalItemsAmount - Number(order.discount_amount)) }}</span>
                                    <div class="text-xs text-green-600 dark:text-green-400">Hemat {{ formatPrice(order.discount_amount) }}</div>
                                </template>
                                <template v-else>
                                    <span class="font-bold">{{ formatPrice(order.total_amount) }}</span>
                                </template>
                            </div>
                        </div>

                        <div class="flex items-center justify-between text-xs text-muted-foreground">
                            <span>Dibuat {{ formatDate(order.created_at) }}</span>
                        </div>
                    </CardContent>
                </Card>

                <!-- Customer Information -->
                <Card>
                    <CardHeader class="pb-3">
                        <CardTitle class="flex items-center justify-between text-base">
                            <span class="flex items-center gap-2">
                                <Mail class="h-4 w-4" />
                                Pelanggan
                            </span>
                            <Button size="sm" variant="ghost" asChild class="text-xs">
                                <Link :href="`/admin/customers/${order.customer.id}`">Detail →</Link>
                            </Button>
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-2 text-sm">
                        <div>
                            <span class="font-medium">{{ order.customer.name }}</span>
                            <span class="ml-1 text-xs text-muted-foreground">#{{ order.customer.id }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-muted-foreground">
                            <Mail class="h-3.5 w-3.5" />
                            <span>{{ order.customer.email }}</span>
                        </div>
                        <div v-if="order.customer.phone" class="flex items-center gap-2 text-muted-foreground">
                            <Phone class="h-3.5 w-3.5" />
                            <span>{{ order.customer.phone }}</span>
                        </div>
                        <div v-if="order.customer.address || order.customer.city || order.customer.country" class="flex items-start gap-2 text-muted-foreground">
                            <MapPin class="mt-0.5 h-3.5 w-3.5 shrink-0" />
                            <span>
                                {{ [order.customer.address, order.customer.city, order.customer.country].filter(Boolean).join(', ') }}
                            </span>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Order Items -->
            <Card class="overflow-hidden">
                <CardHeader class="border-b border-border/50 bg-muted/30 pb-4 pt-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle class="flex items-center gap-2 text-base">
                                <Package class="h-4 w-4" />
                                Item Pesanan
                            </CardTitle>
                            <p class="mt-1 text-xs text-muted-foreground">
                                {{ order.order_items.length }} item
                                <template v-if="order.expires_at">
                                    <span class="mx-1">·</span>
                                    Kadaluarsa:
                                    <span
                                        class="font-medium"
                                        :class="getDaysUntilExpiry(order.expires_at) <= 0
                                            ? 'text-red-600 dark:text-red-400'
                                            : getDaysUntilExpiry(order.expires_at) <= 30
                                                ? 'text-orange-600 dark:text-orange-400'
                                                : getDaysUntilExpiry(order.expires_at) <= 90
                                                    ? 'text-yellow-600 dark:text-yellow-400'
                                                    : ''"
                                    >
                                        {{ formatDateOnly(order.expires_at) }}
                                        <template v-if="getDaysUntilExpiry(order.expires_at) <= 0"> · Berakhir</template>
                                        <template v-else-if="getDaysUntilExpiry(order.expires_at) <= 90"> · {{ getDaysUntilExpiry(order.expires_at) }} hari lagi</template>
                                    </span>
                                </template>
                            </p>
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="p-0">
                    <Table>
                        <TableHeader>
                            <TableRow class="border-b-border/50 hover:bg-transparent">
                                <TableHead class="w-8 pl-5 text-xs font-semibold uppercase tracking-wider text-muted-foreground">#</TableHead>
                                <TableHead class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Layanan</TableHead>
                                <TableHead class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Deskripsi</TableHead>
                                <TableHead class="text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">Qty</TableHead>
                                <TableHead class="text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">Harga Satuan</TableHead>
                                <TableHead class="text-right pr-5 text-xs font-semibold uppercase tracking-wider text-muted-foreground">Total</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="(item, index) in order.order_items"
                                :key="item.id"
                                class="border-b-border/30 group"
                                :class="index % 2 === 0 ? 'bg-muted/10' : ''"
                            >
                                <TableCell class="w-8 pl-5 text-xs text-muted-foreground">{{ index + 1 }}</TableCell>
                                <TableCell>
                                    <div class="flex items-center gap-2.5">
                                        <div
                                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md text-xs font-bold uppercase"
                                            :class="item.item_type === 'hosting'
                                                ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300'
                                                : item.item_type === 'domain'
                                                    ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300'
                                                    : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300'"
                                        >
                                            {{ item.item_type === 'hosting' ? 'H' : item.item_type === 'domain' ? 'D' : 'S' }}
                                        </div>
                                        <div>
                                            <span class="text-sm font-semibold capitalize">{{ item.item_type }}</span>
                                        </div>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <template v-if="item.item_type === 'hosting' && item.hosting_plan">
                                        <div>
                                            <span class="text-sm font-medium">{{ item.hosting_plan.plan_name }}</span>
                                            <div class="mt-0.5 text-xs text-muted-foreground">
                                                {{ item.hosting_plan.storage_gb }}GB SSD · {{ item.hosting_plan.cpu_cores }} Core CPU · {{ item.hosting_plan.ram_gb }}GB RAM
                                            </div>
                                        </div>
                                    </template>
                                    <template v-else-if="item.item_type === 'domain'">
                                        <span class="text-sm font-medium">{{ item.domain_name || order.domain_name || '-' }}</span>
                                    </template>
                                    <template v-else-if="item.service_plan">
                                        <span class="text-sm font-medium">{{ item.service_plan.name }}</span>
                                    </template>
                                    <template v-else>
                                        <span class="text-sm font-medium" v-if="item.domain_name">{{ item.domain_name }}</span>
                                        <span v-else class="text-sm text-muted-foreground">-</span>
                                    </template>
                                </TableCell>
                                <TableCell class="text-right text-sm">{{ item.quantity }}</TableCell>
                                <TableCell class="text-right">
                                    <template v-if="item.item_type === 'hosting' && item.hosting_plan && !item.hosting_plan.use_bulk_pricing && item.hosting_plan.discount_percent > 0">
                                        <div class="text-xs text-muted-foreground line-through">{{ formatPrice(item.hosting_plan.selling_price) }}</div>
                                        <div class="text-sm font-medium">{{ formatPrice(item.price) }}</div>
                                    </template>
                                    <template v-else>
                                        <span class="text-sm">{{ formatPrice(item.price) }}</span>
                                    </template>
                                </TableCell>
                                <TableCell class="text-right pr-5">
                                    <template v-if="item.item_type === 'hosting' && item.hosting_plan && !item.hosting_plan.use_bulk_pricing && item.hosting_plan.discount_percent > 0">
                                        <div class="text-xs text-muted-foreground line-through">{{ formatPrice(item.hosting_plan.selling_price * item.quantity) }}</div>
                                        <div class="text-sm font-semibold">{{ formatPrice(item.price * item.quantity) }}</div>
                                    </template>
                                    <template v-else>
                                        <span class="text-sm font-semibold">{{ formatPrice(item.price * item.quantity) }}</span>
                                    </template>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <!-- Summary -->
                    <div class="border-t border-border/50 bg-muted/20 px-5 py-4">
                        <div class="ml-auto w-full max-w-xs space-y-2">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-muted-foreground">Subtotal</span>
                                <span>{{ formatPrice(totalItemsAmount) }}</span>
                            </div>
                            <div v-if="order.discount_amount && order.discount_amount > 0" class="flex items-center justify-between text-sm">
                                <span class="text-green-600 dark:text-green-400">Diskon</span>
                                <span class="font-medium text-green-600 dark:text-green-400">-{{ formatPrice(order.discount_amount) }}</span>
                            </div>
                            <div class="border-t border-border/80 pt-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-bold uppercase tracking-wider">Total</span>
                                    <span class="text-lg font-bold">{{ formatPrice(order.total_amount) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Invoice Information -->
            <Card>
                <CardHeader class="pb-3">
                    <CardTitle class="flex items-center justify-between text-base">
                        <span class="flex items-center gap-2">
                            <FileText class="h-4 w-4" />
                            Faktur
                        </span>
                        <Button
                            v-if="order.invoice"
                            size="sm"
                            variant="outline"
                            class="flex cursor-pointer items-center gap-1.5 text-xs"
                            :disabled="isSendingInvoice"
                            @click="sendInvoiceToEmail"
                        >
                            <Loader2 v-if="isSendingInvoice" class="h-3.5 w-3.5 animate-spin" />
                            <Send v-else class="h-3.5 w-3.5" />
                            {{ isSendingInvoice ? 'Mengirim...' : 'Kirim Tagihan' }}
                        </Button>
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <template v-if="order.invoice">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-muted-foreground">{{ order.invoice.invoice_number }}</span>
                            <div class="flex items-center gap-3">
                                <span class="font-bold">{{ formatPrice(order.invoice.total_amount) }}</span>
                                <Badge :class="getStatusClass(order.invoice.status)" class="text-xs">
                                    {{ order.invoice.status }}
                                </Badge>
                            </div>
                        </div>
                        <div class="mt-1 text-xs text-muted-foreground">
                            Jatuh tempo: {{ formatDate(order.invoice.due_date) }}
                        </div>
                    </template>
                    <div v-else class="text-sm text-muted-foreground">
                        Belum ada faktur.
                    </div>
                </CardContent>
            </Card>

            <!-- Upgrade/Downgrade Modal -->
            <div v-if="showUpgradeModal" class="fixed inset-0 z-50 flex items-center justify-center">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-black/50" @click="closeUpgradeModal"></div>

                <!-- Modal Content -->
                <div class="relative mx-4 w-full max-w-2xl rounded-lg bg-white shadow-xl dark:bg-gray-900">
                    <div class="p-6">
                        <div class="mb-6 flex items-center justify-between">
                            <h2 class="text-xl font-semibold">Upgrade/Downgrade Layanan</h2>
                            <Button variant="ghost" size="sm" @click="closeUpgradeModal"> × </Button>
                        </div>

                        <!-- Current Plan Info -->
                        <div v-if="currentHostingPlan" class="mb-6 rounded-lg bg-gray-50 p-4 dark:bg-gray-800">
                            <h3 class="mb-3 font-medium">Plan Saat Ini</h3>
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-lg font-semibold">{{ currentHostingPlan.plan_name }}</span>
                                    <span class="font-bold text-blue-600 dark:text-blue-400"
                                        >{{ formatPrice(currentHostingPlan.selling_price) }}/bulan</span
                                    >
                                </div>

                                <!-- Plan Specifications -->
                                <div class="grid grid-cols-3 gap-4 py-3 text-sm">
                                    <div class="text-center">
                                        <div class="font-medium text-gray-900 dark:text-gray-100">{{ currentHostingPlan.storage_gb }}GB</div>
                                        <div class="text-xs text-gray-500">Storage</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="font-medium text-gray-900 dark:text-gray-100">{{ currentHostingPlan.cpu_cores }} Core</div>
                                        <div class="text-xs text-gray-500">CPU</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="font-medium text-gray-900 dark:text-gray-100">{{ currentHostingPlan.ram_gb }}GB</div>
                                        <div class="text-xs text-gray-500">RAM</div>
                                    </div>
                                </div>

                                <!-- Service Info -->
                                <div class="space-y-1 border-t border-gray-200 pt-2 text-sm dark:border-gray-600">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Berakhir:</span>
                                        <span class="font-medium">{{ order.expires_at ? formatDate(order.expires_at) : '-' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Sisa hari:</span>
                                        <span class="font-medium text-orange-600 dark:text-orange-400"
                                            >{{ order.expires_at ? getDaysUntilExpiry(order.expires_at) : 0 }} hari</span
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- New Plan Selection -->
                        <div class="mb-6">
                            <label class="mb-2 block text-sm font-medium">Pilih Plan Baru:</label>
                            <select
                                v-model="selectedPlanId"
                                @change="simulateUpgradeDowngrade"
                                class="w-full rounded-lg border border-gray-300 bg-white p-3 text-gray-900 focus:border-transparent focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                            >
                                <option value="">Pilih plan baru...</option>
                                <option v-for="plan in availablePlans" :key="plan.id" :value="plan.id">
                                    {{ plan.plan_name }} - {{ formatPrice(plan.selling_price) }}/bulan ({{ plan.storage_gb }}GB,
                                    {{ plan.cpu_cores }} CPU, {{ plan.ram_gb }}GB RAM)
                                </option>
                            </select>
                        </div>

                        <!-- Cost Simulation -->
                        <div v-if="isSimulating" class="flex items-center justify-center py-8">
                            <Loader2 class="h-6 w-6 animate-spin" />
                            <span class="ml-2">Menghitung biaya...</span>
                        </div>

                        <div v-else-if="simulation" class="mb-6 rounded-lg bg-blue-50 p-4 dark:bg-blue-900/20">
                            <h4 class="mb-4 font-medium">💰 Simulasi Biaya Upgrade</h4>

                            <!-- Plan Comparison -->
                            <div class="mb-4 grid grid-cols-2 gap-4">
                                <!-- Current Plan -->
                                <div class="rounded-lg bg-white p-3 dark:bg-gray-800">
                                    <h5 class="mb-1 text-xs text-gray-500">PLAN SAAT INI</h5>
                                    <div class="text-sm font-semibold">{{ simulation.current_plan.name }}</div>
                                    <div class="text-lg font-bold text-gray-600">
                                        {{ formatPrice(simulation.current_plan.price) }}<span class="text-xs font-normal">/bulan</span>
                                    </div>
                                    <div class="mt-1 text-xs text-orange-600">
                                        Pro-rated {{ simulation.calculation.remaining_days }} hari:
                                        {{ formatPrice(simulation.current_plan.prorated_amount) }}
                                    </div>
                                </div>

                                <!-- New Plan -->
                                <div class="rounded-lg border-2 border-blue-200 bg-white p-3 dark:border-blue-600 dark:bg-gray-800">
                                    <h5 class="mb-1 text-xs text-blue-600">PLAN BARU</h5>
                                    <div class="text-sm font-semibold">{{ simulation.new_plan.name }}</div>
                                    <div class="text-lg font-bold text-blue-600">
                                        {{ formatPrice(simulation.new_plan.price) }}<span class="text-xs font-normal">/bulan</span>
                                    </div>
                                    <div class="mt-1 text-xs text-orange-600">
                                        Pro-rated {{ simulation.calculation.remaining_days }} hari:
                                        {{ formatPrice(simulation.new_plan.prorated_amount) }}
                                    </div>
                                </div>
                            </div>

                            <!-- Calculation Summary -->
                            <div class="rounded-lg bg-white p-4 dark:bg-gray-800">
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span>Biaya Plan Lama (sisa {{ simulation.calculation.remaining_days }} hari):</span>
                                        <span class="text-gray-500 line-through">{{ formatPrice(simulation.current_plan.prorated_amount) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Biaya Plan Baru ({{ simulation.calculation.remaining_days }} hari):</span>
                                        <span class="font-semibold">{{ formatPrice(simulation.new_plan.prorated_amount) }}</span>
                                    </div>
                                    <hr class="border-gray-300 dark:border-gray-600" />
                                    <div class="flex justify-between text-lg font-bold">
                                        <span v-if="simulation.calculation.is_upgrade">💳 Yang Harus Dibayar:</span>
                                        <span v-else-if="simulation.calculation.is_downgrade">💚 Penghematan:</span>
                                        <span v-else>Tidak ada perubahan biaya</span>

                                        <span
                                            class="rounded-full px-3 py-1 font-bold text-white"
                                            :class="
                                                simulation.calculation.is_upgrade
                                                    ? 'bg-red-500'
                                                    : simulation.calculation.is_downgrade
                                                      ? 'bg-green-500'
                                                      : 'bg-gray-500'
                                            "
                                        >
                                            {{ simulation.calculation.cost_difference > 0 ? '+' : ''
                                            }}{{ formatPrice(Math.abs(simulation.calculation.cost_difference)) }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Warning for Downgrade -->
                            <div
                                v-if="simulation.calculation.is_downgrade"
                                class="mt-3 rounded-lg border border-yellow-200 bg-yellow-50 p-3 dark:border-yellow-700 dark:bg-yellow-900/20"
                            >
                                <p class="text-sm text-yellow-800 dark:text-yellow-200">
                                    ⚠️ <strong>Perhatian:</strong> Downgrade tidak ada refund. Penghematan akan diterapkan pada billing period
                                    berikutnya.
                                </p>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-3">
                            <Button @click="processUpgradeDowngrade" :disabled="!selectedPlanId || isProcessing" class="flex-1">
                                <Loader2 v-if="isProcessing" class="mr-2 h-4 w-4 animate-spin" />
                                <template v-if="simulation?.calculation.is_upgrade"> Buat Invoice Upgrade </template>
                                <template v-else-if="simulation?.calculation.is_downgrade"> Proses Downgrade </template>
                                <template v-else> Proses Perubahan </template>
                            </Button>
                            <Button variant="outline" @click="closeUpgradeModal" :disabled="isProcessing"> Batal </Button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Order Modal -->
            <OrderFormModal
                :show="showEditModal"
                mode="edit"
                :order="order"
                :customers="customers"
                :hosting-plans="hostingPlans"
                :domain-prices="domainPrices"
                :service-plans="servicePlans"
                @close="closeEditModal"
                @submit="handleEditSubmit"
            />
        </div>
    </AppLayout>
</template>
