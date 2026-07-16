<script setup lang="ts">
import CustomerLayout from '@/layouts/CustomerLayout.vue';
import customer from '@/routes/customer';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ArrowRight, Building2, CreditCard, Layers, LogOut, QrCode, ReceiptText, Sparkles, Wallet } from 'lucide-vue-next';
import { computed } from 'vue';

interface Customer {
    id: number;
    name: string;
    email: string;
    phone?: string;
    status: string;
}

interface HostingPlan {
    id: number;
    plan_name: string;
    storage_gb: number;
}

interface Service {
    id: number;
    service_type?: string;
    domain_name?: string;
    status: string;
    expires_at?: string;
    hosting_plan?: HostingPlan;
}

interface OrderItem {
    id: number;
    item_type: string;
    domain_name: string | null;
    price: number;
}

interface Order {
    id: number;
    total_amount: number;
    discount_amount?: number;
    status: string;
    created_at: string;
    order_items: OrderItem[];
}

interface Invoice {
    id: number;
    invoice_number: string;
    amount: number;
    status: string;
    due_date: string;
}

interface PaymentAccount {
    id: number;
    type: 'bank' | 'ewallet' | 'qris';
    name: string;
    account_number: string | null;
    account_name: string | null;
    qris_image_path: string | null;
}

interface Props {
    customer: Customer;
    services: Service[];
    recentOrders: Order[];
    unpaidInvoices: Invoice[];
    paymentAccounts: PaymentAccount[];
}

const props = defineProps<Props>();
const page = usePage();

const logoutForm = useForm({});

const customerRoutes = customer as any;

const getCustomerUrl = (getter: () => string | undefined, fallback: string) => {
    try {
        return getter() || fallback;
    } catch {
        return fallback;
    }
};

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Customer Dashboard', href: getCustomerUrl(() => customerRoutes?.dashboard?.().url, '/customer/dashboard') }];

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
        case 'active':
        case 'completed':
        case 'paid':
            return 'bg-[#c7f0da] text-[#103c25]';
        case 'pending':
        case 'processing':
            return 'bg-[#fef3c7] text-[#92400e]';
        case 'suspended':
        case 'overdue':
            return 'bg-[#fed7aa] text-[#9a3412]';
        case 'terminated':
        case 'cancelled':
            return 'bg-[#fecaca] text-[#991b1b]';
        default:
            return 'bg-[#e5e5e0] text-[#62625b]';
    }
};

const logout = () => {
    logoutForm.post(customer.logout().url);
};

const activeServiceCount = computed(() => props.services.filter((s) => s.status === 'active').length);
const serviceCount = computed(() => props.services.length);
const unpaidInvoiceCount = computed(() => props.unpaidInvoices.length);
const hasUnpaidInvoices = computed(() => unpaidInvoiceCount.value > 0);

const primaryInvoice = computed(() => props.unpaidInvoices[0] || null);

const paymentTypeLabel = (type: PaymentAccount['type']) => {
    if (type === 'bank') return 'Bank';
    if (type === 'ewallet') return 'E-Wallet';
    return 'QRIS';
};

const paymentTypeIcon = (type: PaymentAccount['type']) => {
    if (type === 'bank') return Building2;
    if (type === 'ewallet') return Wallet;
    return QrCode;
};

const displayCustomerName = computed(() => {
    const fromAuth = (page.props as any)?.auth?.customer?.name as string | undefined;
    return fromAuth || props.customer?.name || '';
});

const displayCustomerEmail = computed(() => {
    const fromAuth = (page.props as any)?.auth?.customer?.email as string | undefined;
    return fromAuth || props.customer?.email || '';
});

const timeGreeting = computed(() => {
    const hour = new Date().getHours();
    if (hour >= 5 && hour < 11) return 'Selamat pagi';
    if (hour >= 11 && hour < 15) return 'Selamat siang';
    if (hour >= 15 && hour < 18) return 'Selamat sore';
    return 'Selamat malam';
});

const todayLabel = computed(() =>
    new Date().toLocaleDateString('id-ID', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    }),
);
</script>

<template>
    <Head title="Customer Dashboard" />

    <CustomerLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6 lg:p-8" style="background-color: #fbfbf9;">
            <!-- Hero welcome card -->
            <div class="rounded-2xl bg-white p-6 sm:p-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <div class="mb-3 inline-flex items-center gap-1.5 rounded-full bg-[#f6f6f3] px-3 py-1 text-xs font-bold" style="color: #62625b;">
                            <Sparkles class="h-3.5 w-3.5" style="color: var(--primary)" />
                            <span>Area Pelanggan</span>
                        </div>
                        <h1 class="font-heading text-2xl font-bold leading-tight sm:text-3xl" style="letter-spacing: -1.2px;">
                            {{ timeGreeting }}, {{ displayCustomerName }}
                        </h1>
                        <p class="mt-1.5" style="color: #62625b; font-size: 14px;">
                            {{ todayLabel }}
                            <span class="mx-1.5 inline-block">&middot;</span>
                            <span>{{ displayCustomerEmail }}</span>
                        </p>
                    </div>
                    <div class="flex w-full flex-col gap-2 sm:w-auto sm:min-w-[260px]">
                        <div class="grid grid-cols-2 gap-2">
                            <Link
                                :href="getCustomerUrl(() => customerRoutes?.services?.index?.().url, '/customer/services')"
                                class="inline-flex h-10 items-center justify-between rounded-2xl px-3.5 py-1.5 text-sm font-bold transition-colors"
                                style="background-color: #f6f6f3; color: #000000;"
                                @mouseenter="$event.target.style.backgroundColor = '#c8c8c1'"
                                @mouseleave="$event.target.style.backgroundColor = '#f6f6f3'"
                            >
                                <span class="inline-flex items-center gap-2">
                                    <Layers class="h-4 w-4" style="color: var(--primary)" />
                                    Layanan
                                </span>
                                <ArrowRight class="h-4 w-4 opacity-70" />
                            </Link>
                            <Link
                                :href="getCustomerUrl(() => customerRoutes?.orders?.index?.().url, '/customer/orders')"
                                class="inline-flex h-10 items-center justify-between rounded-2xl px-3.5 py-1.5 text-sm font-bold transition-colors"
                                style="background-color: #f6f6f3; color: #000000;"
                                @mouseenter="$event.target.style.backgroundColor = '#c8c8c1'"
                                @mouseleave="$event.target.style.backgroundColor = '#f6f6f3'"
                            >
                                <span class="inline-flex items-center gap-2">
                                    <ReceiptText class="h-4 w-4" style="color: var(--primary)" />
                                    Pesanan
                                </span>
                                <ArrowRight class="h-4 w-4 opacity-70" />
                            </Link>
                        </div>
                        <Link
                            v-if="primaryInvoice"
                            :href="getCustomerUrl(() => customerRoutes?.invoices?.payment?.(primaryInvoice.id).url, `/customer/invoices/${primaryInvoice.id}/payment`)"
                            class="inline-flex h-10 items-center justify-between rounded-2xl px-3.5 py-1.5 text-sm font-bold transition-opacity hover:opacity-90"
                            style="background-color: var(--primary); color: #ffffff;"
                        >
                            <span class="inline-flex items-center gap-2">
                                <CreditCard class="h-4 w-4" />
                                Bayar Tagihan
                            </span>
                            <ArrowRight class="h-4 w-4 opacity-80" />
                        </Link>
                        <button
                            @click="logout"
                            class="inline-flex h-10 items-center justify-between rounded-2xl px-3.5 py-1.5 text-sm font-bold transition-colors"
                            style="background-color: #f6f6f3; color: #000000;"
                            @mouseenter="$event.target.style.backgroundColor = '#c8c8c1'"
                            @mouseleave="$event.target.style.backgroundColor = '#f6f6f3'"
                        >
                            <span class="inline-flex items-center gap-2">
                                <LogOut class="h-4 w-4" />
                                Logout
                            </span>
                            <span style="color: #91918c; font-size: 12px; font-weight: 400;">Keluar</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Stat cards -->
            <div class="grid gap-4 sm:grid-cols-2 sm:gap-6 lg:grid-cols-3">
                <div class="rounded-2xl bg-[#f6f6f3] p-5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wide" style="color: #62625b;">Layanan Aktif</span>
                        <Layers class="h-4 w-4" style="color: var(--primary)" />
                    </div>
                    <div class="font-heading mt-2 text-3xl font-bold" style="letter-spacing: -1.2px;">{{ activeServiceCount }}</div>
                    <p class="mt-0.5" style="color: #91918c; font-size: 14px;">{{ serviceCount }} total layanan</p>
                </div>

                <div class="rounded-2xl bg-[#f6f6f3] p-5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wide" style="color: #62625b;">Tagihan Belum Bayar</span>
                        <CreditCard class="h-4 w-4" style="color: var(--primary)" />
                    </div>
                    <div class="font-heading mt-2 text-3xl font-bold" style="letter-spacing: -1.2px;">{{ unpaidInvoiceCount }}</div>
                    <p class="mt-0.5" style="color: #91918c; font-size: 14px;">Perlu perhatian</p>
                </div>

                <div class="rounded-2xl bg-[#f6f6f3] p-5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wide" style="color: #62625b;">Status Akun</span>
                        <Sparkles class="h-4 w-4" style="color: var(--primary)" />
                    </div>
                    <div class="font-heading mt-2 text-3xl font-bold capitalize" style="letter-spacing: -1.2px;">{{ customer.status }}</div>
                    <span :class="`mt-1.5 inline-flex items-center rounded-full px-3 py-1 text-xs font-bold ${getStatusColor(customer.status)}`">
                        {{ customer.status }}
                    </span>
                </div>
            </div>

            <!-- Bottom row -->
            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Payment Methods -->
                <div class="rounded-2xl bg-white p-6">
                    <h2 class="font-heading text-xl font-bold" style="letter-spacing: -1.2px;">Metode Pembayaran</h2>
                    <p class="mt-1" style="color: #62625b; font-size: 14px;">Metode yang tersedia untuk pembayaran tagihan</p>
                    <div v-if="paymentAccounts.length === 0" class="py-8 text-center text-sm" style="color: #91918c;">
                        Belum ada metode pembayaran yang tersedia.
                    </div>
                    <div v-else class="mt-4 space-y-1">
                        <div v-for="account in paymentAccounts" :key="account.id" class="flex items-start justify-between gap-3 rounded-2xl px-4 py-3" style="background-color: #f6f6f3;">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <component :is="paymentTypeIcon(account.type)" class="h-4 w-4" style="color: var(--primary)" />
                                    <span class="text-sm font-bold">{{ account.name }}</span>
                                    <span class="rounded-full px-2 py-0.5 text-xs font-bold" style="background-color: #e5e5e0; color: #62625b;">{{ paymentTypeLabel(account.type) }}</span>
                                </div>
                                <div class="mt-1 text-sm" style="color: #91918c;">
                                    <template v-if="account.type === 'bank'">
                                        <span class="font-mono">{{ account.account_number || '-' }}</span>
                                        <span v-if="account.account_name"> a.n. {{ account.account_name }}</span>
                                    </template>
                                    <template v-else-if="account.type === 'ewallet'">
                                        <span>Nomor: </span><span class="font-mono">{{ account.account_number || '-' }}</span>
                                    </template>
                                    <template v-else>
                                        <span v-if="account.qris_image_path">Scan QR untuk bayar</span>
                                        <span v-else>QR belum diupload</span>
                                    </template>
                                </div>
                            </div>
                            <a
                                v-if="account.type === 'qris' && account.qris_image_path"
                                :href="account.qris_image_path"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="shrink-0"
                            >
                                <img
                                    :src="account.qris_image_path"
                                    alt="QRIS"
                                    class="h-20 w-20 rounded-xl border object-contain"
                                    style="border-color: #dadad3; background-color: #ffffff;"
                                />
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Unpaid Invoices -->
                <div
                    class="rounded-2xl p-6"
                    :style="{
                        backgroundColor: hasUnpaidInvoices ? '#ffffff' : '#ffffff',
                    }"
                >
                    <h2 class="font-heading text-xl font-bold" :style="{ letterSpacing: '-1.2px', color: hasUnpaidInvoices ? 'var(--primary)' : undefined }">
                        Tagihan Belum Bayar
                    </h2>
                    <p v-if="hasUnpaidInvoices" class="mt-1 text-sm" style="color: #62625b;">
                        Anda memiliki {{ unpaidInvoices.length }} tagihan belum bayar yang perlu perhatian
                    </p>
                    <p v-else class="mt-1 text-sm" style="color: #62625b;">Tidak ada tagihan belum bayar saat ini.</p>

                    <div v-if="hasUnpaidInvoices" class="mt-4 space-y-1">
                        <div v-for="invoice in unpaidInvoices" :key="invoice.id" class="flex items-start justify-between gap-3 rounded-2xl px-4 py-3" style="background-color: #f6f6f3;">
                            <div class="min-w-0">
                                <Link
                                    :href="getCustomerUrl(() => customerRoutes?.invoices?.show?.(invoice.id).url, `/customer/invoices/${invoice.id}`)"
                                    class="text-sm font-bold hover:underline"
                                >
                                    {{ invoice.invoice_number }}
                                </Link>
                                <div class="mt-0.5 text-sm" style="color: #91918c;">Jatuh tempo {{ formatDate(invoice.due_date) }}</div>
                            </div>
                            <div class="flex shrink-0 flex-col items-end gap-1.5">
                                <div class="text-base font-bold" style="color: var(--primary);">{{ formatPrice(invoice.amount) }}</div>
                                <Link
                                    :href="getCustomerUrl(() => customerRoutes?.invoices?.payment?.(invoice.id).url, `/customer/invoices/${invoice.id}/payment`)"
                                    class="inline-flex h-8 items-center rounded-2xl px-3.5 text-xs font-bold transition-opacity hover:opacity-90"
                                    style="background-color: var(--primary); color: #ffffff;"
                                >
                                    Bayar
                                </Link>
                            </div>
                        </div>
                    </div>
                    <div v-else class="mt-4 rounded-2xl px-4 py-6 text-center text-sm" style="background-color: #f6f6f3; color: #91918c;">
                        Semua tagihan sudah dibayar atau belum ada tagihan.
                    </div>

                    <div class="mt-4 flex flex-col gap-2 sm:flex-row">
                        <Link
                            :href="getCustomerUrl(() => customerRoutes?.invoices?.index?.().url, '/customer/invoices')"
                            class="inline-flex h-10 items-center justify-center rounded-2xl px-3.5 py-1.5 text-sm font-bold transition-colors"
                            style="background-color: #f6f6f3; color: #000000;"
                            @mouseenter="$event.target.style.backgroundColor = '#c8c8c1'"
                            @mouseleave="$event.target.style.backgroundColor = '#f6f6f3'"
                        >
                            Lihat Semua Tagihan
                        </Link>
                        <Link
                            v-if="unpaidInvoices.length > 0"
                            :href="
                                getCustomerUrl(
                                    () => customerRoutes?.invoices?.payment?.(unpaidInvoices[0].id).url,
                                    `/customer/invoices/${unpaidInvoices[0].id}/payment`,
                                )
                            "
                            class="inline-flex h-10 items-center justify-center rounded-2xl px-3.5 py-1.5 text-sm font-bold transition-opacity hover:opacity-90"
                            style="background-color: var(--primary); color: #ffffff;"
                        >
                            Bayar Sekarang
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </CustomerLayout>
</template>
