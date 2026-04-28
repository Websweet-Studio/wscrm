<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Badge } from '@/components/ui/badge';
import { Pencil, Trash2, Plus, X } from 'lucide-vue-next';
import { useToast } from '@/composables/useToast';
import { store, update, destroy } from '@/routes/admin/task-categories';
import { type BreadcrumbItem } from '@/types';

interface TaskCategory {
    id: number;
    name: string;
    color: string | null;
    description: string | null;
    qc_checklist: string[] | null;
    tasks_count?: number;
}

defineProps<{
    categories: TaskCategory[];
}>();

const { success } = useToast();
const isCreateOpen = ref(false);
const isEditOpen = ref(false);
const editingCategory = ref<TaskCategory | null>(null);
const newQcItem = ref('');

const form = useForm({
    name: '',
    color: '#c96442',
    description: '',
    qc_checklist: [] as string[],
});

const addQcItem = () => {
    if (newQcItem.value.trim()) {
        if (!form.qc_checklist) form.qc_checklist = [];
        form.qc_checklist.push(newQcItem.value.trim());
        newQcItem.value = '';
    }
};

const removeQcItem = (index: number) => {
    if (form.qc_checklist) {
        form.qc_checklist.splice(index, 1);
    }
};

const openCreate = () => {
    form.reset();
    form.color = '#c96442';
    form.qc_checklist = [];
    newQcItem.value = '';
    isCreateOpen.value = true;
};

const openEdit = (category: TaskCategory) => {
    editingCategory.value = category;
    form.name = category.name;
    form.color = category.color || '#c96442';
    form.description = category.description || '';
    form.qc_checklist = category.qc_checklist ? [...category.qc_checklist] : [];
    newQcItem.value = '';
    isEditOpen.value = true;
};

const submitCreate = () => {
    form.post(store().url, {
        onSuccess: () => {
            isCreateOpen.value = false;
            form.reset();
            success('Berhasil', 'Kategori berhasil ditambahkan');
        },
        onError: (errors) => {
            console.error(errors);
        }
    });
};

const submitEdit = () => {
    if (!editingCategory.value) return;

    form.put(update(editingCategory.value.id).url, {
        onSuccess: () => {
            isEditOpen.value = false;
            editingCategory.value = null;
            success('Berhasil', 'Kategori berhasil diperbarui');
        },
        onError: (errors) => {
            console.error(errors);
        }
    });
};

const deleteCategory = (category: TaskCategory) => {
    if (confirm('Apakah Anda yakin ingin menghapus kategori ini? Tugas yang menggunakan kategori ini tidak akan memiliki kategori.')) {
        router.delete(destroy(category.id).url, {
            onSuccess: () => {
                success('Berhasil', 'Kategori berhasil dihapus');
            },
        });
    }
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Tugas', href: '/admin/tasks' },
    { title: 'Kategori', href: '/admin/task-categories' },
];
</script>

<template>
    <Head title="Admin - Kategori Tugas" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="w-full max-w-none space-y-4 sm:space-y-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-medium tracking-tight sm:text-3xl" style="font-family: Georgia, serif;">Kategori Tugas</h1>
                    <p class="text-sm text-muted-foreground sm:text-base">Kelola kategori untuk pengelompokan tugas.</p>
                </div>
                <Button @click="openCreate" class="w-full cursor-pointer sm:w-auto">
                    <Plus class="mr-2 h-4 w-4" />
                    <span class="hidden sm:inline">Tambah Kategori</span>
                    <span class="sm:hidden">Tambah</span>
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle style="font-family: Georgia, serif;">Daftar Kategori</CardTitle>
                    <CardDescription>
                        Total {{ categories.length }} kategori tersedia.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Warna</TableHead>
                                <TableHead>Nama</TableHead>
                                <TableHead>Deskripsi</TableHead>
                                <TableHead>Quality Control</TableHead>
                                <TableHead>Jumlah Tugas</TableHead>
                                <TableHead class="text-right">Aksi</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="category in categories" :key="category.id">
                                <TableCell>
                                    <div 
                                        class="h-6 w-6 rounded-full border border-border" 
                                        :style="{ backgroundColor: category.color || '#ccc' }"
                                    ></div>
                                </TableCell>
                                <TableCell class="font-medium">{{ category.name }}</TableCell>
                                <TableCell>{{ category.description || '-' }}</TableCell>
                                <TableCell>
                                    <div v-if="category.qc_checklist && category.qc_checklist.length > 0" class="flex max-w-xs flex-wrap gap-1">
                                        <Badge v-for="(qc, index) in category.qc_checklist" :key="index" variant="outline" class="text-xs font-normal">
                                            {{ qc }}
                                        </Badge>
                                    </div>
                                    <span v-else class="text-muted-foreground">-</span>
                                </TableCell>
                                <TableCell>
                                    <Badge variant="secondary">
                                        {{ category.tasks_count ?? 0 }} Tugas
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <Button variant="ghost" size="icon" class="h-8 w-8 cursor-pointer" @click="openEdit(category)" title="Edit">
                                            <Pencil class="h-4 w-4" />
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="h-8 w-8 cursor-pointer text-destructive hover:text-destructive hover:bg-muted"
                                            @click="deleteCategory(category)"
                                            title="Hapus"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="categories.length === 0">
                                <TableCell colspan="6" class="py-12 text-center text-muted-foreground">
                                    Belum ada kategori. Silakan buat kategori baru.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>

            <!-- Create Modal -->
            <div v-if="isCreateOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="isCreateOpen = false"></div>
                <Card class="relative w-full max-w-lg">
                    <CardHeader class="relative">
                        <Button
                            type="button"
                            variant="ghost"
                            size="icon"
                            class="absolute top-3 right-3 h-8 w-8 cursor-pointer"
                            @click="isCreateOpen = false"
                            title="Tutup"
                        >
                            <X class="h-4 w-4" />
                        </Button>
                        <CardTitle style="font-family: Georgia, serif;">Tambah Kategori</CardTitle>
                        <CardDescription>Buat kategori baru untuk mengelompokkan tugas.</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="submitCreate" class="space-y-4">
                        <div class="space-y-2">
                            <Label for="name">Nama Kategori</Label>
                            <Input id="name" v-model="form.name" required placeholder="Contoh: Bug Fix, Feature, dll" />
                            <span v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</span>
                        </div>
                        
                        <div class="space-y-2">
                            <Label for="color">Warna Label</Label>
                            <div class="flex gap-2">
                                <Input id="color" type="color" v-model="form.color" class="h-10 w-12 p-1" />
                                <Input v-model="form.color" placeholder="#000000" class="flex-1" />
                            </div>
                            <span v-if="form.errors.color" class="text-sm text-destructive">{{ form.errors.color }}</span>
                        </div>

                        <div class="space-y-2">
                            <Label for="description">Deskripsi</Label>
                            <Textarea id="description" v-model="form.description" placeholder="Deskripsi singkat kategori..." />
                            <span v-if="form.errors.description" class="text-sm text-destructive">{{ form.errors.description }}</span>
                        </div>

                        <div class="space-y-2">
                            <Label>Daftar Quality Control</Label>
                            <div class="flex gap-2">
                                <Input v-model="newQcItem" placeholder="Tambah item QC (tekan Enter)" @keydown.enter.prevent="addQcItem" />
                                <Button type="button" size="icon" class="cursor-pointer" @click="addQcItem" title="Tambah item QC">
                                    <Plus class="h-4 w-4" />
                                </Button>
                            </div>
                            <div class="mt-2 space-y-2">
                                <div
                                    v-for="(item, index) in form.qc_checklist"
                                    :key="index"
                                    class="flex items-center justify-between rounded-md border border-border bg-muted/40 p-2"
                                >
                                    <span class="text-sm">{{ item }}</span>
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="icon"
                                        class="h-7 w-7 cursor-pointer text-destructive hover:text-destructive hover:bg-background"
                                        @click="removeQcItem(index)"
                                        title="Hapus item"
                                    >
                                        <X class="h-3 w-3" />
                                    </Button>
                                </div>
                                <p v-if="!form.qc_checklist?.length" class="py-2 text-center text-sm text-muted-foreground">
                                    Belum ada item QC.
                                </p>
                            </div>
                        </div>

                        <div class="mt-4 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                            <Button type="button" variant="outline" class="w-full cursor-pointer sm:w-auto" @click="isCreateOpen = false">Batal</Button>
                            <Button type="submit" class="w-full cursor-pointer sm:w-auto" :disabled="form.processing">Simpan</Button>
                        </div>
                    </form>
                    </CardContent>
                </Card>
            </div>

            <!-- Edit Modal -->
            <div v-if="isEditOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="isEditOpen = false"></div>
                <Card class="relative w-full max-w-lg">
                    <CardHeader class="relative">
                        <Button
                            type="button"
                            variant="ghost"
                            size="icon"
                            class="absolute top-3 right-3 h-8 w-8 cursor-pointer"
                            @click="isEditOpen = false"
                            title="Tutup"
                        >
                            <X class="h-4 w-4" />
                        </Button>
                        <CardTitle style="font-family: Georgia, serif;">Edit Kategori</CardTitle>
                        <CardDescription>Ubah informasi kategori.</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="submitEdit" class="space-y-4">
                        <div class="space-y-2">
                            <Label for="edit-name">Nama Kategori</Label>
                            <Input id="edit-name" v-model="form.name" required />
                            <span v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</span>
                        </div>
                        
                        <div class="space-y-2">
                            <Label for="edit-color">Warna Label</Label>
                            <div class="flex gap-2">
                                <Input id="edit-color" type="color" v-model="form.color" class="h-10 w-12 p-1" />
                                <Input v-model="form.color" class="flex-1" />
                            </div>
                            <span v-if="form.errors.color" class="text-sm text-destructive">{{ form.errors.color }}</span>
                        </div>

                        <div class="space-y-2">
                            <Label for="edit-description">Deskripsi</Label>
                            <Textarea id="edit-description" v-model="form.description" />
                            <span v-if="form.errors.description" class="text-sm text-destructive">{{ form.errors.description }}</span>
                        </div>

                        <div class="space-y-2">
                            <Label>Daftar Quality Control</Label>
                            <div class="flex gap-2">
                                <Input v-model="newQcItem" placeholder="Tambah item QC (tekan Enter)" @keydown.enter.prevent="addQcItem" />
                                <Button type="button" size="icon" class="cursor-pointer" @click="addQcItem" title="Tambah item QC">
                                    <Plus class="h-4 w-4" />
                                </Button>
                            </div>
                            <div class="mt-2 space-y-2">
                                <div
                                    v-for="(item, index) in form.qc_checklist"
                                    :key="index"
                                    class="flex items-center justify-between rounded-md border border-border bg-muted/40 p-2"
                                >
                                    <span class="text-sm">{{ item }}</span>
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="icon"
                                        class="h-7 w-7 cursor-pointer text-destructive hover:text-destructive hover:bg-background"
                                        @click="removeQcItem(index)"
                                        title="Hapus item"
                                    >
                                        <X class="h-3 w-3" />
                                    </Button>
                                </div>
                                <p v-if="!form.qc_checklist?.length" class="py-2 text-center text-sm text-muted-foreground">
                                    Belum ada item QC.
                                </p>
                            </div>
                        </div>

                        <div class="mt-4 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                            <Button type="button" variant="outline" class="w-full cursor-pointer sm:w-auto" @click="isEditOpen = false">Batal</Button>
                            <Button type="submit" class="w-full cursor-pointer sm:w-auto" :disabled="form.processing">Simpan Perubahan</Button>
                        </div>
                    </form>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
