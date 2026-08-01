<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Check, Eye, EyeOff, Key, Link2, RefreshCw, Search, Trash2, User } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

interface Customer { id: number; name: string; email?: string; }
interface Plugin { name: string; version: string; active?: boolean; slug?: string; }
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
    customers: Customer[];
    website: Website | null;
}

const props = defineProps<Props>();
const isEdit = computed(() => !!props.website);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Manage Website', href: '/admin/websites' },
    { title: isEdit.value ? 'Edit Website' : 'Tambah Website', href: '#' },
];

const form = useForm({
    name: props.website?.name || '',
    url: props.website?.url || '',
    customer_id: props.website?.customer_id || null as (number | null),
    wp_username: props.website?.wp_username || '',
    wp_app_password: props.website?.wp_app_password || '',
    wp_version: props.website?.wp_version || '',
    theme_name: props.website?.theme_name || '',
    theme_version: props.website?.theme_version || '',
    plugins: [...(props.website?.plugins || [])] as Plugin[],
    notes: props.website?.notes || '',
    is_active: props.website?.is_active ?? true,
    _method: isEdit.value ? 'PUT' : 'POST',
});

const submitUrl = computed(() => isEdit.value ? `/admin/websites/${props.website?.id}` : '/admin/websites');

// Searchable customer select
const customerSearch = ref('');
const customerOpen = ref(false);
const customersFiltered = ref<Customer[]>([]);

watch(customerSearch, async (val) => {
    if (!val.trim()) {
        customersFiltered.value = props.customers.slice(0, 10);
        return;
    }
    try {
        const res = await fetch(`/admin/api/customers/search?q=${encodeURIComponent(val)}`);
        customersFiltered.value = await res.json();
    } catch {
        customersFiltered.value = [];
    }
}, { immediate: true });

const selectCustomer = (c: Customer) => {
    form.customer_id = c.id;
    customerSearch.value = c.name;
    customerOpen.value = false;
};

const clearCustomer = () => {
    form.customer_id = null;
    customerSearch.value = '';
};

// Show/hide app password
const showAppPassword = ref(false);

// Sync state
const isSyncing = ref(false);
const syncError = ref('');

const syncData = async () => {
    if (!isEdit.value || !props.website?.id) return;
    if (!form.wp_username || !form.wp_app_password) {
        syncError.value = 'Isi username dan Application Password terlebih dahulu.';
        return;
    }

    isSyncing.value = true;
    syncError.value = '';

    try {
        const res = await fetch(`/admin/websites/${props.website.id}/sync`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content,
            },
        });
        const json = await res.json();

        if (json.success && json.data) {
            form.wp_version = json.data.wp_version || '';
            form.theme_name = json.data.theme_name || '';
            form.theme_version = json.data.theme_version || '';
            form.plugins = json.data.plugins || [];
            syncError.value = '';
        } else {
            syncError.value = json.message || 'Gagal sync.';
        }
    } catch (e) {
        syncError.value = 'Gagal menghubungi server WordPress.';
    } finally {
        isSyncing.value = false;
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="isEdit ? 'Edit Website' : 'Tambah Website Klien'" />

        <div class="max-w-2xl mx-auto space-y-6">
            <div>
                <h1 class="text-3xl font-medium tracking-tight" style="font-family: Georgia, serif;">
                    {{ isEdit ? 'Edit Website Klien' : 'Tambah Website Klien' }}
                </h1>
                <p class="text-muted-foreground">Isi data website untuk maintenance.</p>
            </div>

            <form @submit.prevent="form.post(submitUrl)" class="space-y-6">
                <!-- Basic Info -->
                <Card class="overflow-visible">
                    <CardHeader>
                        <CardTitle class="text-base flex items-center gap-2">
                            <Link2 class="h-4 w-4 text-muted-foreground" /> Informasi Website
                        </CardTitle>
                        <CardDescription>Data dasar website klien.</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4 overflow-visible">
                        <div>
                            <Label>Nama Website *</Label>
                            <Input v-model="form.name" required placeholder="Nama website / brand" />
                            <p v-if="form.errors.name" class="text-xs text-red-500 mt-1">{{ form.errors.name }}</p>
                        </div>
                        <div>
                            <Label>URL *</Label>
                            <Input v-model="form.url" required placeholder="https://example.com" />
                            <p v-if="form.errors.url" class="text-xs text-red-500 mt-1">{{ form.errors.url }}</p>
                        </div>

                        <!-- Searchable Customer -->
                        <div>
                            <Label>Customer (opsional)</Label>
                            <div class="relative mt-1">
                                <div class="relative">
                                    <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                                    <input
                                        v-model="customerSearch"
                                        type="text"
                                        placeholder="Cari customer..."
                                        class="w-full rounded-md border border-border bg-input pl-9 pr-8 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring"
                                        @focus="customerOpen = true"
                                        @keydown.escape="customerOpen = false"
                                    />
                                    <button
                                        v-if="form.customer_id"
                                        type="button"
                                        class="absolute right-2 top-2.5 text-muted-foreground hover:text-foreground cursor-pointer"
                                        @click="clearCustomer"
                                    >
                                        <Trash2 class="h-3.5 w-3.5" />
                                    </button>
                                </div>
                                <div
                                    v-if="customerOpen"
                                    class="absolute z-50 mt-1 w-full rounded-md border border-border bg-popover shadow-md max-h-48 overflow-y-auto"
                                >
                                    <div v-if="customersFiltered.length === 0" class="px-3 py-4 text-center text-sm text-muted-foreground">
                                        Tidak ditemukan
                                    </div>
                                    <button
                                        v-for="c in customersFiltered"
                                        :key="c.id"
                                        type="button"
                                        class="w-full flex items-center gap-2 px-3 py-2 text-sm hover:bg-accent cursor-pointer text-left"
                                        :class="{ 'bg-accent': c.id === form.customer_id }"
                                        @click="selectCustomer(c)"
                                    >
                                        <Check v-if="c.id === form.customer_id" class="h-3.5 w-3.5 text-primary flex-shrink-0" />
                                        <span v-else class="w-3.5 flex-shrink-0" />
                                        <span>{{ c.name }}</span>
                                        <span v-if="c.email" class="text-muted-foreground text-xs">{{ c.email }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div>
                            <Label>Status</Label>
                            <select v-model="form.is_active" class="mt-1 w-full rounded-md border border-border bg-input px-3 py-2 text-sm text-foreground">
                                <option :value="true">Aktif</option>
                                <option :value="false">Nonaktif</option>
                            </select>
                        </div>
                    </CardContent>
                </Card>

                <!-- WP Credentials -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-base flex items-center gap-2">
                            <Key class="h-4 w-4 text-muted-foreground" /> Koneksi WordPress
                        </CardTitle>
                        <CardDescription>
                            Kredensial untuk sync data & mengelola website lewat REST API.
                            <a href="https://developer.wordpress.org/rest-api/using-the-rest-api/authentication/#application-passwords" target="_blank" class="text-primary underline">Application Password guide</a>.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div>
                            <Label>Username WP</Label>
                            <div class="relative">
                                <User class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                                <Input v-model="form.wp_username" placeholder="admin" class="pl-9" />
                            </div>
                        </div>
                        <div>
                            <Label>Application Password</Label>
                            <div class="relative">
                                <Key class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                                <input
                                    :type="showAppPassword ? 'text' : 'password'"
                                    v-model="form.wp_app_password"
                                    placeholder="xxxx xxxx xxxx xxxx xxxx xxxx"
                                    class="w-full rounded-md border border-border bg-input pl-9 pr-10 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring"
                                />
                                <button
                                    type="button"
                                    class="absolute right-2.5 top-2.5 text-muted-foreground hover:text-foreground cursor-pointer"
                                    @click="showAppPassword = !showAppPassword"
                                >
                                    <EyeOff v-if="showAppPassword" class="h-4 w-4" />
                                    <Eye v-else class="h-4 w-4" />
                                </button>
                            </div>
                        </div>

                        <!-- Sync Button (only in edit mode) -->
                        <div v-if="isEdit" class="pt-2">
                            <Button
                                type="button"
                                variant="outline"
                                :disabled="isSyncing"
                                @click="syncData"
                                class="cursor-pointer w-full"
                            >
                                <RefreshCw :class="['mr-2 h-4 w-4', isSyncing ? 'animate-spin' : '']" />
                                {{ isSyncing ? 'Syncing...' : 'Sync Data dari WordPress' }}
                            </Button>
                            <p v-if="syncError" class="text-xs text-red-500 mt-2">{{ syncError }}</p>
                        </div>
                        <p v-else class="text-xs text-muted-foreground">
                            Simpan dulu websitenya, lalu sync data WordPress dari halaman edit.
                        </p>
                    </CardContent>
                </Card>

                <!-- WP Info (Read-only, populated by sync) -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-base">WordPress Info</CardTitle>
                        <CardDescription>
                            Data ini diisi otomatis saat sync dari WordPress.
                            <template v-if="isEdit && !form.wp_version && !form.theme_name">Klik "Sync Data" di atas.</template>
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="form.wp_version || form.theme_name" class="space-y-3">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="rounded-md border p-3">
                                    <p class="text-xs text-muted-foreground">Versi WordPress</p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <Badge variant="secondary" class="text-sm">{{ form.wp_version || '-' }}</Badge>
                                    </div>
                                </div>
                                <div class="rounded-md border p-3">
                                    <p class="text-xs text-muted-foreground">Tema Aktif</p>
                                    <p class="text-sm font-medium mt-1">{{ form.theme_name || '-' }}</p>
                                    <p v-if="form.theme_version" class="text-xs text-muted-foreground">v{{ form.theme_version }}</p>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-6 text-sm text-muted-foreground border border-dashed rounded-md">
                            Belum ada data. Sync dari WordPress untuk mengisi otomatis.
                        </div>
                    </CardContent>
                </Card>

                <!-- Plugins (Read-only, populated by sync) -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-base">Plugin</CardTitle>
                        <CardDescription>
                            Daftar plugin diambil otomatis dari WordPress saat sync.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="form.plugins.length > 0" class="space-y-1">
                            <div v-for="(plugin, idx) in form.plugins" :key="idx" class="flex items-center justify-between text-sm py-2 px-3 border rounded-md">
                                <div>
                                    <span class="font-medium">{{ plugin.name }}</span>
                                    <span v-if="plugin.version" class="text-muted-foreground ml-2">v{{ plugin.version }}</span>
                                </div>
                                <Badge :variant="plugin.active ? 'default' : 'secondary'" class="text-xs">
                                    {{ plugin.active ? 'Aktif' : 'Nonaktif' }}
                                </Badge>
                            </div>
                        </div>
                        <div v-else class="text-center py-6 text-sm text-muted-foreground border border-dashed rounded-md">
                            Belum ada data plugin. Sync dari WordPress untuk mengambil daftar plugin.
                        </div>
                    </CardContent>
                </Card>

                <!-- Notes -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-base">Catatan</CardTitle>
                        <CardDescription>Opsional - catatan tambahan.</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Textarea v-model="form.notes" rows="3" placeholder="Catatan tambahan..." />
                        <p v-if="form.errors.notes" class="text-xs text-red-500 mt-1">{{ form.errors.notes }}</p>
                    </CardContent>
                </Card>

                <div class="flex justify-end gap-3">
                    <a href="/admin/websites" class="inline-flex items-center justify-center rounded-md border border-input bg-background px-4 py-2 text-sm font-medium hover:bg-accent hover:text-accent-foreground cursor-pointer">
                        Batal
                    </a>
                    <Button type="submit" :disabled="form.processing" class="cursor-pointer">
                        {{ isEdit ? 'Simpan Perubahan' : 'Simpan Website' }}
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
