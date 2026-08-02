<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { FileArchive, Loader2, Package, Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface ThirdPartyPlugin {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    version: string | null;
    file_path: string;
    file_name: string | null;
    file_size: number | null;
    is_active: boolean;
}

const props = defineProps<{
    plugins: ThirdPartyPlugin[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Manage Website', href: '/admin/websites' },
    { title: 'Plugins', href: '/admin/websites/plugins' },
];

const page = usePage();

const flash = computed(() => (page.props.flash as any) || {});

const showCreate = ref(false);
const showEdit = ref(false);
const editingPlugin = ref<ThirdPartyPlugin | null>(null);

const emptyForm = {
    name: '',
    slug: '',
    version: '',
    description: '',
    file: null as File | null,
};

const createForm = useForm({ ...emptyForm });
const editForm = useForm({ ...emptyForm });

const formatSize = (bytes: number | null): string => {
    if (!bytes) return '-';
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
};

const submitCreate = () => {
    createForm.post('/admin/websites/plugins', {
        preserveScroll: true,
        onSuccess: () => {
            showCreate.value = false;
            createForm.reset();
        },
    });
};

const openEdit = (plugin: ThirdPartyPlugin) => {
    editingPlugin.value = plugin;
    editForm.reset();
    editForm.name = plugin.name;
    editForm.slug = plugin.slug;
    editForm.version = plugin.version || '';
    editForm.description = plugin.description || '';
    showEdit.value = true;
};

const submitEdit = () => {
    if (!editingPlugin.value) return;
    editForm.post(`/admin/websites/plugins/${editingPlugin.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            showEdit.value = false;
            editingPlugin.value = null;
        },
    });
};

const confirmDelete = (plugin: ThirdPartyPlugin) => {
    if (!confirm(`Hapus plugin '${plugin.name}' beserta file zip?`)) return;
    router.delete(`/admin/websites/plugins/${plugin.id}`, { preserveScroll: true });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Plugins - Manage Website" />

        <div class="space-y-4">
            <div v-if="flash.success || flash.error" class="rounded-lg border px-4 py-3 text-sm"
                :class="flash.success ? 'border-green-200 bg-green-50 text-green-700' : 'border-red-200 bg-red-50 text-red-700'">
                {{ flash.success || flash.error }}
            </div>

            <Card>
                <CardHeader class="flex-row items-center justify-between space-y-0">
                    <div>
                        <CardTitle class="flex items-center gap-2">
                            <Package class="h-5 w-5 text-primary" />
                            Plugin Pihak Ketiga
                        </CardTitle>
                        <p class="text-sm text-muted-foreground mt-1">
                            Kelola plugin premium/custom (tidak ada di wordpress.org). Upload zip — theme wsbase di website WP menarik update & install otomatis dari sini.
                        </p>
                    </div>
                    <Button class="cursor-pointer" @click="showCreate = true">
                        <Plus class="mr-2 h-4 w-4" /> Tambah Plugin
                    </Button>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Plugin</TableHead>
                                <TableHead>Slug</TableHead>
                                <TableHead>Versi</TableHead>
                                <TableHead>File ZIP</TableHead>
                                <TableHead class="text-right">Aksi</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="plugin in plugins" :key="plugin.id">
                                <TableCell>
                                    <div class="font-medium">{{ plugin.name }}</div>
                                    <div v-if="plugin.description" class="text-xs text-muted-foreground max-w-xs truncate">
                                        {{ plugin.description }}
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <code class="text-xs bg-muted rounded px-1.5 py-0.5">{{ plugin.slug }}</code>
                                </TableCell>
                                <TableCell>
                                    <Badge variant="outline">v{{ plugin.version || '-' }}</Badge>
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center gap-2 text-xs">
                                        <FileArchive class="h-4 w-4 text-muted-foreground" />
                                        <span class="text-muted-foreground">{{ plugin.file_name || plugin.slug + '.zip' }}</span>
                                        <span class="text-muted-foreground/60">({{ formatSize(plugin.file_size) }})</span>
                                    </div>
                                </TableCell>
                                <TableCell class="text-right">
                                    <div class="flex justify-end gap-1">
                                        <Button size="icon" variant="ghost" class="cursor-pointer" title="Edit" @click="openEdit(plugin)">
                                            <Pencil class="h-4 w-4" />
                                        </Button>
                                        <Button size="icon" variant="ghost" class="cursor-pointer text-destructive" title="Hapus" @click="confirmDelete(plugin)">
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="plugins.length === 0">
                                <TableCell colspan="5" class="text-center text-muted-foreground py-10">
                                    Belum ada plugin. Klik "Tambah Plugin" untuk mengupload zip plugin.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>

        <!-- Modal: Tambah Plugin -->
        <Dialog v-model:open="showCreate">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Tambah Plugin</DialogTitle>
                    <DialogDescription>Upload file zip plugin premium/custom. Slug = nama folder plugin di WordPress.</DialogDescription>
                </DialogHeader>
                <div class="space-y-4">
                    <div class="space-y-2">
                        <Label>Nama Plugin</Label>
                        <Input v-model="createForm.name" placeholder="contoh: Sweet Addons" />
                        <p v-if="createForm.errors.name" class="text-xs text-red-600">{{ createForm.errors.name }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label>Slug (folder plugin di WP)</Label>
                        <Input v-model="createForm.slug" placeholder="contoh: sweetaddons" />
                        <p v-if="createForm.errors.slug" class="text-xs text-red-600">{{ createForm.errors.slug }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label>Versi</Label>
                        <Input v-model="createForm.version" placeholder="contoh: 1.2.0" />
                    </div>
                    <div class="space-y-2">
                        <Label>Deskripsi</Label>
                        <Textarea v-model="createForm.description" rows="2" placeholder="Deskripsi singkat plugin" />
                    </div>
                    <div class="space-y-2">
                        <Label>File ZIP</Label>
                        <Input type="file" accept=".zip" @change="e => createForm.file = (e.target as HTMLInputElement).files?.[0] || null" />
                        <p v-if="createForm.errors.file" class="text-xs text-red-600">{{ createForm.errors.file }}</p>
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="ghost" class="cursor-pointer" @click="showCreate = false">Batal</Button>
                    <Button class="cursor-pointer" :disabled="createForm.processing" @click="submitCreate">
                        <Loader2 v-if="createForm.processing" class="mr-1 h-4 w-4 animate-spin" /> Simpan
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Modal: Edit Plugin -->
        <Dialog v-model:open="showEdit">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Edit Plugin</DialogTitle>
                    <DialogDescription>Perbarui nama/versi atau ganti file zip. Kosongkan file jika hanya update metadata.</DialogDescription>
                </DialogHeader>
                <div class="space-y-4">
                    <div class="space-y-2">
                        <Label>Nama Plugin</Label>
                        <Input v-model="editForm.name" />
                        <p v-if="editForm.errors.name" class="text-xs text-red-600">{{ editForm.errors.name }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label>Slug (folder plugin di WP)</Label>
                        <Input v-model="editForm.slug" />
                        <p v-if="editForm.errors.slug" class="text-xs text-red-600">{{ editForm.errors.slug }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label>Versi</Label>
                        <Input v-model="editForm.version" />
                    </div>
                    <div class="space-y-2">
                        <Label>Deskripsi</Label>
                        <Textarea v-model="editForm.description" rows="2" />
                    </div>
                    <div class="space-y-2">
                        <Label>File ZIP (opsional)</Label>
                        <Input type="file" accept=".zip" @change="e => editForm.file = (e.target as HTMLInputElement).files?.[0] || null" />
                        <p v-if="editForm.errors.file" class="text-xs text-red-600">{{ editForm.errors.file }}</p>
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="ghost" class="cursor-pointer" @click="showEdit = false">Batal</Button>
                    <Button class="cursor-pointer" :disabled="editForm.processing" @click="submitEdit">
                        <Loader2 v-if="editForm.processing" class="mr-1 h-4 w-4 animate-spin" /> Simpan
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
