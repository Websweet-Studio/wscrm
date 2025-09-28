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
import { Edit, Plus, Search, Trash2, X, ChevronUp, ChevronDown } from 'lucide-vue-next';
import { ref } from 'vue';

interface DomainPrice {
    id: number;
    extension: string;
    base_cost: number;
    renewal_cost: number;
    selling_price: number;
    renewal_price_with_tax: number;
    is_active: boolean;
}

interface Props {
    domainPrices: {
        data: DomainPrice[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: any[];
    };
    filters: {
        search?: string;
    };
    sort?: string;
    direction?: string;
}

const props = defineProps<Props>();

const search = ref(props.filters.search || '');
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const selectedDomain = ref<DomainPrice | null>(null);
const domainToDelete = ref<DomainPrice | null>(null);

const createForm = useForm({
    extension: '',
    base_cost: '',
    renewal_cost: '',
    selling_price: '',
    renewal_price_with_tax: '',
    is_active: true,
});

const editForm = useForm({
    extension: '',
    base_cost: '',
    renewal_cost: '',
    selling_price: '',
    renewal_price_with_tax: '',
    is_active: true,
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Harga Domain', href: '/admin/domain-prices' },
];

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(price);
};

const handleSearch = () => {
    router.get(
        '/admin/domain-prices',
        {
            search: search.value,
            sort: props.sort,
            direction: props.direction,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};

const sortBy = (field: string) => {
    let direction = 'asc';

    // If clicking the same field, toggle direction
    if (props.sort === field) {
        direction = props.direction === 'asc' ? 'desc' : 'asc';
    }

    router.get(
        '/admin/domain-prices',
        {
            search: search.value,
            sort: field,
            direction: direction,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};

const submitCreate = () => {
    createForm.post('/admin/domain-prices', {
        onSuccess: () => {
            showCreateModal.value = false;
            createForm.reset();
        },
    });
};

const openEditModal = (domain: DomainPrice) => {
    selectedDomain.value = domain;
    editForm.reset();
    editForm.extension = domain.extension;
    editForm.base_cost = domain.base_cost;
    editForm.renewal_cost = domain.renewal_cost;
    editForm.selling_price = domain.selling_price;
    editForm.renewal_price_with_tax = domain.renewal_price_with_tax;
    editForm.is_active = domain.is_active;
    showEditModal.value = true;
};

const submitEdit = () => {
    if (!selectedDomain.value) return;

    editForm.put(`/admin/domain-prices/${selectedDomain.value.id}`, {
        onSuccess: () => {
            showEditModal.value = false;
            editForm.reset();
            selectedDomain.value = null;
        },
    });
};

const openDeleteModal = (domain: DomainPrice) => {
    domainToDelete.value = domain;
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    if (!domainToDelete.value) return;

    router.delete(`/admin/domain-prices/${domainToDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
            domainToDelete.value = null;
        },
        onError: (errors) => {
            console.error('Delete domain price error:', errors);
        },
    });
};
</script>

<template>
    <Head title="Kelola Harga Domain" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Harga Domain</h1>
                    <p class="text-muted-foreground">Kelola harga ekstensi domain</p>
                </div>
                <Button @click="showCreateModal = true" class="cursor-pointer">
                    <Plus class="mr-2 h-4 w-4" />
                    Tambah Harga Domain
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Harga Domain</CardTitle>
                    <CardDescription> Kelola harga untuk berbagai ekstensi domain </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="mb-4 flex items-center space-x-2">
                        <div class="relative max-w-sm flex-1">
                            <Search class="absolute top-2.5 left-2.5 h-4 w-4 text-muted-foreground" />
                            <Input v-model="search" placeholder="Cari ekstensi domain..." class="pl-8" @keyup.enter="handleSearch" />
                        </div>
                        <Button @click="handleSearch" class="cursor-pointer">Cari</Button>
                    </div>

                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>
                                        <button
                                            @click="sortBy('extension')"
                                            class="flex items-center space-x-1 hover:text-foreground cursor-pointer"
                                        >
                                            <span>Ekstensi</span>
                                            <ChevronUp
                                                v-if="props.sort === 'extension' && props.direction === 'asc'"
                                                class="h-4 w-4"
                                            />
                                            <ChevronDown
                                                v-else-if="props.sort === 'extension' && props.direction === 'desc'"
                                                class="h-4 w-4"
                                            />
                                            <div v-else class="h-4 w-4"></div>
                                        </button>
                                    </TableHead>
                                    <TableHead>
                                        <button
                                            @click="sortBy('base_cost')"
                                            class="flex items-center space-x-1 hover:text-foreground cursor-pointer"
                                        >
                                            <span>Biaya Dasar</span>
                                            <ChevronUp
                                                v-if="props.sort === 'base_cost' && props.direction === 'asc'"
                                                class="h-4 w-4"
                                            />
                                            <ChevronDown
                                                v-else-if="props.sort === 'base_cost' && props.direction === 'desc'"
                                                class="h-4 w-4"
                                            />
                                            <div v-else class="h-4 w-4"></div>
                                        </button>
                                    </TableHead>
                                    <TableHead>
                                        <button
                                            @click="sortBy('renewal_cost')"
                                            class="flex items-center space-x-1 hover:text-foreground cursor-pointer"
                                        >
                                            <span>Biaya Perpanjangan</span>
                                            <ChevronUp
                                                v-if="props.sort === 'renewal_cost' && props.direction === 'asc'"
                                                class="h-4 w-4"
                                            />
                                            <ChevronDown
                                                v-else-if="props.sort === 'renewal_cost' && props.direction === 'desc'"
                                                class="h-4 w-4"
                                            />
                                            <div v-else class="h-4 w-4"></div>
                                        </button>
                                    </TableHead>
                                    <TableHead>
                                        <button
                                            @click="sortBy('selling_price')"
                                            class="flex items-center space-x-1 hover:text-foreground cursor-pointer"
                                        >
                                            <span>Harga Jual</span>
                                            <ChevronUp
                                                v-if="props.sort === 'selling_price' && props.direction === 'asc'"
                                                class="h-4 w-4"
                                            />
                                            <ChevronDown
                                                v-else-if="props.sort === 'selling_price' && props.direction === 'desc'"
                                                class="h-4 w-4"
                                            />
                                            <div v-else class="h-4 w-4"></div>
                                        </button>
                                    </TableHead>
                                    <TableHead>
                                        <button
                                            @click="sortBy('renewal_price_with_tax')"
                                            class="flex items-center space-x-1 hover:text-foreground cursor-pointer"
                                        >
                                            <span>Perpanjangan + Pajak</span>
                                            <ChevronUp
                                                v-if="props.sort === 'renewal_price_with_tax' && props.direction === 'asc'"
                                                class="h-4 w-4"
                                            />
                                            <ChevronDown
                                                v-else-if="props.sort === 'renewal_price_with_tax' && props.direction === 'desc'"
                                                class="h-4 w-4"
                                            />
                                            <div v-else class="h-4 w-4"></div>
                                        </button>
                                    </TableHead>
                                    <TableHead>
                                        <button
                                            @click="sortBy('is_active')"
                                            class="flex items-center space-x-1 hover:text-foreground cursor-pointer"
                                        >
                                            <span>Status</span>
                                            <ChevronUp
                                                v-if="props.sort === 'is_active' && props.direction === 'asc'"
                                                class="h-4 w-4"
                                            />
                                            <ChevronDown
                                                v-else-if="props.sort === 'is_active' && props.direction === 'desc'"
                                                class="h-4 w-4"
                                            />
                                            <div v-else class="h-4 w-4"></div>
                                        </button>
                                    </TableHead>
                                    <TableHead class="w-[100px]">Aksi</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="domain in domainPrices.data" :key="domain.id">
                                    <TableCell class="font-medium">.{{ domain.extension }}</TableCell>
                                    <TableCell>{{ formatPrice(domain.base_cost) }}</TableCell>
                                    <TableCell>{{ formatPrice(domain.renewal_cost) }}</TableCell>
                                    <TableCell>{{ formatPrice(domain.selling_price) }}</TableCell>
                                    <TableCell>{{ formatPrice(domain.renewal_price_with_tax) }}</TableCell>
                                    <TableCell>
                                        <Badge :variant="domain.is_active ? 'default' : 'secondary'">
                                            {{ domain.is_active ? 'Aktif' : 'Nonaktif' }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex items-center space-x-2">
                                            <Button size="sm" variant="outline" @click="openEditModal(domain)" class="cursor-pointer">
                                                <Edit class="h-3 w-3" />
                                            </Button>
                                            <Button size="sm" variant="outline" @click="openDeleteModal(domain)" class="cursor-pointer">
                                                <Trash2 class="h-3 w-3" />
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <!-- Pagination -->
                    <div class="flex items-center justify-between pt-4">
                        <div class="text-sm text-muted-foreground">
                            Showing {{ (domainPrices.current_page - 1) * domainPrices.per_page + 1 }} to
                            {{ Math.min(domainPrices.current_page * domainPrices.per_page, domainPrices.total) }} of {{ domainPrices.total }} results
                        </div>
                        <div class="flex items-center space-x-2">
                            <template v-for="link in domainPrices.links" :key="link.label">
                                <Button
                                    v-if="link.url"
                                    variant="outline"
                                    size="sm"
                                    :disabled="!link.url"
                                    @click="router.visit(link.url)"
                                    v-html="link.label"
                                    class="cursor-pointer"
                                />
                            </template>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Create Domain Price Modal -->
        <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <!-- Overlay -->
            <div class="fixed inset-0 bg-black/50" @click="showCreateModal = false"></div>

            <!-- Modal Content -->
            <div class="relative mx-4 max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-lg bg-white p-6 shadow-xl dark:bg-gray-900">
                <!-- Header -->
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold">Tambah Harga Domain Baru</h2>
                        <p class="text-sm text-muted-foreground">Buat konfigurasi harga domain baru</p>
                    </div>
                    <button @click="showCreateModal = false" class="cursor-pointer text-gray-500 hover:text-gray-700">
                        <X class="h-4 w-4" />
                    </button>
                </div>
                <form @submit.prevent="submitCreate" class="space-y-4">
                    <div>
                        <Label for="create-extension">Ekstensi Domain *</Label>
                        <Input
                            id="create-extension"
                            v-model="createForm.extension"
                            placeholder="com"
                            :class="{ 'border-red-500': createForm.errors.extension }"
                            required
                        />
                        <p class="mt-1 text-sm text-muted-foreground">Masukkan tanpa titik (contoh: "com" bukan ".com")</p>
                        <p v-if="createForm.errors.extension" class="mt-1 text-xs text-red-500">{{ createForm.errors.extension }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <Label for="create-base-cost">Biaya Dasar (IDR) *</Label>
                            <Input
                                id="create-base-cost"
                                v-model="createForm.base_cost"
                                type="number"
                                step="0.01"
                                min="0"
                                :class="{ 'border-red-500': createForm.errors.base_cost }"
                                required
                            />
                            <p v-if="createForm.errors.base_cost" class="mt-1 text-xs text-red-500">{{ createForm.errors.base_cost }}</p>
                        </div>
                        <div>
                            <Label for="create-renewal-cost">Biaya Perpanjangan (IDR) *</Label>
                            <Input
                                id="create-renewal-cost"
                                v-model="createForm.renewal_cost"
                                type="number"
                                step="0.01"
                                min="0"
                                :class="{ 'border-red-500': createForm.errors.renewal_cost }"
                                required
                            />
                            <p v-if="createForm.errors.renewal_cost" class="mt-1 text-xs text-red-500">{{ createForm.errors.renewal_cost }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <Label for="create-selling-price">Harga Jual (IDR) *</Label>
                            <Input
                                id="create-selling-price"
                                v-model="createForm.selling_price"
                                type="number"
                                step="0.01"
                                min="0"
                                :class="{ 'border-red-500': createForm.errors.selling_price }"
                                required
                            />
                            <p v-if="createForm.errors.selling_price" class="mt-1 text-xs text-red-500">{{ createForm.errors.selling_price }}</p>
                        </div>
                        <div>
                            <Label for="create-renewal-price-tax">Harga Perpanjangan + Pajak (IDR) *</Label>
                            <Input
                                id="create-renewal-price-tax"
                                v-model="createForm.renewal_price_with_tax"
                                type="number"
                                step="0.01"
                                min="0"
                                :class="{ 'border-red-500': createForm.errors.renewal_price_with_tax }"
                                required
                            />
                            <p v-if="createForm.errors.renewal_price_with_tax" class="mt-1 text-xs text-red-500">
                                {{ createForm.errors.renewal_price_with_tax }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center space-x-2">
                        <input id="create-is-active" type="checkbox" v-model="createForm.is_active" class="rounded border border-input" />
                        <Label for="create-is-active">Aktif</Label>
                    </div>

                    <div class="flex justify-end space-x-2 pt-4">
                        <Button type="button" variant="outline" @click="showCreateModal = false" class="cursor-pointer"> Batal </Button>
                        <Button type="submit" :disabled="createForm.processing">
                            {{ createForm.processing ? 'Membuat...' : 'Buat Harga Domain' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Domain Price Modal -->
        <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <!-- Overlay -->
            <div class="fixed inset-0 bg-black/50" @click="showEditModal = false"></div>

            <!-- Modal Content -->
            <div class="relative mx-4 max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-lg bg-white p-6 shadow-xl dark:bg-gray-900">
                <!-- Header -->
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold">Edit Harga Domain</h2>
                        <p class="text-sm text-muted-foreground">Perbarui pengaturan harga domain</p>
                    </div>
                    <button @click="showEditModal = false" class="cursor-pointer text-gray-500 hover:text-gray-700">
                        <X class="h-4 w-4" />
                    </button>
                </div>
                <form @submit.prevent="submitEdit" class="space-y-4">
                    <div>
                        <Label for="edit-extension">Ekstensi Domain *</Label>
                        <Input
                            id="edit-extension"
                            v-model="editForm.extension"
                            placeholder="com"
                            :class="{ 'border-red-500': editForm.errors.extension }"
                            required
                        />
                        <p class="mt-1 text-sm text-muted-foreground">Masukkan tanpa titik (contoh: "com" bukan ".com")</p>
                        <p v-if="editForm.errors.extension" class="mt-1 text-xs text-red-500">{{ editForm.errors.extension }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <Label for="edit-base-cost">Biaya Dasar (IDR) *</Label>
                            <Input
                                id="edit-base-cost"
                                v-model="editForm.base_cost"
                                type="number"
                                step="0.01"
                                min="0"
                                :class="{ 'border-red-500': editForm.errors.base_cost }"
                                required
                            />
                            <p v-if="editForm.errors.base_cost" class="mt-1 text-xs text-red-500">{{ editForm.errors.base_cost }}</p>
                        </div>
                        <div>
                            <Label for="edit-renewal-cost">Biaya Perpanjangan (IDR) *</Label>
                            <Input
                                id="edit-renewal-cost"
                                v-model="editForm.renewal_cost"
                                type="number"
                                step="0.01"
                                min="0"
                                :class="{ 'border-red-500': editForm.errors.renewal_cost }"
                                required
                            />
                            <p v-if="editForm.errors.renewal_cost" class="mt-1 text-xs text-red-500">{{ editForm.errors.renewal_cost }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <Label for="edit-selling-price">Harga Jual (IDR) *</Label>
                            <Input
                                id="edit-selling-price"
                                v-model="editForm.selling_price"
                                type="number"
                                step="0.01"
                                min="0"
                                :class="{ 'border-red-500': editForm.errors.selling_price }"
                                required
                            />
                            <p v-if="editForm.errors.selling_price" class="mt-1 text-xs text-red-500">{{ editForm.errors.selling_price }}</p>
                        </div>
                        <div>
                            <Label for="edit-renewal-price-tax">Harga Perpanjangan + Pajak (IDR) *</Label>
                            <Input
                                id="edit-renewal-price-tax"
                                v-model="editForm.renewal_price_with_tax"
                                type="number"
                                step="0.01"
                                min="0"
                                :class="{ 'border-red-500': editForm.errors.renewal_price_with_tax }"
                                required
                            />
                            <p v-if="editForm.errors.renewal_price_with_tax" class="mt-1 text-xs text-red-500">
                                {{ editForm.errors.renewal_price_with_tax }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center space-x-2">
                        <input id="edit-is-active" type="checkbox" v-model="editForm.is_active" class="rounded border border-input" />
                        <Label for="edit-is-active">Aktif</Label>
                    </div>

                    <div class="flex justify-end space-x-2 pt-4">
                        <Button type="button" variant="outline" @click="showEditModal = false" class="cursor-pointer"> Batal </Button>
                        <Button type="submit" :disabled="editForm.processing">
                            {{ editForm.processing ? 'Memperbarui...' : 'Perbarui Harga Domain' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Domain Price Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <!-- Overlay -->
            <div class="fixed inset-0 bg-black/50" @click="showDeleteModal = false"></div>

            <!-- Modal Content -->
            <div class="relative mx-4 w-full max-w-md rounded-lg bg-white p-6 shadow-xl dark:bg-gray-900">
                <!-- Header -->
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-red-600">Konfirmasi Penghapusan</h2>
                    <button @click="showDeleteModal = false" class="cursor-pointer text-gray-500 hover:text-gray-700">
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <div class="space-y-4">
                    <div class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        fill-rule="evenodd"
                                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <h3 class="text-sm font-medium text-red-800 dark:text-red-200">Peringatan: Tindakan ini tidak dapat dibatalkan</h3>
                                <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                                    <p>
                                        Anda akan menghapus secara permanen harga domain <strong>.{{ domainToDelete?.extension }}</strong
                                        >.
                                    </p>
                                    <div class="mt-3 space-y-1">
                                        <p><strong>Ini juga akan menghapus:</strong></p>
                                        <ul class="ml-2 list-inside list-disc space-y-1">
                                            <li>Semua konfigurasi harga</li>
                                            <li>Semua riwayat transaksi terkait</li>
                                            <li>Semua data terkait secara permanen</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            <strong>Ekstensi:</strong> .{{ domainToDelete?.extension }}<br />
                            <strong>Biaya Dasar:</strong> {{ domainToDelete ? formatPrice(domainToDelete.base_cost) : '' }}<br />
                            <strong>Harga Jual:</strong> {{ domainToDelete ? formatPrice(domainToDelete.selling_price) : '' }}<br />
                            <strong>Status:</strong> {{ domainToDelete?.is_active ? 'Aktif' : 'Nonaktif' }}
                        </p>
                    </div>
                </div>

                <!-- Footer -->
                <div class="mt-6 flex justify-end gap-2">
                    <Button type="button" variant="outline" @click="showDeleteModal = false" class="cursor-pointer"> Batal </Button>
                    <Button type="button" class="cursor-pointer bg-red-600 text-white hover:bg-red-700" @click="confirmDelete">
                        Ya, Hapus Harga Domain
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
"}]
