<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Badge } from '@/components/ui/badge';
import { Pencil, Trash2, Plus } from 'lucide-vue-next';
import { useToast } from '@/components/ui/toast/use-toast';

interface TaskCategory {
    id: number;
    name: string;
    color: string | null;
    description: string | null;
    tasks_count?: number;
}

const props = defineProps<{
    categories: TaskCategory[];
}>();

const { toast } = useToast();
const isCreateOpen = ref(false);
const isEditOpen = ref(false);
const editingCategory = ref<TaskCategory | null>(null);

const form = useForm({
    name: '',
    color: '#000000',
    description: '',
});

const openCreate = () => {
    form.reset();
    form.color = '#3b82f6'; // Default blue
    isCreateOpen.value = true;
};

const openEdit = (category: TaskCategory) => {
    editingCategory.value = category;
    form.name = category.name;
    form.color = category.color || '#000000';
    form.description = category.description || '';
    isEditOpen.value = true;
};

const submitCreate = () => {
    form.post(route('admin.task-categories.store'), {
        onSuccess: () => {
            isCreateOpen.value = false;
            form.reset();
            toast({
                title: 'Berhasil',
                description: 'Kategori berhasil ditambahkan',
            });
        },
    });
};

const submitEdit = () => {
    if (!editingCategory.value) return;

    form.put(route('admin.task-categories.update', editingCategory.value.id), {
        onSuccess: () => {
            isEditOpen.value = false;
            editingCategory.value = null;
            toast({
                title: 'Berhasil',
                description: 'Kategori berhasil diperbarui',
            });
        },
    });
};

const deleteCategory = (category: TaskCategory) => {
    if (confirm('Apakah Anda yakin ingin menghapus kategori ini? Task yang menggunakan kategori ini tidak akan memiliki kategori.')) {
        router.delete(route('admin.task-categories.destroy', category.id), {
            onSuccess: () => {
                toast({
                    title: 'Berhasil',
                    description: 'Kategori berhasil dihapus',
                });
            },
        });
    }
};
</script>

<template>
    <Head title="Task Categories" />
    
    <AppLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-3xl font-bold tracking-tight">Kategori Task</h2>
                    <p class="text-muted-foreground">Kelola kategori untuk pengelompokan task.</p>
                </div>
                <Button @click="openCreate">
                    <Plus class="mr-2 h-4 w-4" />
                    Tambah Kategori
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Daftar Kategori</CardTitle>
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
                                <TableHead>Jumlah Task</TableHead>
                                <TableHead class="text-right">Aksi</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="category in categories" :key="category.id">
                                <TableCell>
                                    <div 
                                        class="h-6 w-6 rounded-full border" 
                                        :style="{ backgroundColor: category.color || '#ccc' }"
                                    ></div>
                                </TableCell>
                                <TableCell class="font-medium">{{ category.name }}</TableCell>
                                <TableCell>{{ category.description || '-' }}</TableCell>
                                <TableCell>
                                    <Badge variant="secondary">
                                        {{ category.tasks_count }} Task
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <Button variant="ghost" size="icon" @click="openEdit(category)">
                                            <Pencil class="h-4 w-4" />
                                        </Button>
                                        <Button variant="ghost" size="icon" class="text-red-500 hover:text-red-600" @click="deleteCategory(category)">
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="categories.length === 0">
                                <TableCell colspan="5" class="text-center py-8 text-muted-foreground">
                                    Belum ada kategori. Silakan buat kategori baru.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>

            <!-- Create Modal -->
            <Dialog :open="isCreateOpen" @update:open="isCreateOpen = $event">
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Tambah Kategori</DialogTitle>
                        <DialogDescription>
                            Buat kategori baru untuk mengelompokkan task.
                        </DialogDescription>
                    </DialogHeader>
                    
                    <form @submit.prevent="submitCreate" class="space-y-4">
                        <div class="space-y-2">
                            <Label for="name">Nama Kategori</Label>
                            <Input id="name" v-model="form.name" required placeholder="Contoh: Bug Fix, Feature, dll" />
                        </div>
                        
                        <div class="space-y-2">
                            <Label for="color">Warna Label</Label>
                            <div class="flex gap-2">
                                <Input id="color" type="color" v-model="form.color" class="w-12 h-10 p-1" />
                                <Input v-model="form.color" placeholder="#000000" class="flex-1" />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="description">Deskripsi</Label>
                            <Textarea id="description" v-model="form.description" placeholder="Deskripsi singkat kategori..." />
                        </div>

                        <DialogFooter>
                            <Button type="button" variant="outline" @click="isCreateOpen = false">Batal</Button>
                            <Button type="submit" :disabled="form.processing">Simpan</Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <!-- Edit Modal -->
            <Dialog :open="isEditOpen" @update:open="isEditOpen = $event">
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Edit Kategori</DialogTitle>
                        <DialogDescription>
                            Ubah informasi kategori.
                        </DialogDescription>
                    </DialogHeader>
                    
                    <form @submit.prevent="submitEdit" class="space-y-4">
                        <div class="space-y-2">
                            <Label for="edit-name">Nama Kategori</Label>
                            <Input id="edit-name" v-model="form.name" required />
                        </div>
                        
                        <div class="space-y-2">
                            <Label for="edit-color">Warna Label</Label>
                            <div class="flex gap-2">
                                <Input id="edit-color" type="color" v-model="form.color" class="w-12 h-10 p-1" />
                                <Input v-model="form.color" class="flex-1" />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="edit-description">Deskripsi</Label>
                            <Textarea id="edit-description" v-model="form.description" />
                        </div>

                        <DialogFooter>
                            <Button type="button" variant="outline" @click="isEditOpen = false">Batal</Button>
                            <Button type="submit" :disabled="form.processing">Simpan Perubahan</Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>
