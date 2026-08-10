<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Bell, BellOff, CheckCheck, ExternalLink, Trash2 } from 'lucide-vue-next';

interface NotificationItem {
    id: string;
    data: {
        website_id?: number;
        website_name?: string;
        url?: string;
        message?: string;
        http_code?: number;
        detail?: string;
        checked_at?: string;
    };
    read_at: string | null;
    created_at: string;
}

interface PaginatedData {
    data: NotificationItem[];
    links: any[];
    current_page: number;
    last_page: number;
    next_page_url: string | null;
    prev_page_url: string | null;
    from: number | null;
    to: number | null;
    total: number;
}

interface Props {
    notifications: PaginatedData;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Notifikasi', href: '/admin/notifications' },
];

const unreadCount = props.notifications.data.filter((n) => !n.read_at).length;

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const markRead = (n: NotificationItem) => {
    if (n.read_at) return;
    router.post(`/admin/notifications/${n.id}/read`, {}, { preserveScroll: true });
};

const markAllRead = () => {
    router.post('/admin/notifications/read-all', {}, { preserveScroll: true });
};

const destroy = (n: NotificationItem) => {
    router.delete(`/admin/notifications/${n.id}`, { preserveScroll: true });
};
</script>

<template>
    <Head title="Notifikasi" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <!-- Header -->
            <div
                class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 via-red-900 to-slate-900 p-4 text-white sm:p-6 lg:p-8"
            >
                <div class="absolute inset-0 bg-gradient-to-r from-red-600/20 to-blue-600/20"></div>
                <div class="relative z-10 flex flex-col space-y-4 lg:flex-row lg:items-center lg:justify-between lg:space-y-0">
                    <div class="flex items-center space-x-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20 backdrop-blur-sm sm:h-12 sm:w-12">
                            <Bell class="h-5 w-5 sm:h-6 sm:w-6" />
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold sm:text-3xl">Notifikasi</h1>
                            <p class="hidden text-sm text-white/80 sm:block sm:text-base">
                                Pemberitahuan website down, tugas, dan aktivitas sistem
                            </p>
                        </div>
                    </div>
                    <div class="flex space-x-3">
                        <Button
                            variant="outline"
                            class="cursor-pointer border-white/30 bg-white/20 text-white backdrop-blur-sm hover:bg-white/30"
                            :disabled="unreadCount === 0"
                            @click="markAllRead"
                        >
                            <CheckCheck class="mr-2 h-4 w-4" />
                            Tandai Semua Dibaca
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Summary cards -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <Card>
                    <CardContent class="p-4 sm:p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-medium text-muted-foreground">Belum Dibaca</p>
                                <p class="mt-1 text-2xl font-bold text-red-600">{{ unreadCount }}</p>
                            </div>
                            <Bell class="h-8 w-8 text-red-500" />
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="p-4 sm:p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-medium text-muted-foreground">Total</p>
                                <p class="mt-1 text-2xl font-bold">{{ props.notifications.total }}</p>
                            </div>
                            <BellOff class="h-8 w-8 text-muted-foreground" />
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="p-4 sm:p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-medium text-muted-foreground">Halaman</p>
                                <p class="mt-1 text-2xl font-bold">
                                    {{ props.notifications.from ?? 0 }}–{{ props.notifications.to ?? 0 }}
                                </p>
                            </div>
                            <Bell class="h-8 w-8 text-blue-500" />
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Notification list -->
            <Card>
                <CardContent class="p-0">
                    <div v-if="props.notifications.data.length === 0" class="p-8 text-center text-sm text-muted-foreground">
                        Belum ada notifikasi.
                    </div>

                    <ul v-else class="divide-y">
                        <li
                            v-for="n in props.notifications.data"
                            :key="n.id"
                            class="flex items-start gap-3 px-4 py-4 sm:px-6"
                            :class="n.read_at ? 'opacity-60' : 'bg-red-50/40 dark:bg-red-950/20'"
                        >
                            <button
                                class="mt-1 flex h-8 w-8 flex-shrink-0 cursor-pointer items-center justify-center rounded-full"
                                :class="n.read_at ? 'bg-muted' : 'bg-red-100 dark:bg-red-900'"
                                @click="markRead(n)"
                                :title="n.read_at ? 'Dibaca' : 'Belum dibaca'"
                            >
                                <Bell :class="n.read_at ? 'h-4 w-4 text-muted-foreground' : 'h-4 w-4 text-red-600'" />
                            </button>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-2">
                                    <p class="text-sm font-medium">{{ n.data.message || 'Notifikasi' }}</p>
                                    <span class="flex-shrink-0 text-xs text-muted-foreground">{{ formatDate(n.created_at) }}</span>
                                </div>
                                <p v-if="n.data.website_name" class="mt-1 text-sm text-muted-foreground">
                                    {{ n.data.website_name }}<span v-if="n.data.url"> — {{ n.data.url }}</span>
                                </p>
                                <p v-if="n.data.detail" class="mt-0.5 text-xs text-muted-foreground">{{ n.data.detail }}</p>
                                <div class="mt-2 flex items-center gap-3">
                                    <a
                                        v-if="n.data.website_id"
                                        :href="`/admin/websites/${n.data.website_id}/edit`"
                                        class="inline-flex items-center gap-1 text-xs text-blue-600 hover:underline"
                                    >
                                        <ExternalLink class="h-3 w-3" /> Periksa website
                                    </a>
                                    <Badge v-if="n.data.http_code && n.data.http_code > 0" variant="destructive" class="text-xs">
                                        HTTP {{ n.data.http_code }}
                                    </Badge>
                                    <Badge v-if="!n.read_at" class="text-xs">Baru</Badge>
                                </div>
                            </div>

                            <Button variant="ghost" size="icon" class="cursor-pointer" @click="destroy(n)">
                                <Trash2 class="h-4 w-4 text-muted-foreground" />
                            </Button>
                        </li>
                    </ul>
                </CardContent>
            </Card>

            <!-- Pagination -->
            <div v-if="props.notifications.last_page > 1" class="flex items-center justify-between">
                <p class="text-xs text-muted-foreground">
                    Menampilkan {{ props.notifications.from ?? 0 }}–{{ props.notifications.to ?? 0 }} dari {{ props.notifications.total }}
                </p>
                <div class="flex gap-2">
                    <Button
                        variant="outline"
                        size="sm"
                        class="cursor-pointer"
                        :disabled="!props.notifications.prev_page_url"
                        @click="router.visit(props.notifications.prev_page_url!, { preserveScroll: true })"
                    >
                        Sebelumnya
                    </Button>
                    <Button
                        variant="outline"
                        size="sm"
                        class="cursor-pointer"
                        :disabled="!props.notifications.next_page_url"
                        @click="router.visit(props.notifications.next_page_url!, { preserveScroll: true })"
                    >
                        Berikutnya
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
