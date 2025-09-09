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
import { Cpu, Edit, HardDrive, MemoryStick, Plus, Search, Trash2, X } from 'lucide-vue-next';
import { ref } from 'vue';

interface HostingPlan {
    id: number;
    plan_name: string;
    storage_gb: number;
    cpu_cores: number;
    ram_gb: number;
    bandwidth: string;
    modal_cost: number;
    maintenance_cost: number;
    discount_percent: number;
    selling_price: number;
    features: string[];
    is_active: boolean;
}

interface Props {
    hostingPlans: {
        data: HostingPlan[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: any[];
    };
    filters: {
        search?: string;
    };
}

const props = defineProps<Props>();

const search = ref(props.filters.search || '');
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const selectedPlan = ref<HostingPlan | null>(null);
const planToDelete = ref<HostingPlan | null>(null);

const createForm = useForm({
    plan_name: '',
    storage_gb: '',
    cpu_cores: '',
    ram_gb: '',
    bandwidth: '',
    modal_cost: '',
    maintenance_cost: '',
    discount_percent: '0',
    selling_price: '',
    features: [''],
    is_active: true,
});

const editForm = useForm({
    plan_name: '',
    storage_gb: '',
    cpu_cores: '',
    ram_gb: '',
    bandwidth: '',
    modal_cost: '',
    maintenance_cost: '',
    discount_percent: '0',
    selling_price: '',
    features: [''],
    is_active: true,
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Paket Hosting', href: '/admin/hosting-plans' },
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
        '/admin/hosting-plans',
        { search: search.value },
        {
            preserveState: true,
            replace: true,
        },
    );
};

const addFeature = (form: any) => {
    form.features.push('');
};

const removeFeature = (form: any, index: number) => {
    form.features.splice(index, 1);
};

const submitCreate = () => {
    createForm.post('/admin/hosting-plans', {
        onSuccess: () => {
            showCreateModal.value = false;
            createForm.reset();
            createForm.features = [''];
        },
    });
};

const openEditModal = (plan: HostingPlan) => {
    selectedPlan.value = plan;
    editForm.reset();
    editForm.plan_name = plan.plan_name;
    editForm.storage_gb = plan.storage_gb;
    editForm.cpu_cores = plan.cpu_cores;
    editForm.ram_gb = plan.ram_gb;
    editForm.bandwidth = plan.bandwidth;
    editForm.modal_cost = plan.modal_cost;
    editForm.maintenance_cost = plan.maintenance_cost;
    editForm.discount_percent = plan.discount_percent;
    editForm.selling_price = plan.selling_price;
    editForm.features = plan.features.length > 0 ? [...plan.features] : [''];
    editForm.is_active = plan.is_active;
    showEditModal.value = true;
};

const submitEdit = () => {
    if (!selectedPlan.value) return;

    editForm.put(`/admin/hosting-plans/${selectedPlan.value.id}`, {
        onSuccess: () => {
            showEditModal.value = false;
            editForm.reset();
            selectedPlan.value = null;
        },
    });
};

const openDeleteModal = (plan: HostingPlan) => {
    planToDelete.value = plan;
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    if (!planToDelete.value) return;

    router.delete(`/admin/hosting-plans/${planToDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
            planToDelete.value = null;
        },
        onError: (errors) => {
            console.error('Delete hosting plan error:', errors);
        },
    });
};
</script>

<template>
    <Head title="Kelola Paket Hosting" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Paket Hosting</h1>
                    <p class="text-muted-foreground">Kelola konfigurasi paket hosting</p>
                </div>
                <Button @click="showCreateModal = true" class="cursor-pointer">
                    <Plus class="mr-2 h-4 w-4" />
                    Tambah Paket Hosting
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Paket Hosting</CardTitle>
                    <CardDescription> Kelola paket hosting dan spesifikasinya </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="mb-4 flex items-center space-x-2">
                        <div class="relative max-w-sm flex-1">
                            <Search class="absolute top-2.5 left-2.5 h-4 w-4 text-muted-foreground" />
                            <Input v-model="search" placeholder="Cari paket hosting..." class="pl-8" @keyup.enter="handleSearch" />
                        </div>
                        <Button @click="handleSearch" class="cursor-pointer">Cari</Button>
                    </div>

                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Nama Paket</TableHead>
                                    <TableHead>Spesifikasi</TableHead>
                                    <TableHead>Harga Jual</TableHead>
                                    <TableHead>Biaya</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead class="w-[100px]">Aksi</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="plan in hostingPlans.data" :key="plan.id">
                                    <TableCell class="font-medium">{{ plan.plan_name }}</TableCell>
                                    <TableCell>
                                        <div class="flex flex-wrap gap-2 text-xs">
                                            <div class="flex items-center space-x-1 rounded bg-muted px-2 py-1">
                                                <HardDrive class="h-3 w-3" />
                                                <span>{{ plan.storage_gb }}GB Penyimpanan</span>
                                            </div>
                                            <div class="flex items-center space-x-1 rounded bg-muted px-2 py-1">
                                                <Cpu class="h-3 w-3" />
                                                <span>{{ plan.cpu_cores }} CPU</span>
                                            </div>
                                            <div class="flex items-center space-x-1 rounded bg-muted px-2 py-1">
                                                <MemoryStick class="h-3 w-3" />
                                                <span>{{ plan.ram_gb }}GB RAM</span>
                                            </div>
                                        </div>
                                    </TableCell>
                                    <TableCell>{{ formatPrice(plan.selling_price) }}</TableCell>
                                    <TableCell>
                                        <div class="space-y-1 text-xs">
                                            <div>Modal: {{ formatPrice(plan.modal_cost) }}</div>
                                            <div>Maintenance: {{ formatPrice(plan.maintenance_cost) }}</div>
                                            <div v-if="plan.discount_percent > 0" class="text-green-600">{{ plan.discount_percent }}% diskon</div>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <Badge :variant="plan.is_active ? 'default' : 'secondary'">
                                            {{ plan.is_active ? 'Aktif' : 'Nonaktif' }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex items-center space-x-2">
                                            <Button size="sm" variant="outline" @click="openEditModal(plan)" class="cursor-pointer">
                                                <Edit class="h-3 w-3" />
                                            </Button>
                                            <Button size="sm" variant="outline" @click="openDeleteModal(plan)" class="cursor-pointer">
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
                            Showing {{ (hostingPlans.current_page - 1) * hostingPlans.per_page + 1 }} to
                            {{ Math.min(hostingPlans.current_page * hostingPlans.per_page, hostingPlans.total) }} of {{ hostingPlans.total }} results
                        </div>
                        <div class="flex items-center space-x-2">
                            <template v-for="link in hostingPlans.links" :key="link.label">
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

        <!-- Create Hosting Plan Modal -->
        <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <!-- Overlay -->
            <div class="fixed inset-0 bg-black/50" @click="showCreateModal = false"></div>

            <!-- Modal Content -->
            <div class="relative mx-4 max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-lg bg-white p-6 shadow-xl dark:bg-gray-900">
                <!-- Header -->
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold">Tambah Paket Hosting Baru</h2>
                        <p class="text-sm text-muted-foreground">Buat paket hosting baru dengan spesifikasi dan harga</p>
                    </div>
                    <button @click="showCreateModal = false" class="cursor-pointer text-gray-500 hover:text-gray-700">
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <form @submit.prevent="submitCreate" class="space-y-4">
                    <div>
                        <Label for="create-plan-name">Nama Paket *</Label>
                        <Input
                            id="create-plan-name"
                            v-model="createForm.plan_name"
                            placeholder="Paket Basic"
                            :class="{ 'border-red-500': createForm.errors.plan_name }"
                            required
                        />
                        <p v-if="createForm.errors.plan_name" class="mt-1 text-xs text-red-500">{{ createForm.errors.plan_name }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <Label for="create-storage">Penyimpanan (GB) *</Label>
                            <Input
                                id="create-storage"
                                v-model="createForm.storage_gb"
                                type="number"
                                step="0.01"
                                min="0"
                                :class="{ 'border-red-500': createForm.errors.storage_gb }"
                                required
                            />
                            <p v-if="createForm.errors.storage_gb" class="mt-1 text-xs text-red-500">{{ createForm.errors.storage_gb }}</p>
                        </div>
                        <div>
                            <Label for="create-bandwidth">Bandwidth *</Label>
                            <Input
                                id="create-bandwidth"
                                v-model="createForm.bandwidth"
                                placeholder="Tidak Terbatas"
                                :class="{ 'border-red-500': createForm.errors.bandwidth }"
                                required
                            />
                            <p v-if="createForm.errors.bandwidth" class="mt-1 text-xs text-red-500">{{ createForm.errors.bandwidth }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <Label for="create-cpu">CPU Core *</Label>
                            <Input
                                id="create-cpu"
                                v-model="createForm.cpu_cores"
                                type="number"
                                step="0.1"
                                min="0.1"
                                :class="{ 'border-red-500': createForm.errors.cpu_cores }"
                                required
                            />
                            <p v-if="createForm.errors.cpu_cores" class="mt-1 text-xs text-red-500">{{ createForm.errors.cpu_cores }}</p>
                        </div>
                        <div>
                            <Label for="create-ram">RAM (GB) *</Label>
                            <Input
                                id="create-ram"
                                v-model="createForm.ram_gb"
                                type="number"
                                step="0.1"
                                min="0"
                                :class="{ 'border-red-500': createForm.errors.ram_gb }"
                                required
                            />
                            <p v-if="createForm.errors.ram_gb" class="mt-1 text-xs text-red-500">{{ createForm.errors.ram_gb }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <Label for="create-modal-cost">Biaya Modal (IDR) *</Label>
                            <Input
                                id="create-modal-cost"
                                v-model="createForm.modal_cost"
                                type="number"
                                step="0.01"
                                min="0"
                                :class="{ 'border-red-500': createForm.errors.modal_cost }"
                                required
                            />
                            <p v-if="createForm.errors.modal_cost" class="mt-1 text-xs text-red-500">{{ createForm.errors.modal_cost }}</p>
                        </div>
                        <div>
                            <Label for="create-maintenance-cost">Biaya Perawatan (IDR) *</Label>
                            <Input
                                id="create-maintenance-cost"
                                v-model="createForm.maintenance_cost"
                                type="number"
                                step="0.01"
                                min="0"
                                :class="{ 'border-red-500': createForm.errors.maintenance_cost }"
                                required
                            />
                            <p v-if="createForm.errors.maintenance_cost" class="mt-1 text-xs text-red-500">
                                {{ createForm.errors.maintenance_cost }}
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <Label for="create-discount">Persentase Diskon *</Label>
                            <Input
                                id="create-discount"
                                v-model="createForm.discount_percent"
                                type="number"
                                step="0.01"
                                min="0"
                                max="100"
                                :class="{ 'border-red-500': createForm.errors.discount_percent }"
                                required
                            />
                            <p v-if="createForm.errors.discount_percent" class="mt-1 text-xs text-red-500">
                                {{ createForm.errors.discount_percent }}
                            </p>
                        </div>
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
                    </div>

                    <div>
                        <div class="mb-2 flex items-center justify-between">
                            <Label>Fitur</Label>
                            <Button type="button" size="sm" @click="addFeature(createForm)" class="cursor-pointer">
                                <Plus class="mr-1 h-3 w-3" />
                                Tambah Fitur
                            </Button>
                        </div>
                        <div v-for="(feature, index) in createForm.features" :key="index" class="mb-2 flex items-center gap-2">
                            <Input v-model="createForm.features[index]" placeholder="Deskripsi fitur" class="flex-1" />
                            <Button
                                v-if="createForm.features.length > 1"
                                type="button"
                                size="sm"
                                variant="outline"
                                @click="removeFeature(createForm, index)"
                            >
                                <Trash2 class="h-3 w-3" />
                            </Button>
                        </div>
                    </div>

                    <div class="flex items-center space-x-2">
                        <input id="create-is-active" type="checkbox" v-model="createForm.is_active" class="rounded border border-input" />
                        <Label for="create-is-active">Aktif</Label>
                    </div>

                    <div class="flex justify-end space-x-2 pt-4">
                        <Button type="button" variant="outline" @click="showCreateModal = false" class="cursor-pointer"> Batal </Button>
                        <Button type="submit" :disabled="createForm.processing">
                            {{ createForm.processing ? 'Membuat...' : 'Buat Paket' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Hosting Plan Modal -->
        <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <!-- Overlay -->
            <div class="fixed inset-0 bg-black/50" @click="showEditModal = false"></div>

            <!-- Modal Content -->
            <div class="relative mx-4 max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-lg bg-white p-6 shadow-xl dark:bg-gray-900">
                <!-- Header -->
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold">Edit Paket Hosting</h2>
                        <p class="text-sm text-muted-foreground">Perbarui spesifikasi dan pengaturan paket hosting</p>
                    </div>
                    <button @click="showEditModal = false" class="cursor-pointer text-gray-500 hover:text-gray-700">
                        <X class="h-4 w-4" />
                    </button>
                </div>
                <form @submit.prevent="submitEdit" class="space-y-4">
                    <div>
                        <Label for="edit-plan-name">Nama Paket *</Label>
                        <Input
                            id="edit-plan-name"
                            v-model="editForm.plan_name"
                            placeholder="Paket Basic"
                            :class="{ 'border-red-500': editForm.errors.plan_name }"
                            required
                        />
                        <p v-if="editForm.errors.plan_name" class="mt-1 text-xs text-red-500">{{ editForm.errors.plan_name }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <Label for="edit-storage">Penyimpanan (GB) *</Label>
                            <Input
                                id="edit-storage"
                                v-model="editForm.storage_gb"
                                type="number"
                                step="0.01"
                                min="0"
                                :class="{ 'border-red-500': editForm.errors.storage_gb }"
                                required
                            />
                            <p v-if="editForm.errors.storage_gb" class="mt-1 text-xs text-red-500">{{ editForm.errors.storage_gb }}</p>
                        </div>
                        <div>
                            <Label for="edit-bandwidth">Bandwidth *</Label>
                            <Input
                                id="edit-bandwidth"
                                v-model="editForm.bandwidth"
                                placeholder="Tidak Terbatas"
                                :class="{ 'border-red-500': editForm.errors.bandwidth }"
                                required
                            />
                            <p v-if="editForm.errors.bandwidth" class="mt-1 text-xs text-red-500">{{ editForm.errors.bandwidth }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <Label for="edit-cpu">CPU Core *</Label>
                            <Input
                                id="edit-cpu"
                                v-model="editForm.cpu_cores"
                                type="number"
                                step="0.1"
                                min="0.1"
                                :class="{ 'border-red-500': editForm.errors.cpu_cores }"
                                required
                            />
                            <p v-if="editForm.errors.cpu_cores" class="mt-1 text-xs text-red-500">{{ editForm.errors.cpu_cores }}</p>
                        </div>
                        <div>
                            <Label for="edit-ram">RAM (GB) *</Label>
                            <Input
                                id="edit-ram"
                                v-model="editForm.ram_gb"
                                type="number"
                                step="0.1"
                                min="0"
                                :class="{ 'border-red-500': editForm.errors.ram_gb }"
                                required
                            />
                            <p v-if="editForm.errors.ram_gb" class="mt-1 text-xs text-red-500">{{ editForm.errors.ram_gb }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <Label for="edit-modal-cost">Biaya Modal (IDR) *</Label>
                            <Input
                                id="edit-modal-cost"
                                v-model="editForm.modal_cost"
                                type="number"
                                step="0.01"
                                min="0"
                                :class="{ 'border-red-500': editForm.errors.modal_cost }"
                                required
                            />
                            <p v-if="editForm.errors.modal_cost" class="mt-1 text-xs text-red-500">{{ editForm.errors.modal_cost }}</p>
                        </div>
                        <div>
                            <Label for="edit-maintenance-cost">Biaya Perawatan (IDR) *</Label>
                            <Input
                                id="edit-maintenance-cost"
                                v-model="editForm.maintenance_cost"
                                type="number"
                                step="0.01"
                                min="0"
                                :class="{ 'border-red-500': editForm.errors.maintenance_cost }"
                                required
                            />
                            <p v-if="editForm.errors.maintenance_cost" class="mt-1 text-xs text-red-500">{{ editForm.errors.maintenance_cost }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <Label for="edit-discount">Persentase Diskon *</Label>
                            <Input
                                id="edit-discount"
                                v-model="editForm.discount_percent"
                                type="number"
                                step="0.01"
                                min="0"
                                max="100"
                                :class="{ 'border-red-500': editForm.errors.discount_percent }"
                                required
                            />
                            <p v-if="editForm.errors.discount_percent" class="mt-1 text-xs text-red-500">{{ editForm.errors.discount_percent }}</p>
                        </div>
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
                    </div>

                    <div>
                        <div class="mb-2 flex items-center justify-between">
                            <Label>Fitur</Label>
                            <Button type="button" size="sm" @click="addFeature(editForm)" class="cursor-pointer">
                                <Plus class="mr-1 h-3 w-3" />
                                Tambah Fitur
                            </Button>
                        </div>
                        <div v-for="(feature, index) in editForm.features" :key="index" class="mb-2 flex items-center gap-2">
                            <Input v-model="editForm.features[index]" placeholder="Deskripsi fitur" class="flex-1" />
                            <Button
                                v-if="editForm.features.length > 1"
                                type="button"
                                size="sm"
                                variant="outline"
                                @click="removeFeature(editForm, index)"
                            >
                                <Trash2 class="h-3 w-3" />
                            </Button>
                        </div>
                    </div>

                    <div class="flex items-center space-x-2">
                        <input id="edit-is-active" type="checkbox" v-model="editForm.is_active" class="rounded border border-input" />
                        <Label for="edit-is-active">Aktif</Label>
                    </div>

                    <div class="flex justify-end space-x-2 pt-4">
                        <Button type="button" variant="outline" @click="showEditModal = false" class="cursor-pointer"> Batal </Button>
                        <Button type="submit" :disabled="editForm.processing">
                            {{ editForm.processing ? 'Memperbarui...' : 'Perbarui Paket' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Hosting Plan Modal -->
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
                                        Anda akan menghapus secara permanen paket hosting <strong>{{ planToDelete?.plan_name }}</strong
                                        >.
                                    </p>
                                    <div class="mt-3 space-y-1">
                                        <p><strong>Ini juga akan menghapus:</strong></p>
                                        <ul class="ml-2 list-inside list-disc space-y-1">
                                            <li>Semua konfigurasi paket</li>
                                            <li>Semua layanan yang menggunakan paket ini</li>
                                            <li>Semua data terkait secara permanen</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            <strong>Paket:</strong> {{ planToDelete?.plan_name }}<br />
                            <strong>Spesifikasi:</strong> {{ planToDelete?.storage_gb }}GB, {{ planToDelete?.cpu_cores }} CPU,
                            {{ planToDelete?.ram_gb }}GB RAM<br />
                            <strong>Harga Jual:</strong> {{ planToDelete ? formatPrice(planToDelete.selling_price) : '' }}<br />
                            <strong>Status:</strong> {{ planToDelete?.is_active ? 'Aktif' : 'Nonaktif' }}
                        </p>
                    </div>
                </div>

                <!-- Footer -->
                <div class="mt-6 flex justify-end gap-2">
                    <Button type="button" variant="outline" @click="showDeleteModal = false" class="cursor-pointer"> Batal </Button>
                    <Button type="button" class="cursor-pointer bg-red-600 text-white hover:bg-red-700" @click="confirmDelete">
                        Ya, Hapus Paket Hosting
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
