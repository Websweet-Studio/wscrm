<script setup lang="ts">
import ConfirmModal from '@/components/ConfirmModal.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Coins, Edit, Plus, Search, Trash2, X } from 'lucide-vue-next';
import { ref } from 'vue';

interface Package {
    id: number;
    name: string;
    credits: number;
    price: string;
    discount_amount: string | null;
    is_active: boolean;
    sort_order: number;
    final_price: number;
}

interface Props {
    packages: { data: Package[]; current_page: number; last_page: number; per_page: number; total: number; links: any[] };
    filters?: { search?: string };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Paket Kredit AI', href: '/admin/ai/packages' }];

const search = ref(props.filters?.search || '');
const applySearch = () => {
    router.get('/admin/ai/packages', { search: search.value || undefined }, { preserveState: true, replace: true });
};

const showCreateModal = ref(false);
const showEditModal = ref(false);
const editing = ref<Package | null>(null);

const form = useForm({
    name: '',
    credits: 0,
    price: 0,
    discount_amount: '' as string | number,
    is_active: true,
    sort_order: 0,
});

const openCreate = () => {
    form.reset('name', 'credits', 'price', 'discount_amount', 'is_active', 'sort_order');
    form.discount_amount = '';
    form.clearErrors();
    showCreateModal.value = true;
};

const openEdit = (p: Package) => {
    editing.value = p;
    form.clearErrors();
    form.name = p.name;
    form.credits = p.credits;
    form.price = Number(p.price);
    form.discount_amount = p.discount_amount === null ? '' : Number(p.discount_amount);
    form.is_active = p.is_active;
    form.sort_order = p.sort_order;
    showEditModal.value = true;
};

const submitCreate = () => {
    form.post('/admin/ai/packages', {
        preserveScroll: true,
        onSuccess: () => {
            showCreateModal.value = false;
        },
    });
};

const submitEdit = () => {
    form.put(`/admin/ai/packages/${editing.value!.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            showEditModal.value = false;
        },
    });
};

const confirmDelete = (p: Package) => {
    if (!confirm(`Hapus paket "${p.name}"?`)) return;
    router.delete(`/admin/ai/packages/${p.id}`, { preserveScroll: true });
};

const formatPrice = (price: number): string =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(price);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Paket Kredit AI" />

        <div class="space-y-6">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0">
                    <div>
                        <CardTitle>Paket Kredit AI</CardTitle>
                        <CardDescription>Paket yang bisa dibeli customer lewat invoice</CardDescription>
                    </div>
                    <Button class="cursor-pointer" @click="openCreate">
                        <Plus class="mr-2 h-4 w-4" /> Tambah Paket
                    </Button>
                </CardHeader>
                <CardContent>
                    <div class="mb-4 flex gap-3">
                        <Input v-model="search" placeholder="Cari paket..." class="max-w-sm" @keyup.enter="applySearch" />
                        <Button variant="outline" class="cursor-pointer" @click="applySearch">
                            <Search class="mr-2 h-4 w-4" /> Cari
                        </Button>
                    </div>

                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Nama</TableHead>
                                <TableHead>Kredit</TableHead>
                                <TableHead>Harga</TableHead>
                                <TableHead>Diskon</TableHead>
                                <TableHead>Total</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead class="text-right">Aksi</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="p in packages.data" :key="p.id">
                                <TableCell class="font-medium">{{ p.name }}</TableCell>
                                <TableCell>
                                    <span class="inline-flex items-center gap-1"><Coins class="h-3.5 w-3.5 text-amber-500" /> {{ p.credits }}</span>
                                </TableCell>
                                <TableCell>{{ formatPrice(Number(p.price)) }}</TableCell>
                                <TableCell class="text-muted-foreground">{{ p.discount_amount !== null ? formatPrice(Number(p.discount_amount)) : '-' }}</TableCell>
                                <TableCell class="font-semibold">{{ formatPrice(p.final_price) }}</TableCell>
                                <TableCell>
                                    <Badge :variant="p.is_active ? 'default' : 'secondary'">{{ p.is_active ? 'Aktif' : 'Nonaktif' }}</Badge>
                                </TableCell>
                                <TableCell class="text-right">
                                    <div class="flex justify-end gap-1">
                                        <Button size="sm" variant="outline" class="cursor-pointer" @click="openEdit(p)"><Edit class="h-3 w-3" /></Button>
                                        <Button size="sm" variant="outline" class="cursor-pointer" @click="confirmDelete(p)"><Trash2 class="h-3 w-3" /></Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="packages.data.length === 0">
                                <TableCell colspan="7" class="py-10 text-center text-muted-foreground">Belum ada paket kredit.</TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>

        <!-- Create / Edit Modal -->
        <div v-if="showCreateModal || showEditModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-black/50" @click="showCreateModal = false; showEditModal = false"></div>
            <div class="relative mx-4 w-full max-w-lg rounded-lg bg-white p-6 shadow-xl dark:bg-gray-900">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold">{{ showCreateModal ? 'Tambah Paket' : 'Edit Paket' }}</h2>
                    <button class="cursor-pointer text-muted-foreground hover:text-foreground" @click="showCreateModal = false; showEditModal = false"><X class="h-4 w-4" /></button>
                </div>

                <form @submit.prevent="showCreateModal ? submitCreate() : submitEdit()" class="space-y-4">
                    <div>
                        <Label>Nama *</Label>
                        <Input v-model="form.name" placeholder="mis. Starter 10K" required />
                        <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
                    </div>
                    <div>
                        <Label>Jumlah Kredit *</Label>
                        <Input v-model.number="form.credits" type="number" min="1" required />
                        <p v-if="form.errors.credits" class="mt-1 text-xs text-red-500">{{ form.errors.credits }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <Label>Harga (IDR) *</Label>
                            <Input v-model.number="form.price" type="number" min="0" step="0.01" required />
                            <p v-if="form.errors.price" class="mt-1 text-xs text-red-500">{{ form.errors.price }}</p>
                        </div>
                        <div>
                            <Label>Diskon (IDR)</Label>
                            <Input v-model="form.discount_amount" type="number" min="0" step="0.01" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <Label>Sort Order</Label>
                            <Input v-model.number="form.sort_order" type="number" min="0" />
                        </div>
                        <div class="flex items-end gap-2 pb-2">
                            <Label class="flex cursor-pointer items-center gap-2 text-sm">
                                <input v-model="form.is_active" type="checkbox" class="h-4 w-4" /> Aktif
                            </Label>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <Button type="button" variant="outline" class="cursor-pointer" @click="showCreateModal = false; showEditModal = false">Batal</Button>
                        <Button type="submit" class="cursor-pointer" :disabled="form.processing">Simpan</Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
