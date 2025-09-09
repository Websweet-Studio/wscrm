<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Calendar, DollarSign, Download, FileText, Globe, Server, User } from 'lucide-vue-next';

interface Customer {
    id: number;
    name: string;
    email: string;
}

interface Service {
    id: number;
    service_type: string;
    domain_name: string;
    status: string;
    expires_at: string;
}

interface Order {
    id: number;
    total_amount: number;
    status: string;
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
    notes?: string;
    created_at: string;
    customer: Customer;
    service?: Service;
    order?: Order;
}

interface Props {
    invoice: Invoice;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Invoices', href: '/admin/invoices' },
    { title: props.invoice.invoice_number, href: `/admin/invoices/${props.invoice.id}` },
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
        month: 'long',
        day: 'numeric',
    });
};

const formatDateTime = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
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
</script>

<template>
    <Head :title="`Invoice ${invoice.invoice_number}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">{{ invoice.invoice_number }}</h1>
                    <p class="text-muted-foreground">Invoice details and information</p>
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline">
                        <Download class="mr-2 h-4 w-4" />
                        Download PDF
                    </Button>
                    <Button asChild>
                        <Link :href="`/admin/invoices/${invoice.id}/edit`"> Edit Invoice </Link>
                    </Button>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-3">
                <!-- Invoice Overview -->
                <div class="md:col-span-2">
                    <Card>
                        <CardHeader>
                            <div class="flex items-center justify-between">
                                <div>
                                    <CardTitle class="flex items-center gap-2">
                                        <FileText class="h-5 w-5" />
                                        Invoice Details
                                    </CardTitle>
                                    <CardDescription>{{ invoice.invoice_number }}</CardDescription>
                                </div>
                                <div class="flex items-center gap-2">
                                    <Badge :class="getStatusColor(invoice.status)">
                                        {{ invoice.status }}
                                    </Badge>
                                    <Badge :class="getTypeColor(invoice.invoice_type)">
                                        {{ invoice.invoice_type }}
                                    </Badge>
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <div class="text-sm font-medium text-muted-foreground">Issue Date</div>
                                    <div class="mt-1 flex items-center gap-2">
                                        <Calendar class="h-4 w-4 text-muted-foreground" />
                                        <span>{{ formatDate(invoice.issue_date) }}</span>
                                    </div>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-muted-foreground">Due Date</div>
                                    <div class="mt-1 flex items-center gap-2">
                                        <Calendar class="h-4 w-4 text-muted-foreground" />
                                        <span>{{ formatDate(invoice.due_date) }}</span>
                                    </div>
                                </div>
                            </div>

                            <Separator />

                            <div>
                                <div class="mb-2 text-sm font-medium text-muted-foreground">Amount</div>
                                <div v-if="invoice.discount && invoice.discount > 0" class="space-y-2">
                                    <div class="text-lg text-muted-foreground line-through">{{ formatPrice(invoice.amount) }}</div>
                                    <div class="text-sm text-red-600">Potongan: -{{ formatPrice(invoice.discount) }}</div>
                                    <div class="text-3xl font-bold text-green-600">{{ formatPrice(invoice.amount - invoice.discount) }}</div>
                                    <div class="text-sm text-muted-foreground">Final Amount</div>
                                </div>
                                <div v-else class="text-3xl font-bold">{{ formatPrice(invoice.amount) }}</div>
                                <div class="mt-1 text-sm text-muted-foreground">Billing Cycle: {{ invoice.billing_cycle.replace('_', ' ') }}</div>
                            </div>

                            <Separator v-if="invoice.payment_method || invoice.paid_at" />

                            <div v-if="invoice.payment_method || invoice.paid_at" class="space-y-2">
                                <div v-if="invoice.payment_method">
                                    <div class="text-sm font-medium text-muted-foreground">Payment Method</div>
                                    <div class="mt-1 flex items-center gap-2">
                                        <DollarSign class="h-4 w-4 text-muted-foreground" />
                                        <span>{{ invoice.payment_method }}</span>
                                    </div>
                                </div>
                                <div v-if="invoice.paid_at">
                                    <div class="text-sm font-medium text-muted-foreground">Paid At</div>
                                    <div class="mt-1 flex items-center gap-2">
                                        <Calendar class="h-4 w-4 text-muted-foreground" />
                                        <span>{{ formatDateTime(invoice.paid_at) }}</span>
                                    </div>
                                </div>
                            </div>

                            <Separator v-if="invoice.notes" />

                            <div v-if="invoice.notes">
                                <div class="mb-2 text-sm font-medium text-muted-foreground">Notes</div>
                                <div class="text-sm">{{ invoice.notes }}</div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Customer & Service Info -->
                <div class="space-y-6">
                    <!-- Customer Information -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <User class="h-5 w-5" />
                                Customer
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div>
                                <div class="font-medium">{{ invoice.customer.name }}</div>
                                <div class="text-sm text-muted-foreground">{{ invoice.customer.email }}</div>
                            </div>
                            <Button size="sm" variant="outline" asChild>
                                <Link :href="`/admin/customers/${invoice.customer.id}`"> View Customer </Link>
                            </Button>
                        </CardContent>
                    </Card>

                    <!-- Service Information -->
                    <Card v-if="invoice.service">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <component :is="invoice.service.service_type === 'hosting' ? Server : Globe" class="h-5 w-5" />
                                Service
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div>
                                <div class="font-medium">{{ invoice.service.domain_name }}</div>
                                <div class="text-sm text-muted-foreground">{{ invoice.service.service_type }}</div>
                            </div>
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm font-medium">Status</div>
                                    <Badge
                                        :class="`text-xs ${invoice.service.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}`"
                                    >
                                        {{ invoice.service.status }}
                                    </Badge>
                                </div>
                                <div>
                                    <div class="text-sm font-medium">Expires</div>
                                    <div class="text-sm text-muted-foreground">{{ formatDate(invoice.service.expires_at) }}</div>
                                </div>
                            </div>
                            <Button size="sm" variant="outline" asChild>
                                <Link :href="`/admin/services/${invoice.service.id}`"> View Service </Link>
                            </Button>
                        </CardContent>
                    </Card>

                    <!-- Order Information -->
                    <Card v-if="invoice.order">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <FileText class="h-5 w-5" />
                                Related Order
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div>
                                <div class="font-medium">Order #{{ invoice.order.id }}</div>
                                <div class="text-sm text-muted-foreground">{{ formatPrice(invoice.order.total_amount) }}</div>
                            </div>
                            <Badge
                                :class="`text-xs ${invoice.order.status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}`"
                            >
                                {{ invoice.order.status }}
                            </Badge>
                            <Button size="sm" variant="outline" asChild>
                                <Link :href="`/admin/orders/${invoice.order.id}`"> View Order </Link>
                            </Button>
                        </CardContent>
                    </Card>

                    <!-- Invoice Meta -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Invoice Meta</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Created</span>
                                <span>{{ formatDateTime(invoice.created_at) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Type</span>
                                <Badge :class="getTypeColor(invoice.invoice_type)" variant="outline">
                                    {{ invoice.invoice_type }}
                                </Badge>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
