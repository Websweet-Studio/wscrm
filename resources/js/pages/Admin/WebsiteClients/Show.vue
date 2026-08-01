<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, ExternalLink, Calendar, FileText } from 'lucide-vue-next';

interface Customer {
    id: number;
    name: string;
}

interface Plugin {
    name: string;
    version: string;
}

interface User {
    id: number;
    name: string;
}

interface JournalActivity {
    type: string;
    type_label?: string;
    [key: string]: any;
}

interface Journal {
    id: number;
    entry_date: string;
    activities: JournalActivity[];
    summary?: string;
    user?: User | null;
}

interface Website {
    id: number;
    customer_id: number | null;
    name: string;
    url: string;
    wp_version: string | null;
    theme_name: string | null;
    theme_version: string | null;
    plugins: Plugin[] | null;
    notes: string | null;
    is_active: boolean;
    customer?: Customer | null;
}

interface Props {
    website: Website;
    journals: {
        data: Journal[];
        current_page: number;
        last_page: number;
        links: any[];
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Website Klien', href: '/admin/websites' },
    { title: props.website.name, href: `/admin/websites/${props.website.id}` },
];

const activityTypeLabels: Record<string, string> = {
    wp_update: 'WP Update',
    plugin_update: 'Update Plugin',
    theme_update: 'Update Tema',
    article: 'Artikel',
    page_optimization: 'Optimasi Halaman',
    other: 'Lainnya',
};

const getBadgeVariant = (type: string): "default" | "secondary" | "destructive" | "outline" | null | undefined => {
    const m: Record<string, "default" | "secondary" | "destructive" | "outline"> = {
        wp_update: 'default',
        plugin_update: 'secondary',
        theme_update: 'secondary',
        article: 'outline',
        page_optimization: 'destructive',
        other: 'outline',
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
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`${website.name} - Detail Website`" />

        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link href="/admin/websites" class="text-muted-foreground hover:text-foreground">
                        <ArrowLeft class="h-5 w-5" />
                    </Link>
                    <div>
                        <h1 class="text-3xl font-medium tracking-tight" style="font-family: Georgia, serif;">{{ website.name }}</h1>
                        <a :href="website.url" target="_blank" class="flex items-center gap-1 text-sm text-primary hover:underline">
                            {{ website.url }} <ExternalLink class="h-3 w-3" />
                        </a>
                    </div>
                    <Badge :variant="website.is_active ? 'default' : 'secondary'">
                        {{ website.is_active ? 'Aktif' : 'Nonaktif' }}
                    </Badge>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-3">
                <Card>
                    <CardHeader class="pb-3">
                        <CardTitle class="text-sm font-medium">Customer</CardTitle>
                    </CardHeader>
                    <CardContent><p class="text-lg">{{ website.customer?.name || '-' }}</p></CardContent>
                </Card>
                <Card>
                    <CardHeader class="pb-3">
                        <CardTitle class="text-sm font-medium">Versi WordPress</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Badge v-if="website.wp_version" variant="secondary" class="text-base px-3 py-1">v{{ website.wp_version }}</Badge>
                        <p v-else class="text-muted-foreground">-</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="pb-3">
                        <CardTitle class="text-sm font-medium">Tema</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <template v-if="website.theme_name">
                            <p class="text-lg">{{ website.theme_name }}</p>
                            <span v-if="website.theme_version" class="text-sm text-muted-foreground">v{{ website.theme_version }}</span>
                        </template>
                        <p v-else class="text-muted-foreground">-</p>
                    </CardContent>
                </Card>
            </div>

            <Card v-if="website.plugins && website.plugins.length > 0">
                <CardHeader><CardTitle class="text-base">Plugin Terpasang ({{ website.plugins.length }})</CardTitle></CardHeader>
                <CardContent>
                    <div class="flex flex-wrap gap-2">
                        <Badge v-for="p in website.plugins" :key="p.name" variant="outline" class="text-sm">
                            {{ p.name }}<span v-if="p.version" class="ml-1 text-muted-foreground">v{{ p.version }}</span>
                        </Badge>
                    </div>
                </CardContent>
            </Card>

            <Card v-if="website.notes">
                <CardHeader><CardTitle class="text-base">Catatan</CardTitle></CardHeader>
                <CardContent><p class="text-sm text-muted-foreground whitespace-pre-wrap">{{ website.notes }}</p></CardContent>
            </Card>

            <Card>
                <CardHeader class="flex flex-row items-center justify-between">
                    <CardTitle class="text-base flex items-center gap-2">
                        <Calendar class="h-4 w-4" /> Riwayat Jurnal
                    </CardTitle>
                    <Link href="/admin/journals/create">
                        <Button size="sm" class="cursor-pointer"><FileText class="mr-2 h-4 w-4" /> Catat Jurnal</Button>
                    </Link>
                </CardHeader>
                <CardContent class="pt-0">
                    <div class="overflow-x-auto rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Tanggal</TableHead>
                                    <TableHead>Aktivitas</TableHead>
                                    <TableHead>Oleh</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-if="journals.data.length === 0">
                                    <TableCell colspan="3" class="py-8 text-center text-muted-foreground">
                                        Belum ada jurnal untuk website ini.
                                    </TableCell>
                                </TableRow>
                                <TableRow v-for="j in journals.data" :key="j.id">
                                    <TableCell class="font-medium whitespace-nowrap">{{ j.entry_date }}</TableCell>
                                    <TableCell>
                                        <div class="space-y-1">
                                            <div v-for="(a, idx) in j.activities" :key="idx" class="flex items-start gap-2 text-sm">
                                                <Badge :variant="getBadgeVariant(a.type)" class="whitespace-nowrap text-xs">{{ activityTypeLabels[a.type] || a.type }}</Badge>
                                                <span class="text-muted-foreground">{{ formatDetail(a) }}</span>
                                            </div>
                                            <p v-if="j.summary" class="text-xs text-muted-foreground mt-2 italic">{{ j.summary }}</p>
                                        </div>
                                    </TableCell>
                                    <TableCell class="text-sm text-muted-foreground whitespace-nowrap">{{ j.user?.name || '-' }}</TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
