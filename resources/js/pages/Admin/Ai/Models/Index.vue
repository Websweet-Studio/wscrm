<script setup lang="ts">
import ConfirmModal from '@/components/ConfirmModal.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Edit, Plus, Search, Trash2, X } from 'lucide-vue-next';
import { ref } from 'vue';

interface AiModel {
    id: number;
    model_key: string;
    display_name: string | null;
    input_rate: string;
    output_rate: string;
    is_active: boolean;
    sort_order: number;
    provider: { id: number; name: string } | null;
}

interface Props {
    models: { data: AiModel[]; current_page: number; last_page: number; per_page: number; total: number; links: any[] };
    providers: Array<{ id: number; name: string }>;
    credit_price: number | null;
    filters?: { search?: string; provider_id?: string };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Model AI', href: '/admin/ai/models' }];

const search = ref(props.filters?.search || '');
const providerFilter = ref(props.filters?.provider_id || '');
const applyFilters = () => {
    router.get('/admin/ai/models', { search: search.value || undefined, provider_id: providerFilter.value || undefined }, { preserveState: true, replace: true });
};

const showCreateModal = ref(false);
const showEditModal = ref(false);
const editing = ref<AiModel | null>(null);

const form = useForm({
    provider_id: '' as string | number,
    model_key: '',
    display_name: '',
    input_rate: 0,
    output_rate: 0,
    is_active: true,
    sort_order: 0,
});

const openCreate = () => {
    form.reset('model_key', 'display_name', 'input_rate', 'output_rate', 'is_active', 'sort_order');
    form.provider_id = props.providers[0]?.id ?? '';
    form.clearErrors();
    showCreateModal.value = true;
};

const openEdit = (m: AiModel) => {
    editing.value = m;
    form.clearErrors();
    form.provider_id = m.provider?.id ?? '';
    form.model_key = m.model_key;
    form.display_name = m.display_name || '';
    form.input_rate = Number(m.input_rate);
    form.output_rate = Number(m.output_rate);
    form.is_active = m.is_active;
    form.sort_order = m.sort_order;
    showEditModal.value = true;
};

const submitCreate = () => {
    form.post('/admin/ai/models', {
        preserveScroll: true,
        onSuccess: () => {
            showCreateModal.value = false;
        },
    });
};

const submitEdit = () => {
    form.put(`/admin/ai/models/${editing.value!.id}`, {
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
        router.delete(`/admin/ai/models/${confirmTarget.value.id}`, { preserveScroll: true });
    }
};

const confirmDelete = (m: AiModel) => {
    openConfirm(m, `Hapus model "${m.model_key}"?`);
};

// Harga per 1 juta token: rate kredit/1K × harga 1 kredit × 1000.
const formatRupiah = (n: number): string =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 2 }).format(n);
const priceInput = (m: AiModel): string => (props.credit_price === null ? '—' : formatRupiah(Number(m.input_rate) * props.credit_price * 1000));
const priceOutput = (m: AiModel): string => (props.credit_price === null ? '—' : formatRupiah(Number(m.output_rate) * props.credit_price * 1000));
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Model AI" />

        <div class="space-y-6">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0">
                    <div>
                        <CardTitle>Model AI</CardTitle>
                        <CardDescription v-if="credit_price !== null">Harga per 1 juta token input/output, referensi 1 kredit ≈ {{ formatRupiah(credit_price) }}</CardDescription>
                        <CardDescription v-else>Tambahkan paket kredit aktif agar harga rupiah tampil.</CardDescription>
                    </div>
                    <Button class="cursor-pointer" :disabled="providers.length === 0" @click="openCreate">
                        <Plus class="mr-2 h-4 w-4" /> Tambah Model
                    </Button>
                </CardHeader>
                <CardContent>
                    <div class="mb-4 flex gap-3">
                        <Input v-model="search" placeholder="Cari model..." class="max-w-xs" @keyup.enter="applyFilters" />
                        <select
                            v-model="providerFilter"
                            class="flex h-9 w-56 cursor-pointer rounded-md border border-input bg-background px-3 py-1 text-sm text-foreground shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                            @change="applyFilters"
                        >
                            <option value="">Semua provider</option>
                            <option v-for="p in providers" :key="p.id" :value="String(p.id)">{{ p.name }}</option>
                        </select>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b text-left text-muted-foreground">
                                    <th class="px-3 py-3">Model</th>
                                    <th class="px-3 py-3">Nama Tampilan</th>
                                    <th class="px-3 py-3">Provider</th>
                                    <th class="px-3 py-3">Harga Input / 1M token</th>
                                    <th class="px-3 py-3">Harga Output / 1M token</th>
                                    <th class="px-3 py-3">Status</th>
                                    <th class="px-3 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="m in models.data" :key="m.id" class="border-b last:border-0">
                                    <td class="px-3 py-3 font-mono font-medium">{{ m.model_key }}</td>
                                    <td class="px-3 py-3 text-muted-foreground">{{ m.display_name || '-' }}</td>
                                    <td class="px-3 py-3">{{ m.provider?.name || '-' }}</td>
                                    <td class="px-3 py-3">{{ priceInput(m) }}</td>
                                    <td class="px-3 py-3">{{ priceOutput(m) }}</td>
                                    <td class="px-3 py-3">
                                        <Badge :variant="m.is_active ? 'default' : 'secondary'">{{ m.is_active ? 'Aktif' : 'Nonaktif' }}</Badge>
                                    </td>
                                    <td class="px-3 py-3 text-right">
                                        <div class="flex justify-end gap-1">
                                            <Button size="sm" variant="outline" class="cursor-pointer" @click="openEdit(m)"><Edit class="h-3 w-3" /></Button>
                                            <Button size="sm" variant="outline" class="cursor-pointer" @click="confirmDelete(m)"><Trash2 class="h-3 w-3" /></Button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="models.data.length === 0">
                                    <td colspan="7" class="px-3 py-10 text-center text-muted-foreground">Belum ada model.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Create / Edit Modal -->
        <div v-if="showCreateModal || showEditModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-black/50" @click="showCreateModal = false; showEditModal = false"></div>
            <div class="relative mx-4 w-full max-w-lg rounded-lg bg-white p-6 shadow-xl dark:bg-gray-900">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold">{{ showCreateModal ? 'Tambah Model' : 'Edit Model' }}</h2>
                    <button class="cursor-pointer text-muted-foreground hover:text-foreground" @click="showCreateModal = false; showEditModal = false"><X class="h-4 w-4" /></button>
                </div>

                <form @submit.prevent="showCreateModal ? submitCreate() : submitEdit()" class="space-y-4">
                    <div>
                        <Label>Provider *</Label>
                        <select
                            v-model="form.provider_id"
                            class="mt-1 flex h-9 w-full cursor-pointer rounded-md border border-input bg-background px-3 py-1 text-sm text-foreground shadow-sm focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none dark:bg-gray-800 dark:text-white"
                        >
                            <option v-for="p in providers" :key="p.id" :value="String(p.id)">{{ p.name }}</option>
                        </select>
                        <p v-if="form.errors.provider_id" class="mt-1 text-xs text-red-500">{{ form.errors.provider_id }}</p>
                    </div>
                    <div>
                        <Label>Model Key *</Label>
                        <Input v-model="form.model_key" placeholder="mis. gpt-4o-mini" required />
                        <p v-if="form.errors.model_key" class="mt-1 text-xs text-red-500">{{ form.errors.model_key }}</p>
                    </div>
                    <div>
                        <Label>Nama Tampilan</Label>
                        <Input v-model="form.display_name" placeholder="mis. GPT-4o Mini" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <Label>Rate Input (kredit/1K)</Label>
                            <Input v-model.number="form.input_rate" type="number" min="0" step="0.0001" required />
                        </div>
                        <div>
                            <Label>Rate Output (kredit/1K)</Label>
                            <Input v-model.number="form.output_rate" type="number" min="0" step="0.0001" required />
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

        <ConfirmModal :show="showConfirm" :message="confirmMessage" variant="destructive" confirmText="Hapus" @confirm="handleConfirm" @cancel="showConfirm = false" />
    </AppLayout>
</template>
