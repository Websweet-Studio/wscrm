<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Edit, Eye, Globe, Power, Plus, Search, X } from 'lucide-vue-next';
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
const showPreviewImage = ref(false);
const previewImageUrl = ref('');
const previewImageTitle = ref('');

const openImagePreview = (url: string, title: string) => {
    previewImageUrl.value = url;
    previewImageTitle.value = title;
    showPreviewImage.value = true;
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Demo Website', href: '/admin/demo-websites' },
];

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
                <Button @click="router.visit('/admin/demo-websites/create')" class="cursor-pointer">
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
                                        <div v-if="demo.featured_image_url" class="h-12 w-12 overflow-hidden rounded-md cursor-pointer" @click="openImagePreview(demo.featured_image_url!, demo.title)">
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
                                            <Button size="sm" variant="outline" @click="router.visit(`/admin/demo-websites/${demo.id}/edit`)" class="cursor-pointer" title="Edit & Hapus">
                                                <Edit class="h-3.5 w-3.5" />
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

        <!-- Image Preview Modal -->
        <div v-if="showPreviewImage" class="fixed inset-0 z-[60] flex items-center justify-center" @click.self="showPreviewImage = false">
            <div class="fixed inset-0 bg-black/80" @click="showPreviewImage = false"></div>
            <div class="relative z-10 mx-4 max-h-[90vh] max-w-[90vw]">
                <button @click="showPreviewImage = false" class="absolute -top-3 -right-3 cursor-pointer rounded-full bg-white p-1.5 shadow hover:bg-gray-100" title="Tutup">
                    <X class="h-4 w-4" />
                </button>
                <img :src="previewImageUrl" :alt="previewImageTitle" class="max-h-[85vh] max-w-[90vw] rounded-lg object-contain" />
                <p class="mt-2 text-center text-sm text-white">{{ previewImageTitle }}</p>
            </div>
        </div>

    </AppLayout>
</template>