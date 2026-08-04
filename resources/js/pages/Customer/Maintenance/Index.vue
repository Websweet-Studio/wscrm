<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import CustomerLayout from '@/layouts/CustomerLayout.vue';
import { formatDate } from '@/lib/utils';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Activity, ArrowRight, Calendar, ClipboardList, FileText, Search, Wrench, X } from 'lucide-vue-next';
import DatePicker from '@/components/ui/date-picker/DatePicker.vue';
import { computed, ref } from 'vue';

interface WebsiteClient { id: number; name: string; }
interface JournalActivity { type: string; [key: string]: any; }
interface Journal {
    id: number; website_client_id: number; entry_date: string; activities: JournalActivity[];
    summary: string | null;
    website_client: WebsiteClient;
}

interface ChartDay { date: string; label: string; total: number; byType: Record<string, number>; height_pct: number; }

interface Props {
    journals: { data: Journal[]; current_page: number; last_page: number; per_page: number; total: number; links: any[] };
    websites: WebsiteClient[];
    filters: { website_client_id?: string; date_from?: string; date_to?: string };
    chartData: ChartDay[];
}

const props = defineProps<Props>();

const websiteFilter = ref(props.filters.website_client_id || '');
const dateFrom = ref(props.filters.date_from || '');
const dateTo = ref(props.filters.date_to || '');

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/customer/dashboard' },
    { title: 'Maintenance', href: '/customer/maintenance' },
];

const activityTypeLabels: Record<string, string> = {
    wp_update: 'WP Update', plugin_update: 'Update Plugin', theme_update: 'Update Tema',
    article: 'Artikel', page_optimization: 'Optimasi Halaman', other: 'Lainnya',
};

const getBadgeVariant = (type: string): "default" | "secondary" | "destructive" | "outline" | null | undefined => {
    const m: Record<string, "default" | "secondary" | "destructive" | "outline"> = {
        wp_update: 'default', plugin_update: 'secondary', theme_update: 'secondary',
        article: 'outline', page_optimization: 'destructive', other: 'outline',
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
        default: return a.description || '-';
    }
};

const activitySummary = (j: Journal): string => {
    return j.activities.map(a => `${activityTypeLabels[a.type] || a.type}: ${formatDetail(a)}`).join(' | ');
};

const totalEntries = computed(() => props.journals.total);
const totalActivities = computed(() =>
    props.journals.data.reduce((sum, j) => sum + j.activities.length, 0)
);
const uniqueWebsites = computed(() => new Set(props.journals.data.map(j => j.website_client?.name)).size);

const showActivityModal = ref(false);
const selectedJournal = ref<Journal | null>(null);
const openActivityModal = (j: Journal) => { selectedJournal.value = j; showActivityModal.value = true; };

const handleFilter = () => {
    router.get('/customer/maintenance', {
        website_client_id: websiteFilter.value, date_from: dateFrom.value, date_to: dateTo.value,
    }, { preserveState: true, replace: true });
};

const resetFilter = () => {
    websiteFilter.value = ''; dateFrom.value = ''; dateTo.value = '';
    router.get('/customer/maintenance', {}, { preserveState: true, replace: true });
};
</script>

<template>
    <Head title="Maintenance" />

    <CustomerLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-4 sm:space-y-6 sm:p-6">
            <!-- Hero Card -->
            <Card class="relative overflow-hidden border-border/60 bg-card/70 shadow-sm backdrop-blur">
                <div class="pointer-events-none absolute inset-0 opacity-60 dark:opacity-80">
                    <div class="absolute -inset-24 bg-[radial-gradient(closest-side,rgba(59,130,246,0.16),transparent_65%)]"></div>
                    <div class="absolute -right-24 -top-32 h-96 w-96 bg-[radial-gradient(closest-side,rgba(16,185,129,0.14),transparent_60%)]"></div>
                    <div class="absolute inset-0 bg-[linear-gradient(to_right,transparent_0,rgba(59,130,246,0.05)_50%,transparent_100%)]"></div>
                </div>
                <CardContent class="relative p-4 sm:p-6">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <div class="mb-2 inline-flex items-center gap-2 rounded-full border border-border/60 bg-background/60 px-2.5 py-1 text-xs text-muted-foreground">
                                <Wrench class="h-3.5 w-3.5 text-blue-600 dark:text-blue-400" />
                                <span>Jurnal Maintenance</span>
                            </div>
                            <h1 class="font-heading text-2xl font-medium tracking-tight sm:text-3xl">Maintenance Website</h1>
                            <p class="mt-1 text-sm text-muted-foreground sm:text-base">Pantau progres pengerjaan maintenance website Anda secara transparan</p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Stat Cards -->
            <div class="grid gap-3 sm:grid-cols-2 sm:gap-4 lg:grid-cols-3">
                <Card class="rounded-lg border-border/60 shadow-sm">
                    <CardHeader class="pb-2">
                        <CardDescription>Total Entry</CardDescription>
                        <CardTitle class="text-2xl font-semibold">{{ totalEntries }}</CardTitle>
                    </CardHeader>
                </Card>
                <Card class="rounded-lg border-border/60 shadow-sm">
                    <CardHeader class="pb-2">
                        <CardDescription>Total Aktivitas</CardDescription>
                        <CardTitle class="text-2xl font-semibold">{{ totalActivities }}</CardTitle>
                    </CardHeader>
                </Card>
                <Card class="rounded-lg border-border/60 shadow-sm">
                    <CardHeader class="pb-2">
                        <CardDescription>Website</CardDescription>
                        <CardTitle class="text-2xl font-semibold">{{ uniqueWebsites }}</CardTitle>
                    </CardHeader>
                </Card>
            </div>

            <!-- Chart -->
            <Card v-if="chartData.length > 0" class="rounded-lg border-border/60 shadow-sm">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Activity class="h-5 w-5" />
                        Aktivitas 30 Hari Terakhir
                    </CardTitle>
                    <CardDescription>Jumlah aktivitas maintenance per hari dalam sebulan terakhir</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="flex items-end gap-1 h-40 sm:h-48">
                        <div v-for="(d, i) in chartData" :key="i" class="flex-1 flex flex-col items-center justify-end h-full group relative">
                            <span class="mb-1 text-[10px] text-muted-foreground opacity-0 group-hover:opacity-100 transition-opacity">{{ d.total }}</span>
                            <div
                                class="w-full rounded-t-sm bg-primary/60 hover:bg-primary transition-colors min-h-[2px]"
                                :style="{ height: Math.max(d.height_pct, 2) + '%' }"
                                :title="`${d.label}: ${d.total} aktivitas`"
                            ></div>
                            <span v-if="i % 5 === 0 || i === chartData.length - 1" class="mt-1 text-[10px] text-muted-foreground">{{ d.label }}</span>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Filter -->
            <Card class="overflow-visible rounded-lg border-border/60 shadow-sm">
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

            <!-- Journals Table -->
            <Card class="rounded-lg border-border/60 shadow-sm">
                <CardHeader>
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <CardTitle class="flex items-center gap-2">
                                <ClipboardList class="h-5 w-5" />
                                Jurnal
                            </CardTitle>
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="overflow-hidden rounded-lg border border-border/60">
                        <div class="overflow-x-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Website</TableHead>
                                        <TableHead>Tanggal</TableHead>
                                        <TableHead>Aktivitas</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-if="journals.data.length === 0">
                                        <TableCell colspan="3" class="py-8 text-center text-muted-foreground">
                                            Belum ada catatan maintenance untuk website Anda.
                                        </TableCell>
                                    </TableRow>
                                    <TableRow v-for="j in journals.data" :key="j.id">
                                        <TableCell class="font-medium">{{ j.website_client?.name || '-' }}</TableCell>
                                        <TableCell class="whitespace-nowrap">
                                            <div class="flex items-center gap-2"><Calendar class="h-3.5 w-3.5 text-muted-foreground" />{{ formatDate(j.entry_date, 'long') }}</div>
                                        </TableCell>
                                        <TableCell class="max-w-[480px]">
                                            <div class="cursor-pointer" @click="openActivityModal(j)" title="Klik untuk lihat detail">
                                                <div class="flex items-center gap-2">
                                                    <Badge variant="outline" class="shrink-0 text-xs">{{ j.activities.length }} aktivitas</Badge>
                                                    <span class="min-w-0 truncate text-sm text-muted-foreground">{{ activitySummary(j) }}</span>
                                                </div>
                                                <p v-if="j.summary" class="truncate text-xs text-muted-foreground italic mt-1">{{ j.summary }}</p>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>
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
    </CustomerLayout>
</template>
