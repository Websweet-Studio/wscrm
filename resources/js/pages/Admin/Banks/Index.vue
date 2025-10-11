<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatPrice } from '@/lib/utils';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Building2, Edit, Eye, Plus, ToggleLeft, ToggleRight, Trash2, X } from 'lucide-vue-next';
import { ref } from 'vue';

interface Bank {
    id: number;
    bank_name: string;
    bank_code: string;
    account_number: string;
    account_name: string;
    branch?: string;
    swift_code?: string;
    description?: string;
    is_active: boolean;
    admin_fee: number;
    bank_type: 'local' | 'international';
    created_at: string;
    updated_at: string;
}

interface PaginatedBanks {
    data: Bank[];
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
    banks: PaginatedBanks;
}

const props = defineProps<Props>();

const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const selectedBank = ref<Bank | null>(null);
const bankToDelete = ref<Bank | null>(null);

interface BankForm {
    bank_name: string;
    bank_code: string;
    account_number: string;
    account_name: string;
    branch: string;
    swift_code: string;
    description: string;
    is_active: boolean;
    admin_fee: number;
    bank_type: 'local' | 'international';
}

const createForm = useForm<BankForm>({
    bank_name: '',
    bank_code: '',
    account_number: '',
    account_name: '',
    branch: '',
    swift_code: '',
    description: '',
    is_active: true,
    admin_fee: 0,
    bank_type: 'local',
});

const editForm = useForm<BankForm>({
    bank_name: '',
    bank_code: '',
    account_number: '',
    account_name: '',
    branch: '',
    swift_code: '',
    description: '',
    is_active: true,
    admin_fee: 0,
    bank_type: 'local',
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Manajemen Bank', href: '/admin/banks' },
];

const getStatusClass = (isActive: boolean) => {
    return isActive
        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
        : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
};

const getBankTypeClass = (type: string) => {
    return type === 'local'
        ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200'
        : 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200';
};

const toggleBankStatus = (bank: Bank) => {
    router.patch(
        `/admin/banks/${bank.id}/toggle-status`,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                // Success message will be handled by flash messages
            },
        },
    );
};

const openDeleteModal = (bank: Bank) => {
    bankToDelete.value = bank;
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    if (bankToDelete.value) {
        router.delete(`/admin/banks/${bankToDelete.value.id}`, {
            preserveScroll: true,
            onFinish: () => {
                showDeleteModal.value = false;
                bankToDelete.value = null;
            },
        });
    }
};

const submitCreate = () => {
    createForm.post('/admin/banks', {
        onSuccess: () => {
            showCreateModal.value = false;
            createForm.reset();
        },
    });
};

const openEditModal = (bank: Bank) => {
    selectedBank.value = bank;
    editForm.reset();
    editForm.bank_name = bank.bank_name;
    editForm.bank_code = bank.bank_code;
    editForm.account_number = bank.account_number;
    editForm.account_name = bank.account_name;
    editForm.branch = bank.branch || '';
    editForm.swift_code = bank.swift_code || '';
    editForm.description = bank.description || '';
    editForm.is_active = bank.is_active;
    editForm.admin_fee = bank.admin_fee;
    editForm.bank_type = bank.bank_type;
    showEditModal.value = true;
};

const submitEdit = () => {
    if (!selectedBank.value) return;

    editForm.put(`/admin/banks/${selectedBank.value.id}`, {
        onSuccess: () => {
            showEditModal.value = false;
            editForm.reset();
            selectedBank.value = null;
        },
    });
};
</script>

<template>
    <Head title="Manajemen Bank" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Manajemen Bank</h1>
                    <p class="text-muted-foreground">Kelola bank pembayaran untuk sistem invoice</p>
                </div>
                <Button @click="showCreateModal = true" class="cursor-pointer">
                    <Plus class="mr-2 h-4 w-4" />
                    Tambah Bank
                </Button>
            </div>

            <!-- Banks Table -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Building2 class="h-5 w-5" />
                        Daftar Bank
                    </CardTitle>
                    <CardDescription> Total {{ banks.total }} bank terdaftar </CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="banks.data.length > 0">
                        <div class="rounded-md border overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Bank</TableHead>
                                    <TableHead>Kode</TableHead>
                                    <TableHead>Rekening</TableHead>
                                    <TableHead>Tipe</TableHead>
                                    <TableHead>Admin Fee</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead class="text-right">Aksi</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="bank in banks.data" :key="bank.id">
                                    <TableCell>
                                        <div>
                                            <div class="font-medium">{{ bank.bank_name }}</div>
                                            <div class="text-sm text-muted-foreground">{{ bank.account_name }}</div>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <code class="rounded bg-muted px-2 py-1 text-sm">{{ bank.bank_code }}</code>
                                    </TableCell>
                                    <TableCell>
                                        <div>
                                            <div class="font-mono text-sm">{{ bank.account_number }}</div>
                                            <div v-if="bank.branch" class="text-sm text-muted-foreground">{{ bank.branch }}</div>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <Badge :class="getBankTypeClass(bank.bank_type)">
                                            {{ bank.bank_type === 'local' ? 'Lokal' : 'Internasional' }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        {{ formatPrice(bank.admin_fee) }}
                                    </TableCell>
                                    <TableCell>
                                        <Badge :class="getStatusClass(bank.is_active)">
                                            {{ bank.is_active ? 'Aktif' : 'Nonaktif' }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <Button size="sm" variant="outline" @click="router.visit(`/admin/banks/${bank.id}`)" class="cursor-pointer" title="Lihat Detail">
                                                <Eye class="h-3.5 w-3.5" />
                                            </Button>
                                            <Button size="sm" variant="outline" @click="openEditModal(bank)" class="cursor-pointer" title="Edit">
                                                <Edit class="h-3.5 w-3.5" />
                                            </Button>
                                            <Button size="sm" @click="toggleBankStatus(bank)"
                                                :title="bank.is_active ? 'Nonaktifkan' : 'Aktifkan'"
                                                :class="bank.is_active ? 'bg-green-600 hover:bg-green-700 text-white' : 'bg-gray-600 hover:bg-gray-700 text-white'"
                                                class="cursor-pointer"
                                            >
                                                <ToggleRight v-if="bank.is_active" class="h-3.5 w-3.5" />
                                                <ToggleLeft v-else class="h-3.5 w-3.5" />
                                            </Button>
                                            <Button size="sm" variant="outline" @click="openDeleteModal(bank)"
                                                :title="'Hapus ' + bank.bank_name"
                                                class="cursor-pointer text-red-600 hover:text-red-700 hover:bg-red-50"
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
                        <div v-if="banks.last_page > 1" class="mt-4 flex items-center justify-center space-x-2">
                            <Button
                                v-for="link in banks.links"
                                :key="link.label"
                                variant="ghost"
                                size="sm"
                                :class="{
                                    'bg-primary text-primary-foreground': link.active,
                                    'pointer-events-none opacity-50': !link.url,
                                    'cursor-pointer': link.url,
                                }"
                                @click="link.url && router.get(link.url)"
                                v-html="link.label"
                            />
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="py-12 text-center">
                        <Building2 class="mx-auto h-12 w-12 text-muted-foreground/40" />
                        <h3 class="mt-2 text-sm font-semibold">Belum ada bank</h3>
                        <p class="mt-1 text-sm text-muted-foreground">Mulai dengan menambahkan bank pembayaran pertama.</p>
                        <div class="mt-6">
                            <Button @click="showCreateModal = true" class="cursor-pointer">
                                <Plus class="mr-2 h-4 w-4" />
                                Tambah Bank
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Create Bank Modal -->
        <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <!-- Overlay -->
            <div class="fixed inset-0 bg-black/50" @click="showCreateModal = false"></div>

            <!-- Modal Content -->
            <div class="relative mx-4 max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-lg bg-white p-6 shadow-xl dark:bg-gray-900">
                <!-- Header -->
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold">Tambah Bank</h2>
                        <p class="text-sm text-muted-foreground">Tambahkan bank pembayaran baru ke sistem</p>
                    </div>
                    <button @click="showCreateModal = false" class="cursor-pointer text-gray-500 hover:text-gray-700">
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <form @submit.prevent="submitCreate" class="space-y-4">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <!-- Bank Name -->
                        <div>
                            <Label for="create-bank-name">Nama Bank *</Label>
                            <Input
                                id="create-bank-name"
                                v-model="createForm.bank_name"
                                placeholder="Contoh: Bank Central Asia"
                                :class="{ 'border-red-500': createForm.errors.bank_name }"
                                required
                            />
                            <p v-if="createForm.errors.bank_name" class="mt-1 text-xs text-red-500">{{ createForm.errors.bank_name }}</p>
                        </div>

                        <!-- Bank Code -->
                        <div>
                            <Label for="create-bank-code">Kode Bank *</Label>
                            <Input
                                id="create-bank-code"
                                v-model="createForm.bank_code"
                                placeholder="Contoh: BCA"
                                :class="{ 'border-red-500': createForm.errors.bank_code }"
                                required
                            />
                            <p v-if="createForm.errors.bank_code" class="mt-1 text-xs text-red-500">{{ createForm.errors.bank_code }}</p>
                        </div>

                        <!-- Account Number -->
                        <div>
                            <Label for="create-account-number">Nomor Rekening *</Label>
                            <Input
                                id="create-account-number"
                                v-model="createForm.account_number"
                                placeholder="Contoh: 1234567890"
                                :class="{ 'border-red-500': createForm.errors.account_number }"
                                required
                            />
                            <p v-if="createForm.errors.account_number" class="mt-1 text-xs text-red-500">{{ createForm.errors.account_number }}</p>
                        </div>

                        <!-- Account Name -->
                        <div>
                            <Label for="create-account-name">Nama Pemilik Rekening *</Label>
                            <Input
                                id="create-account-name"
                                v-model="createForm.account_name"
                                placeholder="Contoh: PT. Contoh Indonesia"
                                :class="{ 'border-red-500': createForm.errors.account_name }"
                                required
                            />
                            <p v-if="createForm.errors.account_name" class="mt-1 text-xs text-red-500">{{ createForm.errors.account_name }}</p>
                        </div>

                        <!-- Branch -->
                        <div>
                            <Label for="create-branch">Cabang</Label>
                            <Input
                                id="create-branch"
                                v-model="createForm.branch"
                                placeholder="Contoh: Jakarta Pusat"
                                :class="{ 'border-red-500': createForm.errors.branch }"
                            />
                            <p v-if="createForm.errors.branch" class="mt-1 text-xs text-red-500">{{ createForm.errors.branch }}</p>
                        </div>

                        <!-- SWIFT Code -->
                        <div>
                            <Label for="create-swift-code">Kode SWIFT</Label>
                            <Input
                                id="create-swift-code"
                                v-model="createForm.swift_code"
                                placeholder="Contoh: CENAIDJA"
                                :class="{ 'border-red-500': createForm.errors.swift_code }"
                            />
                            <p v-if="createForm.errors.swift_code" class="mt-1 text-xs text-red-500">{{ createForm.errors.swift_code }}</p>
                        </div>

                        <!-- Bank Type -->
                        <div>
                            <Label for="create-bank-type">Tipe Bank *</Label>
                            <select
                                v-model="createForm.bank_type"
                                id="create-bank-type"
                                :class="[
                                    'flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm text-foreground ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50 dark:bg-gray-800 dark:text-white',
                                    { 'border-red-500': createForm.errors.bank_type },
                                ]"
                                required
                            >
                                <option value="" disabled>Pilih tipe bank</option>
                                <option value="local">Lokal</option>
                                <option value="international">Internasional</option>
                            </select>
                            <p v-if="createForm.errors.bank_type" class="mt-1 text-xs text-red-500">{{ createForm.errors.bank_type }}</p>
                        </div>

                        <!-- Admin Fee -->
                        <div>
                            <Label for="create-admin-fee">Biaya Admin</Label>
                            <Input
                                id="create-admin-fee"
                                v-model.number="createForm.admin_fee"
                                type="number"
                                min="0"
                                step="0.01"
                                placeholder="0"
                                :class="{ 'border-red-500': createForm.errors.admin_fee }"
                            />
                            <p v-if="createForm.errors.admin_fee" class="mt-1 text-xs text-red-500">{{ createForm.errors.admin_fee }}</p>
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <Label for="create-description">Deskripsi</Label>
                        <textarea
                            id="create-description"
                            v-model="createForm.description"
                            placeholder="Deskripsi tambahan tentang bank ini..."
                            rows="3"
                            :class="[
                                'flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50',
                                { 'border-red-500': createForm.errors.description },
                            ]"
                        />
                        <p v-if="createForm.errors.description" class="mt-1 text-xs text-red-500">{{ createForm.errors.description }}</p>
                    </div>

                    <!-- Active Status -->
                    <div class="flex items-center space-x-2">
                        <Switch id="create-is-active" v-model:checked="createForm.is_active" />
                        <Label for="create-is-active">Bank Aktif</Label>
                    </div>

                    <div class="flex justify-end space-x-2 pt-4">
                        <Button type="button" variant="outline" @click="showCreateModal = false" class="cursor-pointer"> Batal </Button>
                        <Button type="submit" :disabled="createForm.processing" class="cursor-pointer disabled:cursor-not-allowed">
                            {{ createForm.processing ? 'Menyimpan...' : 'Simpan Bank' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Bank Modal -->
        <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <!-- Overlay -->
            <div class="fixed inset-0 bg-black/50" @click="showEditModal = false"></div>

            <!-- Modal Content -->
            <div class="relative mx-4 max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-lg bg-white p-6 shadow-xl dark:bg-gray-900">
                <!-- Header -->
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold">Edit Bank</h2>
                        <p class="text-sm text-muted-foreground">Update informasi bank pembayaran</p>
                    </div>
                    <button @click="showEditModal = false" class="cursor-pointer text-gray-500 hover:text-gray-700">
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <form @submit.prevent="submitEdit" class="space-y-4">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <!-- Bank Name -->
                        <div>
                            <Label for="edit-bank-name">Nama Bank *</Label>
                            <Input
                                id="edit-bank-name"
                                v-model="editForm.bank_name"
                                placeholder="Contoh: Bank Central Asia"
                                :class="{ 'border-red-500': editForm.errors.bank_name }"
                                required
                            />
                            <p v-if="editForm.errors.bank_name" class="mt-1 text-xs text-red-500">{{ editForm.errors.bank_name }}</p>
                        </div>

                        <!-- Bank Code -->
                        <div>
                            <Label for="edit-bank-code">Kode Bank *</Label>
                            <Input
                                id="edit-bank-code"
                                v-model="editForm.bank_code"
                                placeholder="Contoh: BCA"
                                :class="{ 'border-red-500': editForm.errors.bank_code }"
                                required
                            />
                            <p v-if="editForm.errors.bank_code" class="mt-1 text-xs text-red-500">{{ editForm.errors.bank_code }}</p>
                        </div>

                        <!-- Account Number -->
                        <div>
                            <Label for="edit-account-number">Nomor Rekening *</Label>
                            <Input
                                id="edit-account-number"
                                v-model="editForm.account_number"
                                placeholder="Contoh: 1234567890"
                                :class="{ 'border-red-500': editForm.errors.account_number }"
                                required
                            />
                            <p v-if="editForm.errors.account_number" class="mt-1 text-xs text-red-500">{{ editForm.errors.account_number }}</p>
                        </div>

                        <!-- Account Name -->
                        <div>
                            <Label for="edit-account-name">Nama Pemilik Rekening *</Label>
                            <Input
                                id="edit-account-name"
                                v-model="editForm.account_name"
                                placeholder="Contoh: PT. Contoh Indonesia"
                                :class="{ 'border-red-500': editForm.errors.account_name }"
                                required
                            />
                            <p v-if="editForm.errors.account_name" class="mt-1 text-xs text-red-500">{{ editForm.errors.account_name }}</p>
                        </div>

                        <!-- Branch -->
                        <div>
                            <Label for="edit-branch">Cabang</Label>
                            <Input
                                id="edit-branch"
                                v-model="editForm.branch"
                                placeholder="Contoh: Jakarta Pusat"
                                :class="{ 'border-red-500': editForm.errors.branch }"
                            />
                            <p v-if="editForm.errors.branch" class="mt-1 text-xs text-red-500">{{ editForm.errors.branch }}</p>
                        </div>

                        <!-- SWIFT Code -->
                        <div>
                            <Label for="edit-swift-code">Kode SWIFT</Label>
                            <Input
                                id="edit-swift-code"
                                v-model="editForm.swift_code"
                                placeholder="Contoh: CENAIDJA"
                                :class="{ 'border-red-500': editForm.errors.swift_code }"
                            />
                            <p v-if="editForm.errors.swift_code" class="mt-1 text-xs text-red-500">{{ editForm.errors.swift_code }}</p>
                        </div>

                        <!-- Bank Type -->
                        <div>
                            <Label for="edit-bank-type">Tipe Bank *</Label>
                            <select
                                v-model="editForm.bank_type"
                                id="edit-bank-type"
                                :class="[
                                    'flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm text-foreground ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50 dark:bg-gray-800 dark:text-white',
                                    { 'border-red-500': editForm.errors.bank_type },
                                ]"
                                required
                            >
                                <option value="" disabled>Pilih tipe bank</option>
                                <option value="local">Lokal</option>
                                <option value="international">Internasional</option>
                            </select>
                            <p v-if="editForm.errors.bank_type" class="mt-1 text-xs text-red-500">{{ editForm.errors.bank_type }}</p>
                        </div>

                        <!-- Admin Fee -->
                        <div>
                            <Label for="edit-admin-fee">Biaya Admin</Label>
                            <Input
                                id="edit-admin-fee"
                                v-model.number="editForm.admin_fee"
                                type="number"
                                min="0"
                                step="0.01"
                                placeholder="0"
                                :class="{ 'border-red-500': editForm.errors.admin_fee }"
                            />
                            <p v-if="editForm.errors.admin_fee" class="mt-1 text-xs text-red-500">{{ editForm.errors.admin_fee }}</p>
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <Label for="edit-description">Deskripsi</Label>
                        <textarea
                            id="edit-description"
                            v-model="editForm.description"
                            placeholder="Deskripsi tambahan tentang bank ini..."
                            rows="3"
                            :class="[
                                'flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50',
                                { 'border-red-500': editForm.errors.description },
                            ]"
                        />
                        <p v-if="editForm.errors.description" class="mt-1 text-xs text-red-500">{{ editForm.errors.description }}</p>
                    </div>

                    <!-- Active Status -->
                    <div class="flex items-center space-x-2">
                        <Switch id="edit-is-active" v-model:checked="editForm.is_active" />
                        <Label for="edit-is-active">Bank Aktif</Label>
                    </div>

                    <div class="flex justify-end space-x-2 pt-4">
                        <Button type="button" variant="outline" @click="showEditModal = false" class="cursor-pointer"> Batal </Button>
                        <Button type="submit" :disabled="editForm.processing" class="cursor-pointer disabled:cursor-not-allowed">
                            {{ editForm.processing ? 'Memperbarui...' : 'Update Bank' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <!-- Overlay -->
            <div class="fixed inset-0 bg-black/50" @click="showDeleteModal = false"></div>

            <!-- Modal Content -->
            <div class="relative mx-4 w-full max-w-md rounded-lg bg-white p-6 shadow-xl dark:bg-gray-900">
                <!-- Header -->
                <div class="mb-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="mx-auto flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/20">
                            <Trash2 class="h-5 w-5 text-red-600 dark:text-red-400" />
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Hapus Bank</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Tindakan ini tidak dapat dibatalkan</p>
                        </div>
                    </div>
                    <button @click="showDeleteModal = false" class="cursor-pointer text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <!-- Content -->
                <div class="mb-6">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Apakah Anda yakin ingin menghapus bank
                        <span class="font-semibold text-gray-900 dark:text-gray-100">{{ bankToDelete?.bank_name }}</span
                        >?
                    </p>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Data bank ini akan dihapus secara permanen dan tidak dapat dikembalikan.
                    </p>
                </div>

                <!-- Actions -->
                <div class="flex justify-end space-x-2">
                    <Button type="button" variant="outline" @click="showDeleteModal = false" class="cursor-pointer"> Batal </Button>
                    <Button type="button" class="cursor-pointer bg-red-600 text-white hover:bg-red-700" @click="confirmDelete"> Hapus Bank </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
