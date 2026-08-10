<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { usePage } from '@inertiajs/vue3';
import { Bell, ExternalLink } from 'lucide-vue-next';
import { computed, onMounted, ref, watch } from 'vue';

interface NotifItem {
    id: string;
    data: {
        website_id?: number;
        website_name?: string;
        url?: string;
        message?: string;
        http_code?: number;
        checked_at?: string;
    };
    read_at: string | null;
    created_at: string;
}

const page = usePage();

const unreadFromProps = computed(() => {
    return (((page.props as any)?.adminBadges?.unread_notifications as number) ?? 0) as number;
});

const unreadCount = ref(unreadFromProps.value);
const items = ref<NotifItem[]>([]);
const open = ref(false);

const loadLatest = async () => {
    try {
        const res = await fetch('/admin/notifications/latest', {
            headers: { Accept: 'application/json' },
        });
        if (!res.ok) return;
        const data = await res.json();
        unreadCount.value = data.unread;
        items.value = data.notifications;
    } catch {
        // abaikan — pakai count dari props
    }
};

onMounted(loadLatest);

// Muat ulang saat halaman berubah (misal selesai tandai dibaca / hapus)
watch(
    () => page.url,
    () => loadLatest(),
);

watch(unreadFromProps, (v) => {
    unreadCount.value = v;
});

const formatDate = (dateString: string) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleString('id-ID', {
        day: 'numeric',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <DropdownMenu v-model:open="open">
        <DropdownMenuTrigger :as-child="true">
            <Button variant="ghost" size="icon" class="relative h-9 w-9 cursor-pointer">
                <Bell class="h-5 w-5" />
                <span
                    v-if="unreadCount > 0"
                    class="absolute right-0.5 top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-semibold text-white"
                >
                    {{ unreadCount > 9 ? '9+' : unreadCount }}
                </span>
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" class="w-80">
            <div class="flex items-center justify-between border-b px-3 py-2">
                <p class="text-sm font-medium">Notifikasi</p>
                <a
                    href="/admin/notifications"
                    class="inline-flex items-center gap-1 text-xs text-blue-600 hover:underline"
                    @click="open = false"
                >
                    Lihat semua <ExternalLink class="h-3 w-3" />
                </a>
            </div>

            <div v-if="items.length === 0" class="px-3 py-6 text-center text-sm text-muted-foreground">
                Tidak ada notifikasi.
            </div>

            <ul v-else class="max-h-96 overflow-y-auto">
                <li
                    v-for="n in items"
                    :key="n.id"
                    class="flex gap-2.5 border-b px-3 py-2.5 last:border-b-0"
                    :class="n.read_at ? '' : 'bg-red-50/50 dark:bg-red-950/20'"
                >
                    <span
                        class="mt-1.5 h-2 w-2 flex-shrink-0 rounded-full"
                        :class="n.read_at ? 'bg-muted' : 'bg-red-500'"
                    ></span>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs leading-snug">
                            {{ n.data.message || 'Notifikasi' }}
                        </p>
                        <div class="mt-1 flex items-center gap-2">
                            <span v-if="n.data.website_name" class="text-[11px] text-muted-foreground">{{ n.data.website_name }}</span>
                            <span class="text-[11px] text-muted-foreground">{{ formatDate(n.created_at) }}</span>
                        </div>
                    </div>
                </li>
            </ul>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
