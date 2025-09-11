<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { AlertTriangle, Clock, DollarSign, Edit, Eye, FileText, Plus, Search, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Customer {
    id: number;
    name: string;
    email: string;
}

interface Service {
    id: number;
    service_type: string;
    domain_name: string;
}

interface Invoice {
    id: number;
    invoice_number: string;
    invoice_type: 'setup' | 'renewal';
    amount: number;
    discount?: number;
    status: 'draft' | 'pending' | 'sent' | 'paid' | 'overdue' | 'cancelled';
    issue_date: string;
    due_date: string;
    billing_cycle: string;
    payment_method?: string;
    paid_at?: string;
    created_at: string;
    order_id?: number;
    customer: Customer;
    service?: Service;
    order?: {
        id: number;
        domain_name?: string;
        service_type?: string;
    };
}

interface Service {
    id: number;
    domain_name: string;
    service_type: string;
    customer_id: number;
    customer: Customer;
}

interface Props {
    invoices: {
        data: Invoice[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: any[];
    };
    filters?: {
        search?: string;
        status?: string;
        invoice_type?: string;
    };
    statistics: {
        total: number;
        revenue: number;
        pending: number;
        overdue: number;
    };
    customers: Customer[];
    services: Service[];
    generationMessage?: string;
}

const props = defineProps<Props>();

const search = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || '');
const typeFilter = ref(props.filters?.invoice_type || '');
const showEditModal = ref(false);
const showCreateModal = ref(false);
const selectedInvoice = ref<Invoice | null>(null);

const editForm = useForm({
    status: 'pending' as 'draft' | 'pending' | 'sent' | 'paid' | 'overdue' | 'cancelled',
    discount: '',
    payment_method: '',
    notes: '',
});

const createForm = useForm({
    customer_id: '',
    service_id: '',
    invoice_type: 'setup' as 'setup' | 'renewal',
    amount: '',
    discount: '',
    billing_cycle: 'monthly' as 'monthly' | 'quarterly' | 'semi_annually' | 'annually',
    due_date: '',
    notes: '',
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Invoices', href: '/admin/invoices' },
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
        case 'paid':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
        case 'pending':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
        case 'sent':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
        case 'overdue':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
        case 'draft':
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
        case 'cancelled':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
    }
};

const getTypeColor = (type: string) => {
    switch (type) {
        case 'setup':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
        case 'renewal':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
    }
};

const handleSearch = () => {
    router.get(
        '/admin/invoices',
        {
            search: search.value,
            status: statusFilter.value,
            invoice_type: typeFilter.value,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};

const openEditModal = (invoice: Invoice) => {
    selectedInvoice.value = invoice;
    editForm.reset();
    editForm.status = invoice.status;
    editForm.discount = invoice.discount?.toString() || '';
    editForm.payment_method = invoice.payment_method || '';
    showEditModal.value = true;
};

const submitEdit = () => {
    if (!selectedInvoice.value) return;

    editForm.patch(`/admin/invoices/${selectedInvoice.value.id}`, {
        preserveState: false,
        preserveScroll: true,
        onSuccess: () => {
            showEditModal.value = false;
            editForm.reset();
            selectedInvoice.value = null;
        },
        onError: (errors) => {
            console.error('Update invoice error:', errors);
        },
    });
};

const submitCreate = () => {
    createForm.post('/admin/invoices', {
        onSuccess: () => {
            showCreateModal.value = false;
            createForm.reset();
        },
        onError: (errors) => {
            console.error('Create invoice error:', errors);
        },
    });
};

const filteredServices = computed(() => {
    if (!createForm.customer_id) return [];
    return props.services.filter((service) => service.customer_id == createForm.customer_id);
});

const getDefaultDueDate = () => {
    const date = new Date();
    date.setDate(date.getDate() + 14); // 14 days from now
    return date.toISOString().split('T')[0];
};
</script>

<template>
    <Head title="Admin - Invoices" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <!-- Auto-generation Message -->
            <div v-if="generationMessage" class="rounded-lg bg-blue-50 border border-blue-200 p-4 dark:bg-blue-950 dark:border-blue-800">
                <div class="flex items-center gap-2">
                    <FileText class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                    <span class="font-medium text-blue-800 dark:text-blue-200">{{ generationMessage }}</span>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Invoice Management</h1>
                    <p class="text-muted-foreground">Manage customer invoices and billing</p>
                </div>
                <Button
                    @click="
                        showCreateModal = true;
                        createForm.due_date = getDefaultDueDate();
                    "
                    class="cursor-pointer"
                >
                    <Plus class="mr-2 h-4 w-4" />
                    Create Invoice
                </Button>
            </div>

            <!-- Statistics Cards -->
            <div class="grid gap-4 md:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Invoices</CardTitle>
                        <FileText class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ statistics.total }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Revenue</CardTitle>
                        <DollarSign class="h-4 w-4 text-green-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-green-600">
                            {{ formatPrice(statistics.revenue) }}
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Pending</CardTitle>
                        <Clock class="h-4 w-4 text-yellow-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-yellow-600">
                            {{ formatPrice(statistics.pending) }}
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Overdue</CardTitle>
                        <AlertTriangle class="h-4 w-4 text-red-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-red-600">
                            {{ formatPrice(statistics.overdue) }}
                        </div>
                    </CardContent>
                </Card>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>All Invoices</CardTitle>
                    <CardDescription>Complete list of customer invoices</CardDescription>
                </CardHeader>
                <CardContent>
                    <!-- Search and Filter -->
                    <div class="mb-6 flex flex-col gap-4 sm:flex-row">
                        <div class="relative max-w-sm flex-1">
                            <Search class="absolute top-2.5 left-2.5 h-4 w-4 text-muted-foreground" />
                            <Input v-model="search" placeholder="Search invoices..." class="pl-8" @keyup.enter="handleSearch" />
                        </div>
                        <select
                            v-model="statusFilter"
                            class="flex h-9 w-[180px] rounded-md border border-input bg-background px-3 py-1 text-sm text-foreground shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none dark:bg-gray-800 dark:text-white"
                        >
                            <option value="">All Status</option>
                            <option value="draft">Draft</option>
                            <option value="pending">Pending</option>
                            <option value="sent">Sent</option>
                            <option value="paid">Paid</option>
                            <option value="overdue">Overdue</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                        <select
                            v-model="typeFilter"
                            class="flex h-9 w-[180px] rounded-md border border-input bg-background px-3 py-1 text-sm text-foreground shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none dark:bg-gray-800 dark:text-white"
                        >
                            <option value="">All Types</option>
                            <option value="setup">Setup</option>
                            <option value="renewal">Renewal</option>
                        </select>
                        <Button @click="handleSearch" class="cursor-pointer">Search</Button>
                    </div>

                    <!-- Invoice Cards -->
                    <div v-if="!invoices?.data || invoices.data.length === 0" class="py-12 text-center text-muted-foreground">
                        <FileText class="mx-auto h-12 w-12 text-muted-foreground/40" />
                        <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-gray-100">No invoices found</h3>
                        <p class="mt-1 text-sm text-muted-foreground">Try adjusting your search criteria.</p>
                    </div>

                    <div v-else class="space-y-4">
                        <div
                            v-for="invoice in invoices.data"
                            :key="invoice.id"
                            class="flex items-center justify-between rounded-lg border p-4 transition-colors hover:bg-muted/30"
                        >
                            <div class="min-w-0 flex-1">
                                <div class="mb-2 flex items-center gap-3">
                                    <h3 class="truncate text-sm font-semibold text-foreground">{{ invoice.invoice_number }}</h3>
                                    <Badge :class="getStatusColor(invoice.status)">
                                        {{ invoice.status }}
                                    </Badge>
                                    <Badge :class="getTypeColor(invoice.invoice_type)">
                                        {{ invoice.invoice_type }}
                                    </Badge>
                                </div>
                                <div class="space-y-1 text-xs text-muted-foreground">
                                    <div class="flex items-center gap-4">
                                        <span>{{ invoice.customer.name }}</span>
                                        <span>{{ invoice.customer.email }}</span>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <span v-if="invoice.order_id">Order #{{ invoice.order_id }}</span>
                                        <span v-if="invoice.order?.domain_name">Domain: {{ invoice.order.domain_name }}</span>
                                        <span v-else-if="invoice.service">Service: {{ invoice.service.domain_name }}</span>
                                        <span>Cycle: {{ invoice.billing_cycle.replace('_', ' ') }}</span>
                                        <span>Due: {{ formatDate(invoice.due_date) }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="ml-4 flex items-center gap-6">
                                <!-- Amount -->
                                <div class="hidden flex-col text-right md:flex">
                                    <div class="text-sm font-medium text-foreground">
                                        {{ formatPrice(invoice.amount - (invoice.discount || 0)) }}
                                    </div>
                                    <div class="text-xs text-muted-foreground">
                                        {{ invoice.discount && invoice.discount > 0 ? 'Final Amount' : 'Amount' }}
                                    </div>
                                    <div v-if="invoice.discount && invoice.discount > 0" class="text-xs text-red-500">
                                        Potongan: {{ formatPrice(invoice.discount) }}
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center gap-2">
                                    <Button size="sm" variant="outline" asChild>
                                        <Link :href="`/admin/invoices/${invoice.id}`" class="cursor-pointer">
                                            <Eye class="h-3 w-3" />
                                        </Link>
                                    </Button>
                                    <Button size="sm" variant="outline" @click="openEditModal(invoice)" class="cursor-pointer">
                                        <Edit class="h-3 w-3" />
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="invoices.links && invoices.links.length > 3" class="flex items-center justify-between border-t pt-6">
                        <div class="text-sm text-muted-foreground">
                            Showing {{ (invoices.current_page - 1) * invoices.per_page + 1 || 0 }} to
                            {{ Math.min(invoices.current_page * invoices.per_page, invoices.total) || 0 }} of {{ invoices.total || 0 }} results
                        </div>
                        <div class="flex items-center gap-1">
                            <template v-for="link in invoices.links" :key="link.label">
                                <Button
                                    v-if="link.url"
                                    variant="outline"
                                    size="sm"
                                    :disabled="!link.url"
                                    :class="link.active ? 'cursor-pointer bg-primary text-primary-foreground' : 'cursor-pointer'"
                                    @click="router.visit(link.url)"
                                    v-html="link.label"
                                />
                                <span v-else class="px-3 py-2 text-sm text-muted-foreground" v-html="link.label" />
                            </template>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Create Invoice Modal -->
        <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <!-- Overlay -->
            <div class="fixed inset-0 bg-black/50" @click="showCreateModal = false"></div>

            <!-- Modal Content -->
            <div class="relative mx-4 max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-lg bg-white p-6 shadow-xl dark:bg-gray-900">
                <!-- Header -->
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold">Create New Invoice</h2>
                        <p class="text-sm text-muted-foreground">Create a new invoice for customer billing</p>
                    </div>
                    <button @click="showCreateModal = false" class="text-gray-500 hover:text-gray-700">
                        <X class="h-4 w-4" />
                    </button>
                </div>
                <form @submit.prevent="submitCreate" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="create-customer" class="mb-2 block text-sm font-medium">Customer *</label>
                            <select
                                id="create-customer"
                                v-model="createForm.customer_id"
                                class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm text-foreground shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none dark:bg-gray-800 dark:text-white"
                                required
                            >
                                <option value="">Select Customer</option>
                                <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                                    {{ customer.name }} ({{ customer.email }})
                                </option>
                            </select>
                            <p v-if="createForm.errors.customer_id" class="mt-1 text-xs text-red-500">{{ createForm.errors.customer_id }}</p>
                        </div>
                        <div>
                            <label for="create-invoice-type" class="mb-2 block text-sm font-medium">Invoice Type *</label>
                            <select
                                id="create-invoice-type"
                                v-model="createForm.invoice_type"
                                class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm text-foreground shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none dark:bg-gray-800 dark:text-white"
                                required
                            >
                                <option value="setup">Setup</option>
                                <option value="renewal">Renewal</option>
                            </select>
                            <p v-if="createForm.errors.invoice_type" class="mt-1 text-xs text-red-500">{{ createForm.errors.invoice_type }}</p>
                        </div>
                    </div>

                    <div>
                        <label for="create-service" class="mb-2 block text-sm font-medium">Service (Optional)</label>
                        <select
                            id="create-service"
                            v-model="createForm.service_id"
                            class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm text-foreground shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none dark:bg-gray-800 dark:text-white"
                            :disabled="!createForm.customer_id"
                        >
                            <option value="">No specific service</option>
                            <option v-for="service in filteredServices" :key="service.id" :value="service.id">
                                {{ service.domain_name }} ({{ service.service_type }})
                            </option>
                        </select>
                        <p v-if="createForm.errors.service_id" class="mt-1 text-xs text-red-500">{{ createForm.errors.service_id }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="create-amount" class="mb-2 block text-sm font-medium">Amount *</label>
                            <Input id="create-amount" v-model="createForm.amount" type="number" step="0.01" min="0" placeholder="0.00" required />
                            <p v-if="createForm.errors.amount" class="mt-1 text-xs text-red-500">{{ createForm.errors.amount }}</p>
                        </div>
                        <div>
                            <label for="create-discount" class="mb-2 block text-sm font-medium">Potongan (Rp)</label>
                            <Input id="create-discount" v-model="createForm.discount" type="number" step="0.01" min="0" placeholder="0.00" />
                            <p v-if="createForm.errors.discount" class="mt-1 text-xs text-red-500">{{ createForm.errors.discount }}</p>
                        </div>
                    </div>

                    <div>
                        <label for="create-billing-cycle" class="mb-2 block text-sm font-medium">Billing Cycle *</label>
                        <select
                            id="create-billing-cycle"
                            v-model="createForm.billing_cycle"
                            class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm text-foreground shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none dark:bg-gray-800 dark:text-white"
                            required
                        >
                            <option value="monthly">Monthly</option>
                            <option value="quarterly">Quarterly</option>
                            <option value="semi_annually">Semi Annually</option>
                            <option value="annually">Annually</option>
                        </select>
                        <p v-if="createForm.errors.billing_cycle" class="mt-1 text-xs text-red-500">{{ createForm.errors.billing_cycle }}</p>
                    </div>

                    <div>
                        <label for="create-due-date" class="mb-2 block text-sm font-medium">Tanggal Jatuh Tempo *</label>
                        <Input
                            id="create-due-date"
                            v-model="createForm.due_date"
                            type="date"
                            placeholder="Pilih tanggal jatuh tempo"
                            :class="{ 'border-red-500': createForm.errors.due_date }"
                            required
                        />
                        <p v-if="createForm.errors.due_date" class="mt-1 text-xs text-red-500">{{ createForm.errors.due_date }}</p>
                    </div>

                    <div>
                        <label for="create-notes" class="mb-2 block text-sm font-medium">Notes</label>
                        <textarea
                            id="create-notes"
                            v-model="createForm.notes"
                            placeholder="Additional notes for the invoice..."
                            class="flex min-h-[80px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                        ></textarea>
                        <p v-if="createForm.errors.notes" class="mt-1 text-xs text-red-500">{{ createForm.errors.notes }}</p>
                    </div>

                    <!-- Footer -->
                    <div class="mt-6 flex justify-end gap-2">
                        <Button type="button" variant="outline" @click="showCreateModal = false"> Cancel </Button>
                        <Button type="submit" :disabled="createForm.processing">
                            {{ createForm.processing ? 'Creating...' : 'Create Invoice' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Invoice Modal -->
        <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <!-- Overlay -->
            <div class="fixed inset-0 bg-black/50" @click="showEditModal = false"></div>

            <!-- Modal Content -->
            <div class="relative mx-4 w-full max-w-md rounded-lg bg-white p-6 shadow-xl dark:bg-gray-900">
                <!-- Header -->
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold">Edit Invoice</h2>
                        <p class="text-sm text-muted-foreground">Update invoice status and payment details</p>
                    </div>
                    <button @click="showEditModal = false" class="text-gray-500 hover:text-gray-700">
                        <X class="h-4 w-4" />
                    </button>
                </div>
                <form @submit.prevent="submitEdit" class="space-y-4">
                    <div>
                        <label for="edit-status" class="mb-2 block text-sm font-medium">Status *</label>
                        <select
                            id="edit-status"
                            v-model="editForm.status"
                            class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm text-foreground shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none dark:bg-gray-800 dark:text-white"
                            required
                        >
                            <option value="draft">Draft</option>
                            <option value="pending">Pending</option>
                            <option value="sent">Sent</option>
                            <option value="paid">Paid</option>
                            <option value="overdue">Overdue</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                        <p v-if="editForm.errors.status" class="mt-1 text-xs text-red-500">{{ editForm.errors.status }}</p>
                    </div>

                    <div>
                        <label for="edit-discount" class="mb-2 block text-sm font-medium">Potongan (Rp)</label>
                        <Input id="edit-discount" v-model="editForm.discount" type="number" step="0.01" min="0" placeholder="0.00" />
                        <p v-if="editForm.errors.discount" class="mt-1 text-xs text-red-500">{{ editForm.errors.discount }}</p>
                    </div>

                    <div v-if="editForm.status === 'paid'">
                        <label for="edit-payment-method" class="mb-2 block text-sm font-medium">Payment Method</label>
                        <Input id="edit-payment-method" v-model="editForm.payment_method" placeholder="Bank Transfer, Credit Card, etc." />
                        <p v-if="editForm.errors.payment_method" class="mt-1 text-xs text-red-500">{{ editForm.errors.payment_method }}</p>
                    </div>

                    <div>
                        <label for="edit-notes" class="mb-2 block text-sm font-medium">Notes</label>
                        <textarea
                            id="edit-notes"
                            v-model="editForm.notes"
                            placeholder="Additional notes..."
                            class="flex min-h-[80px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                        ></textarea>
                        <p v-if="editForm.errors.notes" class="mt-1 text-xs text-red-500">{{ editForm.errors.notes }}</p>
                    </div>

                    <!-- Footer -->
                    <div class="mt-6 flex justify-end gap-2">
                        <Button type="button" variant="outline" @click="showEditModal = false"> Cancel </Button>
                        <Button type="submit" :disabled="editForm.processing">
                            {{ editForm.processing ? 'Updating...' : 'Update Invoice' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
