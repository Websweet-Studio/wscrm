<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { BarChart3, Download, FileText, PenTool, RefreshCw, Search, Settings, Activity, FileEdit } from 'lucide-vue-next';
import DatePicker from '@/components/ui/date-picker/DatePicker.vue';
import { computed, ref } from 'vue';

interface ActivityTypeLabel { value: string; label: string; }
interface JournalActivity { type: string; type_label: string; [key: string]: any; }
interface ReportEntry { website_name: string; entry_date: string; activities: JournalActivity[]; user_name?: string; }
interface ReportStats { total_entries: number; total_activities: number; wp_updates: number; plugin_updates: number; theme_updates: number; articles: number; page_optimizations: number; others: number; }

interface Props {
    reportData: { entries: ReportEntry[]; stats: ReportStats; activity_type_labels: ActivityTypeLabel[]; };
    websites: { id: number; name: string }[];
    filters: { period: string; date_from: string; date_to: string; website_client_id?: string; };
}

const props = defineProps<Props>();
const period = ref(props.filters.period);
const dateFrom = ref(props.filters.date_from);
const dateTo = ref(props.filters.date_to);
const websiteClientId = ref(props.filters.website_client_id || '');

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' }, { title: 'Jurnal', href: '/admin/journals' }, { title: 'Laporan', href: '/admin/journals/report' },
];

const handleFilter = () => {
    router.get('/admin/journals/report', {
        period: period.value, date_from: dateFrom.value, date_to: dateTo.value, website_client_id: websiteClientId.value,
    }, { preserveState: true, replace: true });
};

const exportUrl = computed(() => {
    const p = new URLSearchParams({ period: period.value, date_from: dateFrom.value, date_to: dateTo.value });
    if (websiteClientId.value) p.set('website_client_id', websiteClientId.value);
    return `/admin/journals/export?${p.toString()}`;
});

const getBadgeVariant = (type: string): "default" | "secondary" | "destructive" | "outline" | null | undefined => {
    const m: Record<string, "default" | "secondary" | "destructive" | "outline"> = {
        wp_update: 'default', plugin_update: 'secondary', theme_update: 'secondary',
        article: 'outline', page_optimization: 'destructive', other: 'outline',
    };
    return m[type] || 'outline';
};

const formatDetail = (a: JournalActivity): string => {
    switch (a.type) {
        case 'wp_update': return `WP ${a.from_version || '-'} → ${a.to_version || '-'}${a.note ? '(' + a.note + ')' : ''}`;
        case 'plugin_update': return `${a.plugin || '-'}: ${a.from_version || '-'} → ${a.to_version || '-'}`;
        case 'theme_update': return `${a.theme || '-'}: ${a.from_version || '-'} → ${a.to_version || '-'}`;
        case 'article': return `${a.title || '-'} (${a.word_count || 0} kata)`;
        case 'page_optimization': return `${a.page || '-'}: ${a.detail || '-'}`;
        default: return a.description || '-';
    }
};

const statCards = [
    { label: 'Update WP', value: props.reportData.stats.wp_updates, icon: RefreshCw, color: 'text-blue-600', bg: 'bg-blue-50' },
    { label: 'Update Plugin', value: props.reportData.stats.plugin_updates, icon: Settings, color: 'text-purple-600', bg: 'bg-purple-50' },
    { label: 'Update Tema', value: props.reportData.stats.theme_updates, icon: RefreshCw, color: 'text-indigo-600', bg: 'bg-indigo-50' },
    { label: 'Artikel', value: props.reportData.stats.articles, icon: PenTool, color: 'text-green-600', bg: 'bg-green-50' },
    { label: 'Optimasi', value: props.reportData.stats.page_optimizations, icon: FileEdit, color: 'text-orange-600', bg: 'bg-orange-50' },
    { label: 'Lainnya', value: props.reportData.stats.others, icon: Activity, color: 'text-gray-600', bg: 'bg-gray-50' },
];

const uniqueWebsites = computed(() => new Set(props.reportData.entries.map(e => e.website_name)).size);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Laporan Jurnal Maintenance" />

        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-medium tracking-tight" style="font-family: Georgia, serif;">Laporan Maintenance</h1>
                    <p class="text-muted-foreground">Rekap aktivitas maintenance website klien</p>
                </div>
                <a :href="exportUrl">
                    <Button variant="outline" class="cursor-pointer"><Download class="mr-2 h-4 w-4" /> Export CSV</Button>
                </a>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Total Entry Jurnal</CardTitle>
                        <FileText class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent><p class="text-2xl font-bold">{{ reportData.stats.total_entries }}</p></CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Total Aktivitas</CardTitle>
                        <Activity class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent><p class="text-2xl font-bold">{{ reportData.stats.total_activities }}</p></CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Website Tercakup</CardTitle>
                        <BarChart3 class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent><p class="text-2xl font-bold">{{ uniqueWebsites }}</p></CardContent>
                </Card>
            </div>

            <Card>
                <CardHeader><CardTitle class="text-base">Statistik per Tipe Aktivitas</CardTitle></CardHeader>
                <CardContent>
                    <div class="grid grid-cols-3 md:grid-cols-6 gap-4">
                        <div v-for="s in statCards" :key="s.label" :class="[s.bg, 'rounded-lg p-4 text-center']">
                            <component :is="s.icon" :class="[s.color, 'h-5 w-5 mx-auto mb-2']" />
                            <p class="text-2xl font-bold">{{ s.value }}</p>
                            <p class="text-xs text-muted-foreground">{{ s.label }}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card class="overflow-visible">
                <CardContent class="pt-6">
                    <div class="flex flex-wrap items-end gap-4">
                        <div>
                            <Label>Periode</Label>
                            <select v-model="period" class="mt-1 rounded-md border border-border bg-input px-3 py-2 text-sm text-foreground">
                                <option value="daily">Harian</option>
                                <option value="weekly">Mingguan</option>
                                <option value="monthly">Bulanan</option>
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
                        <div>
                            <Label>Website</Label>
                            <select v-model="websiteClientId" class="mt-1 rounded-md border border-border bg-input px-3 py-2 text-sm text-foreground">
                                <option value="">Semua Website</option>
                                <option v-for="w in websites" :key="w.id" :value="String(w.id)">{{ w.name }}</option>
                            </select>
                        </div>
                        <Button variant="outline" @click="handleFilter" class="cursor-pointer"><Search class="mr-2 h-4 w-4" /> Tampilkan</Button>
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
                                    <TableHead>Periode</TableHead>
                                    <TableHead>Aktivitas</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-if="reportData.entries.length === 0">
                                    <TableCell colspan="3" class="py-8 text-center text-muted-foreground">Tidak ada data untuk periode ini.</TableCell>
                                </TableRow>
                                <TableRow v-for="(e, idx) in reportData.entries" :key="idx">
                                    <TableCell class="font-medium">{{ e.website_name }}</TableCell>
                                    <TableCell class="whitespace-nowrap text-sm">{{ e.entry_date }}</TableCell>
                                    <TableCell>
                                        <div class="space-y-1">
                                            <div v-for="(a, aIdx) in e.activities" :key="aIdx" class="flex items-start gap-2 text-sm">
                                                <Badge :variant="getBadgeVariant(a.type)" class="whitespace-nowrap text-xs">{{ a.type_label || a.type }}</Badge>
                                                <Badge v-if="a.source === 'AI'" variant="outline" class="whitespace-nowrap text-[10px] text-purple-600 border-purple-200 bg-purple-50">AI</Badge>
                                                <span class="text-muted-foreground">{{ formatDetail(a) }}</span>
                                            </div>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
