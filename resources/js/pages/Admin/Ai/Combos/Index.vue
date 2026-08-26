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
import { ArrowDown, ArrowUp, Edit, Plus, Trash2, X } from 'lucide-vue-next';
import { ref } from 'vue';

interface Model {
    id: number;
    model_key: string;
    display_name: string | null;
    provider: { id: number; name: string } | null;
}

interface Combo {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    is_active: boolean;
    sort_order: number;
    models: Array<{
        id: number;
        model_key: string;
        display_name: string | null;
        provider: { id: number; name: string } | null;
        pivot: { priority: number };
    }>;
}

interface Props {
    combos: { data: Combo[]; current_page: number; last_page: number; per_page: number; total: number; links: any[] };
    models: Model[];
    filters?: { search?: string };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Combo AI', href: '/admin/ai/combos' }];

const search = ref(props.filters?.search || '');
const applySearch = () => {
    router.get('/admin/ai/combos', { search: search.value || undefined }, { preserveState: true, replace: true });
};

const showCreateModal = ref(false);
const showEditModal = ref(false);
const editing = ref<Combo | null>(null);

interface ComboFormItem {
    ai_model_id: number;
    priority: number;
}

const form = useForm({
    name: '',
    description: '',
    is_active: true,
    sort_order: 0,
    models: [] as ComboFormItem[],
});

const comboModels = ref<ComboFormItem[]>([]);

const addModel = () => {
    const usedIds = comboModels.value.map((m) => m.ai_model_id);
    const available = props.models.find((m) => !usedIds.includes(m.id));
    if (available) {
        comboModels.value.push({ ai_model_id: available.id, priority: comboModels.value.length });
    }
};

const removeModel = (index: number) => {
    comboModels.value.splice(index, 1);
    reindexPriorities();
};

const moveUp = (index: number) => {
    if (index <= 0) return;
    const temp = comboModels.value[index];
    comboModels.value[index] = comboModels.value[index - 1];
    comboModels.value[index - 1] = temp;
    reindexPriorities();
};

const moveDown = (index: number) => {
    if (index >= comboModels.value.length - 1) return;
    const temp = comboModels.value[index];
    comboModels.value[index] = comboModels.value[index + 1];
    comboModels.value[index + 1] = temp;
    reindexPriorities();
};

const reindexPriorities = () => {
    comboModels.value.forEach((m, i) => (m.priority = i));
};

const openCreate = () => {
    form.reset('name', 'description', 'is_active', 'sort_order');
    form.models = [];
    comboModels.value = [];
    form.clearErrors();
    showCreateModal.value = true;
};

const openEdit = (c: Combo) => {
    editing.value = c;
    form.clearErrors();
    form.name = c.name;
    form.description = c.description || '';
    form.is_active = c.is_active;
    form.sort_order = c.sort_order;
    comboModels.value = c.models.map((m) => ({
        ai_model_id: m.id,
        priority: m.pivot.priority,
    }));
    showEditModal.value = true;
};

const submitCreate = () => {
    form.models = comboModels.value;
    form.post('/admin/ai/combos', {
        preserveScroll: true,
        onSuccess: () => {
            showCreateModal.value = false;
        },
    });
};

const submitEdit = () => {
    form.models = comboModels.value;
    form.put(`/admin/ai/combos/${editing.value!.id}`, {
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
        router.delete(`/admin/ai/combos/${confirmTarget.value.id}`, { preserveScroll: true });
    }
};

const confirmDelete = (c: Combo) => {
    openConfirm(c, `Hapus combo "${c.name}"?`);
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Combo AI" />

        <div class="space-y-6">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0">
                    <div>
                        <CardTitle>Combo AI</CardTitle>
                        <CardDescription>Kelola combo model — atur prioritas &amp; fallback dalam satu combo.</CardDescription>
                    </div>
                    <Button class="cursor-pointer" :disabled="models.length === 0" @click="openCreate">
                        <Plus class="mr-2 h-4 w-4" /> Tambah Combo
                    </Button>
                </CardHeader>
                <CardContent>
                    <div class="mb-4 flex gap-3">
                        <Input v-model="search" placeholder="Cari combo..." class="max-w-xs" @keyup.enter="applySearch" />
                    </div>

                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Nama</TableHead>
                                <TableHead>Deskripsi</TableHead>
                                <TableHead>Urutan Model (prioritas → fallback)</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead class="text-right">Aksi</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="c in combos.data" :key="c.id">
                                <TableCell class="font-medium">{{ c.name }}</TableCell>
                                <TableCell class="text-muted-foreground">{{ c.description || '-' }}</TableCell>
                                <TableCell>
                                    <div class="flex flex-wrap items-center gap-1">
                                        <template v-for="(m, idx) in [...c.models].sort((a, b) => a.pivot.priority - b.pivot.priority)" :key="m.id">
                                            <span v-if="idx > 0" class="text-xs text-muted-foreground">→</span>
                                            <Badge variant="outline" class="font-mono text-xs">{{ m.model_key }}</Badge>
                                        </template>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="c.is_active ? 'default' : 'secondary'">{{ c.is_active ? 'Aktif' : 'Nonaktif' }}</Badge>
                                </TableCell>
                                <TableCell class="text-right">
                                    <div class="flex justify-end gap-1">
                                        <Button size="sm" variant="outline" class="cursor-pointer" @click="openEdit(c)"><Edit class="h-3 w-3" /></Button>
                                        <Button size="sm" variant="outline" class="cursor-pointer" @click="confirmDelete(c)"><Trash2 class="h-3 w-3" /></Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="combos.data.length === 0">
                                <TableCell colspan="5" class="py-10 text-center text-muted-foreground">Belum ada combo.</TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>

        <!-- Create / Edit Modal -->
        <div v-if="showCreateModal || showEditModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-black/50" @click="showCreateModal = false; showEditModal = false"></div>
            <div class="relative mx-4 w-full max-w-2xl rounded-lg bg-white p-6 shadow-xl dark:bg-gray-900">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold">{{ showCreateModal ? 'Tambah Combo' : 'Edit Combo' }}</h2>
                    <button class="cursor-pointer text-muted-foreground hover:text-foreground" @click="showCreateModal = false; showEditModal = false"><X class="h-4 w-4" /></button>
                </div>

                <form @submit.prevent="showCreateModal ? submitCreate() : submitEdit()" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <Label>Nama Combo *</Label>
                            <Input v-model="form.name" placeholder="Harus sama dengan nama combo di 9router" required />
                            <p class="mt-1 text-xs text-muted-foreground">Nama harus persis sama dengan combo yang terdaftar di 9router.</p>
                            <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
                        </div>
                        <div>
                            <Label>Deskripsi</Label>
                            <Input v-model="form.description" placeholder="Keterangan singkat" />
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

                    <!-- Model List -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <Label>Model dalam Combo *</Label>
                            <Button type="button" size="sm" variant="outline" class="cursor-pointer" @click="addModel" :disabled="comboModels.length >= models.length">
                                <Plus class="mr-1 h-3 w-3" /> Tambah Model
                            </Button>
                        </div>
                        <p v-if="form.errors.models" class="text-xs text-red-500">{{ form.errors.models }}</p>

                        <div v-if="comboModels.length === 0" class="rounded-lg border border-dashed border-border/60 p-4 text-center text-sm text-muted-foreground">
                            Belum ada model. Klik "Tambah Model" untuk menambah.
                        </div>

                        <div v-for="(item, idx) in comboModels" :key="idx" class="flex items-center gap-2 rounded-lg border border-border/60 bg-muted/30 p-2">
                            <div class="flex flex-col gap-0.5">
                                <button type="button" class="cursor-pointer text-muted-foreground hover:text-foreground disabled:opacity-30" :disabled="idx === 0" @click="moveUp(idx)">
                                    <ArrowUp class="h-3.5 w-3.5" />
                                </button>
                                <button type="button" class="cursor-pointer text-muted-foreground hover:text-foreground disabled:opacity-30" :disabled="idx === comboModels.length - 1" @click="moveDown(idx)">
                                    <ArrowDown class="h-3.5 w-3.5" />
                                </button>
                            </div>
                            <div class="flex items-center gap-1">
                                <Badge variant="secondary" class="text-xs">#{{ idx + 1 }}</Badge>
                                <span v-if="idx === 0" class="text-xs text-green-600 dark:text-green-400">utama</span>
                                <span v-else class="text-xs text-muted-foreground">fallback</span>
                            </div>
                            <select
                                v-model="item.ai_model_id"
                                class="flex h-8 flex-1 cursor-pointer rounded-md border border-input bg-background px-2 text-sm focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                            >
                                <option v-for="m in models" :key="m.id" :value="m.id" :disabled="comboModels.some((cm, cmIdx) => cmIdx !== idx && cm.ai_model_id === m.id)">
                                    {{ m.model_key }}{{ m.display_name ? ' (' + m.display_name + ')' : '' }} — {{ m.provider?.name || '-' }}
                                </option>
                            </select>
                            <Button type="button" size="sm" variant="outline" class="cursor-pointer" @click="removeModel(idx)">
                                <Trash2 class="h-3 w-3" />
                            </Button>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <Button type="button" variant="outline" class="cursor-pointer" @click="showCreateModal = false; showEditModal = false">Batal</Button>
                        <Button type="submit" class="cursor-pointer" :disabled="form.processing || comboModels.length === 0">Simpan</Button>
                    </div>
                </form>
            </div>
        </div>

        <ConfirmModal :show="showConfirm" :message="confirmMessage" variant="destructive" confirmText="Hapus" @confirm="handleConfirm" @cancel="showConfirm = false" />
    </AppLayout>
</template>
