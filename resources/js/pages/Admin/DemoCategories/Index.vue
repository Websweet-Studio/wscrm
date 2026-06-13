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
import { Edit, Plus, Power, Search, Trash2, X } from 'lucide-vue-next';
import { ref } from 'vue';

interface DemoCategory {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    is_active: boolean;
    sort_order: number;
    created_at: string;
}

interface Props {
    categories: {
        data: DemoCategory[];
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
const selectedCategory = ref<DemoCategory | null>(null);
const categoryToDelete = ref<DemoCategory | null>(null);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Demo Website', href: '/admin/demo-websites' },
    { title: 'Kategori Demo', href: '/admin/demo-categories' },
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
    router.get('/admin/demo-categories', { search: search.value }, { preserveState: true, replace: true });
};

const submitCreate = () => {
    createForm.post('/admin/demo-categories', {
        onSuccess: () => {
            showCreateModal.value = false;
            createForm.reset();
        },
    });
};

const openEditModal = (category: DemoCategory) => {
    selectedCategory.value = category;
    editForm.name = category.name;
    editForm.slug = category.slug;
    editForm.description = category.description || '';
    editForm.is_active = category.is_active;
    editForm.sort_order = category.sort_order;
    showEditModal.value = true;
};

const submitEdit = () => {
    if (!selectedCategory.value) return;
    editForm.post(`/admin/demo-categories/${selectedCategory.value.id}`, {
        onSuccess: () => {
            showEditModal.value = false;
            editForm.reset();
            selectedCategory.value = null;
        },
    });
};

const openDeleteModal = (category: DemoCategory) => {
    categoryToDelete.value = category;
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    if (!categoryToDelete.value) return;
    router.delete(`/admin/demo-categories/${categoryToDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
            categoryToDelete.value = null;
        },
    });
};

const toggleStatus = (category: DemoCategory) => {
    router.patch(`/admin/demo-categories/${category.id}/toggle-status`, {}, { preserveScroll: true });
};
</script>

<template>
    <Head title="Kategori Demo Website" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-medium tracking-tight" style="font-family: Georgia, serif;">Kategori Demo</h1>
                    <p class="text-muted-foreground">Kelola kategori untuk demo website</p>
                </div>
                <Button @click="showCreateModal = true" class="cursor-pointer">
                    <Plus class="mr-2 h-4 w-4" />
                    Tambah Kategori
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle style="font-family: Georgia, serif;">Daftar Kategori</CardTitle>
                    <CardDescription>Kelola kategori demo website portofolio</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="mb-4 flex items-center space-x-2">
                        <div class="relative max-w-sm flex-1">
                            <Search class="absolute top-2.5 left-2.5 h-4 w-4 text-muted-foreground" />
                            <Input v-model="search" placeholder="Cari kategori..." class="pl-8" @keyup.enter="handleSearch" />
                        </div>
                        <Button @click="handleSearch" class="cursor-pointer">Cari</Button>
                    </div>

                    <div class="overflow-x-auto rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Nama</TableHead>
                                    <TableHead>Slug</TableHead>
                                    <TableHead>Deskripsi</TableHead>
                                    <TableHead>Urutan</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead class="w-[120px]">Aksi</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="category in categories.data" :key="category.id">
                                    <TableCell class="font-medium">{{ category.name }}</TableCell>
                                    <TableCell><code class="text-xs">{{ category.slug }}</code></TableCell>
                                    <TableCell class="max-w-[200px] truncate">{{ category.description || '-' }}</TableCell>
                                    <TableCell>{{ category.sort_order }}</TableCell>
                                    <TableCell>
                                        <Badge :variant="category.is_active ? 'default' : 'secondary'">
                                            {{ category.is_active ? 'Aktif' : 'Nonaktif' }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex items-center gap-1">
                                            <Button size="sm" variant="outline" @click="toggleStatus(category)" class="cursor-pointer" :title="category.is_active ? 'Nonaktifkan' : 'Aktifkan'">
                                                <Power class="h-3.5 w-3.5" :class="category.is_active ? 'text-green-600' : 'text-gray-400'" />
                                            </Button>
                                            <Button size="sm" variant="outline" @click="openEditModal(category)" class="cursor-pointer" title="Edit">
                                                <Edit class="h-3.5 w-3.5" />
                                            </Button>
                                            <Button size="sm" variant="outline" @click="openDeleteModal(category)" class="cursor-pointer text-destructive hover:bg-muted hover:text-destructive" title="Hapus">
                                                <Trash2 class="h-3.5 w-3.5" />
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="categories.data.length === 0">
                                    <TableCell colspan="6" class="py-8 text-center text-muted-foreground">
                                        Belum ada kategori. Klik "Tambah Kategori" untuk menambahkan.
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <div v-if="categories.last_page > 1" class="flex items-center justify-between pt-4">
                        <div class="text-sm text-muted-foreground">
                            Menampilkan {{ (categories.current_page - 1) * categories.per_page + 1 }} sampai
                            {{ Math.min(categories.current_page * categories.per_page, categories.total) }} dari {{ categories.total }} data
                        </div>
                        <div class="flex items-center space-x-2">
                            <template v-for="link in categories.links" :key="link.label">
                                <Button v-if="link.url" variant="outline" size="sm" :disabled="!link.url" @click="router.visit(link.url)" v-html="link.label" class="cursor-pointer" />
                            </template>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Create Category Modal -->
        <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-black/50" @click="showCreateModal = false"></div>
            <div class="relative mx-4 max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-lg bg-white p-6 shadow-xl dark:bg-gray-900">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold">Tambah Kategori Baru</h2>
                        <p class="text-sm text-muted-foreground">Buat kategori untuk demo website</p>
                    </div>
                    <button @click="showCreateModal = false" class="cursor-pointer text-gray-500 hover:text-gray-700"><X class="h-4 w-4" /></button>
                </div>
                <form @submit.prevent="submitCreate" class="space-y-4">
                    <div>
                        <Label for="create-name">Nama Kategori *</Label>
                        <Input id="create-name" v-model="createForm.name" placeholder="Contoh: Property, Travel, Company" required />
                        <p v-if="createForm.errors.name" class="mt-1 text-xs text-red-500">{{ createForm.errors.name }}</p>
                    </div>
                    <div>
                        <Label for="create-slug">Slug</Label>
                        <Input id="create-slug" v-model="createForm.slug" placeholder="Otomatis dari nama (opsional)" />
                        <p class="mt-1 text-sm text-muted-foreground">Kosongkan untuk auto-generate dari nama</p>
                    </div>
                    <div>
                        <Label for="create-description">Deskripsi</Label>
                        <Textarea id="create-description" v-model="createForm.description" placeholder="Deskripsi kategori..." rows="3" />
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
                        <Button type="submit" :disabled="createForm.processing">{{ createForm.processing ? 'Menyimpan...' : 'Tambah Kategori' }}</Button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Category Modal -->
        <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-black/50" @click="showEditModal = false"></div>
            <div class="relative mx-4 max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-lg bg-white p-6 shadow-xl dark:bg-gray-900">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold">Edit Kategori</h2>
                        <p class="text-sm text-muted-foreground">Perbarui informasi kategori</p>
                    </div>
                    <button @click="showEditModal = false" class="cursor-pointer text-gray-500 hover:text-gray-700"><X class="h-4 w-4" /></button>
                </div>
                <form @submit.prevent="submitEdit" class="space-y-4">
                    <div>
                        <Label for="edit-name">Nama Kategori *</Label>
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
                        <Button type="submit" :disabled="editForm.processing">{{ editForm.processing ? 'Memperbarui...' : 'Perbarui Kategori' }}</Button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Category Modal -->
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
                            Anda akan menghapus kategori <strong>{{ categoryToDelete?.name }}</strong>. Tindakan ini tidak dapat dibatalkan.
                        </p>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-2">
                    <Button type="button" variant="outline" @click="showDeleteModal = false" class="cursor-pointer">Batal</Button>
                    <Button type="button" class="cursor-pointer bg-red-600 text-white hover:bg-red-700" @click="confirmDelete">Ya, Hapus Kategori</Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>