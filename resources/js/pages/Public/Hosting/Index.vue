<script setup lang="ts">
import CustomerPublicLayout from '@/layouts/CustomerPublicLayout.vue';
import { formatPrice, getHostingPlanFinalPrice } from '@/lib/utils';
import { Link, usePage } from '@inertiajs/vue3';
import { Check, Search } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface HostingPlan {
    id: number;
    plan_name: string;
    service_type: string;
    billing_period: string;
    storage_gb: number;
    cpu_cores: number;
    ram_gb: number;
    bandwidth: string;
    selling_price: number;
    discount_percent: number;
    use_bulk_pricing?: boolean;
    features?: Record<string, unknown> | string | null;
    is_active: boolean;
}

interface Props {
    hostingPlans: HostingPlan[];
    filters: {
        search?: string;
        tab?: string;
    };
}

const props = defineProps<Props>();

const page = usePage();
const isCustomer = page.props.auth?.customer !== null;
const isAdmin = page.props.auth?.user !== null;

const search = ref(props.filters.search || '');
const activeTab = ref(props.filters.tab === 'vps' ? 'vps' : 'hosting');

const setActiveTab = (tab: string) => {
    activeTab.value = tab;
    activeVpsSub.value = '';
};

const billingPeriodLabels: Record<string, string> = {
    monthly: '/bulan',
    quarterly: '/3 bulan',
    semi_annually: '/6 bulan',
    annually: '/tahun',
};

const companyWhatsapp = computed(() => {
    return (page.props.brandingSettings as any)?.company_whatsapp || '';
});

const getWhatsappLink = (text: string) => {
    const raw = companyWhatsapp.value;
    if (!raw) return '';
    const phone = String(raw).replace(/[^\d]/g, '');
    return `https://wa.me/${phone}?text=${encodeURIComponent(text)}`;
};


// Paket Lite (entry-level) disembunyikan dari halaman publik
const visiblePlans = computed(() =>
    props.hostingPlans.filter((plan) => !(plan.service_type === 'hosting' && plan.plan_name.toLowerCase() === 'lite')),
);

const backupLabels: Record<string, string> = { daily: 'harian', weekly: 'mingguan', monthly: 'bulanan' };

/** features tersimpan sebagai objek JSON; ubah jadi label yang bisa dibaca. */
const fiturPaket = (plan: HostingPlan): string[] => {
    const raw = typeof plan.features === 'string' ? JSON.parse(plan.features) : plan.features;
    const f = (raw || {}) as Record<string, unknown>;
    const out: string[] = [];
    const kuota = (value: unknown, satuan: string) =>
        /^\d+$/.test(String(value)) ? `${value} ${satuan}` : `${satuan.charAt(0).toUpperCase() + satuan.slice(1)} tanpa batas`;
    if (f.ssl_certificate) out.push('SSL gratis');
    if (f.backup) out.push(`Backup ${backupLabels[String(f.backup)] || String(f.backup)}`);
    if (f.email_accounts) out.push(kuota(f.email_accounts, 'email'));
    if (f.databases) out.push(kuota(f.databases, 'database'));
    return out;
};

const vpsSubCategories = [
    { key: 'budget', label: 'Budget VPS', match: (name: string) => name.toLowerCase().includes('budget') },
    { key: 'ce', label: 'VPS SE', match: (name: string) => /ce\s/.test(name.toLowerCase()) },
];

const activeVpsSub = ref('');

const filteredPlans = computed(() => {
    let plans = visiblePlans.value;

    if (search.value) {
        plans = plans.filter((plan) => plan.plan_name.toLowerCase().includes(search.value.toLowerCase()));
    }

    if (activeTab.value === 'hosting') {
        plans = plans.filter((plan) => plan.service_type === 'hosting');
    } else if (activeTab.value === 'vps') {
        plans = plans.filter((plan) => plan.service_type === 'vps');
        if (activeVpsSub.value) {
            const sub = vpsSubCategories.find((s) => s.key === activeVpsSub.value);
            if (sub) plans = plans.filter((plan) => sub.match(plan.plan_name));
        }
    }

    return plans
        .map((plan) => ({ ...plan, fitur: fiturPaket(plan) }))
        .sort((a, b) => getHostingPlanFinalPrice(a) - getHostingPlanFinalPrice(b));
});

/** Kolom "Termasuk" hanya berguna bila paket di tab aktif punya rincian fitur. */
const adaFitur = computed(() => filteredPlans.value.some((plan) => plan.fitur.length > 0));

/** Angka desimal gaya Indonesia: 1,5 GB, bukan 1.5 GB. */
const num = (value: number | string) => String(value).replace(/\.0+$/, '').replace('.', ',');
</script>

<template>
    <CustomerPublicLayout title="Paket Shared Hosting & VPS Profesional">
        <section class="kcontainer pt-16 pb-20 md:pt-20 md:pb-24">
            <div class="max-w-2xl">
                <h1 class="text-4xl sm:text-5xl md:text-[3.25rem] md:leading-[1.08]">Hosting yang cukup, tanpa berlebihan.</h1>
                <p class="mt-4 text-base sm:text-lg" style="color: var(--kpub-muted);">
                    Semua paket sudah termasuk SSL, backup, dan panel kontrol. Bayar per periode, tanpa kontrak mengikat.
                </p>
            </div>

            <!-- Toolbar: pencarian, dua tab layanan, lalu filter sub-kategori VPS -->
            <div class="mt-10 flex flex-col gap-4 border-y py-4 lg:flex-row lg:items-center lg:justify-between" style="border-color: var(--kpub-line);">
                <div class="flex items-center gap-2">
                    <label for="hosting-search" class="sr-only">Cari paket</label>
                    <div class="relative">
                        <Search class="pointer-events-none absolute top-1/2 left-4 h-4 w-4 -translate-y-1/2" style="color: var(--kpub-faint);" />
                        <input id="hosting-search" v-model="search" type="search" placeholder="Cari nama paket" class="kfield kfield-icon w-64" />
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <div class="flex items-center gap-1 rounded-full p-1" style="background-color: var(--kpub-surface);">
                        <button
                            v-for="tab in [{ key: 'hosting', label: 'Shared Hosting' }, { key: 'vps', label: 'VPS' }]"
                            :key="tab.key"
                            type="button"
                            class="rounded-full px-4 py-1.5 text-sm transition-colors"
                            :style="
                                activeTab === tab.key
                                    ? 'background-color: #ffffff; color: var(--kpub-ink); box-shadow: 0 1px 2px rgba(13,18,16,0.08);'
                                    : 'color: var(--kpub-muted);'
                            "
                            @click="setActiveTab(tab.key)"
                        >
                            {{ tab.label }}
                        </button>
                    </div>

                    <div v-if="activeTab === 'vps'" class="flex flex-wrap items-center gap-1.5">
                        <button type="button" class="kbtn kbtn-outline !px-3 !py-1.5 !text-xs" :style="activeVpsSub === '' ? 'border-color: var(--kpub-ink); font-weight: 600;' : ''" @click="activeVpsSub = ''">Semua</button>
                        <button
                            v-for="sub in vpsSubCategories"
                            :key="sub.key"
                            type="button"
                            class="kbtn kbtn-outline !px-3 !py-1.5 !text-xs"
                            :style="activeVpsSub === sub.key ? 'border-color: var(--kpub-ink); font-weight: 600;' : ''"
                            @click="activeVpsSub = sub.key"
                        >
                            {{ sub.label }}
                        </button>
                    </div>
                </div>
            </div>

            <p class="knum mt-4 text-sm" style="color: var(--kpub-muted);">
                {{ filteredPlans.length }} paket · harga per {{ activeTab === 'vps' ? 'periode sewa' : 'periode tagihan' }}, belum termasuk PPN bila berlaku
            </p>

            <!-- Tabel paket: satu baris per paket, angka rata kanan -->
            <div v-if="filteredPlans.length" class="kcard mt-6 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[860px] border-collapse text-left">
                        <thead>
                            <tr class="border-b" style="border-color: var(--kpub-line);">
                                <th class="px-6 py-3.5 text-xs font-medium tracking-wide uppercase" style="color: var(--kpub-muted);">Paket</th>
                                <th class="px-4 py-3.5 text-xs font-medium tracking-wide uppercase" style="color: var(--kpub-muted);">Penyimpanan</th>
                                <th class="px-4 py-3.5 text-xs font-medium tracking-wide uppercase" style="color: var(--kpub-muted);">CPU</th>
                                <th class="px-4 py-3.5 text-xs font-medium tracking-wide uppercase" style="color: var(--kpub-muted);">RAM</th>
                                <th class="px-4 py-3.5 text-xs font-medium tracking-wide uppercase" style="color: var(--kpub-muted);">Bandwidth</th>
                                <th v-if="adaFitur" class="px-4 py-3.5 text-xs font-medium tracking-wide uppercase" style="color: var(--kpub-muted);">Termasuk</th>
                                <th class="px-4 py-3.5 text-right text-xs font-medium tracking-wide uppercase" style="color: var(--kpub-muted);">Harga</th>
                                <th class="px-6 py-3.5" />
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="plan in filteredPlans"
                                :key="plan.id"
                                class="border-b transition-colors last:border-b-0"
                                style="border-color: var(--kpub-line);"
                            >
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-medium">{{ plan.plan_name }}</span>
                                        <span
                                            class="rounded-full px-2 py-0.5 text-[10px] font-medium tracking-wide uppercase"
                                            :style="
                                                plan.service_type === 'vps'
                                                    ? 'background-color: #f1f0fb; color: #4c3f9e;'
                                                    : 'background-color: var(--kpub-green-soft); color: var(--kpub-green);'
                                            "
                                        >{{ plan.service_type === 'vps' ? 'VPS' : 'Shared' }}</span>
                                    </div>
                                    <div v-if="!plan.use_bulk_pricing && plan.discount_percent > 0" class="knum mt-0.5 text-xs" style="color: var(--kpub-green);">
                                        hemat {{ plan.discount_percent }}%
                                    </div>
                                </td>
                                <td class="knum whitespace-nowrap px-4 py-4 text-sm">{{ num(plan.storage_gb) }} GB SSD</td>
                                <td class="knum whitespace-nowrap px-4 py-4 text-sm">{{ num(plan.cpu_cores) }} vCPU</td>
                                <td class="knum whitespace-nowrap px-4 py-4 text-sm">{{ num(plan.ram_gb) }} GB</td>
                                <td class="knum whitespace-nowrap px-4 py-4 text-sm">{{ /^unlimited$/i.test(plan.bandwidth) ? 'Tanpa batas' : plan.bandwidth }}</td>
                                <td v-if="adaFitur" class="px-4 py-4">
                                    <div class="flex max-w-[220px] flex-wrap gap-x-3 gap-y-1 text-xs" style="color: var(--kpub-muted);">
                                        <span v-for="feature in plan.fitur" :key="feature" class="inline-flex items-center gap-1">
                                            <Check class="h-3 w-3 shrink-0" style="color: var(--kpub-green);" />
                                            {{ feature }}
                                        </span>
                                    </div>
                                </td>
                                <td class="knum px-4 py-4 text-right">
                                    <div v-if="!plan.use_bulk_pricing && plan.discount_percent > 0" class="text-xs line-through" style="color: var(--kpub-faint);">
                                        {{ formatPrice(plan.selling_price) }}
                                    </div>
                                    <div class="text-sm font-medium">{{ formatPrice(getHostingPlanFinalPrice(plan)) }}</div>
                                    <div class="text-xs" style="color: var(--kpub-muted);">{{ billingPeriodLabels[plan.billing_period] || '/tahun' }}</div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a
                                        :href="getWhatsappLink(`Halo, saya ingin beli ${plan.service_type.toUpperCase()} ${plan.plan_name} ${num(plan.storage_gb)} GB.`)"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="kbtn kbtn-primary !px-4 !py-2 !text-xs"
                                    >Pesan</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-else class="kcard mt-6 px-6 py-16 text-center">
                <h2 class="text-xl">Tidak ada paket yang cocok</h2>
                <p class="mt-2 text-sm" style="color: var(--kpub-muted);">
                    {{ search ? 'Coba kata kunci lain, atau kosongkan pencarian.' : 'Belum ada paket aktif untuk kategori ini.' }}
                </p>
            </div>

            <!-- Penutup -->
            <div v-if="!isCustomer && !isAdmin" class="mt-14 overflow-hidden rounded-[25px] px-6 py-12 sm:px-12" style="background-color: var(--kpub-deep);">
                <div class="flex flex-col items-start justify-between gap-6 sm:flex-row sm:items-end">
                    <div class="max-w-lg">
                        <h2 class="text-2xl sm:text-3xl" style="color: #ffffff;">Belum yakin paket mana?</h2>
                        <p class="mt-3 text-sm" style="color: var(--kpub-deep-ink);">
                            Sebutkan kebutuhan Anda, kami sarankan yang paling murah yang masih cukup. Atau langsung buat akun dan pesan sendiri.
                        </p>
                    </div>
                    <div class="flex flex-col gap-2 sm:flex-row">
                        <Link href="/customer/register" class="kbtn" style="background-color: #ffffff; color: var(--kpub-deep);">Buat akun</Link>
                        <Link href="/domains" class="kbtn" style="border: 1px solid rgba(255, 255, 255, 0.28); color: #ffffff;">Lihat domain</Link>
                    </div>
                </div>
            </div>
        </section>
    </CustomerPublicLayout>
</template>
