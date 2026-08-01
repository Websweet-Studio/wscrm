<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Edit, ExternalLink, Plus, Search, Trash2, X } from 'lucide-vue-next';
import { ref } from 'vue';

interface Customer {
    id: number;
    name: string;
}

interface WebsiteClient {
    id: number;
    customer_id: number | null;
    name: string;
    url: string;
    wp_version: string | null;
    theme_name: string | null;
    theme_version: string | null;
    plugins: { name: string; version: string }[] | null;
    notes: string | null;
    is_active: boolean;
    auto_update_enabled: boolean;
    last_auto_update_at: string | null;
    last_auto_update_status: string | null;
    customer?: Customer | null;
}

interface Props {
    websites: {
        data: WebsiteClient[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: any[];
    };
    filters: {
        search?: string;
        customer_id?: string;
        is_active?: string;
    };
}

const props = defineProps<Props>();

const search = ref(props.filters.search || '');
const isActiveFilter = ref(props.filters.is_active ?? '');

const showDeleteModal = ref(false);
const websiteToDelete = ref<WebsiteClient | null>(null);
const selectedIds = ref<number[]>([]);
const showBulkDeleteModal = ref(false);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Manage Website', href: '/admin/websites' },
];

const handleSearch = () => {
    router.get('/admin/websites', {
        search: search.value,
        is_active: isActiveFilter.value,
    }, { preserveState: true, replace: true });
};

const openDeleteModal = (website: WebsiteClient) => {
    websiteToDelete.value = website;
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    if (!websiteToDelete.value) return;
    router.delete(`/admin/websites/${websiteToDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
            websiteToDelete.value = null;
        },
    });
};

const confirmBulkDelete = () => {
    router.delete('/admin/websites/bulk', {
        data: { ids: selectedIds.value },
        preserveScroll: true,
        onSuccess: () => {
            showBulkDeleteModal.value = false;
            selectedIds.value = [];
        },
    });
};

const toggleSelect = (id: number) => {
    const idx = selectedIds.value.indexOf(id);
    if (idx > -1) selectedIds.value.splice(idx, 1);
    else selectedIds.value.push(id);
};

const isAllSelected = () => {
    return props.websites.data.length > 0 && selectedIds.value.length === props.websites.data.length;
};

const toggleAll = () => {
    if (isAllSelected()) {
        selectedIds.value = [];
    } else {
        selectedIds.value = props.websites.data.map(w => w.id);
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Manage Website" />

        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-medium tracking-tight" style="font-family: Georgia, serif;">Website Klien</h1>
                    <p class="text-muted-foreground">Kelola website klien untuk maintenance</p>
                </div>
                <Link href="/admin/websites/create">
                    <Button class="cursor-pointer">
                        <Plus class="mr-2 h-4 w-4" />
                        Tambah Website
                    </Button>
                </Link>
            </div>

            <!-- Filters -->
            <Card>
                <CardContent class="pt-6">
                    <div class="flex flex-wrap items-end gap-4">
                        <div class="flex-1 min-w-[200px]">
                            <Label>Cari</Label>
                            <div class="relative">
                                <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                                <Input v-model="search" placeholder="Cari nama atau URL..." class="pl-9" @keyup.enter="handleSearch" />
                            </div>
                        </div>
                        <div>
                            <Label>Status</Label>
                            <select v-model="isActiveFilter" class="mt-1 w-full rounded-md border border-border bg-input px-3 py-2 text-sm text-foreground">
                                <option value="">Semua</option>
                                <option value="1">Aktif</option>
                                <option value="0">Nonaktif</option>
                            </select>
                        </div>
                        <Button variant="outline" @click="handleSearch" class="cursor-pointer">Filter</Button>
                        <Button variant="ghost" @click="() => { search = ''; isActiveFilter = ''; handleSearch(); }" class="cursor-pointer">
                            <X class="mr-2 h-4 w-4" /> Reset
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Bulk Actions -->
            <div v-if="selectedIds.length > 0" class="flex items-center gap-3">
                <span class="text-sm text-muted-foreground">{{ selectedIds.length }} terpilih</span>
                <Button variant="destructive" size="sm" @click="showBulkDeleteModal = true" class="cursor-pointer">
                    <Trash2 class="mr-2 h-4 w-4" /> Hapus Terpilih
                </Button>
                <Button variant="ghost" size="sm" @click="selectedIds = []" class="cursor-pointer">Batal</Button>
            </div>

            <Card>
                <CardContent class="pt-6">
                    <div class="overflow-x-auto rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead class="w-10">
                                        <input type="checkbox" :checked="isAllSelected()" @change="toggleAll()" class="rounded border-gray-300" />
                                    </TableHead>
                                    <TableHead>Nama Website</TableHead>
                                    <TableHead>URL</TableHead>
                                    <TableHead>Customer</TableHead>
                                    <TableHead>WP Version</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Auto Update</TableHead>
                                    <TableHead class="w-[100px]">Aksi</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-if="websites.data.length === 0">
                                    <TableCell colspan="8" class="py-8 text-center text-muted-foreground">
                                        Belum ada website. Klik "Tambah Website" untuk memulai.
                                    </TableCell>
                                </TableRow>
                                <TableRow v-for="website in websites.data" :key="website.id">
                                    <TableCell>
                                        <input type="checkbox" :checked="selectedIds.includes(website.id)" @change="() => toggleSelect(website.id)" class="rounded border-gray-300" />
                                    </TableCell>
                                    <TableCell class="font-medium">
                                        <Link :href="`/admin/websites/${website.id}`" class="hover:underline text-primary">{{ website.name }}</Link>
                                    </TableCell>
                                    <TableCell>
                                        <a :href="website.url" target="_blank" class="flex items-center gap-1 text-primary hover:underline text-sm">
                                            {{ website.url }} <ExternalLink class="h-3 w-3" />
                                        </a>
                                    </TableCell>
                                    <TableCell>{{ website.customer?.name || '-' }}</TableCell>
                                    <TableCell>
                                        <Badge v-if="website.wp_version" variant="secondary">{{ website.wp_version }}</Badge>
                                        <span v-else class="text-muted-foreground text-sm">-</span>
                                    </TableCell>
                                    <TableCell>
                                        <Badge :variant="website.is_active ? 'default' : 'secondary'">
                                            {{ website.is_active ? 'Aktif' : 'Nonaktif' }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <template v-if="website.auto_update_enabled">
                                            <Badge :variant="(website.last_auto_update_status || '').startsWith('Gagal') ? 'destructive' : website.last_auto_update_at ? 'default' : 'secondary'" :title="website.last_auto_update_status || ''">
                                                {{ website.last_auto_update_at ? 'Jalan' : 'Belum Jalan' }}
                                            </Badge>
                                            <p v-if="website.last_auto_update_at" class="text-xs text-muted-foreground mt-1">
                                                {{ new Date(website.last_auto_update_at).toLocaleString('id-ID') }}
                                            </p>
                                        </template>
                                        <Badge v-else variant="outline" class="text-muted-foreground">Nonaktif</Badge>
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex items-center gap-1">
                                            <Link :href="`/admin/websites/${website.id}/edit`">
                                                <Button size="sm" variant="outline" class="cursor-pointer" title="Edit">
                                                    <Edit class="h-3.5 w-3.5" />
                                                </Button>
                                            </Link>
                                            <Button size="sm" variant="outline" @click="openDeleteModal(website)" class="cursor-pointer text-destructive" title="Hapus">
                                                <Trash2 class="h-3.5 w-3.5" />
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                    <div v-if="websites.last_page > 1" class="flex items-center justify-between pt-4">
                        <div class="text-sm text-muted-foreground">
                            Menampilkan {{ (websites.current_page - 1) * websites.per_page + 1 }} sampai
                            {{ Math.min(websites.current_page * websites.per_page, websites.total) }} dari {{ websites.total }} data
                        </div>
                        <div class="flex items-center space-x-2">
                            <template v-for="link in websites.links" :key="link.label">
                                <Button v-if="link.url" variant="outline" size="sm" :disabled="!link.url" @click="router.visit(link.url)" v-html="link.label" class="cursor-pointer" />
                            </template>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Delete Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-black/50" @click="showDeleteModal = false"></div>
            <div class="relative mx-4 w-full max-w-md rounded-lg bg-white p-6 shadow-xl dark:bg-gray-900">
                <div class="mb-4">
                    <h2 class="text-lg font-semibold">Hapus Website</h2>
                    <p class="text-sm text-muted-foreground mt-2">
                        Yakin ingin menghapus <strong>{{ websiteToDelete?.name }}</strong>? Semua jurnal terkait juga akan dihapus.
                    </p>
                </div>
                <div class="flex justify-end gap-3">
                    <Button variant="outline" @click="showDeleteModal = false" class="cursor-pointer">Batal</Button>
                    <Button variant="destructive" @click="confirmDelete" class="cursor-pointer">Hapus</Button>
                </div>
            </div>
        </div>

        <!-- Bulk Delete Modal -->
        <div v-if="showBulkDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-black/50" @click="showBulkDeleteModal = false"></div>
            <div class="relative mx-4 w-full max-w-md rounded-lg bg-white p-6 shadow-xl dark:bg-gray-900">
                <div class="mb-4">
                    <h2 class="text-lg font-semibold">Hapus Banyak Website</h2>
                    <p class="text-sm text-muted-foreground mt-2">Yakin ingin menghapus {{ selectedIds.length }} website?</p>
                </div>
                <div class="flex justify-end gap-3">
                    <Button variant="outline" @click="showBulkDeleteModal = false" class="cursor-pointer">Batal</Button>
                    <Button variant="destructive" @click="confirmBulkDelete" class="cursor-pointer">Hapus Semua</Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
