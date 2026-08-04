<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Ban, BarChart3, Eye, Globe, Search, Trash2, Unlock } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface TrackingItem {
    id: number;
    referer_url: string | null;
    referer_host: string | null;
    embed_type: 'listing' | 'single' | 'oembed' | 'api';
    demo_website_id: number | null;
    hits: number;
    is_blocked: boolean;
    blocked_reason: string | null;
    blocked_at: string | null;
    first_seen_at: string | null;
    last_seen_at: string | null;
    created_at: string;
}

interface Stats {
    total: number;
    total_hits: number;
    blocked: number;
}

interface Props {
    trackings: {
        data: TrackingItem[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: any[];
    };
    stats: Stats;
    filters: {
        search?: string;
        type?: string;
    };
}

const props = defineProps<Props>();

const search = ref(props.filters.search || '');
const typeFilter = ref(props.filters.type || '');
const selectedIds = ref<number[]>([]);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Demo Website', href: '/admin/demo-websites' },
    { title: 'Tracking Embed', href: '/admin/demo-embed-trackings' },
];

const typeLabels: Record<string, string> = {
    listing: 'Widget',
    single: 'Single',
    oembed: 'oEmbed',
    api: 'API',
};

const handleSearch = () => {
    router.get('/admin/demo-embed-trackings', {
        search: search.value || undefined,
        type: typeFilter.value || undefined,
    }, { preserveState: true, replace: true });
};

const toggleBlock = (item: TrackingItem) => {
    if (item.is_blocked) {
        router.patch(`/admin/demo-embed-trackings/${item.id}/toggle-block`, {}, {
            preserveState: true,
        });
    } else {
        const reason = prompt('Alasan block domain ini:', 'Penyalahgunaan API / scraping berlebihan');
        if (reason === null) return; // cancelled
        router.patch(`/admin/demo-embed-trackings/${item.id}/toggle-block`, {
            reason: reason || 'Domain diblokir oleh admin.',
        }, {
            preserveState: true,
        });
    }
};

const deleteItem = (item: TrackingItem) => {
    if (!confirm(`Hapus record untuk ${item.referer_host || item.referer_url}?`)) return;
    router.delete(`/admin/demo-embed-trackings/${item.id}`, {
        preserveState: true,
    });
};

const toggleSelectAll = () => {
    if (selectedIds.value.length === props.trackings.data.length) {
        selectedIds.value = [];
    } else {
        selectedIds.value = props.trackings.data.map((t) => t.id);
    }
};

const toggleSelect = (id: number) => {
    const idx = selectedIds.value.indexOf(id);
    if (idx === -1) {
        selectedIds.value.push(id);
    } else {
        selectedIds.value.splice(idx, 1);
    }
};

const bulkDelete = () => {
    if (!selectedIds.value.length) return;
    if (!confirm(`Hapus ${selectedIds.value.length} record?`)) return;
    router.delete('/admin/demo-embed-trackings-bulk', {
        data: { ids: selectedIds.value },
        preserveState: true,
        onSuccess: () => {
            selectedIds.value = [];
        },
    });
};

const formatDate = (date: string | null) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const formatNumber = (n: number) => {
    return n.toLocaleString('id-ID');
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Tracking Embed" />

        <div class="flex flex-col gap-6 p-6">
            <!-- Stats -->
            <div class="grid gap-4 md:grid-cols-3">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Domain</CardTitle>
                        <Globe class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatNumber(stats.total) }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Hits</CardTitle>
                        <BarChart3 class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatNumber(stats.total_hits) }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Di-block</CardTitle>
                        <Ban class="h-4 w-4 text-red-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-red-600">{{ formatNumber(stats.blocked) }}</div>
                    </CardContent>
                </Card>
            </div>

            <!-- Filters -->
            <Card>
                <CardContent class="pt-6">
                    <div class="flex flex-col gap-3 sm:flex-row">
                        <div class="relative flex-1">
                            <Search class="absolute left-3 top-2.5 h-4 w-4 text-muted-foreground" />
                            <Input
                                v-model="search"
                                placeholder="Cari domain atau URL..."
                                class="pl-9"
                                @keyup.enter="handleSearch"
                            />
                        </div>
                        <select
                            v-model="typeFilter"
                            class="rounded-md border px-3 py-2 text-sm"
                            @change="handleSearch"
                        >
                            <option value="">Semua Tipe</option>
                            <option value="listing">Widget</option>
                            <option value="single">Single</option>
                            <option value="oembed">oEmbed</option>
                            <option value="api">API</option>
                        </select>
                        <Button @click="handleSearch" class="cursor-pointer">Cari</Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Bulk actions -->
            <div v-if="selectedIds.length > 0" class="flex items-center gap-3">
                <span class="text-sm text-muted-foreground">{{ selectedIds.length }} dipilih</span>
                <Button variant="destructive" size="sm" @click="bulkDelete" class="cursor-pointer">
                    <Trash2 class="mr-1 h-4 w-4" />
                    Hapus
                </Button>
            </div>

            <!-- Table -->
            <Card>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="w-10">
                                <input
                                    type="checkbox"
                                    :checked="selectedIds.length === trackings.data.length && trackings.data.length > 0"
                                    @change="toggleSelectAll"
                                    class="cursor-pointer"
                                />
                            </TableHead>
                            <TableHead>Domain / URL</TableHead>
                            <TableHead>Tipe</TableHead>
                            <TableHead class="text-right">Hits</TableHead>
                            <TableHead>Pertama</TableHead>
                            <TableHead>Terakhir</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead class="text-right">Aksi</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-if="trackings.data.length === 0">
                            <TableCell :colspan="8" class="text-center text-muted-foreground py-8">
                                Belum ada data tracking.
                            </TableCell>
                        </TableRow>
                        <TableRow v-for="item in trackings.data" :key="item.id">
                            <TableCell>
                                <input
                                    type="checkbox"
                                    :checked="selectedIds.includes(item.id)"
                                    @change="toggleSelect(item.id)"
                                    class="cursor-pointer"
                                />
                            </TableCell>
                            <TableCell>
                                <div class="flex flex-col">
                                    <span class="font-medium text-sm">{{ item.referer_host || '-' }}</span>
                                    <span v-if="item.referer_url" class="text-xs text-muted-foreground truncate max-w-64">{{ item.referer_url }}</span>
                                </div>
                            </TableCell>
                            <TableCell>
                                <Badge variant="outline">{{ typeLabels[item.embed_type] || item.embed_type }}</Badge>
                            </TableCell>
                            <TableCell class="text-right font-mono">{{ formatNumber(item.hits) }}</TableCell>
                            <TableCell class="text-xs text-muted-foreground">{{ formatDate(item.first_seen_at) }}</TableCell>
                            <TableCell class="text-xs text-muted-foreground">{{ formatDate(item.last_seen_at) }}</TableCell>
                            <TableCell>
                                <Badge :variant="item.is_blocked ? 'destructive' : 'default'" class="whitespace-nowrap">
                                    {{ item.is_blocked ? 'Blocked' : 'Active' }}
                                </Badge>
                                <span v-if="item.is_blocked && item.blocked_reason" class="ml-1.5 text-xs text-muted-foreground" :title="item.blocked_reason">
                                    {{ item.blocked_reason.length > 30 ? item.blocked_reason.slice(0, 30) + '...' : item.blocked_reason }}
                                </span>
                            </TableCell>
                            <TableCell class="text-right">
                                <div class="flex justify-end gap-1">
                                    <Button
                                        size="icon"
                                        variant="ghost"
                                        :title="item.is_blocked ? 'Unblock' : 'Block'"
                                        @click="toggleBlock(item)"
                                        class="cursor-pointer"
                                    >
                                        <Unlock v-if="item.is_blocked" class="h-4 w-4 text-green-600" />
                                        <Ban v-else class="h-4 w-4 text-red-500" />
                                    </Button>
                                    <Button
                                        size="icon"
                                        variant="ghost"
                                        title="Hapus"
                                        @click="deleteItem(item)"
                                        class="cursor-pointer"
                                    >
                                        <Trash2 class="h-4 w-4 text-muted-foreground" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </Card>

            <!-- Pagination -->
            <div v-if="trackings.links && trackings.links.length > 3" class="flex items-center justify-center gap-2">
                <template v-for="link in trackings.links" :key="link.label">
                    <Button
                        v-if="link.url"
                        :variant="link.active ? 'default' : 'outline'"
                        size="sm"
                        @click="router.visit(link.url, { preserveState: true })"
                        class="cursor-pointer"
                        v-html="link.label"
                    />
                    <span v-else class="px-2 text-sm text-muted-foreground" v-html="link.label" />
                </template>
            </div>
        </div>
    </AppLayout>
</template>
