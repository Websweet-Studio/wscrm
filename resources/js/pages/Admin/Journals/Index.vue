<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { formatDate } from '@/lib/utils';
import { Head, Link, router } from '@inertiajs/vue3';
import { BarChart3, Bot, Calendar, Download, Edit, Plus, Search, Trash2, X } from 'lucide-vue-next';
import DatePicker from '@/components/ui/date-picker/DatePicker.vue';
import { computed, ref } from 'vue';

interface WebsiteClient { id: number; name: string; customer?: { id: number; name: string; } }
interface User { id: number; name: string; }
interface JournalActivity { type: string; type_label?: string; [key: string]: any; }
interface Journal {
    id: number; website_client_id: number; entry_date: string; activities: JournalActivity[];
    summary: string | null; user_id: number | null;
    website_client: WebsiteClient; user?: User | null;
}

interface Props {
    journals: { data: Journal[]; current_page: number; last_page: number; per_page: number; total: number; links: any[]; };
    websites: WebsiteClient[];
    filters: { website_client_id?: string; user_id?: string; date_from?: string; date_to?: string; };
}

const props = defineProps<Props>();
const websiteFilter = ref(props.filters.website_client_id || '');
const dateFrom = ref(props.filters.date_from || '');
const dateTo = ref(props.filters.date_to || '');
const showDeleteModal = ref(false);
const journalToDelete = ref<Journal | null>(null);
const showActivityModal = ref(false);
const selectedJournal = ref<Journal | null>(null);

const activitySummary = (j: Journal): string => {
    return j.activities.map(a => `${activityTypeLabels[a.type] || a.type}: ${formatDetail(a)}`).join(' | ');
};

const openActivityModal = (j: Journal) => { selectedJournal.value = j; showActivityModal.value = true; };

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' }, { title: 'Jurnal', href: '/admin/journals' },
];

const activityTypeLabels: Record<string, string> = {
    wp_update: 'WP Update', plugin_update: 'Update Plugin', theme_update: 'Update Tema',
    article: 'Artikel', page_optimization: 'Optimasi Halaman', plugin_remove: 'Hapus Plugin', other: 'Lainnya',
};

const getBadgeVariant = (type: string): "default" | "secondary" | "destructive" | "outline" | null | undefined => {
    const m: Record<string, "default" | "secondary" | "destructive" | "outline"> = {
        wp_update: 'default', plugin_update: 'secondary', theme_update: 'secondary',
        article: 'outline', page_optimization: 'destructive', plugin_remove: 'destructive', other: 'outline',
    };
    return m[type] || 'outline';
};

const formatDetail = (a: JournalActivity): string => {
    switch (a.type) {
        case 'wp_update': return `WP ${a.from_version || '-'} → ${a.to_version || '-'}${a.note ? ' (' + a.note + ')' : ''}`;
        case 'plugin_update': return `${a.plugin || '-'}: ${a.from_version || '-'} → ${a.to_version || '-'}`;
        case 'theme_update': return `${a.theme || '-'}: ${a.from_version || '-'} → ${a.to_version || '-'}`;
        case 'article': return `${a.title || '-'} (${a.word_count || 0} kata)`;
        case 'page_optimization': return `${a.page || '-'}: ${a.detail || '-'}`;
        case 'plugin_remove': return `Hapus ${a.plugin || '-'}${a.note ? ' (' + a.note + ')' : ''}`;
        default: return a.description || '-';
    }
};

const handleFilter = () => {
    router.get('/admin/journals', {
        website_client_id: websiteFilter.value, date_from: dateFrom.value, date_to: dateTo.value,
    }, { preserveState: true, replace: true });
};

const resetFilter = () => {
    websiteFilter.value = ''; dateFrom.value = ''; dateTo.value = '';
    router.get('/admin/journals', {}, { preserveState: true, replace: true });
};

const exportExcelUrl = computed(() => {
    const p = new URLSearchParams();
    if (websiteFilter.value) p.set('website_client_id', websiteFilter.value);
    if (dateFrom.value) p.set('date_from', dateFrom.value);
    if (dateTo.value) p.set('date_to', dateTo.value);
    const qs = p.toString();
    return qs ? `/admin/journals/export-excel?${qs}` : '/admin/journals/export-excel';
});

const openDeleteModal = (j: Journal) => { journalToDelete.value = j; showDeleteModal.value = true; };
const confirmDelete = () => {
    if (!journalToDelete.value) return;
    router.delete(`/admin/journals/${journalToDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => { showDeleteModal.value = false; journalToDelete.value = null; },
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Jurnal Maintenance" />

        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-medium tracking-tight" style="font-family: Georgia, serif;">Jurnal Maintenance</h1>
                    <p class="text-muted-foreground">Catatan aktivitas maintenance harian per website</p>
                </div>
                <div class="flex gap-2">
                    <Link href="/admin/journals/report">
                        <Button variant="outline" class="cursor-pointer"><BarChart3 class="mr-2 h-4 w-4" /> Laporan</Button>
                    </Link>
                    <a :href="exportExcelUrl">
                        <Button variant="outline" class="cursor-pointer"><Download class="mr-2 h-4 w-4" /> Export Excel</Button>
                    </a>
                    <Link href="/admin/websites/ai?prompt=/jurnal">
                        <Button variant="outline" class="cursor-pointer"><Bot class="mr-2 h-4 w-4" /> Tulis via AI</Button>
                    </Link>
                    <Link href="/admin/journals/create">
                        <Button class="cursor-pointer"><Plus class="mr-2 h-4 w-4" /> Catat Jurnal</Button>
                    </Link>
                </div>
            </div>

            <Card class="overflow-visible">
                <CardContent class="pt-6">
                    <div class="flex flex-wrap items-end gap-4">
                        <div>
                            <Label>Website</Label>
                            <select v-model="websiteFilter" class="mt-1 rounded-md border border-border bg-input px-3 py-2 text-sm text-foreground">
                                <option value="">Semua Website</option>
                                <option v-for="w in websites" :key="w.id" :value="String(w.id)">{{ w.name }}</option>
                            </select>
                        </div>
                        <div>
                            <Label>Dari Tanggal</Label>
                            <DatePicker v-model="dateFrom" placeholder="Dari tanggal" />
                        </div>
                        <div>
                            <Label>Sampai Tanggal</Label>
                            <DatePicker v-model="dateTo" placeholder="Sampai tanggal" />
                        </div>
                        <Button variant="outline" @click="handleFilter" class="cursor-pointer"><Search class="mr-2 h-4 w-4" /> Filter</Button>
                        <Button variant="ghost" @click="resetFilter" class="cursor-pointer"><X class="mr-2 h-4 w-4" /> Reset</Button>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardContent class="pt-6">
                    <div class="overflow-x-auto rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Website</TableHead>
                                    <TableHead>Customer</TableHead>
                                    <TableHead>Tanggal</TableHead>
                                    <TableHead>Aktivitas</TableHead>
                                    <TableHead>Oleh</TableHead>
                                    <TableHead class="w-[100px]">Aksi</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-if="journals.data.length === 0">
                                    <TableCell colspan="6" class="py-8 text-center text-muted-foreground">
                                        Belum ada jurnal. Klik "Catat Jurnal" untuk memulai.
                                    </TableCell>
                                </TableRow>
                                <TableRow v-for="j in journals.data" :key="j.id">
                                    <TableCell class="font-medium">{{ j.website_client?.name || '-' }}</TableCell>
                                    <TableCell class="whitespace-nowrap text-sm">{{ j.website_client?.customer?.name || '-' }}</TableCell>
                                    <TableCell class="whitespace-nowrap">
                                        <div class="flex items-center gap-2"><Calendar class="h-3.5 w-3.5 text-muted-foreground" />{{ formatDate(j.entry_date, 'long') }}</div>
                                    </TableCell>
                                    <TableCell class="w-[45%] max-w-[480px]">
                                        <div class="cursor-pointer" @click="openActivityModal(j)" title="Klik untuk lihat detail">
                                            <div class="flex items-center gap-2">
                                                <Badge variant="outline" class="shrink-0 text-xs">{{ j.activities.length }} aktivitas</Badge>
                                                <span class="min-w-0 truncate text-sm text-muted-foreground">{{ activitySummary(j) }}</span>
                                            </div>
                                            <p v-if="j.summary" class="truncate text-xs text-muted-foreground italic mt-1">{{ j.summary }}</p>
                                        </div>
                                    </TableCell>
                                    <TableCell class="text-sm text-muted-foreground whitespace-nowrap">{{ j.user?.name || '-' }}</TableCell>
                                    <TableCell>
                                        <div class="flex items-center gap-1">
                                            <Link :href="`/admin/journals/${j.id}/edit`">
                                                <Button size="sm" variant="outline" class="cursor-pointer" title="Edit"><Edit class="h-3.5 w-3.5" /></Button>
                                            </Link>
                                            <Button size="sm" variant="outline" @click="openDeleteModal(j)" class="cursor-pointer text-destructive" title="Hapus"><Trash2 class="h-3.5 w-3.5" /></Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                    <div v-if="journals.last_page > 1" class="flex items-center justify-between pt-4">
                        <div class="text-sm text-muted-foreground">
                            Menampilkan {{ (journals.current_page - 1) * journals.per_page + 1 }} sampai {{ Math.min(journals.current_page * journals.per_page, journals.total) }} dari {{ journals.total }} data
                        </div>
                        <div class="flex items-center space-x-2">
                            <template v-for="link in journals.links" :key="link.label">
                                <Button v-if="link.url" variant="outline" size="sm" @click="router.visit(link.url)" v-html="link.label" class="cursor-pointer" />
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
                    <h2 class="text-lg font-semibold">Hapus Jurnal</h2>
                    <p class="text-sm text-muted-foreground mt-2">
                        Yakin ingin menghapus jurnal tanggal <strong>{{ journalToDelete?.entry_date }}</strong> untuk <strong>{{ journalToDelete?.website_client?.name }}</strong>?
                    </p>
                </div>
                <div class="flex justify-end gap-3">
                    <Button variant="outline" @click="showDeleteModal = false" class="cursor-pointer">Batal</Button>
                    <Button variant="destructive" @click="confirmDelete" class="cursor-pointer">Hapus</Button>
                </div>
            </div>
        </div>

        <!-- Activity Detail Modal -->
        <div v-if="showActivityModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-black/50" @click="showActivityModal = false"></div>
            <div class="relative mx-4 w-full max-w-lg max-h-[80vh] overflow-y-auto rounded-lg bg-white p-6 shadow-xl dark:bg-gray-900">
                <div class="mb-4">
                    <h2 class="text-lg font-semibold">Detail Aktivitas</h2>
                    <p class="text-sm text-muted-foreground mt-1">
                        {{ selectedJournal?.website_client?.name }} &mdash; {{ selectedJournal ? formatDate(selectedJournal.entry_date, 'long') : '' }}
                    </p>
                </div>
                <div class="space-y-3">
                    <div v-for="(a, idx) in selectedJournal?.activities" :key="idx" class="flex items-start gap-3 rounded-md border p-3">
                        <Badge :variant="getBadgeVariant(a.type)" class="shrink-0 whitespace-nowrap text-xs">{{ activityTypeLabels[a.type] || a.type }}</Badge>
                        <span class="text-sm">{{ formatDetail(a) }}</span>
                    </div>
                </div>
                <div v-if="selectedJournal?.summary" class="mt-4 pt-4 border-t">
                    <Label class="text-xs text-muted-foreground">Ringkasan</Label>
                    <p class="text-sm mt-1">{{ selectedJournal?.summary }}</p>
                </div>
                <div class="flex justify-end mt-6">
                    <Button variant="outline" @click="showActivityModal = false" class="cursor-pointer">Tutup</Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
