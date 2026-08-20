<script setup lang="ts">
import CustomerLayout from '@/layouts/CustomerLayout.vue';
import customer from '@/routes/customer';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { AlertTriangle, ArrowRight, Bot, Building2, CreditCard, Layers, QrCode, ReceiptText, Sparkles, Wallet, Wrench } from 'lucide-vue-next';
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
    expires_at?: string;
    order_items: OrderItem[];
    hosting_plan?: HostingPlan;
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

interface ExpiringService {
    id: number;
    name: string;
    expires_at: string;
    is_expired: boolean;
    days_left: number;
}

interface JournalItem {
    id: number;
    website_name: string | null;
    entry_date: string | null;
    summary: string | null;
    activity_count: number;
}

interface Alert {
    id: string;
    kind: 'danger' | 'warning' | 'info';
    title: string;
    message: string;
    href?: string;
}

interface Props {
    customer: Customer;
    services: Service[];
    recentOrders: Order[];
    unpaidInvoices: Invoice[];
    unpaidTotal: number;
    expenseMonth: number;
    outstandingTotal: number;
    paymentAccounts: PaymentAccount[];
    aiBalance: number;
    expiringSoon: ExpiringService[];
    recentJournals: JournalItem[];
}

const props = defineProps<Props>();
const page = usePage();

const customerRoutes = customer as any;

const getCustomerUrl = (getter: () => string | undefined, fallback: string) => {
    try {
        return getter() || fallback;
    } catch {
        return fallback;
    }
};

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Dashboard Pelanggan', href: getCustomerUrl(() => customerRoutes?.dashboard?.().url, '/customer/dashboard') }];

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

const orderStatusLabel = (status: string): string => {
    const labels: Record<string, string> = {
        pending: 'Menunggu',
        processing: 'Diproses',
        completed: 'Selesai',
        active: 'Aktif',
        suspended: 'Ditangguhkan',
        expired: 'Kadaluarsa',
        terminated: 'Dihentikan',
        cancelled: 'Dibatalkan',
    };
    return labels[status] || status;
};

const activeServiceCount = computed(() => props.services.filter((s) => s.status === 'active').length);
const serviceCount = computed(() => props.services.length);
const unpaidInvoiceCount = computed(() => props.unpaidInvoices.length);
const hasUnpaidInvoices = computed(() => unpaidInvoiceCount.value > 0);
const unpaidOverdueCount = computed(() => props.unpaidInvoices.filter((i) => new Date(i.due_date) < new Date()).length);

const primaryInvoice = computed(() => props.unpaidInvoices[0] || null);

// Label due date relatif: hari ini / terlewati / sisa hari.
const dueLabel = (invoice: Invoice) => {
    const now = new Date();
    now.setHours(0, 0, 0, 0);
    const due = new Date(invoice.due_date);
    due.setHours(0, 0, 0, 0);
    const days = Math.round((due.getTime() - now.getTime()) / 86400000);

    if (days < 0) return { text: `Terlambat ${Math.abs(days)} hari`, overdue: true };
    if (days === 0) return { text: 'Jatuh tempo hari ini', overdue: true };
    return { text: `Sisa ${days} hari`, overdue: false };
};

// Notifikasi ringkas: tagihan terlambat, layanan hampir habis/kadaluarsa, kredit menipis.
const alerts = computed<Alert[]>(() => {
    const list: Alert[] = [];

    for (const inv of props.unpaidInvoices) {
        if (new Date(inv.due_date) < new Date()) {
            list.push({
                id: `invoice-${inv.id}`,
                kind: 'danger',
                title: 'Tagihan terlambat',
                message: `${inv.invoice_number} · ${formatPrice(inv.amount)} · jatuh tempo ${formatDate(inv.due_date)}`,
                href: getCustomerUrl(() => customerRoutes?.invoices?.payment?.(inv.id).url, `/customer/invoices/${inv.id}/payment`),
            });
        }
    }

    for (const s of props.expiringSoon) {
        list.push({
            id: `service-${s.id}`,
            kind: s.is_expired ? 'danger' : 'warning',
            title: s.is_expired ? 'Layanan kadaluarsa' : 'Layanan hampir habis',
            message: `${s.name} · ${s.is_expired ? 'sudah berakhir' : `sisa ${s.days_left} hari (${formatDate(s.expires_at)})`}`,
            href: getCustomerUrl(() => customerRoutes?.services?.index?.().url, '/customer/services'),
        });
    }

    if (props.aiBalance <= 0) {
        list.push({
            id: 'ai-low',
            kind: 'info',
            title: 'Saldo token AI habis',
            message: 'Beli paket kredit agar tetap bisa memakai token AI.',
            href: getCustomerUrl(() => customerRoutes?.ai?.packages?.().url, '/customer/ai/packages'),
        });
    } else if (props.aiBalance < 100) {
        list.push({
            id: 'ai-low',
            kind: 'warning',
            title: 'Saldo token AI menipis',
            message: `Sisa ${props.aiBalance.toLocaleString('id-ID')} kredit.`,
            href: getCustomerUrl(() => customerRoutes?.ai?.packages?.().url, '/customer/ai/packages'),
        });
    }

    return list.slice(0, 5);
});

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

const displayStatus = computed(() => props.customer?.status || 'active');
const statusLabel = computed(() => {
    const labels: Record<string, string> = { active: 'Aktif', pending: 'Pending', suspended: 'Ditangguhkan', terminated: 'Dihentikan', inactive: 'Tidak Aktif' };
    return labels[displayStatus.value] || displayStatus.value;
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
    <Head title="Dashboard Pelanggan" />

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
                                class="inline-flex h-10 items-center justify-between rounded-2xl px-3.5 py-1.5 text-sm font-bold transition-colors hover:bg-[#c8c8c1] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--primary)]/50"
                                style="background-color: #f6f6f3; color: #000000;"
                            >
                                <span class="inline-flex items-center gap-2">
                                    <Layers class="h-4 w-4" style="color: var(--primary)" />
                                    Layanan
                                </span>
                                <ArrowRight class="h-4 w-4 opacity-70" />
                            </Link>
                            <Link
                                :href="getCustomerUrl(() => customerRoutes?.orders?.index?.().url, '/customer/orders')"
                                class="inline-flex h-10 items-center justify-between rounded-2xl px-3.5 py-1.5 text-sm font-bold transition-colors hover:bg-[#c8c8c1] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--primary)]/50"
                                style="background-color: #f6f6f3; color: #000000;"
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
                        <Link
                            :href="getCustomerUrl(() => customerRoutes?.ai?.packages?.().url, '/customer/ai/packages')"
                            class="inline-flex h-10 items-center justify-between rounded-2xl px-3.5 py-1.5 text-sm font-bold transition-colors"
                            style="background-color: #f6f6f3; color: #000000;"
                            @mouseenter="$event.target.style.backgroundColor = '#c8c8c1'"
                            @mouseleave="$event.target.style.backgroundColor = '#f6f6f3'"
                        >
                            <span class="inline-flex items-center gap-2">
                                <Bot class="h-4 w-4" style="color: var(--primary)" />
                                Token AI
                            </span>
                            <ArrowRight class="h-4 w-4 opacity-70" />
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Stat cards -->
            <div class="grid gap-4 sm:grid-cols-2 sm:gap-6 lg:grid-cols-4">
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
                    <p v-if="unpaidInvoiceCount > 0" class="mt-0.5" style="color: #91918c; font-size: 14px;">
                        {{ formatPrice(unpaidTotal) }}
                        <span v-if="unpaidOverdueCount > 0" class="font-bold" style="color: #dc2626;"> · {{ unpaidOverdueCount }} terlambat</span>
                    </p>
                    <p v-else class="mt-0.5" style="color: #91918c; font-size: 14px;">Semua tagihan lunas</p>
                </div>

                <div class="rounded-2xl bg-[#f6f6f3] p-5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wide" style="color: #62625b;">Saldo Token AI</span>
                        <Bot class="h-4 w-4" style="color: var(--primary)" />
                    </div>
                    <Link
                        :href="getCustomerUrl(() => customerRoutes?.ai?.index?.().url, '/customer/ai')"
                        class="font-heading mt-2 block text-3xl font-bold transition-opacity hover:opacity-70"
                        style="letter-spacing: -1.2px;"
                    >
                        {{ aiBalance.toLocaleString('id-ID') }}
                    </Link>
                    <p class="mt-0.5" style="color: #91918c; font-size: 14px;">
                        <Link :href="getCustomerUrl(() => customerRoutes?.ai?.packages?.().url, '/customer/ai/packages')" class="font-bold hover:underline" style="color: var(--primary);">
                            Beli kredit
                        </Link>
                    </p>
                </div>

                <div class="rounded-2xl bg-[#f6f6f3] p-5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wide" style="color: #62625b;">Status Akun</span>
                        <Sparkles class="h-4 w-4" style="color: var(--primary)" />
                    </div>
                    <div class="font-heading mt-2 text-3xl font-bold capitalize" style="letter-spacing: -1.2px;">{{ statusLabel }}</div>
                    <span :class="`mt-1.5 inline-flex items-center rounded-full px-3 py-1 text-xs font-bold ${getStatusColor(displayStatus)}`">
                        {{ statusLabel }}
                    </span>
                </div>
            </div>

            <!-- Ringkasan pengeluaran -->
            <div class="rounded-2xl bg-white p-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="font-heading text-lg font-bold" style="letter-spacing: -1px;">Ringkasan Pengeluaran</h2>
                    <div class="flex flex-wrap gap-2 sm:gap-3">
                        <div class="rounded-2xl px-4 py-2" style="background-color: #f6f6f3;">
                            <div class="text-xs font-bold uppercase tracking-wide" style="color: #62625b;">Bulan Ini</div>
                            <div class="mt-0.5 text-lg font-bold tabular-nums" style="color: #103c25;">{{ formatPrice(expenseMonth) }}</div>
                        </div>
                        <div class="rounded-2xl px-4 py-2" style="background-color: #f6f6f3;">
                            <div class="text-xs font-bold uppercase tracking-wide" style="color: #62625b;">Total Menunggak</div>
                            <div class="mt-0.5 text-lg font-bold tabular-nums" :style="{ color: outstandingTotal > 0 ? '#dc2626' : '#103c25' }">{{ formatPrice(outstandingTotal) }}</div>
                        </div>
                    </div>
                </div>
                <div class="mt-1 text-sm" style="color: #91918c;">Total tagihan lunas pada bulan berjalan dan total yang belum dibayar.</div>
                <div class="mt-4 flex flex-wrap gap-2">
                    <Link
                        :href="getCustomerUrl(() => customerRoutes?.invoices?.index?.().url, '/customer/invoices')"
                        class="inline-flex h-9 items-center justify-center rounded-2xl px-3.5 text-sm font-bold transition-opacity hover:opacity-90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--primary)]/50"
                        style="background-color: var(--primary); color: #ffffff;"
                    >
                        Lihat Tagihan
                        <ArrowRight class="ml-1.5 h-4 w-4" />
                    </Link>
                    <Link
                        v-if="primaryInvoice"
                        :href="getCustomerUrl(() => customerRoutes?.invoices?.payment?.(primaryInvoice.id).url, `/customer/invoices/${primaryInvoice.id}/payment`)"
                        class="inline-flex h-9 items-center justify-center rounded-2xl px-3.5 text-sm font-bold transition-opacity hover:opacity-90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--primary)]/50"
                        style="background-color: #f6f6f3; color: #000000;"
                    >
                        <CreditCard class="mr-1.5 h-4 w-4" style="color: var(--primary)" />
                        Bayar Menunggak
                    </Link>
                </div>
            </div>

            <!-- CTA renew layanan hampir habis / kadaluarsa -->
            <div v-if="expiringSoon.length > 0" class="rounded-2xl border px-5 py-4" style="border-color: #fcd34d; background-color: #fffbeb;">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <AlertTriangle class="h-5 w-5 shrink-0 text-amber-600" />
                        <div>
                            <div class="text-sm font-bold text-amber-900">Layanan perlu perhatian</div>
                            <div class="text-sm text-amber-800">
                                {{ expiringSoon.length }} layanan {{ expiringSoon.some((s) => s.is_expired) ? 'kedaluwarsa / ' : '' }}akan berakhir dalam 30 hari.
                            </div>
                        </div>
                    </div>
                    <Link
                        :href="getCustomerUrl(() => customerRoutes?.services?.index?.().url, '/customer/services')"
                        class="inline-flex h-9 items-center justify-center rounded-2xl px-3.5 text-sm font-bold transition-opacity hover:opacity-90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-500/50"
                        style="background-color: #f59e0b; color: #ffffff;"
                    >
                        Perbarui Layanan
                        <ArrowRight class="ml-1.5 h-4 w-4" />
                    </Link>
                </div>
                <div class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                    <div v-for="s in expiringSoon" :key="s.id" class="flex items-center justify-between gap-2 rounded-2xl bg-white px-3 py-2 text-sm" style="border: 1px solid #fde68a;">
                        <span class="truncate font-bold" style="color: #92400e;">{{ s.name }}</span>
                        <span class="shrink-0 rounded-full px-2 py-0.5 text-xs font-bold" :style="s.is_expired ? 'background-color: #fecaca; color: #991b1b;' : 'background-color: #fef3c7; color: #92400e;'">
                            {{ s.is_expired ? 'Kadaluarsa' : `Sisa ${s.days_left} hari` }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Alerts / notifikasi ringkas -->
            <div v-if="alerts.length > 0" class="space-y-2">
                <template v-for="alert in alerts" :key="alert.id">
                    <Link
                        v-if="alert.href"
                        :href="alert.href"
                        class="flex items-start gap-3 rounded-2xl border px-4 py-3 text-sm transition-opacity hover:opacity-90"
                        :class="alert.kind === 'danger' ? 'border-red-200 bg-red-50' : alert.kind === 'warning' ? 'border-amber-200 bg-amber-50' : 'border-sky-200 bg-sky-50'"
                    >
                        <AlertTriangle
                            class="mt-0.5 h-4 w-4 shrink-0"
                            :class="alert.kind === 'danger' ? 'text-red-600' : alert.kind === 'warning' ? 'text-amber-600' : 'text-sky-600'"
                        />
                        <div class="min-w-0">
                            <div class="font-bold" :class="alert.kind === 'danger' ? 'text-red-800' : alert.kind === 'warning' ? 'text-amber-800' : 'text-sky-800'">{{ alert.title }}</div>
                            <div class="mt-0.5" :class="alert.kind === 'danger' ? 'text-red-700' : alert.kind === 'warning' ? 'text-amber-700' : 'text-sky-700'">{{ alert.message }}</div>
                        </div>
                        <ArrowRight class="ml-auto h-4 w-4 shrink-0 opacity-60" />
                    </Link>
                    <div
                        v-else
                        class="flex items-start gap-3 rounded-2xl border px-4 py-3 text-sm"
                        :class="alert.kind === 'danger' ? 'border-red-200 bg-red-50' : alert.kind === 'warning' ? 'border-amber-200 bg-amber-50' : 'border-sky-200 bg-sky-50'"
                    >
                        <AlertTriangle
                            class="mt-0.5 h-4 w-4 shrink-0"
                            :class="alert.kind === 'danger' ? 'text-red-600' : alert.kind === 'warning' ? 'text-amber-600' : 'text-sky-600'"
                        />
                        <div class="min-w-0">
                            <div class="font-bold" :class="alert.kind === 'danger' ? 'text-red-800' : alert.kind === 'warning' ? 'text-amber-800' : 'text-sky-800'">{{ alert.title }}</div>
                            <div class="mt-0.5" :class="alert.kind === 'danger' ? 'text-red-700' : alert.kind === 'warning' ? 'text-amber-700' : 'text-sky-700'">{{ alert.message }}</div>
                        </div>
                    </div>
                </template>
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
                                <div class="mt-0.5 text-sm" :style="{ color: dueLabel(invoice).overdue ? '#dc2626' : '#91918c', fontWeight: dueLabel(invoice).overdue ? 700 : 400 }">
                                    Jatuh tempo {{ formatDate(invoice.due_date) }} · {{ dueLabel(invoice).text }}
                                </div>
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

            <!-- Pesanan terbaru -->
            <div class="rounded-2xl border p-5" style="background-color: #ffffff;">
                <div class="flex items-center justify-between">
                    <h2 class="font-heading text-xl font-bold" style="letter-spacing: -1.2px;">Pesanan Terbaru</h2>
                    <Link
                        :href="getCustomerUrl(() => customerRoutes?.orders?.index?.().url, '/customer/orders')"
                        class="inline-flex items-center gap-1 text-sm font-bold hover:underline"
                        style="color: var(--primary);"
                    >
                        Lihat Semua
                        <ArrowRight class="h-4 w-4" />
                    </Link>
                </div>

                <div v-if="recentOrders.length > 0" class="mt-4 space-y-1">
                    <div v-for="order in recentOrders" :key="order.id" class="flex items-center justify-between gap-3 rounded-2xl px-4 py-3" style="background-color: #f6f6f3;">
                        <div class="min-w-0">
                            <Link
                                :href="getCustomerUrl(() => customerRoutes?.orders?.show?.(order.id).url, `/customer/orders/${order.id}`)"
                                class="text-sm font-bold hover:underline"
                            >
                                Pesanan #{{ order.id }}
                            </Link>
                            <div class="mt-0.5 truncate text-sm" style="color: #91918c;">
                                {{ order.order_items?.length || 0 }} item · {{ formatDate(order.created_at) }}
                            </div>
                        </div>
                        <div class="flex shrink-0 items-center gap-2.5">
                            <span :class="`inline-flex rounded-full px-3 py-1 text-xs font-bold ${getStatusColor(order.status)}`">
                                {{ orderStatusLabel(order.status) }}
                            </span>
                            <div class="text-base font-bold" style="color: var(--primary);">{{ formatPrice(order.total_amount) }}</div>
                        </div>
                    </div>
                </div>
                <div v-else class="mt-4 rounded-2xl px-4 py-6 text-center text-sm" style="background-color: #f6f6f3; color: #91918c;">
                    Belum ada pesanan.
                    <Link :href="getCustomerUrl(() => customerRoutes?.services?.index?.().url, '/customer/services')" class="font-bold hover:underline" style="color: var(--primary);">
                        Mulai berlangganan
                    </Link>
                </div>
            </div>

            <!-- Aktivitas maintenance terbaru -->
            <div class="rounded-2xl border p-5" style="background-color: #ffffff;">
                <div class="flex items-center justify-between">
                    <h2 class="font-heading text-xl font-bold" style="letter-spacing: -1.2px;">Aktivitas Maintenance</h2>
                    <Link
                        :href="getCustomerUrl(() => customerRoutes?.maintenance?.index?.().url, '/customer/maintenance')"
                        class="inline-flex items-center gap-1 text-sm font-bold hover:underline"
                        style="color: var(--primary);"
                    >
                        Lihat Semua
                        <ArrowRight class="h-4 w-4" />
                    </Link>
                </div>

                <div v-if="recentJournals.length > 0" class="mt-4 space-y-1">
                    <div v-for="journal in recentJournals" :key="journal.id" class="rounded-2xl px-4 py-3" style="background-color: #f6f6f3;">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex min-w-0 items-center gap-2">
                                <Wrench class="h-4 w-4 shrink-0" style="color: var(--primary)" />
                                <span class="truncate text-sm font-bold">{{ journal.website_name || 'Website' }}</span>
                            </div>
                            <span class="shrink-0 text-xs font-medium" style="color: #91918c;">{{ journal.entry_date ? formatDate(journal.entry_date) : '' }}</span>
                        </div>
                        <p v-if="journal.summary" class="mt-1 line-clamp-2 text-sm" style="color: #62625b;">{{ journal.summary }}</p>
                        <p v-else class="mt-1 text-sm" style="color: #91918c;">{{ journal.activity_count }} aktivitas dicatat</p>
                    </div>
                </div>
                <div v-else class="mt-4 rounded-2xl px-4 py-6 text-center text-sm" style="background-color: #f6f6f3; color: #91918c;">
                    Belum ada jurnal maintenance untuk website Anda.
                </div>
            </div>
        </div>
    </CustomerLayout>
</template>
