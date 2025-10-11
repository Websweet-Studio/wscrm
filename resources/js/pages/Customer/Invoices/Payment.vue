<script setup>
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

import { Separator } from '@/components/ui/separator';
import { formatDate, formatPrice } from '@/lib/utils';
import { AlertCircle, Building2, CheckCircle, CreditCard, FileText } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps({
    invoice: Object,
    banks: Array,
});

const form = useForm({
    bank_id: props.invoice.bank_id || '',
    payment_method: props.invoice.payment_method || 'bank_transfer',
});

const confirmForm = useForm({
    payment_proof: '',
});

const selectedBank = ref(null);
const showConfirmation = ref(false);

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

// Initialize selected bank if already set
if (props.invoice.bank_id) {
    selectedBank.value = props.banks.find((bank) => bank.id == props.invoice.bank_id);
    showConfirmation.value = true;
}
</script>

<template>
    <Head :title="`Pembayaran Invoice ${invoice.invoice_number}`" />

    <AppLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl leading-tight font-semibold text-gray-800 dark:text-gray-200">
                        Pembayaran Invoice {{ invoice.invoice_number }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Pilih metode pembayaran dan selesaikan transaksi</p>
                </div>
                            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl space-y-6 sm:px-6 lg:px-8">
                <!-- Invoice Summary -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <FileText class="h-5 w-5" />
                            Ringkasan Invoice
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">Invoice Number</label>
                                <p class="font-semibold">{{ invoice.invoice_number }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">Status</label>
                                <div class="mt-1">
                                    <Badge :class="getStatusClass(invoice.status)">
                                        {{ getStatusText(invoice.status) }}
                                    </Badge>
                                </div>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">Due Date</label>
                                <p :class="isOverdue ? 'font-semibold text-red-600' : ''">{{ formatDate(invoice.due_date) }}</p>
                            </div>
                        </div>

                        <Separator class="my-4" />

                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span>Subtotal:</span>
                                <span class="font-medium">{{ formatPrice(invoice.amount) }}</span>
                            </div>
                            <div v-if="selectedBank && selectedBank.admin_fee > 0" class="flex justify-between text-sm text-muted-foreground">
                                <span>Admin Fee ({{ selectedBank.bank_name }}):</span>
                                <span>{{ formatPrice(selectedBank.admin_fee) }}</span>
                            </div>
                            <Separator />
                            <div class="flex justify-between text-lg font-bold">
                                <span>Total:</span>
                                <span class="text-primary">{{ formatPrice(totalWithFee) }}</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Payment Method Selection -->
                <Card v-if="!showConfirmation">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <CreditCard class="h-5 w-5" />
                            Pilih Metode Pembayaran
                        </CardTitle>
                        <CardDescription> Pilih bank dan metode pembayaran yang Anda inginkan </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <form @submit.prevent="submitPayment">
                            <!-- Payment Method -->
                            <div class="space-y-3">
                                <Label>Metode Pembayaran</Label>
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                    <div class="flex items-center space-x-2 rounded-lg border p-4">
                                        <input
                                            type="radio"
                                            value="bank_transfer"
                                            id="bank_transfer"
                                            v-model="form.payment_method"
                                            class="h-4 w-4 border-gray-300 text-primary focus:ring-primary"
                                        />
                                        <Label for="bank_transfer" class="flex-1 cursor-pointer">
                                            <div class="font-medium">Transfer Bank</div>
                                            <div class="text-sm text-muted-foreground">Transfer melalui ATM/Internet Banking</div>
                                        </Label>
                                    </div>
                                    <div class="flex items-center space-x-2 rounded-lg border p-4">
                                        <input
                                            type="radio"
                                            value="credit_card"
                                            id="credit_card"
                                            v-model="form.payment_method"
                                            class="h-4 w-4 border-gray-300 text-primary focus:ring-primary"
                                        />
                                        <Label for="credit_card" class="flex-1 cursor-pointer">
                                            <div class="font-medium">Kartu Kredit</div>
                                            <div class="text-sm text-muted-foreground">Visa, Mastercard, dll</div>
                                        </Label>
                                    </div>
                                    <div class="flex items-center space-x-2 rounded-lg border p-4">
                                        <input
                                            type="radio"
                                            value="e_wallet"
                                            id="e_wallet"
                                            v-model="form.payment_method"
                                            class="h-4 w-4 border-gray-300 text-primary focus:ring-primary"
                                        />
                                        <Label for="e_wallet" class="flex-1 cursor-pointer">
                                            <div class="font-medium">E-Wallet</div>
                                            <div class="text-sm text-muted-foreground">GoPay, OVO, DANA, dll</div>
                                        </Label>
                                    </div>
                                </div>
                                <div v-if="form.errors.payment_method" class="text-sm text-red-600">
                                    {{ form.errors.payment_method }}
                                </div>
                            </div>

                            <!-- Bank Selection -->
                            <div class="space-y-3">
                                <Label for="bank_id">Pilih Bank</Label>
                                <select
                                    v-model="form.bank_id"
                                    @change="updateSelectedBank"
                                    id="bank_id"
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm text-foreground ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50 dark:bg-gray-800 dark:text-white"
                                >
                                    <option value="" disabled>Pilih bank untuk pembayaran</option>
                                    <option v-for="bank in banks" :key="bank.id" :value="bank.id.toString()">
                                        {{ bank.bank_name }} ({{ bank.bank_code }}){{
                                            bank.admin_fee > 0 ? ' - Fee: ' + formatPrice(bank.admin_fee) : ''
                                        }}
                                    </option>
                                </select>
                                <div v-if="form.errors.bank_id" class="text-sm text-red-600">
                                    {{ form.errors.bank_id }}
                                </div>
                            </div>

                            <!-- Selected Bank Details -->
                            <Card v-if="selectedBank" class="bg-muted/50">
                                <CardHeader>
                                    <CardTitle class="text-base">Detail Bank Terpilih</CardTitle>
                                </CardHeader>
                                <CardContent class="space-y-2">
                                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                        <div>
                                            <label class="text-sm font-medium text-muted-foreground">Bank</label>
                                            <p class="font-medium">{{ selectedBank.bank_name }}</p>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-muted-foreground">Kode Bank</label>
                                            <p>{{ selectedBank.bank_code }}</p>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-muted-foreground">No. Rekening</label>
                                            <p class="font-mono">{{ selectedBank.account_number }}</p>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-muted-foreground">Nama Rekening</label>
                                            <p>{{ selectedBank.account_name }}</p>
                                        </div>
                                    </div>
                                    <div v-if="selectedBank.branch">
                                        <label class="text-sm font-medium text-muted-foreground">Cabang</label>
                                        <p>{{ selectedBank.branch }}</p>
                                    </div>
                                    <div
                                        v-if="selectedBank.admin_fee > 0"
                                        class="rounded border border-yellow-200 bg-yellow-50 p-3 dark:border-yellow-800 dark:bg-yellow-900/20"
                                    >
                                        <div class="flex items-center">
                                            <AlertCircle class="mr-2 h-4 w-4 text-yellow-600" />
                                            <span class="text-sm">Biaya admin: {{ formatPrice(selectedBank.admin_fee) }}</span>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>

                            <Button type="submit" :disabled="form.processing" class="w-full">
                                <CreditCard class="mr-2 h-4 w-4" />
                                {{ form.processing ? 'Memproses...' : 'Lanjutkan Pembayaran' }}
                            </Button>
                        </form>
                    </CardContent>
                </Card>

                <!-- Payment Confirmation -->
                <Card v-if="showConfirmation">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Building2 class="h-5 w-5" />
                            Instruksi Pembayaran
                        </CardTitle>
                        <CardDescription> Silakan lakukan pembayaran sesuai instruksi di bawah ini </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <AlertCircle class="h-5 w-5 text-blue-400" />
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">Instruksi Pembayaran</h3>
                                    <p class="mt-1 text-sm text-blue-700 dark:text-blue-300">
                                        Transfer sejumlah {{ formatPrice(totalWithFee) }} ke rekening di bawah ini.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div v-if="selectedBank" class="space-y-4">
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-muted-foreground">Bank Tujuan</label>
                                    <p class="text-lg font-semibold">{{ selectedBank.bank_name }}</p>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-muted-foreground">Kode Bank</label>
                                    <p class="font-mono text-lg">{{ selectedBank.bank_code }}</p>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-muted-foreground">No. Rekening</label>
                                    <p class="font-mono text-xl font-bold text-primary">{{ selectedBank.account_number }}</p>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-muted-foreground">Nama Rekening</label>
                                    <p class="text-lg font-semibold">{{ selectedBank.account_name }}</p>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="text-sm font-medium text-muted-foreground">Jumlah Transfer</label>
                                <p class="text-2xl font-bold text-primary">{{ formatPrice(totalWithFee) }}</p>
                            </div>

                            <div v-if="selectedBank.branch" class="space-y-2">
                                <label class="text-sm font-medium text-muted-foreground">Cabang</label>
                                <p>{{ selectedBank.branch }}</p>
                            </div>
                        </div>

                        <Separator />

                        <!-- Payment Confirmation Form -->
                        <form @submit.prevent="confirmPayment" class="space-y-4">
                            <div class="space-y-2">
                                <Label for="payment_proof">Catatan Pembayaran (Opsional)</Label>
                                <textarea
                                    id="payment_proof"
                                    v-model="confirmForm.payment_proof"
                                    placeholder="Masukkan nomor referensi transfer atau catatan lainnya..."
                                    rows="3"
                                    class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                                />
                                <p class="text-sm text-muted-foreground">Anda dapat menambahkan nomor referensi transfer atau catatan lainnya.</p>
                            </div>

                            <div class="flex gap-3">
                                <Button type="submit" :disabled="confirmForm.processing" class="flex-1">
                                    <CheckCircle class="mr-2 h-4 w-4" />
                                    {{ confirmForm.processing ? 'Memproses...' : 'Konfirmasi Pembayaran' }}
                                </Button>
                                <Button type="button" variant="outline" @click="showConfirmation = false"> Ubah Metode </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
