<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { formatDate } from '@/lib/utils';
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, Boxes, Calendar, CheckCircle2, ExternalLink, FileText, Key, Loader2, RefreshCw, Trash2, XCircle } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';

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
    wp_username: string | null;
    wp_app_password: string | null;
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
    plugin_remove: 'Hapus Plugin',
    other: 'Lainnya',
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

// Ringkas aktivitas jadi satu baris, seperti di halaman Jurnal
const activitySummary = (j: Journal): string => {
    return j.activities.map(a => `${activityTypeLabels[a.type] || a.type}: ${formatDetail(a)}`).join(' | ');
};

interface LivePlugin {
    plugin: string;
    name: string;
    version: string | null;
    active: boolean;
}

const hasCreds = computed(() => !!(props.website.wp_username && props.website.wp_app_password));
const livePlugins = ref<LivePlugin[]>([]);
const pluginsLoading = ref(false);
const pluginsLoaded = ref(false);
const pluginsError = ref<string | null>(null);
const deletingPlugin = ref<LivePlugin | null>(null);
const deleteError = ref<string | null>(null);
const deleting = ref(false);
const pluginNotice = ref<{ type: 'success' | 'error'; message: string } | null>(null);

const csrfToken = () => (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '';

const loadPlugins = async () => {
    if (!hasCreds.value) return;
    pluginsLoading.value = true;
    pluginsError.value = null;
    try {
        const res = await fetch(`/admin/websites/${props.website.id}/plugins`, {
            headers: { 'X-CSRF-TOKEN': csrfToken() },
        });
        const body = await res.json();
        if (!res.ok || !body.success) {
            throw new Error(body.message || 'Gagal memuat daftar plugin.');
        }
        livePlugins.value = body.plugins || [];
        pluginsLoaded.value = true;
    } catch (e: any) {
        pluginsError.value = e.message || 'Terjadi kesalahan saat memuat daftar plugin.';
    } finally {
        pluginsLoading.value = false;
    }
};

const confirmDeletePlugin = () => {
    const p = deletingPlugin.value;
    if (!p) return;
    deleting.value = true;
    deleteError.value = null;
    fetch(`/admin/websites/${props.website.id}/plugins`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
        },
        body: JSON.stringify({ plugin: p.plugin, name: p.name }),
    })
        .then(async (res) => {
            const body = await res.json();
            if (!res.ok || !body.success) {
                deleteError.value = body.message || 'Gagal menghapus plugin.';
                return;
            }
            livePlugins.value = livePlugins.value.filter(x => x.plugin !== p.plugin);
            deletingPlugin.value = null;
            pluginNotice.value = { type: 'success', message: body.message || 'Plugin berhasil dihapus.' };
            router.reload({ only: ['website', 'journals'] });
        })
        .catch(() => {
            deleteError.value = 'Terjadi kesalahan saat menghapus plugin.';
        })
        .finally(() => {
            deleting.value = false;
        });
};

onMounted(loadPlugins);
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

            <Card>
                <CardHeader class="flex flex-row items-center justify-between">
                    <CardTitle class="text-base flex items-center gap-2">
                        <Boxes class="h-4 w-4" /> Manajemen Plugin
                        <span v-if="pluginsLoaded" class="text-sm font-normal text-muted-foreground">({{ livePlugins.length }})</span>
                    </CardTitle>
                    <Button v-if="hasCreds" size="sm" variant="outline" class="cursor-pointer" :disabled="pluginsLoading" @click="loadPlugins">
                        <RefreshCw :class="['mr-2 h-4 w-4', pluginsLoading && 'animate-spin']" /> Muat Ulang
                    </Button>
                </CardHeader>
                <CardContent>
                    <div v-if="!hasCreds" class="text-sm text-muted-foreground">
                        Konfigurasi kredensial WordPress dulu untuk melihat & menghapus plugin.
                        <Link :href="`/admin/websites/${website.id}/edit`" class="ml-1 text-primary underline">Konfigurasi</Link>
                    </div>

                    <div v-else-if="pluginsError" class="text-sm text-destructive">
                        {{ pluginsError }}
                        <Button size="sm" variant="outline" class="ml-2 cursor-pointer" @click="loadPlugins">Coba lagi</Button>
                    </div>

                    <template v-else-if="pluginsLoaded">
                        <div v-if="pluginNotice" :class="['mb-3 rounded-md p-3 text-sm', pluginNotice.type === 'success' ? 'bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400' : 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400']">
                            {{ pluginNotice.message }}
                        </div>

                        <p v-if="livePlugins.length === 0" class="text-sm text-muted-foreground">Tidak ada plugin terpasang.</p>

                        <div v-else class="overflow-x-auto rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Nama Plugin</TableHead>
                                        <TableHead>Versi</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead class="w-[80px]">Aksi</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="p in livePlugins" :key="p.plugin">
                                        <TableCell>
                                            <p class="font-medium">{{ p.name }}</p>
                                            <p class="font-mono text-xs text-muted-foreground">{{ p.plugin }}</p>
                                        </TableCell>
                                        <TableCell class="text-sm">{{ p.version || '-' }}</TableCell>
                                        <TableCell>
                                            <Badge :variant="p.active ? 'default' : 'secondary'">{{ p.active ? 'Aktif' : 'Nonaktif' }}</Badge>
                                        </TableCell>
                                        <TableCell>
                                            <Button size="sm" variant="outline" class="cursor-pointer text-destructive" title="Hapus plugin" @click="deletingPlugin = p; deleteError = null">
                                                <Trash2 class="h-3.5 w-3.5" />
                                            </Button>
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>
                    </template>

                    <p v-else class="text-sm text-muted-foreground">Memuat daftar plugin...</p>
                </CardContent>
            </Card>

            <Card v-if="website.notes">
                <CardHeader><CardTitle class="text-base">Catatan</CardTitle></CardHeader>
                <CardContent><p class="text-sm text-muted-foreground whitespace-pre-wrap">{{ website.notes }}</p></CardContent>
            </Card>

            <!-- WP Connection Status -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-base flex items-center gap-2">
                        <Key class="h-4 w-4" /> Koneksi WordPress
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div v-if="website.wp_username && website.wp_app_password" class="flex items-start gap-3">
                        <CheckCircle2 class="h-5 w-5 text-green-500 mt-0.5 flex-shrink-0" />
                        <div>
                            <p class="text-sm font-medium">Terkoneksi</p>
                            <p class="text-sm text-muted-foreground">
                                Username: <span class="font-mono">{{ website.wp_username }}</span>
                                <span class="mx-2">|</span>
                                Password: <span class="font-mono">••••••••</span>
                            </p>
                        </div>
                    </div>
                    <div v-else class="flex items-start gap-3">
                        <XCircle class="h-5 w-5 text-yellow-500 mt-0.5 flex-shrink-0" />
                        <div>
                            <p class="text-sm font-medium">Belum dikonfigurasi</p>
                            <p class="text-sm text-muted-foreground">
                                Tambahkan username & Application Password untuk mengelola website dari panel ini.
                            </p>
                            <Link :href="`/admin/websites/${website.id}/edit`" class="text-primary text-sm underline mt-1 inline-block">
                                Konfigurasi sekarang
                            </Link>
                        </div>
                    </div>
                </CardContent>
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
                                    <TableCell class="font-medium whitespace-nowrap">{{ formatDate(j.entry_date, 'long') }}</TableCell>
                                    <TableCell>
                                        <p class="text-sm text-muted-foreground">{{ activitySummary(j) }}</p>
                                        <p v-if="j.summary" class="text-xs text-muted-foreground mt-1 italic">{{ j.summary }}</p>
                                    </TableCell>
                                    <TableCell class="text-sm text-muted-foreground whitespace-nowrap">{{ j.user?.name || '-' }}</TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Delete Plugin Modal -->
        <div v-if="deletingPlugin" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-black/50" @click="deletingPlugin = null"></div>
            <div class="relative mx-4 w-full max-w-md rounded-lg bg-white p-6 shadow-xl dark:bg-gray-900">
                <div class="mb-4">
                    <h2 class="text-lg font-semibold">Hapus Plugin</h2>
                    <p class="text-sm text-muted-foreground mt-2">
                        Yakin ingin menghapus plugin <strong>{{ deletingPlugin.name }}</strong> ({{ deletingPlugin.plugin }})
                        dari website WordPress? Plugin akan dinonaktifkan lalu dihapus, dan tindakan ini tercatat di jurnal.
                    </p>
                </div>
                <div v-if="deleteError" class="mb-3 rounded-md border border-destructive/50 bg-destructive/10 p-3 text-sm text-destructive">
                    {{ deleteError }}
                </div>
                <div class="flex justify-end gap-3">
                    <Button variant="outline" @click="deletingPlugin = null" class="cursor-pointer" :disabled="deleting">Batal</Button>
                    <Button variant="destructive" @click="confirmDeletePlugin" class="cursor-pointer" :disabled="deleting">
                        <Loader2 v-if="deleting" class="mr-2 h-4 w-4 animate-spin" /> Hapus
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
