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
import { Head, Link, router, useForm } from '@inertiajs/vue3';
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
    customers: Customer[];
    filters: {
        search?: string;
        customer_id?: string;
        is_active?: string;
    };
    editingWebsite?: WebsiteClient | null;
}

const props = defineProps<Props>();

const search = ref(props.filters.search || '');
const customerFilter = ref(props.filters.customer_id || '');
const isActiveFilter = ref(props.filters.is_active ?? '');

const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const editingWebsite = ref<WebsiteClient | null>(null);
const websiteToDelete = ref<WebsiteClient | null>(null);
const selectedIds = ref<number[]>([]);
const showBulkDeleteModal = ref(false);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Website Klien', href: '/admin/websites' },
];

const createForm = useForm({
    name: '',
    url: '',
    customer_id: null as (number | null),
    wp_version: '',
    theme_name: '',
    theme_version: '',
    plugins: [] as { name: string; version: string }[],
    notes: '',
    is_active: true,
});

const editForm = useForm({
    name: '',
    url: '',
    customer_id: null as (number | null),
    wp_version: '',
    theme_name: '',
    theme_version: '',
    plugins: [] as { name: string; version: string }[],
    notes: '',
    is_active: true,
    _method: 'PUT',
});

const handleSearch = () => {
    router.get('/admin/websites', {
        search: search.value,
        customer_id: customerFilter.value,
        is_active: isActiveFilter.value,
    }, { preserveState: true, replace: true });
};

const submitCreate = () => {
    createForm.post('/admin/websites', {
        onSuccess: () => {
            showCreateModal.value = false;
            createForm.reset();
        },
    });
};

const openEditModal = (website: WebsiteClient) => {
    editingWebsite.value = website;
    editForm.name = website.name;
    editForm.url = website.url;
    editForm.customer_id = website.customer_id;
    editForm.wp_version = website.wp_version || '';
    editForm.theme_name = website.theme_name || '';
    editForm.theme_version = website.theme_version || '';
    editForm.plugins = [...(website.plugins || [])];
    editForm.notes = website.notes || '';
    editForm.is_active = website.is_active;
    showEditModal.value = true;
};

const submitEdit = () => {
    if (!editingWebsite.value) return;
    editForm.post(`/admin/websites/${editingWebsite.value.id}`, {
        onSuccess: () => {
            showEditModal.value = false;
            editForm.reset();
            editingWebsite.value = null;
        },
    });
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

const newPluginName = ref('');
const newPluginVersion = ref('');

const addPlugin = (form: any) => {
    if (!newPluginName.value.trim()) return;
    form.plugins.push({ name: newPluginName.value.trim(), version: newPluginVersion.value.trim() });
    newPluginName.value = '';
    newPluginVersion.value = '';
};

const removePlugin = (form: any, index: number) => {
    form.plugins.splice(index, 1);
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
        <Head title="Website Klien" />

        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-medium tracking-tight" style="font-family: Georgia, serif;">Website Klien</h1>
                    <p class="text-muted-foreground">Kelola website klien untuk maintenance</p>
                </div>
                <Button @click="showCreateModal = true" class="cursor-pointer">
                    <Plus class="mr-2 h-4 w-4" />
                    Tambah Website
                </Button>
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
                            <Label>Customer</Label>
                            <select v-model="customerFilter" class="mt-1 w-full rounded-md border border-border bg-input px-3 py-2 text-sm text-foreground">
                                <option value="">Semua Customer</option>
                                <option v-for="c in customers" :key="c.id" :value="String(c.id)">{{ c.name }}</option>
                            </select>
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
                        <Button variant="ghost" @click="() => { search = ''; customerFilter = ''; isActiveFilter = ''; handleSearch(); }" class="cursor-pointer">
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
                                    <TableHead class="w-[100px]">Aksi</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-if="websites.data.length === 0">
                                    <TableCell colspan="7" class="py-8 text-center text-muted-foreground">
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
                                        <div class="flex items-center gap-1">
                                            <Button size="sm" variant="outline" @click="openEditModal(website)" class="cursor-pointer" title="Edit">
                                                <Edit class="h-3.5 w-3.5" />
                                            </Button>
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

        <!-- Create Modal -->
        <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-black/50" @click="showCreateModal = false"></div>
            <div class="relative mx-4 max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-lg bg-white p-6 shadow-xl dark:bg-gray-900">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold">Tambah Website Klien</h2>
                        <p class="text-sm text-muted-foreground">Isi data website klien untuk maintenance.</p>
                    </div>
                    <button @click="showCreateModal = false" class="cursor-pointer text-gray-500 hover:text-gray-700"><X class="h-4 w-4" /></button>
                </div>
                <form @submit.prevent="submitCreate" class="space-y-4">
                    <div>
                        <Label for="create-name">Nama Website *</Label>
                        <Input id="create-name" v-model="createForm.name" required placeholder="Nama website / brand" />
                        <p v-if="createForm.errors.name" class="mt-1 text-xs text-red-500">{{ createForm.errors.name }}</p>
                    </div>
                    <div>
                        <Label for="create-url">URL *</Label>
                        <Input id="create-url" v-model="createForm.url" required placeholder="https://example.com" />
                        <p v-if="createForm.errors.url" class="mt-1 text-xs text-red-500">{{ createForm.errors.url }}</p>
                    </div>
                    <div>
                        <Label for="create-customer">Customer (opsional)</Label>
                        <select id="create-customer" v-model="createForm.customer_id" class="mt-1 w-full rounded-md border border-border bg-input px-3 py-2 text-sm text-foreground">
                            <option :value="null">- Tanpa Customer -</option>
                            <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <Label for="create-wpver">Versi WP</Label>
                            <Input id="create-wpver" v-model="createForm.wp_version" placeholder="6.6" />
                        </div>
                        <div>
                            <Label for="create-theme">Nama Tema</Label>
                            <Input id="create-theme" v-model="createForm.theme_name" placeholder="Nama tema" />
                        </div>
                    </div>
                    <div>
                        <Label for="create-themever">Versi Tema</Label>
                        <Input id="create-themever" v-model="createForm.theme_version" placeholder="1.0" />
                    </div>
                    <div>
                        <Label>Plugin</Label>
                        <div class="flex gap-2 mb-2">
                            <Input v-model="newPluginName" placeholder="Nama plugin" class="flex-1" />
                            <Input v-model="newPluginVersion" placeholder="Versi" class="w-24" />
                            <Button type="button" variant="outline" size="sm" @click="addPlugin(createForm)" class="cursor-pointer">Tambah</Button>
                        </div>
                        <div v-if="createForm.plugins.length > 0" class="space-y-1 border rounded-md p-2">
                            <div v-for="(plugin, idx) in createForm.plugins" :key="idx" class="flex items-center justify-between text-sm py-1 px-2 bg-muted/30 rounded">
                                <span>{{ plugin.name }} <span v-if="plugin.version" class="text-muted-foreground">v{{ plugin.version }}</span></span>
                                <button type="button" @click="removePlugin(createForm, idx)" class="cursor-pointer text-gray-400 hover:text-red-500"><X class="h-3 w-3" /></button>
                            </div>
                        </div>
                    </div>
                    <div>
                        <Label for="create-notes">Catatan</Label>
                        <Textarea id="create-notes" v-model="createForm.notes" rows="2" placeholder="Catatan tambahan..." />
                    </div>
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <Button type="button" variant="outline" @click="showCreateModal = false" class="cursor-pointer">Batal</Button>
                        <Button type="submit" :disabled="createForm.processing" class="cursor-pointer">Simpan</Button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Modal -->
        <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-black/50" @click="showEditModal = false"></div>
            <div class="relative mx-4 max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-lg bg-white p-6 shadow-xl dark:bg-gray-900">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold">Edit Website Klien</h2>
                    <button @click="showEditModal = false" class="cursor-pointer text-gray-500 hover:text-gray-700"><X class="h-4 w-4" /></button>
                </div>
                <form @submit.prevent="submitEdit" class="space-y-4">
                    <div>
                        <Label for="edit-name">Nama Website *</Label>
                        <Input id="edit-name" v-model="editForm.name" required />
                    </div>
                    <div>
                        <Label for="edit-url">URL *</Label>
                        <Input id="edit-url" v-model="editForm.url" required />
                    </div>
                    <div>
                        <Label for="edit-customer">Customer</Label>
                        <select id="edit-customer" v-model="editForm.customer_id" class="mt-1 w-full rounded-md border border-border bg-input px-3 py-2 text-sm text-foreground">
                            <option :value="null">- Tanpa Customer -</option>
                            <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <Label for="edit-wpver">Versi WP</Label>
                            <Input id="edit-wpver" v-model="editForm.wp_version" placeholder="6.6" />
                        </div>
                        <div>
                            <Label for="edit-theme">Nama Tema</Label>
                            <Input id="edit-theme" v-model="editForm.theme_name" placeholder="Nama tema" />
                        </div>
                    </div>
                    <div>
                        <Label for="edit-themever">Versi Tema</Label>
                        <Input id="edit-themever" v-model="editForm.theme_version" placeholder="1.0" />
                    </div>
                    <div>
                        <Label>Plugin</Label>
                        <div class="flex gap-2 mb-2">
                            <Input v-model="newPluginName" placeholder="Nama plugin" class="flex-1" />
                            <Input v-model="newPluginVersion" placeholder="Versi" class="w-24" />
                            <Button type="button" variant="outline" size="sm" @click="addPlugin(editForm)" class="cursor-pointer">Tambah</Button>
                        </div>
                        <div v-if="editForm.plugins.length > 0" class="space-y-1 border rounded-md p-2">
                            <div v-for="(plugin, idx) in editForm.plugins" :key="idx" class="flex items-center justify-between text-sm py-1 px-2 bg-muted/30 rounded">
                                <span>{{ plugin.name }} <span v-if="plugin.version" class="text-muted-foreground">v{{ plugin.version }}</span></span>
                                <button type="button" @click="removePlugin(editForm, idx)" class="cursor-pointer text-gray-400 hover:text-red-500"><X class="h-3 w-3" /></button>
                            </div>
                        </div>
                    </div>
                    <div>
                        <Label for="edit-notes">Catatan</Label>
                        <Textarea id="edit-notes" v-model="editForm.notes" rows="2" placeholder="Catatan tambahan..." />
                    </div>
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <Button type="button" variant="outline" @click="showEditModal = false" class="cursor-pointer">Batal</Button>
                        <Button type="submit" :disabled="editForm.processing" class="cursor-pointer">Simpan</Button>
                    </div>
                </form>
            </div>
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
