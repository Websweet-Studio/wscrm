<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import CustomerLayout from '@/layouts/CustomerLayout.vue';
import customer from '@/routes/customer';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';

import { Separator } from '@/components/ui/separator';
import { cn, formatDate, formatPrice } from '@/lib/utils';
import { AlertCircle, ArrowRight, Building2, CheckCircle, Copy, CreditCard, FileText, ShieldCheck, Wallet } from 'lucide-vue-next';
import { computed, ref } from 'vue';

type PaymentAccount = {
    id: number;
    type: 'bank' | 'ewallet' | 'qris';
    name: string;
    account_number: string | null;
    account_name: string | null;
    qris_provider: string | null;
    qris_image_path: string | null;
};

const props = defineProps<{
    invoice: any;
    paymentAccounts: PaymentAccount[];
}>();

const form = useForm({
    payment_account_id: props.invoice.payment_account_id || '',
});

const confirmForm = useForm({
    payment_proof: '',
});

const showConfirmation = ref(false);

const customerRoutes = customer as any;
const getCustomerUrl = (getter: () => string | undefined, fallback: string) => {
    try {
        return getter() || fallback;
    } catch {
        return fallback;
    }
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Customer Dashboard', href: getCustomerUrl(() => customerRoutes?.dashboard?.().url, '/customer/dashboard') },
    { title: 'Tagihan', href: getCustomerUrl(() => customerRoutes?.invoices?.index?.().url, '/customer/invoices') },
    { title: props.invoice.invoice_number || 'Pembayaran', href: getCustomerUrl(() => customerRoutes?.invoices?.payment?.(props.invoice.id).url, `/customer/invoices/${props.invoice.id}/payment`) },
];

const submitPayment = () => {
    form.post(`/customer/invoices/${props.invoice.id}/process-payment`, {
        onSuccess: () => {
            showConfirmation.value = true;
        },
    });
};

const confirmPayment = () => {
    confirmForm.post(`/customer/invoices/${props.invoice.id}/confirm-payment`);
};

const getStatusClass = (status) => {
    const classes = {
        draft: 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300',
        sent: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        paid: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        overdue: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
        cancelled: 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300',
    };
    return classes[status] || classes.draft;
};

const getStatusText = (status) => {
    const texts = {
        draft: 'Draft',
        sent: 'Terkirim',
        paid: 'Dibayar',
        overdue: 'Terlambat',
        cancelled: 'Dibatalkan',
    };
    return texts[status] || status;
};

const isOverdue = computed(() => {
    if (props.invoice.status === 'paid') return false;
    return new Date(props.invoice.due_date) < new Date();
});

const totalWithFee = computed(() => {
    const baseAmount = parseFloat(props.invoice.amount);
    return baseAmount;
});

const copyValue = async (value: string) => {
    try {
        await navigator.clipboard.writeText(value);
    } catch {
        //
    }
};

const selectedPaymentAccount = computed(() => {
    const id = Number(form.payment_account_id);
    if (!Number.isFinite(id) || id <= 0) return null;
    return props.paymentAccounts.find((p) => p.id === id) || null;
});

const groupedPaymentAccounts = computed(() => {
    const groups: Record<string, PaymentAccount[]> = { bank: [], ewallet: [], qris: [] };
    (props.paymentAccounts || []).forEach((p) => {
        if (p.type === 'bank') groups.bank.push(p);
        else if (p.type === 'ewallet') groups.ewallet.push(p);
        else if (p.type === 'qris') groups.qris.push(p);
    });
    return groups as { bank: PaymentAccount[]; ewallet: PaymentAccount[]; qris: PaymentAccount[] };
});

const paymentTypeLabel = (type: PaymentAccount['type']) => {
    if (type === 'bank') return 'Bank';
    if (type === 'ewallet') return 'E-Wallet';
    return 'QRIS';
};

const getTypeIcon = (type: PaymentAccount['type']) => {
    if (type === 'bank') return Building2;
    if (type === 'ewallet') return Wallet;
    return CreditCard;
};

if (props.invoice.payment_account_id) {
    showConfirmation.value = true;
}
</script>

<template>
    <Head :title="`Pembayaran Invoice ${invoice.invoice_number}`" />

    <CustomerLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full max-w-5xl space-y-4 p-4 sm:space-y-6 sm:p-6">
            <Card class="relative overflow-hidden border-border/60 bg-card/70 shadow-sm backdrop-blur">
                <div class="pointer-events-none absolute inset-0 opacity-60 dark:opacity-80">
                    <div class="absolute -inset-24 bg-[radial-gradient(closest-side,rgba(16,185,129,0.18),transparent_65%)]"></div>
                    <div class="absolute -right-24 -top-32 h-96 w-96 bg-[radial-gradient(closest-side,rgba(34,211,238,0.16),transparent_60%)]"></div>
                    <div class="absolute inset-0 bg-[linear-gradient(to_right,transparent_0,rgba(16,185,129,0.05)_50%,transparent_100%)]"></div>
                </div>
                <CardContent class="relative p-4 sm:p-6">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <div class="mb-2 inline-flex items-center gap-2 rounded-full border border-border/60 bg-background/60 px-2.5 py-1 text-xs text-muted-foreground">
                                <ShieldCheck class="h-3.5 w-3.5 text-emerald-600 dark:text-green-400" />
                                <span>Pembayaran</span>
                            </div>
                            <h1 class="font-serif text-2xl font-medium leading-tight tracking-tight sm:text-3xl">
                                Invoice <span class="font-mono">{{ invoice.invoice_number }}</span>
                            </h1>
                            <div class="mt-2 flex flex-wrap items-center gap-2">
                                <Badge :class="getStatusClass(invoice.status)">{{ getStatusText(invoice.status) }}</Badge>
                                <span class="text-sm text-muted-foreground">
                                    Jatuh tempo
                                    <span :class="cn('font-medium', isOverdue ? 'text-red-600 dark:text-red-400' : '')">{{ formatDate(invoice.due_date) }}</span>
                                </span>
                            </div>
                        </div>

                        <div class="flex w-full flex-col gap-2 sm:w-auto sm:min-w-[280px]">
                            <Button
                                :as="Link"
                                :href="getCustomerUrl(() => customerRoutes?.invoices?.show?.(invoice.id).url, `/customer/invoices/${invoice.id}`)"
                                variant="outline"
                                size="sm"
                                class="w-full justify-between"
                            >
                                <span class="inline-flex items-center gap-2">
                                    <FileText class="h-4 w-4 text-emerald-600 dark:text-green-400" />
                                    Lihat Invoice
                                </span>
                                <ArrowRight class="h-4 w-4 opacity-70" />
                            </Button>
                            <div class="rounded-lg border border-border/60 bg-background/60 p-3">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-muted-foreground">Subtotal</span>
                                    <span class="font-medium">{{ formatPrice(invoice.amount) }}</span>
                                </div>
                                <Separator class="my-2" />
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-muted-foreground">Total transfer</span>
                                    <span class="font-serif text-xl font-medium text-emerald-700 dark:text-green-400">{{ formatPrice(totalWithFee) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <div class="grid gap-4 md:grid-cols-5 md:gap-6">
                <div class="space-y-4 md:col-span-3">
                    <Card v-if="!showConfirmation" class="rounded-lg border-border/60 shadow-sm">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <CreditCard class="h-5 w-5 text-emerald-600 dark:text-green-400" />
                                Pilih Metode Pembayaran
                            </CardTitle>
                            <CardDescription>Pilih metode pembayaran untuk mendapatkan instruksi</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-6">
                            <form @submit.prevent="submitPayment" class="space-y-6">
                                <div class="space-y-3">
                                    <Label>Metode Pembayaran</Label>
                                    <div class="space-y-5">
                                        <div v-for="(items, type) in groupedPaymentAccounts" :key="type" v-if="items.length > 0" class="space-y-3">
                                            <div class="flex items-center gap-2 text-sm font-medium">
                                                <component :is="getTypeIcon(type as any)" class="h-4 w-4 text-emerald-600 dark:text-green-400" />
                                                <span>{{ paymentTypeLabel(type as any) }}</span>
                                            </div>
                                            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                                                <label
                                                    v-for="item in items"
                                                    :key="item.id"
                                                    class="flex cursor-pointer items-start gap-3 rounded-lg border border-border/60 bg-background/40 p-3 transition-colors hover:bg-muted/40"
                                                >
                                                    <input
                                                        type="radio"
                                                        :value="item.id.toString()"
                                                        v-model="form.payment_account_id"
                                                        class="mt-1 h-4 w-4 border-gray-300 text-primary focus:ring-primary"
                                                    />
                                                    <span class="min-w-0 flex-1 space-y-1">
                                                        <span class="block font-medium">{{ item.name }}</span>
                                                        <template v-if="item.type === 'bank'">
                                                            <span class="block text-xs text-muted-foreground">No. Rek: {{ item.account_number || '-' }}</span>
                                                            <span class="block text-xs text-muted-foreground">a.n. {{ item.account_name || '-' }}</span>
                                                        </template>
                                                        <template v-else-if="item.type === 'ewallet'">
                                                            <span class="block text-xs text-muted-foreground">Nomor: {{ item.account_number || '-' }}</span>
                                                        </template>
                                                        <template v-else-if="item.type === 'qris'">
                                                            <span class="block text-xs text-muted-foreground">Scan QR untuk bayar</span>
                                                            <img
                                                                v-if="item.qris_image_path"
                                                                :src="item.qris_image_path"
                                                                alt="QRIS"
                                                                class="mt-2 h-28 w-28 rounded-md border border-border/60 object-contain"
                                                            />
                                                        </template>
                                                    </span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-if="form.errors.payment_account_id" class="text-sm text-red-600">
                                        {{ form.errors.payment_account_id }}
                                    </div>
                                </div>

                                <Button type="submit" :disabled="form.processing" class="w-full justify-between">
                                    <span class="inline-flex items-center gap-2">
                                        <CreditCard class="h-4 w-4" />
                                        {{ form.processing ? 'Memproses...' : 'Lanjutkan' }}
                                    </span>
                                    <ArrowRight class="h-4 w-4 opacity-80" />
                                </Button>
                            </form>
                        </CardContent>
                    </Card>

                    <Card v-else class="rounded-lg border-border/60 shadow-sm">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Building2 class="h-5 w-5 text-emerald-600 dark:text-green-400" />
                                Instruksi Pembayaran
                            </CardTitle>
                            <CardDescription>Ikuti instruksi berikut, lalu konfirmasi</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-6">
                            <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-emerald-900 dark:border-emerald-900 dark:bg-emerald-900/20 dark:text-emerald-100">
                                <div class="flex items-start gap-3">
                                    <AlertCircle class="mt-0.5 h-5 w-5" />
                                    <div class="min-w-0">
                                        <div class="text-sm font-medium">Transfer sejumlah</div>
                                        <div class="mt-1 flex flex-wrap items-center gap-2">
                                            <div class="font-serif text-2xl font-medium">{{ formatPrice(totalWithFee) }}</div>
                                            <button
                                                type="button"
                                                class="inline-flex items-center gap-1 rounded-full border border-border/60 bg-background/60 px-2.5 py-1 text-xs text-muted-foreground hover:text-foreground"
                                                @click="copyValue(String(totalWithFee))"
                                            >
                                                <Copy class="h-3.5 w-3.5" />
                                                Copy
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-if="selectedPaymentAccount" class="grid gap-3 sm:grid-cols-2">
                                <div class="rounded-lg border border-border/60 bg-background/40 p-3">
                                    <div class="text-xs text-muted-foreground">Metode</div>
                                    <div class="mt-0.5 flex items-center gap-2 font-medium">
                                        <component :is="getTypeIcon(selectedPaymentAccount.type)" class="h-4 w-4 text-emerald-600 dark:text-green-400" />
                                        <span>{{ paymentTypeLabel(selectedPaymentAccount.type) }}</span>
                                    </div>
                                </div>
                                <div class="rounded-lg border border-border/60 bg-background/40 p-3">
                                    <div class="text-xs text-muted-foreground">Nama</div>
                                    <div class="mt-0.5 font-medium">{{ selectedPaymentAccount.name }}</div>
                                </div>

                                <div v-if="selectedPaymentAccount.type === 'bank'" class="rounded-lg border border-border/60 bg-background/40 p-3 sm:col-span-2">
                                    <div class="flex items-center justify-between">
                                        <div class="text-xs text-muted-foreground">No. Rekening</div>
                                        <button
                                            type="button"
                                            class="inline-flex items-center gap-1 text-xs text-muted-foreground hover:text-foreground"
                                            @click="copyValue(String(selectedPaymentAccount.account_number || ''))"
                                        >
                                            <Copy class="h-3.5 w-3.5" />
                                            Copy
                                        </button>
                                    </div>
                                    <div class="mt-0.5 font-mono text-xl font-semibold text-emerald-700 dark:text-green-400">{{ selectedPaymentAccount.account_number || '-' }}</div>
                                    <div class="mt-1 text-xs text-muted-foreground">a.n. {{ selectedPaymentAccount.account_name || '-' }}</div>
                                </div>

                                <div v-else-if="selectedPaymentAccount.type === 'ewallet'" class="rounded-lg border border-border/60 bg-background/40 p-3 sm:col-span-2">
                                    <div class="flex items-center justify-between">
                                        <div class="text-xs text-muted-foreground">Nomor E-Wallet</div>
                                        <button
                                            type="button"
                                            class="inline-flex items-center gap-1 text-xs text-muted-foreground hover:text-foreground"
                                            @click="copyValue(String(selectedPaymentAccount.account_number || ''))"
                                        >
                                            <Copy class="h-3.5 w-3.5" />
                                            Copy
                                        </button>
                                    </div>
                                    <div class="mt-0.5 font-mono text-xl font-semibold text-emerald-700 dark:text-green-400">{{ selectedPaymentAccount.account_number || '-' }}</div>
                                </div>

                                <div v-else-if="selectedPaymentAccount.type === 'qris'" class="rounded-lg border border-border/60 bg-background/40 p-3 sm:col-span-2">
                                    <div class="text-xs text-muted-foreground">QRIS</div>
                                    <div class="mt-2 flex flex-col items-start gap-3 sm:flex-row sm:items-center">
                                        <img
                                            v-if="selectedPaymentAccount.qris_image_path"
                                            :src="selectedPaymentAccount.qris_image_path"
                                            alt="QRIS"
                                            class="h-44 w-44 rounded-md border border-border/60 object-contain"
                                        />
                                        <div class="text-sm text-muted-foreground">
                                            Scan QR untuk melakukan pembayaran.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <Separator />

                            <form @submit.prevent="confirmPayment" class="space-y-4">
                                <div class="space-y-2">
                                    <Label for="payment_proof">Catatan Pembayaran (Opsional)</Label>
                                    <textarea
                                        id="payment_proof"
                                        v-model="confirmForm.payment_proof"
                                        placeholder="Masukkan nomor referensi transfer atau catatan lainnya..."
                                        rows="3"
                                        class="flex min-h-[88px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                                    />
                                    <p class="text-xs text-muted-foreground">Contoh: nomor referensi, jam transfer, atau catatan lain untuk admin.</p>
                                </div>

                                <div class="flex flex-col gap-2 sm:flex-row">
                                    <Button type="submit" :disabled="confirmForm.processing" class="w-full justify-between sm:flex-1">
                                        <span class="inline-flex items-center gap-2">
                                            <CheckCircle class="h-4 w-4" />
                                            {{ confirmForm.processing ? 'Memproses...' : 'Konfirmasi Pembayaran' }}
                                        </span>
                                        <ArrowRight class="h-4 w-4 opacity-80" />
                                    </Button>
                                    <Button type="button" variant="outline" class="w-full sm:w-auto" @click="showConfirmation = false">
                                        Ubah Metode
                                    </Button>
                                </div>
                            </form>
                        </CardContent>
                    </Card>
                </div>

                <div class="space-y-4 md:col-span-2">
                    <Card class="rounded-lg border-border/60 shadow-sm">
                        <CardHeader class="pb-3">
                            <CardTitle class="text-base">Ringkasan</CardTitle>
                            <CardDescription>Detail pembayaran invoice</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-muted-foreground">Invoice</span>
                                <span class="font-mono font-medium">{{ invoice.invoice_number }}</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-muted-foreground">Status</span>
                                <Badge :class="getStatusClass(invoice.status)">{{ getStatusText(invoice.status) }}</Badge>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-muted-foreground">Jatuh tempo</span>
                                <span :class="cn('font-medium', isOverdue ? 'text-red-600 dark:text-red-400' : '')">{{ formatDate(invoice.due_date) }}</span>
                            </div>
                            <Separator />
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-muted-foreground">Subtotal</span>
                                <span class="font-medium">{{ formatPrice(invoice.amount) }}</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-muted-foreground">Metode</span>
                                <span class="font-medium">{{ selectedPaymentAccount ? paymentTypeLabel(selectedPaymentAccount.type) : '-' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">Total</span>
                                <span class="font-serif text-xl font-medium text-emerald-700 dark:text-green-400">{{ formatPrice(totalWithFee) }}</span>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </CustomerLayout>
</template>
