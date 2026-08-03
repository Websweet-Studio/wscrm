<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { AlertCircle, Ban, CheckCircle2, RefreshCw, Server, Unlock } from 'lucide-vue-next';
import { reactive, ref } from 'vue';

interface LinkedOrder {
    id: number;
    customer: { name: string };
    status: string;
}

interface Account {
    username: string;
    email: string;
    domain: string;
    package: string;
    suspended: boolean;
    linked_order?: LinkedOrder | null;
}

interface UnlinkedOrder {
    id: number;
    domain_name: string;
    status: string;
    customer: { name: string };
}

interface Props {
    settings: {
        scheme: string;
        host: string;
        port: string;
        username: string;
        password: string;
        verify_ssl: boolean;
    };
    accounts: Account[];
    unlinkedOrders: UnlinkedOrder[];
    connection: { ok: boolean; message: string };
    error: string | null;
}

const props = defineProps<Props>();
const $page = usePage();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'DirectAdmin', href: '/admin/directadmin' },
];

const saving = ref(false);
const acting = ref<string | null>(null);

const form = reactive({
    scheme: props.settings.scheme,
    host: props.settings.host,
    port: props.settings.port,
    username: props.settings.username,
    password: '',
    verify_ssl: props.settings.verify_ssl,
});

const saveSettings = () => {
    saving.value = true;
    router.post('/admin/directadmin/settings', form, {
        preserveScroll: true,
        onFinish: () => {
            saving.value = false;
            form.password = '';
        },
    });
};

const suspend = (account: Account) => {
    if (!confirm(`Suspend akun DirectAdmin "${account.username}"? Status order terhubung akan ikut menjadi "Ditangguhkan".`)) return;
    acting.value = account.username;
    router.post(`/admin/directadmin/accounts/${account.username}/suspend`, {}, {
        preserveScroll: true,
        onFinish: () => (acting.value = null),
    });
};

const unsuspend = (account: Account) => {
    if (!confirm(`Aktifkan kembali akun DirectAdmin "${account.username}"? Status order terhubung akan ikut menjadi "Aktif".`)) return;
    acting.value = account.username;
    router.post(`/admin/directadmin/accounts/${account.username}/unsuspend`, {}, {
        preserveScroll: true,
        onFinish: () => (acting.value = null),
    });
};

const getOrderStatusText = (status: string) => {
    switch (status) {
        case 'active': return 'Aktif';
        case 'suspended': return 'Ditangguhkan';
        case 'expired': return 'Kedaluwarsa';
        case 'terminated': return 'Dihentikan';
        default: return status;
    }
};
</script>

<template>
    <Head title="DirectAdmin" />

    <AppLayout>
        <template #breadcrumbs>
            {{ breadcrumbs }}
        </template>

        <div class="space-y-6">
            <div class="flex flex-col space-y-4 md:flex-row md:items-center md:justify-between md:space-y-0">
                <div>
                    <h1 class="text-3xl font-medium" style="font-family: Georgia, serif;">DirectAdmin</h1>
                    <p class="text-muted-foreground">
                        Kelola server DirectAdmin, sinkronkan akun dengan order, dan suspend/unsuspend layanan.
                    </p>
                </div>
                <div class="flex items-center space-x-2">
                    <span
                        v-if="connection.ok"
                        class="inline-flex items-center gap-1 rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700 dark:bg-green-900/40 dark:text-green-300"
                    >
                        <CheckCircle2 class="h-3.5 w-3.5" />
                        Terhubung
                    </span>
                    <span
                        v-else-if="settings.host"
                        class="inline-flex items-center gap-1 rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-700 dark:bg-red-900/40 dark:text-red-300"
                    >
                        <AlertCircle class="h-3.5 w-3.5" />
                        Koneksi gagal
                    </span>
                    <span v-else class="inline-flex items-center gap-1 rounded-full bg-muted px-3 py-1 text-xs font-medium text-muted-foreground">
                        <Server class="h-3.5 w-3.5" />
                        Belum dikonfigurasi
                    </span>
                </div>
            </div>

            <!-- Pengaturan server -->
            <Card class="rounded-2xl shadow-[rgba(0,0,0,0.05)_0px_4px_24px]">
                <CardHeader>
                    <CardTitle class="flex items-center space-x-2 font-serif font-medium tracking-tight">
                        <Server class="h-4 w-4" />
                        <span>Pengaturan Server</span>
                    </CardTitle>
                    <CardDescription class="leading-relaxed">
                        Kredensial login admin/reseller DirectAdmin (port default 2222). Bisa memakai password atau Login Key.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="saveSettings" class="space-y-4">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                            <div class="md:col-span-2">
                                <Label for="host">Host / IP Server</Label>
                                <Input id="host" v-model="form.host" required placeholder="server.example.com" class="mt-1" />
                            </div>
                            <div>
                                <Label for="port">Port</Label>
                                <Input id="port" v-model="form.port" required type="number" min="1" max="65535" class="mt-1" />
                            </div>
                            <div>
                                <Label for="scheme">Protokol</Label>
                                <select
                                    id="scheme"
                                    v-model="form.scheme"
                                    class="mt-1 flex h-9 w-full cursor-pointer rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm"
                                >
                                    <option value="https">HTTPS</option>
                                    <option value="http">HTTP</option>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <Label for="username">Username</Label>
                                <Input id="username" v-model="form.username" required class="mt-1" />
                            </div>
                            <div class="md:col-span-2">
                                <Label for="password">Password / Login Key</Label>
                                <Input
                                    id="password"
                                    v-model="form.password"
                                    type="password"
                                    :placeholder="settings.password ? '******** (biarkan kosong untuk mempertahankan)' : ''"
                                    class="mt-1"
                                />
                            </div>
                            <div class="md:col-span-2 flex items-end">
                                <div class="flex items-center space-x-2">
                                    <Switch id="verify_ssl" v-model:checked="form.verify_ssl" />
                                    <Label for="verify_ssl" class="cursor-pointer">Verifikasi sertifikat SSL</Label>
                                </div>
                            </div>
                        </div>
                        <div>
                            <Button type="submit" :disabled="saving" class="cursor-pointer">
                                <RefreshCw v-if="saving" class="mr-2 h-4 w-4 animate-spin" />
                                <span v-else class="mr-2">Simpan & Tes Koneksi</span>
                                {{ saving ? 'Menyimpan...' : '' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>

            <!-- Pesan error koneksi -->
            <div
                v-if="error && !connection.ok"
                class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-950/40 dark:text-red-300"
            >
                {{ error }}
            </div>

            <!-- Daftar akun -->
            <Card>
                <CardHeader>
                    <CardTitle style="font-family: Georgia, serif;">Daftar Akun DirectAdmin</CardTitle>
                    <CardDescription>
                        {{ accounts.length }} akun ditemukan. Akun dicocokkan dengan order berdasarkan domain.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="accounts.length === 0" class="py-8 text-center text-sm text-muted-foreground">
                        <template v-if="connection.ok">
                            Koneksi berhasil, tapi tidak ada akun user ditemukan di server. Pastikan login yang dipakai
                            berlevel admin/reseller (izin <code class="rounded bg-muted px-1">CMD_API_SELECT_USERS</code>)
                            dan server memiliki akun user.
                        </template>
                        <template v-else>
                            Belum ada data akun. Pastikan server sudah dikonfigurasi dan koneksi berhasil.
                        </template>
                    </div>
                    <div v-else class="overflow-x-auto">
                        <table class="w-full table-auto">
                            <thead>
                                <tr class="border-b">
                                    <th class="pb-3 text-left font-medium">Username</th>
                                    <th class="pb-3 text-left font-medium">Domain</th>
                                    <th class="pb-3 text-left font-medium">Email</th>
                                    <th class="pb-3 text-left font-medium">Status DA</th>
                                    <th class="pb-3 text-left font-medium">Order Terhubung</th>
                                    <th class="pb-3 text-center font-medium">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="account in accounts" :key="account.username" class="border-b hover:bg-muted/50">
                                    <td class="py-3">
                                        <div class="font-medium">{{ account.username }}</div>
                                        <div class="text-xs text-muted-foreground">{{ account.package }}</div>
                                    </td>
                                    <td class="py-3 text-sm">{{ account.domain || '-' }}</td>
                                    <td class="py-3 text-sm">{{ account.email || '-' }}</td>
                                    <td class="py-3">
                                        <span
                                            :class="account.suspended
                                                ? 'bg-muted text-muted-foreground'
                                                : 'bg-primary text-primary-foreground'"
                                            class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                        >
                                            {{ account.suspended ? 'Ditangguhkan' : 'Aktif' }}
                                        </span>
                                    </td>
                                    <td class="py-3 text-sm">
                                        <template v-if="account.linked_order">
                                            <Link :href="`/admin/orders/${account.linked_order.id}`" class="font-medium text-primary hover:underline">
                                                #{{ account.linked_order.id }}
                                            </Link>
                                            <span class="text-muted-foreground"> — {{ account.linked_order.customer.name }}</span>
                                            <span class="text-muted-foreground"> ({{ getOrderStatusText(account.linked_order.status) }})</span>
                                        </template>
                                        <span v-else class="text-muted-foreground">Tidak ada order cocok</span>
                                    </td>
                                    <td class="py-3">
                                        <div class="flex items-center justify-center gap-1">
                                            <Button
                                                v-if="!account.suspended"
                                                size="sm"
                                                variant="destructive"
                                                class="cursor-pointer"
                                                :disabled="acting === account.username"
                                                @click="suspend(account)"
                                            >
                                                <Ban class="mr-1.5 h-3.5 w-3.5" />
                                                Suspend
                                            </Button>
                                            <Button
                                                v-else
                                                size="sm"
                                                variant="outline"
                                                class="cursor-pointer"
                                                :disabled="acting === account.username"
                                                @click="unsuspend(account)"
                                            >
                                                <Unlock class="mr-1.5 h-3.5 w-3.5" />
                                                Unsuspend
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>

            <!-- Order tanpa akun DA -->
            <Card v-if="unlinkedOrders.length > 0">
                <CardHeader>
                    <CardTitle style="font-family: Georgia, serif;">Order Tanpa Akun DirectAdmin</CardTitle>
                    <CardDescription>
                        {{ unlinkedOrders.length }} layanan aktif yang domainnya belum cocok dengan akun DA mana pun.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full table-auto">
                            <thead>
                                <tr class="border-b">
                                    <th class="pb-3 text-left font-medium">ID</th>
                                    <th class="pb-3 text-left font-medium">Pelanggan</th>
                                    <th class="pb-3 text-left font-medium">Domain</th>
                                    <th class="pb-3 text-left font-medium">Status</th>
                                    <th class="pb-3 text-center font-medium">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="order in unlinkedOrders" :key="order.id" class="border-b hover:bg-muted/50">
                                    <td class="py-3 font-medium">#{{ order.id }}</td>
                                    <td class="py-3 text-sm">{{ order.customer.name }}</td>
                                    <td class="py-3 text-sm">{{ order.domain_name }}</td>
                                    <td class="py-3 text-sm">{{ getOrderStatusText(order.status) }}</td>
                                    <td class="py-3 text-center">
                                        <Button size="sm" variant="outline" asChild class="cursor-pointer">
                                            <Link :href="`/admin/orders/${order.id}`">Lihat Order</Link>
                                        </Button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
