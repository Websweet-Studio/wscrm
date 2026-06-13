<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Edit, Package, Plus, Power, Search, Trash2, X } from 'lucide-vue-next';
import { ref } from 'vue';

interface DemoPackage {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    is_active: boolean;
    sort_order: number;
    created_at: string;
}

interface Props {
    packages: {
        data: DemoPackage[];
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
const selectedPackage = ref<DemoPackage | null>(null);
const packageToDelete = ref<DemoPackage | null>(null);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Demo Website', href: '/admin/demo-websites' },
    { title: 'Paket Demo', href: '/admin/demo-packages' },
];

const createForm = useForm({
    name: '',
    slug: '',
    description: '',
    is_active: true,
    sort_order: 0,
});

const editForm = useForm({
    name: '',
    slug: '',
    description: '',
    is_active: true,
    sort_order: 0,
    _method: 'PUT',
});

const handleSearch = () => {
    router.get('/admin/demo-packages', { search: search.value }, { preserveState: true, replace: true });
};

const submitCreate = () => {
    createForm.post('/admin/demo-packages', {
        onSuccess: () => {
            showCreateModal.value = false;
            createForm.reset();
        },
    });
};

const openEditModal = (pkg: DemoPackage) => {
    selectedPackage.value = pkg;
    editForm.name = pkg.name;
    editForm.slug = pkg.slug;
    editForm.description = pkg.description || '';
    editForm.is_active = pkg.is_active;
    editForm.sort_order = pkg.sort_order;
    showEditModal.value = true;
};

const submitEdit = () => {
    if (!selectedPackage.value) return;
    editForm.post(`/admin/demo-packages/${selectedPackage.value.id}`, {
        onSuccess: () => {
            showEditModal.value = false;
            editForm.reset();
            selectedPackage.value = null;
        },
    });
};

const openDeleteModal = (pkg: DemoPackage) => {
    packageToDelete.value = pkg;
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    if (!packageToDelete.value) return;
    router.delete(`/admin/demo-packages/${packageToDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
            packageToDelete.value = null;
        },
    });
};

const toggleStatus = (pkg: DemoPackage) => {
    router.patch(`/admin/demo-packages/${pkg.id}/toggle-status`, {}, { preserveScroll: true });
};
</script>

<template>
    <Head title="Paket Demo Website" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-medium tracking-tight" style="font-family: Georgia, serif;">Paket Demo</h1>
                    <p class="text-muted-foreground">Kelola paket untuk demo website</p>
                </div>
                <Button @click="showCreateModal = true" class="cursor-pointer">
                    <Plus class="mr-2 h-4 w-4" />
                    Tambah Paket
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle style="font-family: Georgia, serif;">Daftar Paket</CardTitle>
                    <CardDescription>Kelola paket demo website portofolio</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="mb-4 flex items-center space-x-2">
                        <div class="relative max-w-sm flex-1">
                            <Search class="absolute top-2.5 left-2.5 h-4 w-4 text-muted-foreground" />
                            <Input v-model="search" placeholder="Cari paket..." class="pl-8" @keyup.enter="handleSearch" />
                        </div>
                        <Button @click="handleSearch" class="cursor-pointer">Cari</Button>
                    </div>

                    <div class="overflow-x-auto rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Nama Paket</TableHead>
                                    <TableHead>Slug</TableHead>
                                    <TableHead>Deskripsi</TableHead>
                                    <TableHead>Urutan</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead class="w-[120px]">Aksi</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="pkg in packages.data" :key="pkg.id">
                                    <TableCell class="font-medium">{{ pkg.name }}</TableCell>
                                    <TableCell><code class="text-xs">{{ pkg.slug }}</code></TableCell>
                                    <TableCell class="max-w-[200px] truncate">{{ pkg.description || '-' }}</TableCell>
                                    <TableCell>{{ pkg.sort_order }}</TableCell>
                                    <TableCell>
                                        <Badge :variant="pkg.is_active ? 'default' : 'secondary'">
                                            {{ pkg.is_active ? 'Aktif' : 'Nonaktif' }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex items-center gap-1">
                                            <Button size="sm" variant="outline" @click="toggleStatus(pkg)" class="cursor-pointer" :title="pkg.is_active ? 'Nonaktifkan' : 'Aktifkan'">
                                                <Power class="h-3.5 w-3.5" :class="pkg.is_active ? 'text-green-600' : 'text-gray-400'" />
                                            </Button>
                                            <Button size="sm" variant="outline" @click="openEditModal(pkg)" class="cursor-pointer" title="Edit">
                                                <Edit class="h-3.5 w-3.5" />
                                            </Button>
                                            <Button size="sm" variant="outline" @click="openDeleteModal(pkg)" class="cursor-pointer text-destructive hover:bg-muted hover:text-destructive" title="Hapus">
                                                <Trash2 class="h-3.5 w-3.5" />
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="packages.data.length === 0">
                                    <TableCell colspan="6" class="py-8 text-center text-muted-foreground">
                                        Belum ada paket. Klik "Tambah Paket" untuk menambahkan.
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <div v-if="packages.last_page > 1" class="flex items-center justify-between pt-4">
                        <div class="text-sm text-muted-foreground">
                            Menampilkan {{ (packages.current_page - 1) * packages.per_page + 1 }} sampai
                            {{ Math.min(packages.current_page * packages.per_page, packages.total) }} dari {{ packages.total }} data
                        </div>
                        <div class="flex items-center space-x-2">
                            <template v-for="link in packages.links" :key="link.label">
                                <Button v-if="link.url" variant="outline" size="sm" :disabled="!link.url" @click="router.visit(link.url)" v-html="link.label" class="cursor-pointer" />
                            </template>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Create Package Modal -->
        <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-black/50" @click="showCreateModal = false"></div>
            <div class="relative mx-4 max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-lg bg-white p-6 shadow-xl dark:bg-gray-900">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold">Tambah Paket Baru</h2>
                        <p class="text-sm text-muted-foreground">Buat paket untuk demo website</p>
                    </div>
                    <button @click="showCreateModal = false" class="cursor-pointer text-gray-500 hover:text-gray-700"><X class="h-4 w-4" /></button>
                </div>
                <form @submit.prevent="submitCreate" class="space-y-4">
                    <div>
                        <Label for="create-name">Nama Paket *</Label>
                        <Input id="create-name" v-model="createForm.name" placeholder="Contoh: Starter, Pro, Enterprise" required />
                        <p v-if="createForm.errors.name" class="mt-1 text-xs text-red-500">{{ createForm.errors.name }}</p>
                    </div>
                    <div>
                        <Label for="create-slug">Slug</Label>
                        <Input id="create-slug" v-model="createForm.slug" placeholder="Otomatis dari nama (opsional)" />
                        <p class="mt-1 text-sm text-muted-foreground">Kosongkan untuk auto-generate dari nama</p>
                    </div>
                    <div>
                        <Label for="create-description">Deskripsi</Label>
                        <Textarea id="create-description" v-model="createForm.description" placeholder="Deskripsi paket..." rows="3" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex items-center space-x-2">
                            <input id="create-is-active" type="checkbox" v-model="createForm.is_active" class="rounded border border-input" />
                            <Label for="create-is-active">Aktif</Label>
                        </div>
                        <div>
                            <Label for="create-sort-order">Urutan</Label>
                            <Input id="create-sort-order" v-model.number="createForm.sort_order" type="number" min="0" />
                        </div>
                    </div>
                    <div class="flex justify-end space-x-2 pt-4">
                        <Button type="button" variant="outline" @click="showCreateModal = false" class="cursor-pointer">Batal</Button>
                        <Button type="submit" :disabled="createForm.processing">{{ createForm.processing ? 'Menyimpan...' : 'Tambah Paket' }}</Button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Package Modal -->
        <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-black/50" @click="showEditModal = false"></div>
            <div class="relative mx-4 max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-lg bg-white p-6 shadow-xl dark:bg-gray-900">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold">Edit Paket</h2>
                        <p class="text-sm text-muted-foreground">Perbarui informasi paket</p>
                    </div>
                    <button @click="showEditModal = false" class="cursor-pointer text-gray-500 hover:text-gray-700"><X class="h-4 w-4" /></button>
                </div>
                <form @submit.prevent="submitEdit" class="space-y-4">
                    <div>
                        <Label for="edit-name">Nama Paket *</Label>
                        <Input id="edit-name" v-model="editForm.name" required />
                        <p v-if="editForm.errors.name" class="mt-1 text-xs text-red-500">{{ editForm.errors.name }}</p>
                    </div>
                    <div>
                        <Label for="edit-slug">Slug</Label>
                        <Input id="edit-slug" v-model="editForm.slug" />
                    </div>
                    <div>
                        <Label for="edit-description">Deskripsi</Label>
                        <Textarea id="edit-description" v-model="editForm.description" rows="3" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex items-center space-x-2">
                            <input id="edit-is-active" type="checkbox" v-model="editForm.is_active" class="rounded border border-input" />
                            <Label for="edit-is-active">Aktif</Label>
                        </div>
                        <div>
                            <Label for="edit-sort-order">Urutan</Label>
                            <Input id="edit-sort-order" v-model.number="editForm.sort_order" type="number" min="0" />
                        </div>
                    </div>
                    <div class="flex justify-end space-x-2 pt-4">
                        <Button type="button" variant="outline" @click="showEditModal = false" class="cursor-pointer">Batal</Button>
                        <Button type="submit" :disabled="editForm.processing">{{ editForm.processing ? 'Memperbarui...' : 'Perbarui Paket' }}</Button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Package Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-black/50" @click="showDeleteModal = false"></div>
            <div class="relative mx-4 w-full max-w-md rounded-lg bg-white p-6 shadow-xl dark:bg-gray-900">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-red-600">Konfirmasi Penghapusan</h2>
                    <button @click="showDeleteModal = false" class="cursor-pointer text-gray-500 hover:text-gray-700"><X class="h-4 w-4" /></button>
                </div>
                <div class="space-y-4">
                    <div class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
                        <p class="text-sm text-red-700 dark:text-red-300">
                            Anda akan menghapus paket <strong>{{ packageToDelete?.name }}</strong>. Tindakan ini tidak dapat dibatalkan.
                        </p>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-2">
                    <Button type="button" variant="outline" @click="showDeleteModal = false" class="cursor-pointer">Batal</Button>
                    <Button type="button" class="cursor-pointer bg-red-600 text-white hover:bg-red-700" @click="confirmDelete">Ya, Hapus Paket</Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>