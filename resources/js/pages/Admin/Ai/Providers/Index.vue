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
import { Edit, KeyRound, Plus, Search, Trash2, X } from 'lucide-vue-next';
import { ref } from 'vue';

interface Provider {
    id: number;
    name: string;
    endpoint: string;
    is_active: boolean;
    sort_order: number;
    api_key_set: boolean;
    models_count: number;
}

interface Props {
    providers: { data: Provider[]; current_page: number; last_page: number; per_page: number; total: number; links: any[] };
    filters?: { search?: string };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Provider AI', href: '/admin/ai/providers' }];

const search = ref(props.filters?.search || '');
const applySearch = () => {
    router.get('/admin/ai/providers', { search: search.value || undefined }, { preserveState: true, replace: true });
};

const showCreateModal = ref(false);
const showEditModal = ref(false);
const editing = ref<Provider | null>(null);

const form = useForm({
    name: '',
    endpoint: '',
    api_key: '',
    is_active: true,
    sort_order: 0,
});

const resetForm = () => form.reset('name', 'endpoint', 'api_key', 'is_active', 'sort_order');

const openCreate = () => {
    resetForm();
    form.clearErrors();
    showCreateModal.value = true;
};

const openEdit = (p: Provider) => {
    editing.value = p;
    form.clearErrors();
    form.name = p.name;
    form.endpoint = p.endpoint;
    form.api_key = '';
    form.is_active = p.is_active;
    form.sort_order = p.sort_order;
    showEditModal.value = true;
};

const submitCreate = () => {
    form.post('/admin/ai/providers', {
        preserveScroll: true,
        onSuccess: () => {
            showCreateModal.value = false;
            resetForm();
        },
    });
};

const submitEdit = () => {
    form.put(`/admin/ai/providers/${editing.value!.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            showEditModal.value = false;
        },
    });
};

const showConfirm = ref(false);
const confirmTarget = ref<any>(null);
const confirmMessage = ref('');

const openConfirm = (target: any, message: string) => {
    confirmTarget.value = target;
    confirmMessage.value = message;
    showConfirm.value = true;
};

const handleConfirm = () => {
    showConfirm.value = false;
    if (confirmTarget.value) {
        router.delete(`/admin/ai/providers/${confirmTarget.value.id}`, { preserveScroll: true });
    }
};

const confirmDelete = (p: Provider) => {
    openConfirm(p, `Hapus provider "${p.name}"? Model di bawahnya ikut terhapus.`);
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Provider AI" />

        <div class="space-y-6">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0">
                    <div>
                        <CardTitle>Provider AI</CardTitle>
                        <CardDescription>Daftar gateway AI (OpenAI-compatible) yang bisa dipakai customer</CardDescription>
                    </div>
                    <Button class="cursor-pointer" @click="openCreate">
                        <Plus class="mr-2 h-4 w-4" /> Tambah Provider
                    </Button>
                </CardHeader>
                <CardContent>
                    <div class="mb-4 flex gap-3">
                        <Input v-model="search" placeholder="Cari provider..." class="max-w-sm" @keyup.enter="applySearch" />
                        <Button variant="outline" class="cursor-pointer" @click="applySearch">
                            <Search class="mr-2 h-4 w-4" /> Cari
                        </Button>
                    </div>

                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Nama</TableHead>
                                <TableHead>Endpoint</TableHead>
                                <TableHead>API Key</TableHead>
                                <TableHead>Model</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead class="text-right">Aksi</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="p in providers.data" :key="p.id">
                                <TableCell class="font-medium">{{ p.name }}</TableCell>
                                <TableCell class="text-muted-foreground">{{ p.endpoint }}</TableCell>
                                <TableCell>
                                    <span class="inline-flex items-center gap-1 text-xs" :class="p.api_key_set ? 'text-green-600' : 'text-muted-foreground'">
                                        <KeyRound class="h-3 w-3" /> {{ p.api_key_set ? 'Terisi' : 'Kosong' }}
                                    </span>
                                </TableCell>
                                <TableCell>{{ p.models_count }}</TableCell>
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
                            <TableRow v-if="providers.data.length === 0">
                                <TableCell colspan="6" class="py-10 text-center text-muted-foreground">Belum ada provider. Tambahkan provider AI pertama.</TableCell>
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
                    <h2 class="text-lg font-semibold">{{ showCreateModal ? 'Tambah Provider' : 'Edit Provider' }}</h2>
                    <button class="cursor-pointer text-muted-foreground hover:text-foreground" @click="showCreateModal = false; showEditModal = false"><X class="h-4 w-4" /></button>
                </div>

                <form @submit.prevent="showCreateModal ? submitCreate() : submitEdit()" class="space-y-4">
                    <div>
                        <Label>Nama *</Label>
                        <Input v-model="form.name" placeholder="mis. OpenRouter" required />
                        <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
                    </div>
                    <div>
                        <Label>Endpoint (base URL) *</Label>
                        <Input v-model="form.endpoint" placeholder="https://openrouter.ai/api/v1" required />
                        <p v-if="form.errors.endpoint" class="mt-1 text-xs text-red-500">{{ form.errors.endpoint }}</p>
                    </div>
                    <div>
                        <Label>API Key {{ showEditModal ? '(kosongkan utk pertahankan)' : '' }}</Label>
                        <Input v-model="form.api_key" type="password" placeholder="sk-..." />
                        <p v-if="form.errors.api_key" class="mt-1 text-xs text-red-500">{{ form.errors.api_key }}</p>
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

        <ConfirmModal :show="showConfirm" :message="confirmMessage" variant="destructive" confirmText="Hapus" @confirm="handleConfirm" @cancel="showConfirm = false" />
    </AppLayout>
</template>
