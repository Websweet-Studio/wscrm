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

type PaymentMethod = {
    key: string;
    label: string;
    description?: string;
};

const props = defineProps<{
    invoice: any;
    banks: any[];
    paymentMethods: PaymentMethod[];
}>();

const availablePaymentMethods = computed(() => {
    const methods = Array.isArray(props.paymentMethods) ? props.paymentMethods : [];
    if (methods.length > 0) return methods;
    return [{ key: 'bank_transfer', label: 'Transfer Bank', description: 'ATM/Internet Banking' }];
});

const form = useForm({
    bank_id: props.invoice.bank_id || '',
    payment_method: props.invoice.payment_method || availablePaymentMethods.value[0]?.key || 'bank_transfer',
});

const confirmForm = useForm({
    payment_proof: '',
});

const selectedBank = ref<any | null>(null);
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

const updateSelectedBank = () => {
    if (form.bank_id) {
        selectedBank.value = props.banks.find((bank) => bank.id == form.bank_id);
    } else {
        selectedBank.value = null;
    }
};

const submitPayment = () => {
    form.post(`/customer/invoices/${props.invoice.id}/process-payment`, {
        onSuccess: () => {
            showConfirmation.value = true;
            updateSelectedBank();
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
    const adminFee = selectedBank.value ? parseFloat(selectedBank.value.admin_fee || 0) : 0;
    return baseAmount + adminFee;
});

const copyValue = async (value: string) => {
    try {
        await navigator.clipboard.writeText(value);
    } catch {
        //
    }
};

const getMethodIcon = (key: string) => {
    if (key === 'bank_transfer') return Building2;
    if (key === 'e_wallet') return Wallet;
    return CreditCard;
};

// Initialize selected bank if already set
if (props.invoice.bank_id) {
    selectedBank.value = props.banks.find((bank) => bank.id == props.invoice.bank_id);
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
                                <div v-if="selectedBank && Number(selectedBank.admin_fee) > 0" class="mt-1 flex items-center justify-between text-sm">
                                    <span class="text-muted-foreground">Biaya admin</span>
                                    <span class="font-medium">{{ formatPrice(selectedBank.admin_fee) }}</span>
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
                            <CardDescription>Pilih metode, pilih bank, lalu lanjutkan ke instruksi transfer</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-6">
                            <form @submit.prevent="submitPayment" class="space-y-6">
                                <div class="space-y-3">
                                    <Label>Metode Pembayaran</Label>
                                    <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                                        <label
                                            v-for="method in availablePaymentMethods"
                                            :key="method.key"
                                            class="flex cursor-pointer items-start gap-3 rounded-lg border border-border/60 bg-background/40 p-3 transition-colors hover:bg-muted/40"
                                        >
                                            <input
                                                type="radio"
                                                :value="method.key"
                                                v-model="form.payment_method"
                                                class="mt-1 h-4 w-4 border-gray-300 text-primary focus:ring-primary"
                                            />
                                            <span class="min-w-0">
                                                <span class="flex items-center gap-2 font-medium">
                                                    <component :is="getMethodIcon(method.key)" class="h-4 w-4 text-emerald-600 dark:text-green-400" />
                                                    <span class="truncate">{{ method.label || method.key }}</span>
                                                </span>
                                                <span v-if="method.description" class="block text-xs text-muted-foreground">{{ method.description }}</span>
                                            </span>
                                        </label>
                                    </div>
                                    <div v-if="form.errors.payment_method" class="text-sm text-red-600">
                                        {{ form.errors.payment_method }}
                                    </div>
                                </div>

                                <div class="space-y-3">
                                    <Label for="bank_id">Pilih Bank</Label>
                                    <select
                                        v-model="form.bank_id"
                                        @change="updateSelectedBank"
                                        id="bank_id"
                                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm text-foreground ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        <option value="" disabled>Pilih bank untuk pembayaran</option>
                                        <option v-for="bank in banks" :key="bank.id" :value="bank.id.toString()">
                                            {{ bank.bank_name }} ({{ bank.bank_code }}){{ bank.admin_fee > 0 ? ' - Fee: ' + formatPrice(bank.admin_fee) : '' }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.bank_id" class="text-sm text-red-600">
                                        {{ form.errors.bank_id }}
                                    </div>
                                </div>

                                <Card v-if="selectedBank" class="border-border/60 bg-muted/20">
                                    <CardHeader class="pb-3">
                                        <CardTitle class="text-base">Detail Bank</CardTitle>
                                        <CardDescription>Gunakan detail ini saat transfer</CardDescription>
                                    </CardHeader>
                                    <CardContent class="space-y-3">
                                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                            <div class="rounded-md border border-border/60 bg-background/50 p-3">
                                                <div class="text-xs text-muted-foreground">Bank</div>
                                                <div class="mt-0.5 font-medium">{{ selectedBank.bank_name }}</div>
                                            </div>
                                            <div class="rounded-md border border-border/60 bg-background/50 p-3">
                                                <div class="text-xs text-muted-foreground">Kode Bank</div>
                                                <div class="mt-0.5 font-mono font-medium">{{ selectedBank.bank_code }}</div>
                                            </div>
                                            <div class="rounded-md border border-border/60 bg-background/50 p-3 sm:col-span-2">
                                                <div class="flex items-center justify-between">
                                                    <div class="text-xs text-muted-foreground">No. Rekening</div>
                                                    <button
                                                        type="button"
                                                        class="inline-flex items-center gap-1 text-xs text-muted-foreground hover:text-foreground"
                                                        @click="copyValue(String(selectedBank.account_number || ''))"
                                                    >
                                                        <Copy class="h-3.5 w-3.5" />
                                                        Copy
                                                    </button>
                                                </div>
                                                <div class="mt-0.5 font-mono text-lg font-semibold text-emerald-700 dark:text-green-400">
                                                    {{ selectedBank.account_number }}
                                                </div>
                                                <div class="mt-1 text-xs text-muted-foreground">a.n. {{ selectedBank.account_name }}</div>
                                            </div>
                                        </div>
                                        <div
                                            v-if="Number(selectedBank.admin_fee) > 0"
                                            class="rounded-lg border border-yellow-200 bg-yellow-50 p-3 text-sm text-yellow-900 dark:border-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-100"
                                        >
                                            <div class="flex items-start gap-2">
                                                <AlertCircle class="mt-0.5 h-4 w-4" />
                                                <div class="min-w-0">
                                                    Biaya admin <span class="font-medium">{{ formatPrice(selectedBank.admin_fee) }}</span> sudah dihitung ke total.
                                                </div>
                                            </div>
                                        </div>
                                    </CardContent>
                                </Card>

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
                            <CardDescription>Transfer sesuai detail berikut, lalu konfirmasi</CardDescription>
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

                            <div v-if="selectedBank" class="grid gap-3 sm:grid-cols-2">
                                <div class="rounded-lg border border-border/60 bg-background/40 p-3">
                                    <div class="text-xs text-muted-foreground">Bank</div>
                                    <div class="mt-0.5 font-medium">{{ selectedBank.bank_name }}</div>
                                </div>
                                <div class="rounded-lg border border-border/60 bg-background/40 p-3">
                                    <div class="text-xs text-muted-foreground">Kode Bank</div>
                                    <div class="mt-0.5 font-mono font-medium">{{ selectedBank.bank_code }}</div>
                                </div>
                                <div class="rounded-lg border border-border/60 bg-background/40 p-3 sm:col-span-2">
                                    <div class="flex items-center justify-between">
                                        <div class="text-xs text-muted-foreground">No. Rekening</div>
                                        <button
                                            type="button"
                                            class="inline-flex items-center gap-1 text-xs text-muted-foreground hover:text-foreground"
                                            @click="copyValue(String(selectedBank.account_number || ''))"
                                        >
                                            <Copy class="h-3.5 w-3.5" />
                                            Copy
                                        </button>
                                    </div>
                                    <div class="mt-0.5 font-mono text-xl font-semibold text-emerald-700 dark:text-green-400">{{ selectedBank.account_number }}</div>
                                    <div class="mt-1 text-xs text-muted-foreground">a.n. {{ selectedBank.account_name }}</div>
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
                                <span class="text-muted-foreground">Biaya admin</span>
                                <span class="font-medium">{{ selectedBank ? formatPrice(selectedBank.admin_fee || 0) : formatPrice(0) }}</span>
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
