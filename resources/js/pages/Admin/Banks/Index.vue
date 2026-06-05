<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Building2, Edit, Plus, ToggleLeft, ToggleRight, Trash2, X } from 'lucide-vue-next';
import { ref } from 'vue';

type PaymentType = 'bank' | 'ewallet' | 'qris';

interface PaymentAccount {
    id: number;
    type: PaymentType;
    name: string;
    account_number: string | null;
    account_name: string | null;
    qris_provider: string | null;
    qris_image_path: string | null;
    is_active: boolean;
    sort: number;
    created_at: string;
    updated_at: string;
}

interface PaginatedPayments {
    data: PaymentAccount[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
}

interface Props {
    payments: PaginatedPayments;
}

const props = defineProps<Props>();

const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const selectedPayment = ref<PaymentAccount | null>(null);
const paymentToDelete = ref<PaymentAccount | null>(null);

interface PaymentForm {
    type: PaymentType;
    name: string;
    account_number: string;
    account_name: string;
    qris_provider: string;
    sort: number;
    is_active: boolean;
    qris_image: File | null;
}

const createForm = useForm<PaymentForm>({
    type: 'bank',
    name: '',
    account_number: '',
    account_name: '',
    qris_provider: '',
    sort: 0,
    is_active: true,
    qris_image: null,
});

const editForm = useForm<PaymentForm>({
    type: 'bank',
    name: '',
    account_number: '',
    account_name: '',
    qris_provider: '',
    sort: 0,
    is_active: true,
    qris_image: null,
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Payment Management', href: '/admin/payments' },
];

const getStatusClass = (isActive: boolean) => {
    return isActive
        ? 'bg-emerald-100 text-emerald-800'
        : 'bg-muted text-muted-foreground';
};

const formatPaginationLabel = (label: string) => {
    const withoutTags = label.replace(/<[^>]*>/g, ' ').trim();
    return withoutTags
        .replace(/&laquo;/g, '«')
        .replace(/&raquo;/g, '»')
        .replace(/&nbsp;/g, ' ')
        .replace(/&amp;/g, '&')
        .replace(/\s+/g, ' ')
        .trim();
};

const togglePaymentStatus = (payment: PaymentAccount) => {
    router.patch(
        `/admin/payments/${payment.id}/toggle-status`,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                // Success message will be handled by flash messages
            },
        },
    );
};

const openDeleteModal = (payment: PaymentAccount) => {
    paymentToDelete.value = payment;
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    if (paymentToDelete.value) {
        router.delete(`/admin/payments/${paymentToDelete.value.id}`, {
            preserveScroll: true,
            onFinish: () => {
                showDeleteModal.value = false;
                paymentToDelete.value = null;
            },
        });
    }
};

const submitCreate = () => {
    createForm.post('/admin/payments', {
        forceFormData: true,
        onSuccess: () => {
            showCreateModal.value = false;
            createForm.reset();
            createForm.type = 'bank';
            createForm.is_active = true;
        },
    });
};

const openEditModal = (payment: PaymentAccount) => {
    selectedPayment.value = payment;
    editForm.reset();
    editForm.type = payment.type;
    editForm.name = payment.name;
    editForm.account_number = payment.account_number || '';
    editForm.account_name = payment.account_name || '';
    editForm.qris_provider = payment.qris_provider || '';
    editForm.sort = payment.sort || 0;
    editForm.is_active = payment.is_active;
    editForm.qris_image = null;
    showEditModal.value = true;
};

const submitEdit = () => {
    if (!selectedPayment.value) return;

    editForm.put(`/admin/payments/${selectedPayment.value.id}`, {
        forceFormData: true,
        onSuccess: () => {
            showEditModal.value = false;
            editForm.reset();
            selectedPayment.value = null;
        },
    });
};

const paymentTypeLabel = (type: PaymentType) => {
    if (type === 'bank') return 'Bank';
    if (type === 'ewallet') return 'E-Wallet';
    return 'QRIS';
};

const onCreateQrisImageChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    createForm.qris_image = target.files?.[0] || null;
};

const onEditQrisImageChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    editForm.qris_image = target.files?.[0] || null;
};
</script>

<template>
    <Head title="Admin - Payment Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="w-full max-w-none space-y-4 sm:space-y-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-medium tracking-tight sm:text-3xl" style="font-family: Georgia, serif;">Payment Management</h1>
                    <p class="text-sm text-muted-foreground sm:text-base">Kelola bank, e-wallet, dan QRIS untuk pembayaran invoice</p>
                </div>
                <Button @click="showCreateModal = true" class="w-full cursor-pointer sm:w-auto">
                    <Plus class="mr-2 h-4 w-4" />
                    <span class="hidden sm:inline">Tambah Metode</span>
                    <span class="sm:hidden">Tambah</span>
                </Button>
            </div>

            <!-- Banks Table -->
            <Card>
                <CardHeader>
                    <CardTitle style="font-family: Georgia, serif;" class="flex items-center gap-2">
                        <Building2 class="h-5 w-5" />
                        Daftar Metode Pembayaran
                    </CardTitle>
                    <CardDescription> Total {{ payments.total }} metode terdaftar </CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="payments.data.length > 0">
                        <div class="overflow-x-auto rounded-md border border-border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Jenis</TableHead>
                                        <TableHead>Nama</TableHead>
                                        <TableHead>Detail</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead class="text-right">Aksi</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="payment in payments.data" :key="payment.id">
                                        <TableCell>
                                            <Badge class="text-xs" variant="secondary">{{ paymentTypeLabel(payment.type) }}</Badge>
                                        </TableCell>
                                        <TableCell>
                                            <div class="font-medium">{{ payment.name }}</div>
                                        </TableCell>
                                        <TableCell>
                                            <div class="space-y-0.5 text-sm">
                                                <template v-if="payment.type === 'bank'">
                                                    <div class="font-mono">{{ payment.account_number || '-' }}</div>
                                                    <div class="text-muted-foreground">{{ payment.account_name || '-' }}</div>
                                                </template>
                                                <template v-else-if="payment.type === 'ewallet'">
                                                    <div class="text-muted-foreground">{{ payment.account_number || '—' }}</div>
                                                </template>
                                                <template v-else>
                                                    <div class="text-muted-foreground">{{ payment.qris_provider || payment.name }}</div>
                                                    <a v-if="payment.qris_image_path" :href="payment.qris_image_path" target="_blank" class="text-primary hover:underline">Lihat QR</a>
                                                </template>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <Badge :class="getStatusClass(payment.is_active)" class="text-xs">
                                                {{ payment.is_active ? 'Aktif' : 'Nonaktif' }}
                                            </Badge>
                                        </TableCell>
                                        <TableCell class="text-right">
                                            <div class="flex items-center justify-end gap-1">
                                                <Button size="sm" variant="outline" @click="openEditModal(payment)" class="cursor-pointer" title="Edit">
                                                    <Edit class="h-3.5 w-3.5" />
                                                </Button>
                                                <Button
                                                    size="sm"
                                                    variant="outline"
                                                    @click="togglePaymentStatus(payment)"
                                                    :title="payment.is_active ? 'Nonaktifkan' : 'Aktifkan'"
                                                    class="cursor-pointer"
                                                >
                                                    <ToggleRight v-if="payment.is_active" class="h-3.5 w-3.5 text-primary" />
                                                    <ToggleLeft v-else class="h-3.5 w-3.5 text-muted-foreground" />
                                                </Button>
                                                <Button
                                                    size="sm"
                                                    variant="outline"
                                                    @click="openDeleteModal(payment)"
                                                    :title="'Hapus ' + payment.name"
                                                    class="cursor-pointer text-destructive hover:bg-muted hover:text-destructive"
                                                >
                                                    <Trash2 class="h-3.5 w-3.5" />
                                                </Button>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="payments.last_page > 1" class="mt-4 flex items-center justify-center space-x-2">
                            <Button
                                v-for="link in payments.links"
                                :key="`${link.label}:${link.url ?? ''}`"
                                variant="ghost"
                                size="sm"
                                :class="{
                                    'bg-primary text-primary-foreground': link.active,
                                    'cursor-pointer': link.url,
                                }"
                                :disabled="!link.url"
                                @click="link.url && router.get(link.url)"
                            >
                                {{ formatPaginationLabel(link.label) }}
                            </Button>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="py-12 text-center">
                        <Building2 class="mx-auto h-12 w-12 text-muted-foreground/40" />
                        <h3 class="mt-2 text-sm font-semibold">Belum ada metode pembayaran</h3>
                        <p class="mt-1 text-sm text-muted-foreground">Mulai dengan menambahkan metode pembayaran pertama.</p>
                        <div class="mt-6">
                            <Button @click="showCreateModal = true" class="cursor-pointer">
                                <Plus class="mr-2 h-4 w-4" />
                                Tambah Metode
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Create Payment Modal -->
        <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="showCreateModal = false"></div>

            <Card class="relative w-full max-w-2xl overflow-y-auto max-h-[90vh]">
                <CardHeader class="relative">
                    <Button
                        type="button"
                        variant="ghost"
                        size="icon"
                        class="absolute top-3 right-3 h-8 w-8 cursor-pointer"
                        @click="showCreateModal = false"
                        title="Tutup"
                    >
                        <X class="h-4 w-4" />
                    </Button>
                    <CardTitle style="font-family: Georgia, serif;">Tambah Metode Pembayaran</CardTitle>
                    <CardDescription>Tambahkan metode pembayaran baru (bank / e-wallet / QRIS)</CardDescription>
                </CardHeader>
                <CardContent class="pb-6">
                    <form @submit.prevent="submitCreate" class="space-y-4">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="md:col-span-2">
                                <Label for="create-type">Jenis *</Label>
                                <select
                                    id="create-type"
                                    v-model="createForm.type"
                                    class="flex h-9 w-full cursor-pointer rounded-md border border-input bg-background px-3 py-1 text-sm text-foreground shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none aria-invalid:border-destructive aria-invalid:ring-destructive/20"
                                    :aria-invalid="!!createForm.errors.type"
                                    required
                                >
                                    <option value="bank">Bank</option>
                                    <option value="ewallet">E-Wallet</option>
                                    <option value="qris">QRIS</option>
                                </select>
                                <p v-if="createForm.errors.type" class="mt-1 text-xs text-destructive">{{ createForm.errors.type }}</p>
                            </div>

                            <div v-if="createForm.type === 'bank'">
                                <Label for="create-name-bank">Nama Bank *</Label>
                                <Input id="create-name-bank" v-model="createForm.name" placeholder="Contoh: BCA" :aria-invalid="!!createForm.errors.name" required />
                                <p v-if="createForm.errors.name" class="mt-1 text-xs text-destructive">{{ createForm.errors.name }}</p>
                            </div>

                            <div v-if="createForm.type === 'ewallet'">
                                <Label for="create-name-ewallet">Nama E-Wallet *</Label>
                                <Input id="create-name-ewallet" v-model="createForm.name" placeholder="Contoh: DANA / OVO / GoPay" :aria-invalid="!!createForm.errors.name" required />
                                <p v-if="createForm.errors.name" class="mt-1 text-xs text-destructive">{{ createForm.errors.name }}</p>
                            </div>

                            <div v-if="createForm.type === 'ewallet'">
                                <Label for="create-ewallet-number">Nomor E-Wallet *</Label>
                                <Input
                                    id="create-ewallet-number"
                                    v-model="createForm.account_number"
                                    placeholder="Contoh: 62812xxxxxxx"
                                    :aria-invalid="!!createForm.errors.account_number"
                                    required
                                />
                                <p v-if="createForm.errors.account_number" class="mt-1 text-xs text-destructive">{{ createForm.errors.account_number }}</p>
                            </div>

                            <div v-if="createForm.type === 'qris'">
                                <Label for="create-name-qris">Penyedia QRIS *</Label>
                                <Input id="create-name-qris" v-model="createForm.name" placeholder="Contoh: QRIS (Nama Toko)" :aria-invalid="!!createForm.errors.name" required />
                                <p v-if="createForm.errors.name" class="mt-1 text-xs text-destructive">{{ createForm.errors.name }}</p>
                            </div>

                            <div v-if="createForm.type === 'bank'">
                                <Label for="create-account-number">Nomor Rekening *</Label>
                                <Input id="create-account-number" v-model="createForm.account_number" placeholder="Contoh: 1234567890" :aria-invalid="!!createForm.errors.account_number" required />
                                <p v-if="createForm.errors.account_number" class="mt-1 text-xs text-destructive">{{ createForm.errors.account_number }}</p>
                            </div>

                            <div v-if="createForm.type === 'bank'">
                                <Label for="create-account-name">Nama Pemilik Rekening *</Label>
                                <Input id="create-account-name" v-model="createForm.account_name" placeholder="Contoh: PT. Contoh Indonesia" :aria-invalid="!!createForm.errors.account_name" required />
                                <p v-if="createForm.errors.account_name" class="mt-1 text-xs text-destructive">{{ createForm.errors.account_name }}</p>
                            </div>

                            <div v-if="createForm.type === 'qris'" class="md:col-span-2">
                                <Label for="create-qris-image">Upload QRIS *</Label>
                                <input
                                    id="create-qris-image"
                                    type="file"
                                    accept="image/*"
                                    class="block w-full cursor-pointer rounded-md border border-input bg-background px-3 py-2 text-sm text-foreground file:mr-4 file:rounded-full file:border-0 file:bg-secondary file:px-4 file:py-2 file:text-sm file:font-semibold file:text-secondary-foreground hover:file:bg-accent"
                                    @change="onCreateQrisImageChange"
                                />
                                <p v-if="createForm.errors.qris_image" class="mt-1 text-xs text-destructive">{{ createForm.errors.qris_image }}</p>
                            </div>

                            <div class="md:col-span-2">
                                <input v-model.number="createForm.sort" type="hidden" />
                            </div>
                        </div>

                    <div class="flex flex-col-reverse gap-2 pt-4 sm:flex-row sm:justify-end">
                        <Button type="button" variant="outline" @click="showCreateModal = false" class="w-full cursor-pointer sm:w-auto">Batal</Button>
                        <Button type="submit" :disabled="createForm.processing" class="w-full cursor-pointer disabled:cursor-not-allowed sm:w-auto">
                            {{ createForm.processing ? 'Menyimpan...' : 'Simpan Metode' }}
                        </Button>
                    </div>
                </form>
                </CardContent>
            </Card>
        </div>

        <!-- Edit Payment Modal -->
        <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="showEditModal = false"></div>

            <Card class="relative w-full max-w-2xl overflow-y-auto max-h-[90vh]">
                <CardHeader class="relative">
                    <Button
                        type="button"
                        variant="ghost"
                        size="icon"
                        class="absolute top-3 right-3 h-8 w-8 cursor-pointer"
                        @click="showEditModal = false"
                        title="Tutup"
                    >
                        <X class="h-4 w-4" />
                    </Button>
                    <CardTitle style="font-family: Georgia, serif;">Edit Metode Pembayaran</CardTitle>
                    <CardDescription>Update informasi metode pembayaran</CardDescription>
                </CardHeader>
                <CardContent class="pb-6">
                    <form @submit.prevent="submitEdit" class="space-y-4">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="md:col-span-2">
                                <Label for="edit-type">Jenis *</Label>
                                <select
                                    id="edit-type"
                                    v-model="editForm.type"
                                    class="flex h-9 w-full cursor-pointer rounded-md border border-input bg-background px-3 py-1 text-sm text-foreground shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none aria-invalid:border-destructive aria-invalid:ring-destructive/20"
                                    :aria-invalid="!!editForm.errors.type"
                                    required
                                >
                                    <option value="bank">Bank</option>
                                    <option value="ewallet">E-Wallet</option>
                                    <option value="qris">QRIS</option>
                                </select>
                                <p v-if="editForm.errors.type" class="mt-1 text-xs text-destructive">{{ editForm.errors.type }}</p>
                            </div>

                            <div v-if="editForm.type === 'bank'">
                                <Label for="edit-name-bank">Nama Bank *</Label>
                                <Input id="edit-name-bank" v-model="editForm.name" placeholder="Contoh: BCA" :aria-invalid="!!editForm.errors.name" required />
                                <p v-if="editForm.errors.name" class="mt-1 text-xs text-destructive">{{ editForm.errors.name }}</p>
                            </div>

                            <div v-if="editForm.type === 'ewallet'">
                                <Label for="edit-name-ewallet">Nama E-Wallet *</Label>
                                <Input id="edit-name-ewallet" v-model="editForm.name" placeholder="Contoh: DANA / OVO / GoPay" :aria-invalid="!!editForm.errors.name" required />
                                <p v-if="editForm.errors.name" class="mt-1 text-xs text-destructive">{{ editForm.errors.name }}</p>
                            </div>

                            <div v-if="editForm.type === 'ewallet'">
                                <Label for="edit-ewallet-number">Nomor E-Wallet *</Label>
                                <Input
                                    id="edit-ewallet-number"
                                    v-model="editForm.account_number"
                                    placeholder="Contoh: 62812xxxxxxx"
                                    :aria-invalid="!!editForm.errors.account_number"
                                    required
                                />
                                <p v-if="editForm.errors.account_number" class="mt-1 text-xs text-destructive">{{ editForm.errors.account_number }}</p>
                            </div>

                            <div v-if="editForm.type === 'qris'">
                                <Label for="edit-name-qris">Penyedia QRIS *</Label>
                                <Input id="edit-name-qris" v-model="editForm.name" placeholder="Contoh: QRIS (Nama Toko)" :aria-invalid="!!editForm.errors.name" required />
                                <p v-if="editForm.errors.name" class="mt-1 text-xs text-destructive">{{ editForm.errors.name }}</p>
                            </div>

                            <div v-if="editForm.type === 'bank'">
                                <Label for="edit-account-number">Nomor Rekening *</Label>
                                <Input id="edit-account-number" v-model="editForm.account_number" placeholder="Contoh: 1234567890" :aria-invalid="!!editForm.errors.account_number" required />
                                <p v-if="editForm.errors.account_number" class="mt-1 text-xs text-destructive">{{ editForm.errors.account_number }}</p>
                            </div>

                            <div v-if="editForm.type === 'bank'">
                                <Label for="edit-account-name">Nama Pemilik Rekening *</Label>
                                <Input id="edit-account-name" v-model="editForm.account_name" placeholder="Contoh: PT. Contoh Indonesia" :aria-invalid="!!editForm.errors.account_name" required />
                                <p v-if="editForm.errors.account_name" class="mt-1 text-xs text-destructive">{{ editForm.errors.account_name }}</p>
                            </div>

                            <div v-if="editForm.type === 'qris'" class="md:col-span-2 space-y-2">
                                <div v-if="selectedPayment?.qris_image_path" class="text-sm">
                                    <a :href="selectedPayment.qris_image_path" target="_blank" class="text-primary hover:underline">Lihat QRIS saat ini</a>
                                </div>
                                <div>
                                    <Label for="edit-qris-image">Upload QRIS</Label>
                                    <input
                                        id="edit-qris-image"
                                        type="file"
                                        accept="image/*"
                                        class="block w-full cursor-pointer rounded-md border border-input bg-background px-3 py-2 text-sm text-foreground file:mr-4 file:rounded-full file:border-0 file:bg-secondary file:px-4 file:py-2 file:text-sm file:font-semibold file:text-secondary-foreground hover:file:bg-accent"
                                        @change="onEditQrisImageChange"
                                    />
                                    <p v-if="editForm.errors.qris_image" class="mt-1 text-xs text-destructive">{{ editForm.errors.qris_image }}</p>
                                </div>
                            </div>

                            <div class="md:col-span-2">
                                <input v-model.number="editForm.sort" type="hidden" />
                            </div>
                        </div>

                    <div class="flex flex-col-reverse gap-2 pt-4 sm:flex-row sm:justify-end">
                        <Button type="button" variant="outline" @click="showEditModal = false" class="w-full cursor-pointer sm:w-auto">Batal</Button>
                        <Button type="submit" :disabled="editForm.processing" class="w-full cursor-pointer disabled:cursor-not-allowed sm:w-auto">
                            {{ editForm.processing ? 'Memperbarui...' : 'Update Metode' }}
                        </Button>
                    </div>
                </form>
                </CardContent>
            </Card>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="showDeleteModal = false"></div>

            <Card class="relative w-full max-w-md">
                <CardHeader class="relative">
                    <Button
                        type="button"
                        variant="ghost"
                        size="icon"
                        class="absolute top-3 right-3 h-8 w-8 cursor-pointer"
                        @click="showDeleteModal = false"
                        title="Tutup"
                    >
                        <X class="h-4 w-4" />
                    </Button>
                    <CardTitle style="font-family: Georgia, serif;" class="flex items-center gap-2">
                        <Trash2 class="h-4 w-4 text-destructive" />
                        Hapus Metode Pembayaran
                    </CardTitle>
                    <CardDescription>Tindakan ini tidak dapat dibatalkan</CardDescription>
                </CardHeader>
                <CardContent class="pb-6">
                    <div class="space-y-2 text-sm text-muted-foreground">
                        <p>
                            Apakah Anda yakin ingin menghapus
                            <span class="font-medium text-foreground">{{ paymentToDelete?.name }}</span>?
                        </p>
                        <p>Data ini akan dihapus secara permanen dan tidak dapat dikembalikan.</p>
                    </div>

                    <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                        <Button type="button" variant="outline" @click="showDeleteModal = false" class="w-full cursor-pointer sm:w-auto">Batal</Button>
                        <Button type="button" variant="destructive" class="w-full cursor-pointer sm:w-auto" @click="confirmDelete">Hapus</Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
