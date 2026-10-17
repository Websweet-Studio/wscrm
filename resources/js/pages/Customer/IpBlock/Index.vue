<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import CustomerLayout from '@/layouts/CustomerLayout.vue';
import ToastContainer from '@/components/ToastContainer.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import {
    AlertTriangle,
    Ban,
    CheckCircle2,
    Clock,
    Globe,
    History,
    Info,
    ListChecks,
    ShieldAlert,
    ShieldCheck,
    Unlock,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface WhitelistInfo {
    note: string | null;
    since: string;
    expires: string | null;
    temporary: boolean;
    active: boolean;
}

interface Blokir {
    ip: string;
    reason: string | null;
    domain: string | null;
    kind: string | null;
    cc: string | null;
    asn: string | null;
    hits: number | null;
    since: string;
    expires: string | null;
    expires_in: number | null;
    ip_saya: boolean;
    agent: string | null;
    whitelist: WhitelistInfo | null;
}

interface Riwayat {
    id: number;
    ip: string;
    domain: string | null;
    reason: string | null;
    hours: number;
    status: string;
    note: string | null;
    server: string | null;
    expires_at: string | null;
    created_at: string | null;
}

const props = defineProps<{
    ipSaya: string;
    blokirIpSaya: Blokir[];
    blokirLayanan: Blokir[];
    total: number;
    totalLayanan: number;
    tampil: number;
    q: string;
    domains: string[];
    layananSiap: boolean;
    error: string | null;
    jam: number;
    riwayat: Riwayat[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/customer/dashboard' },
    { title: 'IP Block', href: '/customer/ip-block' },
];

// --- modal: buka blokir sementara -------------------------------------------
const modalBuka = ref(false);
const dikirim = ref(false);
const target = ref<Blokir | null>(null);
const alasan = ref('');

const bukaModal = (b: Blokir) => {
    target.value = b;
    alasan.value = '';
    modalBuka.value = true;
};

const bolehAjukan = computed(() => props.layananSiap && props.blokirLayanan.length + props.blokirIpSaya.length > 0);

// --- kotak cari IP -----------------------------------------------------------
// Satu klien bisa punya ratusan IP terkunci (uji 21 Sep 2026: 112 pada satu domain),
// jadi tabel cuma menampilkan sebagian; sisanya dicari lewat kotak ini.
const cari = ref(props.q || '');

const cariSekarang = () => {
    router.get('/customer/ip-block', cari.value ? { q: cari.value } : {}, { preserveState: true, preserveScroll: true, replace: true });
};

const bersihkanCari = () => {
    cari.value = '';
    cariSekarang();
};

const kirim = () => {
    if (!target.value) return;
    dikirim.value = true;
    router.post(
        '/customer/ip-block/whitelist',
        { ip: target.value.ip, reason: alasan.value || null },
        {
            preserveScroll: true,
            onFinish: () => {
                dikirim.value = false;
            },
            onSuccess: () => {
                modalBuka.value = false;
                target.value = null;
                alasan.value = '';
            },
        },
    );
};

// --- modal: domain yang dipantau -------------------------------------------
const modalDomain = ref(false);

const durasiTeks = (detik: number | null) => {
    if (detik === null || detik === undefined) return '-';
    const jam = Math.floor(detik / 3600);
    if (jam >= 1) return `${jam} jam lagi`;
    return `${Math.max(1, Math.round(detik / 60))} menit lagi`;
};
</script>

<template>
    <Head title="IP Block" />

    <CustomerLayout :breadcrumbs="breadcrumbs">
        <!-- Flash dari server (berhasil/gagal) — layout pelanggan belum punya penampil toast,
             jadi dipasang di halaman ini saja supaya pesan pengajuan tidak hilang diam-diam. -->
        <ToastContainer />
        <div class="space-y-4 p-4 sm:space-y-6 sm:p-6">
            <!-- Hero -->
            <Card class="relative overflow-hidden border-border/60 bg-card/70 shadow-sm backdrop-blur">
                <div class="pointer-events-none absolute inset-0 opacity-60 dark:opacity-80">
                    <div class="absolute -inset-24 bg-[radial-gradient(closest-side,rgba(239,68,68,0.14),transparent_65%)]"></div>
                    <div class="absolute -right-24 -top-32 h-96 w-96 bg-[radial-gradient(closest-side,rgba(59,130,246,0.14),transparent_60%)]"></div>
                </div>
                <CardContent class="relative p-4 sm:p-6">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <div class="mb-2 inline-flex items-center gap-2 rounded-full border border-border/60 bg-background/60 px-2.5 py-1 text-xs text-muted-foreground">
                                <ShieldAlert class="h-3.5 w-3.5 text-red-600 dark:text-red-400" />
                                <span>Keamanan server</span>
                            </div>
                            <h1 class="font-heading text-2xl font-medium tracking-tight sm:text-3xl">IP Block</h1>
                            <p class="mt-1 text-sm text-muted-foreground sm:text-base">
                                Lihat IP yang sedang diblokir di layanan kamu, lalu ajukan buka blokir sementara —
                                pengajuan langsung diterima dan berlaku {{ jam }} jam.
                            </p>
                        </div>
                        <Button variant="outline" class="shrink-0 gap-2" @click="modalDomain = true">
                            <Globe class="h-4 w-4" />
                            Domain dipantau ({{ domains.length }})
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Layanan tidak bisa dihubungi -->
            <Card v-if="!layananSiap || error" class="border-amber-300/60 bg-amber-50/60 dark:border-amber-500/30 dark:bg-amber-500/10">
                <CardContent class="flex items-start gap-3 p-4">
                    <AlertTriangle class="mt-0.5 h-5 w-5 shrink-0 text-amber-600 dark:text-amber-400" />
                    <div class="text-sm">
                        <div class="font-medium">Data blokir belum bisa ditampilkan</div>
                        <p class="mt-1 text-muted-foreground">
                            {{ error || 'Layanan keamanan sedang tidak bisa dihubungi. Coba muat ulang sebentar lagi.' }}
                        </p>
                    </div>
                </CardContent>
            </Card>

            <!-- Ringkasan -->
            <div class="grid gap-3 sm:grid-cols-3 sm:gap-4">
                <Card class="rounded-lg border-border/60 shadow-sm">
                    <CardHeader class="pb-2">
                        <CardDescription class="flex items-center gap-2">
                            <Globe class="h-3.5 w-3.5" /> IP kamu sekarang
                        </CardDescription>
                        <CardTitle class="flex items-center gap-2 text-lg font-semibold">
                            <code>{{ ipSaya || '-' }}</code>
                            <Badge v-if="blokirIpSaya.length" variant="destructive">terblokir</Badge>
                            <Badge v-else variant="secondary">aman</Badge>
                        </CardTitle>
                    </CardHeader>
                </Card>
                <Card class="rounded-lg border-border/60 shadow-sm">
                    <CardHeader class="pb-2">
                        <CardDescription class="flex items-center gap-2">
                            <Ban class="h-3.5 w-3.5" /> Blokir aktif di layanan kamu
                        </CardDescription>
                        <CardTitle class="text-2xl font-semibold">{{ total }}</CardTitle>
                    </CardHeader>
                </Card>
                <Card class="rounded-lg border-border/60 shadow-sm">
                    <CardHeader class="pb-2">
                        <CardDescription class="flex items-center gap-2">
                            <ListChecks class="h-3.5 w-3.5" /> Buka blokir
                        </CardDescription>
                        <CardTitle class="text-2xl font-semibold">Tanpa batas</CardTitle>
                    </CardHeader>
                </Card>
            </div>

            <!-- IP kamu terblokir: paling penting, taruh di atas -->
            <Card v-if="blokirIpSaya.length" class="border-red-300/70 bg-red-50/50 shadow-sm dark:border-red-500/30 dark:bg-red-500/10">
                <CardHeader class="pb-3">
                    <CardTitle class="flex items-center gap-2 text-base">
                        <ShieldAlert class="h-4 w-4 text-red-600 dark:text-red-400" />
                        IP kamu sedang diblokir
                    </CardTitle>
                    <CardDescription>
                        Karena itu layanan kamu bisa menolak akses dari jaringan ini. Ajukan buka blokir sementara
                        ({{ jam }} jam) — langsung aktif, tanpa menunggu admin.
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-3">
                    <div v-for="b in blokirIpSaya" :key="b.ip + b.domain" class="flex flex-col gap-3 rounded-lg border border-border/60 bg-background/70 p-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0 text-sm">
                            <div class="flex flex-wrap items-center gap-2">
                                <code class="font-medium">{{ b.ip }}</code>
                                <Badge variant="outline">{{ b.domain || 'server' }}</Badge>
                                <Badge v-if="b.whitelist && b.whitelist.active" variant="secondary">
                                    <Unlock class="mr-1 h-3 w-3" /> whitelist s/d {{ b.whitelist.expires }}
                                </Badge>
                            </div>
                            <div class="mt-1 truncate text-muted-foreground">{{ b.reason || 'Diblokir otomatis oleh pengaman server' }}</div>
                            <div class="mt-1 text-xs text-muted-foreground">
                                Sejak {{ b.since }} · berlaku sampai {{ b.expires || 'dibuka manual' }}
                                <span v-if="b.expires_in">({{ durasiTeks(b.expires_in) }})</span>
                            </div>
                        </div>
                        <Button class="shrink-0 gap-2" :disabled="!bolehAjukan" @click="bukaModal(b)">
                            <Unlock class="h-4 w-4" />
                            Buka blokir {{ jam }} jam
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Tabel blokir layanan -->
            <Card class="border-border/60 shadow-sm">
                <CardHeader class="pb-3">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div class="space-y-1.5">
                            <CardTitle class="flex items-center gap-2 text-base">
                                <Ban class="h-4 w-4" /> Blokir aktif pada layanan kamu
                            </CardTitle>
                            <CardDescription>
                                IP di bawah ini diblokir karena pola aksesnya dianggap menyerang situs kamu. Kalau ini
                                jaringan kamu sendiri (kantor/rumah), ajukan buka blokir sementara.
                            </CardDescription>
                        </div>
                        <form class="flex shrink-0 items-center gap-2" @submit.prevent="cariSekarang">
                            <Input v-model="cari" placeholder="Cari IP / alasan…" class="h-9 w-full sm:w-48" />
                            <Button type="submit" size="sm" variant="secondary">Cari</Button>
                            <Button v-if="q" type="button" size="sm" variant="ghost" @click="bersihkanCari">Reset</Button>
                        </form>
                    </div>
                    <p v-if="q" class="text-xs text-muted-foreground">
                        <template v-if="tampil">Ditemukan {{ tampil }} baris untuk &ldquo;{{ q }}&rdquo;.</template>
                        <template v-else>Tidak ada blokir yang cocok dengan &ldquo;{{ q }}&rdquo;.</template>
                    </p>
                    <p v-else-if="totalLayanan > tampil" class="text-xs text-muted-foreground">
                        Menampilkan {{ tampil }} dari {{ totalLayanan }} blokir terbaru — cari IP tertentu lewat kotak di atas.
                    </p>
                </CardHeader>
                <CardContent class="p-0 sm:p-6 sm:pt-0">
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>IP</TableHead>
                                    <TableHead>Domain</TableHead>
                                    <TableHead class="hidden md:table-cell">Alasan</TableHead>
                                    <TableHead class="hidden lg:table-cell">Sejak</TableHead>
                                    <TableHead class="hidden lg:table-cell">Berlaku s/d</TableHead>
                                    <TableHead class="text-right">Aksi</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="b in blokirLayanan" :key="b.ip + b.domain">
                                    <TableCell>
                                        <code class="text-xs sm:text-sm">{{ b.ip }}</code>
                                        <div class="text-[11px] text-muted-foreground">{{ b.cc || '' }}{{ b.asn ? ' · ' + b.asn : '' }}</div>
                                    </TableCell>
                                    <TableCell class="text-sm">
                                        {{ b.domain || '-' }}
                                        <Badge v-if="b.whitelist && b.whitelist.active" variant="secondary" class="ml-1">whitelist s/d {{ b.whitelist.expires }}</Badge>
                                    </TableCell>
                                    <TableCell class="hidden max-w-[260px] truncate text-sm text-muted-foreground md:table-cell">
                                        {{ b.reason || '-' }}
                                    </TableCell>
                                    <TableCell class="hidden whitespace-nowrap text-xs text-muted-foreground lg:table-cell">{{ b.since }}</TableCell>
                                    <TableCell class="hidden whitespace-nowrap text-xs text-muted-foreground lg:table-cell">
                                        <span v-if="b.expires">{{ b.expires }}<br />{{ durasiTeks(b.expires_in) }}</span>
                                        <span v-else>dibuka manual</span>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <Button size="sm" variant="outline" class="gap-1.5" :disabled="!bolehAjukan" @click="bukaModal(b)">
                                            <Unlock class="h-3.5 w-3.5" />
                                            Buka blokir
                                        </Button>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="!blokirLayanan.length">
                                    <TableCell colspan="6" class="py-8 text-center text-sm text-muted-foreground">
                                        <ShieldCheck class="mx-auto mb-2 h-6 w-6 text-emerald-600 dark:text-emerald-400" />
                                        Tidak ada IP lain yang diblokir pada layanan kamu.
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </CardContent>
            </Card>

            <!-- Riwayat pengajuan -->
            <Card class="border-border/60 shadow-sm">
                <CardHeader class="pb-3">
                    <CardTitle class="flex items-center gap-2 text-base">
                        <History class="h-4 w-4" /> Riwayat buka blokir
                    </CardTitle>
                    <CardDescription>10 aktivitas buka blokir terakhir dari akun kamu, termasuk hasil dan masa berlaku.</CardDescription>
                </CardHeader>
                <CardContent class="p-0 sm:p-6 sm:pt-0">
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Waktu</TableHead>
                                    <TableHead>IP</TableHead>
                                    <TableHead class="hidden md:table-cell">Domain</TableHead>
                                    <TableHead>Durasi</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead class="hidden lg:table-cell">Berlaku s/d</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="r in riwayat" :key="r.id">
                                    <TableCell class="whitespace-nowrap text-xs text-muted-foreground">{{ r.created_at }}</TableCell>
                                    <TableCell><code class="text-xs sm:text-sm">{{ r.ip }}</code></TableCell>
                                    <TableCell class="hidden text-sm md:table-cell">{{ r.domain || '-' }}</TableCell>
                                    <TableCell class="whitespace-nowrap text-sm">{{ r.hours }} jam</TableCell>
                                    <TableCell>
                                        <Badge :variant="r.status === 'diterima' ? 'secondary' : 'destructive'">
                                            <CheckCircle2 v-if="r.status === 'diterima'" class="mr-1 h-3 w-3" />
                                            {{ r.status }}
                                        </Badge>
                                        <div v-if="r.note" class="mt-1 max-w-[220px] truncate text-[11px] text-muted-foreground">{{ r.note }}</div>
                                    </TableCell>
                                    <TableCell class="hidden whitespace-nowrap text-xs text-muted-foreground lg:table-cell">{{ r.expires_at || '-' }}</TableCell>
                                </TableRow>
                                <TableRow v-if="!riwayat.length">
                                    <TableCell colspan="6" class="py-8 text-center text-sm text-muted-foreground">
                                        Belum ada pengajuan dari akun ini.
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </CardContent>
            </Card>

            <div class="flex items-start gap-2 rounded-lg border border-border/60 bg-muted/20 p-3 text-xs text-muted-foreground">
                <Info class="mt-0.5 h-3.5 w-3.5 shrink-0" />
                <p>
                    Buka blokir di halaman ini langsung disetujui tanpa batas jumlah, bersifat
                    <span class="font-medium">sementara ({{ jam }} jam)</span>, dan tercatat atas nama akun kamu.
                    Setelah masa berlaku habis, aturan blokir otomatis berlaku lagi — kalau IP kamu memang sah,
                    mintakan whitelist permanen ke admin.
                </p>
            </div>
        </div>

        <!-- Modal: ajukan buka blokir -->
        <Dialog v-model:open="modalBuka">
            <DialogContent class="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2">
                        <Unlock class="h-4 w-4 text-red-600 dark:text-red-400" />
                        Buka blokir sementara
                    </DialogTitle>
                    <DialogDescription>
                        Blokir dibuka sekarang dan IP kamu diwhitelist selama {{ jam }} jam. Pengajuan langsung
                        diterima — tidak perlu menunggu admin.
                    </DialogDescription>
                </DialogHeader>

                <div v-if="target" class="space-y-3">
                    <div class="rounded-lg border border-border/60 bg-muted/20 p-3 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <code class="font-medium">{{ target.ip }}</code>
                            <Badge variant="outline">{{ target.domain || 'server' }}</Badge>
                        </div>
                        <div class="mt-1 text-muted-foreground">{{ target.reason || 'Diblokir otomatis oleh pengaman server' }}</div>
                        <div class="mt-2 flex flex-wrap items-center gap-3 text-xs text-muted-foreground">
                            <span class="inline-flex items-center gap-1"><Clock class="h-3 w-3" /> sejak {{ target.since }}</span>
                            <span v-if="target.agent">server: {{ target.agent }}</span>
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="alasan">Alasan (opsional)</Label>
                        <textarea
                            id="alasan"
                            v-model="alasan"
                            rows="3"
                            maxlength="160"
                            placeholder="Contoh: IP kantor kami, tim marketing perlu akses untuk update konten."
                            class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                        ></textarea>
                        <p class="text-xs text-muted-foreground">Alasan ikut tercatat di catatan keamanan server.</p>
                    </div>
                </div>

                <DialogFooter class="gap-2">
                    <Button type="button" variant="outline" :disabled="dikirim" @click="modalBuka = false">Batal</Button>
                    <Button type="button" :disabled="dikirim" class="gap-2" @click="kirim">
                        <Unlock class="h-4 w-4" />
                        {{ dikirim ? 'Memproses…' : `Buka blokir ${jam} jam` }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Modal: domain dipantau -->
        <Dialog v-model:open="modalDomain">
            <DialogContent class="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2">
                        <Globe class="h-4 w-4" /> Domain yang dipantau
                    </DialogTitle>
                    <DialogDescription>
                        Blokir yang ditampilkan di halaman ini hanya milik domain di bawah.
                    </DialogDescription>
                </DialogHeader>
                <div class="max-h-72 space-y-1 overflow-y-auto">
                    <div
                        v-for="d in domains"
                        :key="d"
                        class="flex items-center gap-2 rounded-md border border-border/50 bg-muted/10 px-3 py-1.5 text-sm"
                    >
                        <Globe class="h-3.5 w-3.5 text-muted-foreground" />
                        <code>{{ d }}</code>
                    </div>
                    <p v-if="!domains.length" class="py-6 text-center text-sm text-muted-foreground">
                        Belum ada domain yang terdaftar pada akun ini.
                    </p>
                </div>
                <DialogFooter>
                    <Button type="button" variant="outline" @click="modalDomain = false">Tutup</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </CustomerLayout>
</template>
