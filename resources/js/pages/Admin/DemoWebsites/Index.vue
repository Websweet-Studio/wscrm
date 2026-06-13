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
import { Edit, Eye, Globe, Power, Plus, Search, Trash2, X } from 'lucide-vue-next';
import { ref } from 'vue';

interface DemoCategory {
    id: number;
    name: string;
    slug: string;
}

interface DemoPackage {
    id: number;
    name: string;
    slug: string;
}

interface DemoWebsite {
    id: number;
    title: string;
    url: string;
    demo_category_id: number | null;
    category: string | null;
    featured_image: string | null;
    featured_image_url: string | null;
    description: string | null;
    is_active: boolean;
    sort_order: number;
    demo_category?: DemoCategory;
    demo_packages?: DemoPackage[];
    created_at: string;
    updated_at: string;
}

interface Props {
    demos: {
        data: DemoWebsite[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: any[];
    };
    categories: DemoCategory[];
    packages: DemoPackage[];
    filters: {
        search?: string;
        category?: string;
        package?: string;
        status?: string;
    };
}

const props = defineProps<Props>();

const search = ref(props.filters.search || '');
const categoryFilter = ref(props.filters.category || '');
const packageFilter = ref(props.filters.package || '');
const statusFilter = ref(props.filters.status || '');
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const selectedDemo = ref<DemoWebsite | null>(null);
const demoToDelete = ref<DemoWebsite | null>(null);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Demo Website', href: '/admin/demo-websites' },
];

const createForm = useForm({
    title: '',
    url: '',
    demo_category_id: '',
    demo_packages: [] as string[],
    featured_image: null as File | null,
    description: '',
    is_active: true,
    sort_order: 0,
});

const editForm = useForm({
    title: '',
    url: '',
    demo_category_id: '',
    demo_packages: [] as string[],
    featured_image: null as File | null,
    description: '',
    is_active: true,
    sort_order: 0,
    _method: 'PUT',
});

const handleSearch = () => {
    router.get(
        '/admin/demo-websites',
        {
            search: search.value,
            category: categoryFilter.value,
            package: packageFilter.value,
            status: statusFilter.value,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};

const submitCreate = () => {
    createForm.post('/admin/demo-websites', {
        onSuccess: () => {
            showCreateModal.value = false;
            createForm.reset();
            createForm.demo_packages = [];
        },
    });
};

const openEditModal = (demo: DemoWebsite) => {
    selectedDemo.value = demo;
    editForm.title = demo.title;
    editForm.url = demo.url;
    editForm.demo_category_id = String(demo.demo_category_id || '');
    editForm.demo_packages = demo.demo_packages?.map((p: DemoPackage) => String(p.id)) || [];
    editForm.description = demo.description || '';
    editForm.is_active = demo.is_active;
    editForm.sort_order = demo.sort_order;
    editForm.featured_image = null;
    showEditModal.value = true;
};

const submitEdit = () => {
    if (!selectedDemo.value) return;

    editForm.post(`/admin/demo-websites/${selectedDemo.value.id}`, {
        onSuccess: () => {
            showEditModal.value = false;
            editForm.reset();
            editForm.demo_packages = [];
            selectedDemo.value = null;
        },
    });
};

const openDeleteModal = (demo: DemoWebsite) => {
    demoToDelete.value = demo;
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    if (!demoToDelete.value) return;

    router.delete(`/admin/demo-websites/${demoToDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
            demoToDelete.value = null;
        },
    });
};

const toggleStatus = (demo: DemoWebsite) => {
    router.patch(`/admin/demo-websites/${demo.id}/toggle-status`, {}, {
        preserveScroll: true,
    });
};

const getCategoryName = (demo: DemoWebsite) => {
    return demo.demo_category?.name || demo.category || '-';
};

const getPackageNames = (demo: DemoWebsite) => {
    if (demo.demo_packages && demo.demo_packages.length > 0) {
        return demo.demo_packages.map((p: DemoPackage) => p.name);
    }
    return [];
};
</script>

<template>
    <Head title="Kelola Demo Website" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-medium tracking-tight" style="font-family: Georgia, serif;">Demo Website</h1>
                    <p class="text-muted-foreground">Kelola demo website yang tersedia untuk publik</p>
                </div>
                <Button @click="showCreateModal = true" class="cursor-pointer">
                    <Plus class="mr-2 h-4 w-4" />
                    Tambah Demo Website
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle style="font-family: Georgia, serif;">Daftar Demo Website</CardTitle>
                    <CardDescription>Kelola demo website untuk showcase portofolio</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="mb-4 flex flex-wrap items-center gap-2">
                        <div class="relative max-w-sm flex-1">
                            <Search class="absolute top-2.5 left-2.5 h-4 w-4 text-muted-foreground" />
                            <Input v-model="search" placeholder="Cari demo website..." class="pl-8" @keyup.enter="handleSearch" />
                        </div>
                        <select
                            v-model="categoryFilter"
                            class="rounded-md border border-input bg-background px-3 py-2 text-sm"
                            @change="handleSearch"
                        >
                            <option value="">Semua Kategori</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                        </select>
                        <select
                            v-model="packageFilter"
                            class="rounded-md border border-input bg-background px-3 py-2 text-sm"
                            @change="handleSearch"
                        >
                            <option value="">Semua Paket</option>
                            <option v-for="pkg in packages" :key="pkg.id" :value="pkg.id">{{ pkg.name }}</option>
                        </select>
                        <select
                            v-model="statusFilter"
                            class="rounded-md border border-input bg-background px-3 py-2 text-sm"
                            @change="handleSearch"
                        >
                            <option value="">Semua Status</option>
                            <option value="active">Aktif</option>
                            <option value="inactive">Nonaktif</option>
                        </select>
                        <Button @click="handleSearch" class="cursor-pointer">Cari</Button>
                    </div>

                    <div class="overflow-x-auto rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead class="w-[80px]">Preview</TableHead>
                                    <TableHead>Judul</TableHead>
                                    <TableHead>Kategori</TableHead>
                                    <TableHead>Paket</TableHead>
                                    <TableHead>URL</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead class="w-[120px]">Aksi</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="demo in demos.data" :key="demo.id">
                                    <TableCell>
                                        <div v-if="demo.featured_image_url" class="h-12 w-12 overflow-hidden rounded-md">
                                            <img :src="demo.featured_image_url" :alt="demo.title" class="h-full w-full object-cover" />
                                        </div>
                                        <div v-else class="flex h-12 w-12 items-center justify-center rounded-md bg-muted">
                                            <Globe class="h-5 w-5 text-muted-foreground" />
                                        </div>
                                    </TableCell>
                                    <TableCell class="font-medium">{{ demo.title }}</TableCell>
                                    <TableCell>
                                        <Badge variant="outline">{{ getCategoryName(demo) }}</Badge>
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex flex-wrap gap-1">
                                            <Badge v-for="pkg in getPackageNames(demo)" :key="pkg" variant="secondary" class="text-xs">
                                                {{ pkg }}
                                            </Badge>
                                            <span v-if="getPackageNames(demo).length === 0" class="text-muted-foreground">-</span>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <a :href="demo.url" target="_blank" class="inline-flex items-center gap-1 text-blue-600 hover:underline">
                                            <Eye class="h-3 w-3" />
                                            Lihat Demo
                                        </a>
                                    </TableCell>
                                    <TableCell>
                                        <Badge :variant="demo.is_active ? 'default' : 'secondary'">
                                            {{ demo.is_active ? 'Aktif' : 'Nonaktif' }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex items-center gap-1">
                                            <Button size="sm" variant="outline" @click="toggleStatus(demo)" class="cursor-pointer" :title="demo.is_active ? 'Nonaktifkan' : 'Aktifkan'">
                                                <Power class="h-3.5 w-3.5" :class="demo.is_active ? 'text-green-600' : 'text-gray-400'" />
                                            </Button>
                                            <Button size="sm" variant="outline" @click="openEditModal(demo)" class="cursor-pointer" title="Edit">
                                                <Edit class="h-3.5 w-3.5" />
                                            </Button>
                                            <Button size="sm" variant="outline" @click="openDeleteModal(demo)" class="cursor-pointer text-destructive hover:bg-muted hover:text-destructive" title="Hapus">
                                                <Trash2 class="h-3.5 w-3.5" />
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="demos.data.length === 0">
                                    <TableCell colspan="7" class="py-8 text-center text-muted-foreground">
                                        Belum ada demo website. Klik "Tambah Demo Website" untuk menambahkan.
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <div v-if="demos.last_page > 1" class="flex items-center justify-between pt-4">
                        <div class="text-sm text-muted-foreground">
                            Menampilkan {{ (demos.current_page - 1) * demos.per_page + 1 }} sampai
                            {{ Math.min(demos.current_page * demos.per_page, demos.total) }} dari {{ demos.total }} data
                        </div>
                        <div class="flex items-center space-x-2">
                            <template v-for="link in demos.links" :key="link.label">
                                <Button v-if="link.url" variant="outline" size="sm" :disabled="!link.url" @click="router.visit(link.url)" v-html="link.label" class="cursor-pointer" />
                            </template>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Create Demo Website Modal -->
        <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-black/50" @click="showCreateModal = false"></div>
            <div class="relative mx-4 max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-lg bg-white p-6 shadow-xl dark:bg-gray-900">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold">Tambah Demo Website Baru</h2>
                        <p class="text-sm text-muted-foreground">Tambahkan demo website untuk portofolio</p>
                    </div>
                    <button @click="showCreateModal = false" class="cursor-pointer text-gray-500 hover:text-gray-700">
                        <X class="h-4 w-4" />
                    </button>
                </div>
                <form @submit.prevent="submitCreate" class="space-y-4">
                    <div>
                        <Label for="create-title">Judul *</Label>
                        <Input id="create-title" v-model="createForm.title" placeholder="Contoh: Website Travel - Desain 1" required />
                        <p v-if="createForm.errors.title" class="mt-1 text-xs text-red-500">{{ createForm.errors.title }}</p>
                    </div>

                    <div>
                        <Label for="create-url">URL Demo *</Label>
                        <Input id="create-url" v-model="createForm.url" type="url" placeholder="https://demo.example.com" required />
                        <p v-if="createForm.errors.url" class="mt-1 text-xs text-red-500">{{ createForm.errors.url }}</p>
                    </div>

                    <div>
                        <Label for="create-category">Kategori *</Label>
                        <select id="create-category" v-model="createForm.demo_category_id" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm" required>
                            <option value="">Pilih Kategori</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                        </select>
                        <p v-if="createForm.errors.demo_category_id" class="mt-1 text-xs text-red-500">{{ createForm.errors.demo_category_id }}</p>
                    </div>

                    <div>
                        <Label>Paket (bisa lebih dari satu)</Label>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <label v-for="pkg in packages" :key="pkg.id" class="flex items-center gap-2 rounded-md border border-input px-3 py-2 cursor-pointer hover:bg-accent transition-colors" :class="createForm.demo_packages.includes(String(pkg.id)) ? 'bg-accent border-primary' : ''">
                                <input type="checkbox" :value="String(pkg.id)" v-model="createForm.demo_packages" class="rounded border border-input" />
                                <span class="text-sm">{{ pkg.name }}</span>
                            </label>
                        </div>
                        <p class="mt-1 text-sm text-muted-foreground">Pilih satu atau lebih paket yang sesuai</p>
                    </div>

                    <div>
                        <Label for="create-featured-image">Featured Image</Label>
                        <Input id="create-featured-image" type="file" accept="image/*" @input="createForm.featured_image = ($event.target as HTMLInputElement).files?.[0] || null" />
                        <p class="mt-1 text-sm text-muted-foreground">Upload gambar screenshot demo (maks 2MB)</p>
                        <p v-if="createForm.errors.featured_image" class="mt-1 text-xs text-red-500">{{ createForm.errors.featured_image }}</p>
                    </div>

                    <div>
                        <Label for="create-description">Deskripsi</Label>
                        <Textarea id="create-description" v-model="createForm.description" placeholder="Deskripsi demo website..." rows="3" />
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
                        <Button type="submit" :disabled="createForm.processing">
                            {{ createForm.processing ? 'Menyimpan...' : 'Tambah Demo Website' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Demo Website Modal -->
        <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-black/50" @click="showEditModal = false"></div>
            <div class="relative mx-4 max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-lg bg-white p-6 shadow-xl dark:bg-gray-900">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold">Edit Demo Website</h2>
                        <p class="text-sm text-muted-foreground">Perbarui informasi demo website</p>
                    </div>
                    <button @click="showEditModal = false" class="cursor-pointer text-gray-500 hover:text-gray-700">
                        <X class="h-4 w-4" />
                    </button>
                </div>
                <form @submit.prevent="submitEdit" class="space-y-4">
                    <div>
                        <Label for="edit-title">Judul *</Label>
                        <Input id="edit-title" v-model="editForm.title" required />
                        <p v-if="editForm.errors.title" class="mt-1 text-xs text-red-500">{{ editForm.errors.title }}</p>
                    </div>

                    <div>
                        <Label for="edit-url">URL Demo *</Label>
                        <Input id="edit-url" v-model="editForm.url" type="url" required />
                        <p v-if="editForm.errors.url" class="mt-1 text-xs text-red-500">{{ editForm.errors.url }}</p>
                    </div>

                    <div>
                        <Label for="edit-category">Kategori *</Label>
                        <select id="edit-category" v-model="editForm.demo_category_id" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm" required>
                            <option value="">Pilih Kategori</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                        </select>
                        <p v-if="editForm.errors.demo_category_id" class="mt-1 text-xs text-red-500">{{ editForm.errors.demo_category_id }}</p>
                    </div>

                    <div>
                        <Label>Paket (bisa lebih dari satu)</Label>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <label v-for="pkg in packages" :key="pkg.id" class="flex items-center gap-2 rounded-md border border-input px-3 py-2 cursor-pointer hover:bg-accent transition-colors" :class="editForm.demo_packages.includes(String(pkg.id)) ? 'bg-accent border-primary' : ''">
                                <input type="checkbox" :value="String(pkg.id)" v-model="editForm.demo_packages" class="rounded border border-input" />
                                <span class="text-sm">{{ pkg.name }}</span>
                            </label>
                        </div>
                        <p class="mt-1 text-sm text-muted-foreground">Pilih satu atau lebih paket yang sesuai</p>
                    </div>

                    <div>
                        <Label for="edit-featured-image">Featured Image</Label>
                        <div v-if="selectedDemo?.featured_image_url" class="mb-2">
                            <img :src="selectedDemo.featured_image_url" alt="Current image" class="h-20 w-20 rounded-md object-cover" />
                            <p class="mt-1 text-xs text-muted-foreground">Gambar saat ini. Upload yang baru untuk mengganti.</p>
                        </div>
                        <Input id="edit-featured-image" type="file" accept="image/*" @input="editForm.featured_image = ($event.target as HTMLInputElement).files?.[0] || null" />
                        <p v-if="editForm.errors.featured_image" class="mt-1 text-xs text-red-500">{{ editForm.errors.featured_image }}</p>
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
                        <Button type="submit" :disabled="editForm.processing">
                            {{ editForm.processing ? 'Memperbarui...' : 'Perbarui Demo Website' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Demo Website Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-black/50" @click="showDeleteModal = false"></div>
            <div class="relative mx-4 w-full max-w-md rounded-lg bg-white p-6 shadow-xl dark:bg-gray-900">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-red-600">Konfirmasi Penghapusan</h2>
                    <button @click="showDeleteModal = false" class="cursor-pointer text-gray-500 hover:text-gray-700">
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <div class="space-y-4">
                    <div class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
                        <p class="text-sm text-red-700 dark:text-red-300">
                            Anda akan menghapus demo website <strong>{{ demoToDelete?.title }}</strong
                            >. Tindakan ini tidak dapat dibatalkan.
                        </p>
                    </div>

                    <div v-if="demoToDelete" class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            <strong>Judul:</strong> {{ demoToDelete.title }}<br />
                            <strong>Kategori:</strong> {{ getCategoryName(demoToDelete) }}<br />
                            <strong>Paket:</strong> {{ getPackageNames(demoToDelete).join(', ') || '-' }}<br />
                            <strong>URL:</strong> {{ demoToDelete.url }}<br />
                            <strong>Status:</strong> {{ demoToDelete.is_active ? 'Aktif' : 'Nonaktif' }}
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <Button type="button" variant="outline" @click="showDeleteModal = false" class="cursor-pointer">Batal</Button>
                    <Button type="button" class="cursor-pointer bg-red-600 text-white hover:bg-red-700" @click="confirmDelete">
                        Ya, Hapus Demo Website
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>