<script setup lang="ts">
import OrderFormModal from '@/components/OrderFormModal.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ChevronDown, ChevronUp, Edit, Package, Plus, Search, ShoppingCart, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

interface Customer {
    id: number;
    name: string;
    email: string;
}

interface HostingPlan {
    id: number;
    plan_name: string;
    selling_price: number;
    storage_gb: number;
    cpu_cores: number;
    ram_gb: number;
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
    description: string;
}

interface OrderItem {
    id: number;
    item_type: string;
    price: number;
}

interface Order {
    id: number;
    total_amount: number;
    discount_amount?: number;
    status: 'pending' | 'processing' | 'active' | 'suspended' | 'expired' | 'terminated' | 'cancelled';
    billing_cycle: string;
    service_type?: string;
    domain_name?: string;
    expires_at?: string;
    auto_renew?: boolean;
    created_at: string;
    customer: Customer;
    order_items: OrderItem[];
    hosting_plan?: HostingPlan;
}

interface Props {
    orders: {
        data: Order[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: any[];
    };
    view?: 'orders' | 'services';
    filters?: {
        search?: string;
        status?: string;
        service_type?: string;
        customer_id?: string;
    };
    sort?: string;
    direction?: string;
    customers: Customer[];
    hostingPlans: HostingPlan[];
    domainPrices: DomainPrice[];
    servicePlans: ServicePlan[];
}

const props = defineProps<Props>();

const search = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || '');
const serviceTypeFilter = ref(props.filters?.service_type || '');
const customerFilter = ref(props.filters?.customer_id || '');
const currentView = ref(props.view || 'orders');
const showOrderFormModal = ref(false);
const orderFormMode = ref<'create' | 'edit'>('create');
const showDeleteModal = ref(false);
const selectedOrder = ref<Order | null>(null);
const orderToDelete = ref<Order | null>(null);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    {
        title: currentView.value === 'services' ? 'Layanan' : 'Pesanan',
        href: currentView.value === 'services' ? '/admin/orders?view=services' : '/admin/orders',
    },
];

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(price);
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const getStatusColor = (status: string) => {
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
            return 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300';
        case 'cancelled':
        case 'terminated':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
    }
};

const getStatusText = (status: string) => {
    switch (status) {
        case 'completed':
            return 'Selesai';
        case 'processing':
            return 'Diproses';
        case 'pending':
            return 'Menunggu';
        case 'cancelled':
            return 'Dibatalkan';
        case 'active':
            return 'Aktif';
        case 'suspended':
            return 'Ditangguhkan';
        case 'expired':
            return 'Kedaluwarsa';
        case 'terminated':
            return 'Dihentikan';
        default:
            return status;
    }
};

const getBillingCycleText = (cycle: string) => {
    switch (cycle) {
        case 'onetime':
            return 'Sekali Bayar';
        case 'monthly':
            return 'Bulanan';
        case 'quarterly':
            return 'Triwulan';
        case 'semi_annually':
            return 'Semi Annual';
        case 'annually':
            return 'Tahunan';
        default:
            return cycle;
    }
};

const getBillingCycleColor = (cycle: string) => {
    switch (cycle) {
        case 'onetime':
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
        case 'monthly':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
        case 'quarterly':
            return 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300';
        case 'semi_annually':
            return 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300';
        case 'annually':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
    }
};

const getDaysUntilExpiry = (expiryDate: string): number => {
    if (!expiryDate) return -1; // -1 for no expiry date

    const today = new Date();
    const expiry = new Date(expiryDate);
    const diffTime = expiry.getTime() - today.getTime();
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

    return diffDays;
};

const getExpiryBadge = (expiryDate: string) => {
    const daysLeft = getDaysUntilExpiry(expiryDate);

    if (daysLeft === -1) return null; // No expiry date

    const threeMonthsInDays = 90;

    if (daysLeft < 0) {
        // Expired
        return {
            text: 'Kedaluwarsa',
            class: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
        };
    } else if (daysLeft <= 15) {
        // Critical - less than 15 days
        return {
            text: `${daysLeft} hari lagi`,
            class: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
        };
    } else if (daysLeft <= threeMonthsInDays) {
        // Warning - less than 3 months
        return {
            text: `${daysLeft} hari lagi`,
            class: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        };
    }

    return null; // More than 3 months, no badge
};

const getServiceTypeColor = (itemType: string) => {
    switch (itemType) {
        case 'hosting':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
        case 'domain':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
        case 'service':
            return 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300';
        case 'app':
            return 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300';
        case 'web':
            return 'bg-cyan-100 text-cyan-800 dark:bg-cyan-900 dark:text-cyan-300';
        case 'maintenance':
            return 'bg-pink-100 text-pink-800 dark:bg-pink-900 dark:text-pink-300';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
    }
};

const getServiceTypeText = (itemType: string) => {
    switch (itemType) {
        case 'hosting':
            return 'Hosting';
        case 'domain':
            return 'Domain';
        case 'service':
            return 'Layanan';
        case 'app':
            return 'Aplikasi';
        case 'web':
            return 'Website';
        case 'maintenance':
            return 'Maintenance';
        default:
            return itemType;
    }
};

const getServiceIcon = (itemType: string) => {
    switch (itemType) {
        case 'hosting':
            return '🌐';
        case 'domain':
            return '🔗';
        case 'service':
            return '⚙️';
        case 'app':
            return '📱';
        case 'web':
            return '💻';
        case 'maintenance':
            return '🔧';
        default:
            return '📦';
    }
};

const applyFilters = () => {
    const params = new URLSearchParams();

    if (search.value) params.set('search', search.value);
    if (statusFilter.value) params.set('status', statusFilter.value);
    if (serviceTypeFilter.value) params.set('service_type', serviceTypeFilter.value);
    if (customerFilter.value) params.set('customer_id', customerFilter.value);
    if (currentView.value) params.set('view', currentView.value);

    const url = `/admin/orders${params.toString() ? '?' + params.toString() : ''}`;
    router.get(url, {}, { preserveState: true });
};

const resetFilters = () => {
    search.value = '';
    statusFilter.value = '';
    serviceTypeFilter.value = '';
    customerFilter.value = '';
    applyFilters();
};

const changeView = (view: 'orders' | 'services') => {
    currentView.value = view;
    applyFilters();
};

const openCreateModal = () => {
    orderFormMode.value = 'create';
    selectedOrder.value = null;
    showOrderFormModal.value = true;
};

const openEditModal = (order: Order) => {
    orderFormMode.value = 'edit';
    selectedOrder.value = order;
    showOrderFormModal.value = true;
};

const closeOrderFormModal = () => {
    showOrderFormModal.value = false;
    selectedOrder.value = null;
};

const handleOrderFormSubmit = (data: any) => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    if (orderFormMode.value === 'create') {
        // Create order
        router.post(
            '/admin/orders',
            {
                ...data,
                _token: csrfToken,
            },
            {
                preserveState: false,
                preserveScroll: true,
                onSuccess: () => {
                    closeOrderFormModal();
                },
                onError: (errors) => {
                    // Errors will be handled by Inertia
                    console.error('Form errors:', errors);
                },
            },
        );
    } else if (orderFormMode.value === 'edit' && selectedOrder.value) {
        // Update order
        router.put(`/admin/orders/${selectedOrder.value.id}`, data, {
            preserveState: false,
            preserveScroll: true,
            onSuccess: () => {
                closeOrderFormModal();
            },
            onError: (errors) => {
                // Errors will be handled by Inertia
                console.error('Form errors:', errors);
            },
        });
    }
};

const confirmDelete = (order: Order) => {
    orderToDelete.value = order;
    showDeleteModal.value = true;
};

const deleteOrder = () => {
    if (orderToDelete.value) {
        router.delete(`/admin/orders/${orderToDelete.value.id}`, {
            onSuccess: () => {
                showDeleteModal.value = false;
                orderToDelete.value = null;
            },
        });
    }
};

const sortBy = (field: string) => {
    const params = new URLSearchParams();

    // Preserve existing filters
    if (search.value) params.set('search', search.value);
    if (statusFilter.value) params.set('status', statusFilter.value);
    if (serviceTypeFilter.value) params.set('service_type', serviceTypeFilter.value);
    if (customerFilter.value) params.set('customer_id', customerFilter.value);
    if (currentView.value) params.set('view', currentView.value);

    // Handle sorting
    let direction = 'asc';
    if (props.sort === field && props.direction === 'asc') {
        direction = 'desc';
    }

    params.set('sort', field);
    params.set('direction', direction);

    const url = `/admin/orders${params.toString() ? '?' + params.toString() : ''}`;
    router.get(url, {}, { preserveState: true });
};

const getSortIcon = (field: string) => {
    if (props.sort !== field) return null;
    return props.direction === 'asc' ? ChevronUp : ChevronDown;
};
</script>

<template>
    <Head title="Pesanan & Layanan" />

    <AppLayout>
        <template #breadcrumbs>
            {{ breadcrumbs }}
        </template>

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col space-y-4 md:flex-row md:items-center md:justify-between md:space-y-0">
                <div>
                    <h1 class="text-2xl font-bold">{{ currentView === 'services' ? 'Layanan' : 'Pesanan' }}</h1>
                    <p class="text-muted-foreground">
                        {{ currentView === 'services' ? 'Kelola layanan aktif pelanggan' : 'Kelola pesanan dan transaksi pelanggan' }}
                    </p>
                </div>
                <div class="flex space-x-2">
                    <Button @click="openCreateModal" class="flex items-center space-x-2">
                        <Plus class="h-4 w-4" />
                        <span>Buat Pesanan</span>
                    </Button>
                </div>
            </div>

            <!-- View Toggle -->
            <div class="flex space-x-1 rounded-lg bg-muted p-1">
                <button
                    @click="changeView('orders')"
                    :class="[
                        'flex-1 rounded-md px-3 py-2 text-sm font-medium transition-colors',
                        currentView === 'orders' ? 'bg-background text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground',
                    ]"
                >
                    <ShoppingCart class="mr-2 inline h-4 w-4" />
                    Pesanan
                </button>
                <button
                    @click="changeView('services')"
                    :class="[
                        'flex-1 rounded-md px-3 py-2 text-sm font-medium transition-colors',
                        currentView === 'services' ? 'bg-background text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground',
                    ]"
                >
                    <Package class="mr-2 inline h-4 w-4" />
                    Layanan
                </button>
            </div>

            <!-- Filters -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center space-x-2">
                        <Search class="h-4 w-4" />
                        <span>Filter & Pencarian</span>
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-5">
                        <div>
                            <Label for="search">Pencarian</Label>
                            <Input id="search" v-model="search" placeholder="Cari nama, email, atau domain..." @input="applyFilters" />
                        </div>
                        <div>
                            <Label for="status">Status</Label>
                            <select
                                id="status"
                                v-model="statusFilter"
                                @change="applyFilters"
                                class="flex h-9 w-full cursor-pointer rounded-md border border-input bg-background px-3 py-1 text-sm text-foreground shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none dark:bg-gray-800 dark:text-white"
                            >
                                <option value="">Semua Status</option>
                                <option value="pending">Menunggu</option>
                                <option value="processing">Diproses</option>
                                <option value="active">Aktif</option>
                                <option value="suspended">Ditangguhkan</option>
                                <option value="expired">Kedaluwarsa</option>
                                <option value="cancelled">Dibatalkan</option>
                                <option value="terminated">Dihentikan</option>
                            </select>
                        </div>
                        <div>
                            <Label for="service_type">Tipe Layanan</Label>
                            <select
                                id="service_type"
                                v-model="serviceTypeFilter"
                                @change="applyFilters"
                                class="flex h-9 w-full cursor-pointer rounded-md border border-input bg-background px-3 py-1 text-sm text-foreground shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none dark:bg-gray-800 dark:text-white"
                            >
                                <option value="">Semua Tipe</option>
                                <option value="hosting">Hosting</option>
                                <option value="domain">Domain</option>
                                <option value="service">Layanan</option>
                                <option value="app">Aplikasi</option>
                                <option value="web">Website</option>
                                <option value="maintenance">Maintenance</option>
                            </select>
                        </div>
                        <div>
                            <Label for="customer">Pelanggan</Label>
                            <select
                                id="customer"
                                v-model="customerFilter"
                                @change="applyFilters"
                                class="flex h-9 w-full cursor-pointer rounded-md border border-input bg-background px-3 py-1 text-sm text-foreground shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none dark:bg-gray-800 dark:text-white"
                            >
                                <option value="">Semua Pelanggan</option>
                                <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                                    {{ customer.name }} ({{ customer.email }})
                                </option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <Button @click="resetFilters" variant="outline" class="w-full"> Reset Filter </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Orders Table -->
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle>{{ currentView === 'services' ? 'Daftar Layanan' : 'Daftar Pesanan' }}</CardTitle>
                            <CardDescription>
                                Total {{ orders.total }} {{ currentView === 'services' ? 'layanan' : 'pesanan' }} ditemukan
                            </CardDescription>
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full table-auto">
                            <thead>
                                <tr class="border-b">
                                    <th class="pb-3 text-left font-medium">
                                        <button @click="sortBy('id')" class="flex cursor-pointer items-center space-x-1 hover:text-primary">
                                            <span>ID</span>
                                            <component :is="getSortIcon('id')" v-if="getSortIcon('id')" class="h-4 w-4" />
                                        </button>
                                    </th>
                                    <th class="pb-3 text-left font-medium">
                                        <button
                                            @click="sortBy('customer_name')"
                                            class="flex cursor-pointer items-center space-x-1 hover:text-primary"
                                        >
                                            <span>Pelanggan</span>
                                            <component :is="getSortIcon('customer_name')" v-if="getSortIcon('customer_name')" class="h-4 w-4" />
                                        </button>
                                    </th>
                                    <th class="pb-3 text-left font-medium">Layanan</th>
                                    <th class="pb-3 text-left font-medium">Domain</th>
                                    <th class="pb-3 text-left font-medium">
                                        <button @click="sortBy('total_amount')" class="flex cursor-pointer items-center space-x-1 hover:text-primary">
                                            <span>Total</span>
                                            <component :is="getSortIcon('total_amount')" v-if="getSortIcon('total_amount')" class="h-4 w-4" />
                                        </button>
                                    </th>
                                    <th class="pb-3 text-left font-medium">
                                        <button @click="sortBy('status')" class="flex cursor-pointer items-center space-x-1 hover:text-primary">
                                            <span>Status</span>
                                            <component :is="getSortIcon('status')" v-if="getSortIcon('status')" class="h-4 w-4" />
                                        </button>
                                    </th>
                                    <th class="pb-3 text-left font-medium">
                                        <button
                                            @click="sortBy('billing_cycle')"
                                            class="flex cursor-pointer items-center space-x-1 hover:text-primary"
                                        >
                                            <span>Siklus</span>
                                            <component :is="getSortIcon('billing_cycle')" v-if="getSortIcon('billing_cycle')" class="h-4 w-4" />
                                        </button>
                                    </th>
                                    <th v-if="currentView === 'services'" class="pb-3 text-left font-medium">
                                        <button @click="sortBy('expires_at')" class="flex cursor-pointer items-center space-x-1 hover:text-primary">
                                            <span>Kadaluwarsa</span>
                                            <component :is="getSortIcon('expires_at')" v-if="getSortIcon('expires_at')" class="h-4 w-4" />
                                        </button>
                                    </th>
                                    <th class="pb-3 text-left font-medium">
                                        <button @click="sortBy('created_at')" class="flex cursor-pointer items-center space-x-1 hover:text-primary">
                                            <span>{{ currentView === 'services' ? 'Dibuat' : 'Tanggal' }}</span>
                                            <component :is="getSortIcon('created_at')" v-if="getSortIcon('created_at')" class="h-4 w-4" />
                                        </button>
                                    </th>
                                    <th class="pb-3 text-center font-medium">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="order in orders.data" :key="order.id" class="border-b hover:bg-muted/50">
                                    <td class="py-3">
                                        <div class="font-medium">#{{ order.id }}</div>
                                    </td>
                                    <td class="py-3">
                                        <div>
                                            <div class="font-medium">{{ order.customer.name }}</div>
                                            <div class="text-sm text-muted-foreground">{{ order.customer.email }}</div>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <div class="flex flex-wrap gap-1">
                                            <div
                                                v-for="orderItem in order.order_items"
                                                :key="orderItem.id"
                                                class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                                :class="getServiceTypeColor(orderItem.item_type)"
                                            >
                                                <span class="mr-1">{{ getServiceIcon(orderItem.item_type) }}</span>
                                                {{ getServiceTypeText(orderItem.item_type) }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <div
                                            v-if="order.domain_name"
                                            class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-300"
                                        >
                                            {{ order.domain_name }}
                                        </div>
                                        <div
                                            v-else
                                            class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800 dark:bg-gray-900 dark:text-gray-300"
                                        >
                                            Tidak ada domain
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <template v-if="order.discount_amount && order.discount_amount > 0">
                                            <div class="text-xs text-muted-foreground line-through">
                                                {{ formatPrice(order.total_amount) }}
                                            </div>
                                            <div class="font-medium text-green-600 dark:text-green-400">
                                                {{ formatPrice(Number(order.total_amount) - Number(order.discount_amount)) }}
                                            </div>
                                            <div class="text-xs text-green-600 dark:text-green-400">
                                                Hemat: {{ formatPrice(order.discount_amount) }}
                                            </div>
                                        </template>
                                        <template v-else>
                                            <div class="font-medium">{{ formatPrice(order.total_amount) }}</div>
                                        </template>
                                    </td>
                                    <td class="py-3">
                                        <span
                                            :class="[
                                                'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                                                getStatusColor(order.status),
                                            ]"
                                        >
                                            {{ getStatusText(order.status) }}
                                        </span>
                                    </td>
                                    <td class="py-3 text-sm">
                                        <span
                                            class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium"
                                            :class="getBillingCycleColor(order.billing_cycle)"
                                        >
                                            {{ getBillingCycleText(order.billing_cycle) }}
                                        </span>
                                    </td>
                                    <td v-if="currentView === 'services'" class="py-3 text-sm">
                                        <div v-if="order.expires_at" class="space-y-1">
                                            <div class="text-muted-foreground">
                                                {{ formatDate(order.expires_at) }}
                                            </div>
                                            <div
                                                v-if="getExpiryBadge(order.expires_at)"
                                                class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
                                                :class="getExpiryBadge(order.expires_at)?.class"
                                            >
                                                {{ getExpiryBadge(order.expires_at)?.text }}
                                            </div>
                                        </div>
                                        <div v-else class="text-muted-foreground italic">Tidak terbatas</div>
                                    </td>
                                    <td class="py-3 text-sm text-muted-foreground">{{ formatDate(order.created_at) }}</td>
                                    <td class="py-3">
                                        <div class="flex items-center justify-center gap-1">
                                            <Button size="sm" variant="outline" asChild class="cursor-pointer" title="Lihat Detail">
                                                <Link :href="`/admin/orders/${order.id}`">
                                                    <Package class="h-3.5 w-3.5" />
                                                </Link>
                                            </Button>
                                            <Button size="sm" variant="outline" @click="openEditModal(order)" class="cursor-pointer" title="Edit">
                                                <Edit class="h-3.5 w-3.5" />
                                            </Button>
                                            <Button
                                                size="sm"
                                                variant="outline"
                                                @click="confirmDelete(order)"
                                                class="cursor-pointer text-red-600 hover:bg-red-50 hover:text-red-700"
                                                title="Hapus"
                                            >
                                                <Trash2 class="h-3.5 w-3.5" />
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="orders.links && orders.links.length > 3" class="mt-6 flex justify-center">
                        <nav class="flex space-x-1">
                            <template v-for="link in orders.links" :key="link.label">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    :class="['rounded px-3 py-2 text-sm', link.active ? 'bg-primary text-primary-foreground' : 'hover:bg-muted']"
                                    v-html="link.label"
                                />
                                <span v-else :class="['cursor-not-allowed rounded px-3 py-2 text-sm opacity-50']" v-html="link.label" />
                            </template>
                        </nav>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Order Form Modal (Create/Edit) -->
        <OrderFormModal
            :show="showOrderFormModal"
            :mode="orderFormMode"
            :order="selectedOrder"
            :customers="customers"
            :hosting-plans="hostingPlans"
            :domain-prices="domainPrices"
            :service-plans="servicePlans"
            @close="closeOrderFormModal"
            @submit="handleOrderFormSubmit"
        />

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <!-- Overlay -->
            <div class="fixed inset-0 bg-black/50" @click="showDeleteModal = false"></div>
            <!-- Modal Content -->
            <div class="relative mx-4 w-full max-w-md rounded-lg bg-white p-6 shadow-xl dark:bg-gray-900">
                <div class="mb-4">
                    <h3 class="text-lg font-semibold">Konfirmasi Hapus</h3>
                    <p class="text-sm text-muted-foreground">
                        Apakah Anda yakin ingin menghapus {{ orderToDelete?.isOrder ? 'pesanan' : 'layanan' }} #{{ orderToDelete?.id }}? Tindakan ini
                        tidak dapat dibatalkan.
                    </p>
                </div>
                <div class="flex justify-end space-x-3">
                    <Button variant="outline" @click="showDeleteModal = false"> Batal </Button>
                    <Button variant="destructive" @click="deleteOrder"> Hapus </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
