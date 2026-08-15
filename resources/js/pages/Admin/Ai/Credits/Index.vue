<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Coins, Minus, Plus, Search, Settings2, X, ArrowUpDown, ChevronDown, ChevronUp } from 'lucide-vue-next';
import { ref } from 'vue';

interface CustomerRow {
    id: number;
    name: string;
    email: string;
    username: string | null;
    ai_balance: number | null;
}

interface Props {
    customers: { data: CustomerRow[]; current_page: number; last_page: number; per_page: number; total: number; links: any[] };
    filters?: { search?: string; sort_by?: string; sort_dir?: string };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Kredit AI Customer', href: '/admin/ai/credits' }];

const search = ref(props.filters?.search || '');
const sortBy = ref(props.filters?.sort_by || 'name');
const sortDir = ref(props.filters?.sort_dir === 'desc' ? 'desc' : 'asc');
const applySearch = () => {
    router.get('/admin/ai/credits', { search: search.value || undefined, sort_by: sortBy.value, sort_dir: sortDir.value }, { preserveState: true, replace: true });
};

const toggleSort = (col: string) => {
    if (sortBy.value === col) {
        sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortBy.value = col;
        sortDir.value = 'asc';
    }
    router.get('/admin/ai/credits', { search: search.value || undefined, sort_by: sortBy.value, sort_dir: sortDir.value }, { preserveState: true, replace: true });
};

const sortIcon = (col: string) => {
    if (sortBy.value !== col) return 'none';
    return sortDir.value === 'asc' ? 'asc' : 'desc';
};

const showModal = ref(false);
const editing = ref<CustomerRow | null>(null);

const form = useForm({
    customer_id: 0,
    action: 'add',
    credits: 0,
    description: '',
});

const openAdjust = (c: CustomerRow) => {
    editing.value = c;
    form.clearErrors();
    form.customer_id = c.id;
    form.action = 'add';
    form.credits = 0;
    form.description = '';
    showModal.value = true;
};

const submit = () => {
    form.post('/admin/ai/credits/adjust', {
        preserveScroll: true,
        onSuccess: () => {
            showModal.value = false;
        },
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Kredit AI Customer" />

        <div class="space-y-6">
            <Card>
                <CardHeader>
                    <CardTitle>Saldo Kredit AI Customer</CardTitle>
                    <CardDescription>Kelola saldo kredit AI per customer (tambah, kurangi, atau set)</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="mb-4 flex gap-3">
                        <Input v-model="search" placeholder="Cari nama / email / username..." class="max-w-sm" @keyup.enter="applySearch" />
                        <Button variant="outline" class="cursor-pointer" @click="applySearch">
                            <Search class="mr-2 h-4 w-4" /> Cari
                        </Button>
                    </div>

                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>
                                    <button type="button" class="inline-flex cursor-pointer items-center gap-1 hover:text-foreground" @click="toggleSort('name')">
                                        Customer
                                        <ChevronUp v-if="sortIcon('name') === 'asc'" class="h-3.5 w-3.5 text-primary" />
                                        <ChevronDown v-else-if="sortIcon('name') === 'desc'" class="h-3.5 w-3.5 text-primary" />
                                        <ArrowUpDown v-else class="h-3.5 w-3.5 opacity-40" />
                                    </button>
                                </TableHead>
                                <TableHead>
                                    <button type="button" class="inline-flex cursor-pointer items-center gap-1 hover:text-foreground" @click="toggleSort('username')">
                                        Username
                                        <ChevronUp v-if="sortIcon('username') === 'asc'" class="h-3.5 w-3.5 text-primary" />
                                        <ChevronDown v-else-if="sortIcon('username') === 'desc'" class="h-3.5 w-3.5 text-primary" />
                                        <ArrowUpDown v-else class="h-3.5 w-3.5 opacity-40" />
                                    </button>
                                </TableHead>
                                <TableHead>
                                    <button type="button" class="inline-flex cursor-pointer items-center gap-1 hover:text-foreground" @click="toggleSort('ai_balance')">
                                        Saldo Kredit
                                        <ChevronUp v-if="sortIcon('ai_balance') === 'asc'" class="h-3.5 w-3.5 text-primary" />
                                        <ChevronDown v-else-if="sortIcon('ai_balance') === 'desc'" class="h-3.5 w-3.5 text-primary" />
                                        <ArrowUpDown v-else class="h-3.5 w-3.5 opacity-40" />
                                    </button>
                                </TableHead>
                                <TableHead class="text-right">Aksi</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="c in customers.data" :key="c.id">
                                <TableCell>
                                    <div class="font-medium">{{ c.name }}</div>
                                    <div class="text-xs text-muted-foreground">{{ c.email }}</div>
                                </TableCell>
                                <TableCell class="text-muted-foreground">{{ c.username || '-' }}</TableCell>
                                <TableCell>
                                    <span class="inline-flex items-center gap-1 font-semibold">
                                        <Coins class="h-4 w-4 text-amber-500" /> {{ c.ai_balance ?? 0 }}
                                    </span>
                                </TableCell>
                                <TableCell class="text-right">
                                    <Button size="sm" variant="outline" class="cursor-pointer" @click="openAdjust(c)">
                                        <Settings2 class="mr-1 h-3 w-3" /> Adjust
                                    </Button>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="customers.data.length === 0">
                                <TableCell colspan="4" class="py-10 text-center text-muted-foreground">Tidak ada customer.</TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>

        <!-- Adjust Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-black/50" @click="showModal = false"></div>
            <div class="relative mx-4 w-full max-w-md rounded-lg bg-white p-6 shadow-xl dark:bg-gray-900">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold">Adjust Saldo — {{ editing?.name }}</h2>
                    <button class="cursor-pointer text-muted-foreground hover:text-foreground" @click="showModal = false"><X class="h-4 w-4" /></button>
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <Label>Aksi</Label>
                        <div class="mt-1 grid grid-cols-3 gap-2">
                            <button
                                type="button"
                                class="cursor-pointer rounded-md border px-3 py-2 text-sm font-medium"
                                :class="form.action === 'add' ? 'border-primary bg-primary/10 text-primary' : 'hover:bg-muted'"
                                @click="form.action = 'add'"
                            >
                                <Plus class="mr-1 inline h-3.5 w-3.5" /> Tambah
                            </button>
                            <button
                                type="button"
                                class="cursor-pointer rounded-md border px-3 py-2 text-sm font-medium"
                                :class="form.action === 'subtract' ? 'border-destructive bg-destructive/10 text-destructive' : 'hover:bg-muted'"
                                @click="form.action = 'subtract'"
                            >
                                <Minus class="mr-1 inline h-3.5 w-3.5" /> Kurangi
                            </button>
                            <button
                                type="button"
                                class="cursor-pointer rounded-md border px-3 py-2 text-sm font-medium"
                                :class="form.action === 'set' ? 'border-primary bg-primary/10 text-primary' : 'hover:bg-muted'"
                                @click="form.action = 'set'"
                            >
                                Set
                            </button>
                        </div>
                    </div>
                    <div>
                        <Label>{{ form.action === 'set' ? 'Saldo Akhir' : 'Jumlah Kredit' }}</Label>
                        <Input v-model.number="form.credits" type="number" min="0" required />
                        <p v-if="form.errors.credits" class="mt-1 text-xs text-red-500">{{ form.errors.credits }}</p>
                    </div>
                    <div>
                        <Label>Catatan</Label>
                        <Input v-model="form.description" placeholder="mis. bonus promo, koreksi saldo" />
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <Button type="button" variant="outline" class="cursor-pointer" @click="showModal = false">Batal</Button>
                        <Button type="submit" class="cursor-pointer" :disabled="form.processing">Simpan</Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
