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
import { Building2, Edit, Eye, Plus, ToggleLeft, ToggleRight, Trash2, X } from 'lucide-vue-next';
import { ref } from 'vue';

interface Bank {
    id: number;
    bank_name: string;
    account_number: string;
    account_name: string;
    is_active: boolean;
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

defineProps<Props>();

const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const selectedBank = ref<Bank | null>(null);
const bankToDelete = ref<Bank | null>(null);

interface BankForm {
    bank_name: string;
    account_number: string;
    account_name: string;
}

const createForm = useForm<BankForm>({
    bank_name: '',
    account_number: '',
    account_name: '',
});

const editForm = useForm<BankForm>({
    bank_name: '',
    account_number: '',
    account_name: '',
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Manajemen Bank', href: '/admin/banks' },
];

const getStatusClass = (isActive: boolean) => {
    return isActive ? 'bg-primary/10 text-primary' : 'bg-muted text-muted-foreground';
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
    editForm.account_number = bank.account_number;
    editForm.account_name = bank.account_name;
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
    <Head title="Admin - Manajemen Bank" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="w-full max-w-none space-y-4 sm:space-y-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-medium tracking-tight sm:text-3xl" style="font-family: Georgia, serif;">Manajemen Bank</h1>
                    <p class="text-sm text-muted-foreground sm:text-base">Kelola bank pembayaran untuk sistem invoice</p>
                </div>
                <Button @click="showCreateModal = true" class="w-full cursor-pointer sm:w-auto">
                    <Plus class="mr-2 h-4 w-4" />
                    <span class="hidden sm:inline">Tambah Bank</span>
                    <span class="sm:hidden">Tambah</span>
                </Button>
            </div>

            <!-- Banks Table -->
            <Card>
                <CardHeader>
                    <CardTitle style="font-family: Georgia, serif;" class="flex items-center gap-2">
                        <Building2 class="h-5 w-5" />
                        Daftar Bank
                    </CardTitle>
                    <CardDescription> Total {{ banks.total }} bank terdaftar </CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="banks.data.length > 0">
                        <div class="overflow-x-auto rounded-md border border-border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Bank</TableHead>
                                        <TableHead>Nomor Rekening</TableHead>
                                        <TableHead>Nama Pemilik</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead class="text-right">Aksi</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="bank in banks.data" :key="bank.id">
                                        <TableCell>
                                            <div class="font-medium">{{ bank.bank_name }}</div>
                                        </TableCell>
                                        <TableCell>
                                            <div class="font-mono text-sm">{{ bank.account_number }}</div>
                                        </TableCell>
                                        <TableCell>
                                            {{ bank.account_name }}
                                        </TableCell>
                                        <TableCell>
                                            <Badge :class="getStatusClass(bank.is_active)" class="text-xs">
                                                {{ bank.is_active ? 'Aktif' : 'Nonaktif' }}
                                            </Badge>
                                        </TableCell>
                                        <TableCell class="text-right">
                                            <div class="flex items-center justify-end gap-1">
                                                <Button
                                                    size="sm"
                                                    variant="outline"
                                                    @click="router.visit(`/admin/banks/${bank.id}`)"
                                                    class="cursor-pointer"
                                                    title="Lihat Detail"
                                                >
                                                    <Eye class="h-3.5 w-3.5" />
                                                </Button>
                                                <Button size="sm" variant="outline" @click="openEditModal(bank)" class="cursor-pointer" title="Edit">
                                                    <Edit class="h-3.5 w-3.5" />
                                                </Button>
                                                <Button
                                                    size="sm"
                                                    variant="outline"
                                                    @click="toggleBankStatus(bank)"
                                                    :title="bank.is_active ? 'Nonaktifkan' : 'Aktifkan'"
                                                    class="cursor-pointer"
                                                >
                                                    <ToggleRight v-if="bank.is_active" class="h-3.5 w-3.5 text-primary" />
                                                    <ToggleLeft v-else class="h-3.5 w-3.5 text-muted-foreground" />
                                                </Button>
                                                <Button
                                                    size="sm"
                                                    variant="outline"
                                                    @click="openDeleteModal(bank)"
                                                    :title="'Hapus ' + bank.bank_name"
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
                        <div v-if="banks.last_page > 1" class="mt-4 flex items-center justify-center space-x-2">
                            <Button
                                v-for="link in banks.links"
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
                    <CardTitle style="font-family: Georgia, serif;">Tambah Bank</CardTitle>
                    <CardDescription>Tambahkan bank pembayaran baru ke sistem</CardDescription>
                </CardHeader>
                <CardContent class="pb-6">
                    <form @submit.prevent="submitCreate" class="space-y-4">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <!-- Bank Name -->
                        <div>
                            <Label for="create-bank-name">Nama Bank *</Label>
                            <Input
                                id="create-bank-name"
                                v-model="createForm.bank_name"
                                placeholder="Contoh: Bank Central Asia"
                                :aria-invalid="!!createForm.errors.bank_name"
                                required
                            />
                            <p v-if="createForm.errors.bank_name" class="mt-1 text-xs text-destructive">{{ createForm.errors.bank_name }}</p>
                        </div>

                        <!-- Account Number -->
                        <div>
                            <Label for="create-account-number">Nomor Rekening *</Label>
                            <Input
                                id="create-account-number"
                                v-model="createForm.account_number"
                                placeholder="Contoh: 1234567890"
                                :aria-invalid="!!createForm.errors.account_number"
                                required
                            />
                            <p v-if="createForm.errors.account_number" class="mt-1 text-xs text-destructive">{{ createForm.errors.account_number }}</p>
                        </div>

                        <!-- Account Name -->
                        <div>
                            <Label for="create-account-name">Nama Pemilik Rekening *</Label>
                            <Input
                                id="create-account-name"
                                v-model="createForm.account_name"
                                placeholder="Contoh: PT. Contoh Indonesia"
                                :aria-invalid="!!createForm.errors.account_name"
                                required
                            />
                            <p v-if="createForm.errors.account_name" class="mt-1 text-xs text-destructive">{{ createForm.errors.account_name }}</p>
                        </div>
                    </div>

                    <div class="flex flex-col-reverse gap-2 pt-4 sm:flex-row sm:justify-end">
                        <Button type="button" variant="outline" @click="showCreateModal = false" class="w-full cursor-pointer sm:w-auto">Batal</Button>
                        <Button type="submit" :disabled="createForm.processing" class="w-full cursor-pointer disabled:cursor-not-allowed sm:w-auto">
                            {{ createForm.processing ? 'Menyimpan...' : 'Simpan Bank' }}
                        </Button>
                    </div>
                </form>
                </CardContent>
            </Card>
        </div>

        <!-- Edit Bank Modal -->
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
                    <CardTitle style="font-family: Georgia, serif;">Edit Bank</CardTitle>
                    <CardDescription>Update informasi bank pembayaran</CardDescription>
                </CardHeader>
                <CardContent class="pb-6">
                    <form @submit.prevent="submitEdit" class="space-y-4">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <!-- Bank Name -->
                        <div>
                            <Label for="edit-bank-name">Nama Bank *</Label>
                            <Input
                                id="edit-bank-name"
                                v-model="editForm.bank_name"
                                placeholder="Contoh: Bank Central Asia"
                                :aria-invalid="!!editForm.errors.bank_name"
                                required
                            />
                            <p v-if="editForm.errors.bank_name" class="mt-1 text-xs text-destructive">{{ editForm.errors.bank_name }}</p>
                        </div>

                        <!-- Account Number -->
                        <div>
                            <Label for="edit-account-number">Nomor Rekening *</Label>
                            <Input
                                id="edit-account-number"
                                v-model="editForm.account_number"
                                placeholder="Contoh: 1234567890"
                                :aria-invalid="!!editForm.errors.account_number"
                                required
                            />
                            <p v-if="editForm.errors.account_number" class="mt-1 text-xs text-destructive">{{ editForm.errors.account_number }}</p>
                        </div>

                        <!-- Account Name -->
                        <div>
                            <Label for="edit-account-name">Nama Pemilik Rekening *</Label>
                            <Input
                                id="edit-account-name"
                                v-model="editForm.account_name"
                                placeholder="Contoh: PT. Contoh Indonesia"
                                :aria-invalid="!!editForm.errors.account_name"
                                required
                            />
                            <p v-if="editForm.errors.account_name" class="mt-1 text-xs text-destructive">{{ editForm.errors.account_name }}</p>
                        </div>
                    </div>

                    <div class="flex flex-col-reverse gap-2 pt-4 sm:flex-row sm:justify-end">
                        <Button type="button" variant="outline" @click="showEditModal = false" class="w-full cursor-pointer sm:w-auto">Batal</Button>
                        <Button type="submit" :disabled="editForm.processing" class="w-full cursor-pointer disabled:cursor-not-allowed sm:w-auto">
                            {{ editForm.processing ? 'Memperbarui...' : 'Update Bank' }}
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
                        Hapus Bank
                    </CardTitle>
                    <CardDescription>Tindakan ini tidak dapat dibatalkan</CardDescription>
                </CardHeader>
                <CardContent class="pb-6">
                    <div class="space-y-2 text-sm text-muted-foreground">
                        <p>
                            Apakah Anda yakin ingin menghapus bank
                            <span class="font-medium text-foreground">{{ bankToDelete?.bank_name }}</span>?
                        </p>
                        <p>Data bank ini akan dihapus secara permanen dan tidak dapat dikembalikan.</p>
                    </div>

                    <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                        <Button type="button" variant="outline" @click="showDeleteModal = false" class="w-full cursor-pointer sm:w-auto">Batal</Button>
                        <Button type="button" variant="destructive" class="w-full cursor-pointer sm:w-auto" @click="confirmDelete">Hapus Bank</Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
