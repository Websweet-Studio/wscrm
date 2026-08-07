 <script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import CustomerPublicLayout from '@/layouts/CustomerPublicLayout.vue';
import { getHostingPlanFinalPrice } from '@/lib/utils';
import { Link, router, usePage } from '@inertiajs/vue3';
import { Check, Search, Server, ShoppingCart } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface HostingPlan {
    id: number;
    plan_name: string;
    service_type: string;
    storage_gb: number;
    cpu_cores: number;
    ram_gb: number;
    bandwidth: string;
    selling_price: number;
    discount_percent: number;
    use_bulk_pricing?: boolean;
    features: string[];
    is_active: boolean;
}

interface Props {
    hostingPlans: HostingPlan[];
    filters: {
        search?: string;
    };
}

const props = defineProps<Props>();

const page = usePage();
const isCustomer = page.props.auth?.customer !== null;
const isAdmin = page.props.auth?.user !== null;

const search = ref(props.filters.search || '');
const activeTab = ref('all');

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(price);
};

const companyWhatsapp = computed(() => {
    return (page.props.brandingSettings as any)?.company_whatsapp || '';
});

const getWhatsappLink = (text: string) => {
    const raw = companyWhatsapp.value;
    if (!raw) return '';

    const phone = String(raw).replace(/[^\d]/g, '');
    const message = encodeURIComponent(text);
    return `https://wa.me/${phone}?text=${message}`;
};

const handleSearch = () => {
    router.get(
        '/hosting',
        { search: search.value },
        {
            preserveState: true,
            replace: true,
        },
    );
};

// Paket Lite (entry-level) disembunyikan dari halaman publik
const visiblePlans = computed(() =>
    props.hostingPlans.filter((plan) => !(plan.service_type === 'hosting' && plan.plan_name.toLowerCase() === 'lite')),
);

const filteredPlans = computed(() => {
    let plans = visiblePlans.value;

    if (search.value) {
        plans = plans.filter((plan) => plan.plan_name.toLowerCase().includes(search.value.toLowerCase()));
    }

    if (activeTab.value === 'hosting') {
        plans = plans.filter((plan) => plan.service_type === 'hosting');
    } else if (activeTab.value === 'vps') {
        plans = plans.filter((plan) => plan.service_type === 'vps');
    }

    return plans
        .map((plan) => ({
            ...plan,
            features: typeof plan.features === 'string' ? JSON.parse(plan.features) : Array.isArray(plan.features) ? plan.features : [],
        }))
        .sort((a, b) => getHostingPlanFinalPrice(a) - getHostingPlanFinalPrice(b));
});
</script>

<template>
    <CustomerPublicLayout title="Paket Shared Hosting & VPS Profesional">
        <section class="mx-auto max-w-6xl px-4 py-12 sm:px-6 sm:py-16 lg:py-20">
            <!-- Hero -->
            <div class="mb-12 text-center sm:mb-16">
                <h1 class="mb-4 text-4xl leading-tight font-medium sm:text-5xl md:text-6xl" style="color: #141413; line-height: 1.1; font-family: Georgia, serif;">
                    Paket Shared Hosting &amp; VPS Profesional
                </h1>
                <p class="mx-auto mb-6 max-w-2xl text-base sm:text-lg lg:text-xl" style="color: #5e5d59; line-height: 1.6;">
                    Pilih paket hosting atau VPS yang sempurna untuk website dan aplikasi Anda. Cepat, terpercaya, dan aman.
                </p>
            </div>

            <!-- Search + Filter -->
            <div class="mb-12 rounded-2xl bg-white p-6" style="border: 1px solid #dadad3;">
                <div class="mb-4 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div class="flex items-center gap-3">
                        <div class="rounded-full p-2" style="background-color: var(--secondary);">
                            <Search class="h-5 w-5" style="color: var(--primary);" />
                        </div>
                        <div>
                            <div class="text-base font-semibold" style="color: #000000;">Cari & Filter Paket</div>
                            <div class="text-sm" style="color: #62625b;">Temukan paket yang sesuai dengan kebutuhan Anda</div>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <div class="relative flex-1">
                            <Search class="absolute top-2.5 left-3 h-4 w-4" style="color: #91918c;" />
                            <Input v-model="search" placeholder="Cari paket..." class="pl-8" style="background-color: #ffffff; border: 1px solid #c8c8c1; color: #000000; border-radius: 16px;" @keyup.enter="handleSearch" />
                        </div>
                        <Button @click="handleSearch" style="background-color: var(--secondary); color: #000000; border-radius: 16px;">Filter</Button>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <button
                        @click="activeTab = 'all'"
                        class="rounded-full px-4 py-2 text-sm font-bold transition-colors"
                        :style="activeTab === 'all' ? 'background-color: #000000; color: #ffffff;' : 'background-color: var(--secondary); color: #000000;'"
                    >Semua</button>
                    <button
                        @click="activeTab = 'hosting'"
                        class="rounded-full px-4 py-2 text-sm font-bold transition-colors"
                        :style="activeTab === 'hosting' ? 'background-color: #000000; color: #ffffff;' : 'background-color: var(--secondary); color: #000000;'"
                    >
                        <Server class="mr-1 inline h-4 w-4" />Shared Hosting
                    </button>
                    <button
                        @click="activeTab = 'vps'"
                        class="rounded-full px-4 py-2 text-sm font-bold transition-colors"
                        :style="activeTab === 'vps' ? 'background-color: #000000; color: #ffffff;' : 'background-color: var(--secondary); color: #000000;'"
                    >
                        <Server class="mr-1 inline h-4 w-4" />VPS
                    </button>
                </div>

                <div class="mt-3 text-sm" style="color: #62625b;">
                    <span v-if="activeTab === 'all'">Menampilkan semua paket shared hosting &amp; VPS</span>
                    <span v-else-if="activeTab === 'hosting'">Paket shared hosting &mdash; sempurna untuk website pribadi &amp; bisnis</span>
                    <span v-else-if="activeTab === 'vps'">Paket VPS &mdash; kontrol penuh untuk proyek &amp; aplikasi besar</span>
                    &middot; {{ filteredPlans.length }} paket
                </div>
            </div>

            <!-- Table: Daftar Paket Hosting -->
            <div class="mb-12">
                <div class="overflow-hidden rounded-2xl" style="border: 1px solid #dadad3;">
                    <div class="overflow-x-auto">
                        <table class="w-full" style="background-color: #ffffff;">
                            <thead>
                                <tr style="background-color: var(--secondary);">
                                    <th class="px-4 py-3 text-left text-sm font-bold" style="color: #33332e;">Paket</th>
                                    <th class="px-4 py-3 text-left text-sm font-bold" style="color: #33332e;">Storage</th>
                                    <th class="px-4 py-3 text-left text-sm font-bold" style="color: #33332e;">CPU</th>
                                    <th class="px-4 py-3 text-left text-sm font-bold" style="color: #33332e;">RAM</th>
                                    <th class="px-4 py-3 text-left text-sm font-bold" style="color: #33332e;">Bandwidth</th>
                                    <th class="px-4 py-3 text-left text-sm font-bold" style="color: #33332e;">Fitur</th>
                                    <th class="px-4 py-3 text-right text-sm font-bold" style="color: #33332e;">Harga</th>
                                    <th class="px-4 py-3 text-center text-sm font-bold" style="color: #33332e;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="plan in filteredPlans"
                                    :key="plan.id"
                                    class="transition-colors hover:bg-[#f6f6f3]"
                                    style="border-top: 1px solid #e5e5e0;"
                                >
                                    <td class="px-4 py-4">
                                        <div class="flex items-center gap-2">
                                            <div class="rounded-full p-1.5" style="background-color: var(--secondary);">
                                                <Server class="h-4 w-4" style="color: var(--primary);" />
                                            </div>
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <div class="text-sm font-semibold" style="color: #000000;">{{ plan.plan_name }}</div>
                                                    <span
                                                        class="rounded-full px-2 py-0.5 text-[10px] font-bold uppercase"
                                                        :style="plan.service_type === 'vps'
                                                            ? 'background-color: #ede9fe; color: #6d28d9;'
                                                            : 'background-color: #ecfdf5; color: #047857;'"
                                                    >{{ plan.service_type === 'vps' ? 'VPS' : 'Shared Hosting' }}</span>
                                                </div>
                                                <div v-if="!plan.use_bulk_pricing && plan.discount_percent > 0" class="text-xs" style="color: var(--primary);">Diskon {{ plan.discount_percent }}%</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-sm" style="color: #33332e;">{{ plan.storage_gb }} GB SSD</td>
                                    <td class="px-4 py-4 text-sm" style="color: #33332e;">{{ plan.cpu_cores }} vCPU</td>
                                    <td class="px-4 py-4 text-sm" style="color: #33332e;">{{ plan.ram_gb }} GB</td>
                                    <td class="px-4 py-4 text-sm" style="color: #33332e;">{{ plan.bandwidth }}</td>
                                    <td class="px-4 py-4">
                                        <div class="flex max-w-[200px] flex-wrap gap-1">
                                            <span
                                                v-for="(feature, i) in (plan.features || []).slice(0, 3)"
                                                :key="i"
                                                class="inline-flex items-center gap-0.5 rounded-full bg-[#f6f6f3] px-2 py-0.5 text-xs"
                                                style="color: #62625b;"
                                            >
                                                <Check class="h-3 w-3" style="color: var(--primary);" />
                                                {{ feature }}
                                            </span>
                                            <span v-if="(plan.features || []).length > 3" class="text-xs" style="color: #91918c;">+{{ (plan.features || []).length - 3 }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-right">
                                        <div v-if="!plan.use_bulk_pricing && plan.discount_percent > 0" class="text-xs line-through" style="color: #91918c;">
                                            {{ formatPrice(plan.selling_price) }}
                                        </div>
                                        <div class="text-base font-bold" style="color: var(--primary);">
                                            {{ formatPrice(getHostingPlanFinalPrice(plan)) }}
                                        </div>
                                        <div class="text-xs" style="color: #62625b;">/tahun</div>
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <Button
                                            asChild
                                            size="sm"
                                            style="background-color: var(--primary); color: #ffffff; border-radius: 16px;"
                                        >
                                            <a
                                                :href="getWhatsappLink(`Halo, saya ingin beli ${plan.service_type.toUpperCase()} ${plan.plan_name}.`)"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                            >
                                                <ShoppingCart class="mr-1 h-3 w-3" />
                                                Beli
                                            </a>
                                        </Button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="filteredPlans.length === 0" class="py-12 text-center">
                <Server class="mx-auto mb-4 h-12 w-12" style="color: #91918c;" />
                <h3 class="mb-2 text-xl font-semibold" style="color: #000000;">Tidak ada paket ditemukan</h3>
                <p class="text-sm" style="color: #62625b;">
                    {{ search ? 'Coba sesuaikan kriteria pencarian Anda.' : 'Tidak ada paket hosting & VPS yang tersedia saat ini.' }}
                </p>
            </div>

            <!-- CTA -->
            <div v-if="!isCustomer && !isAdmin" class="mt-12 pt-16 text-center">
                <div class="rounded-2xl bg-[#262622] px-8 py-12" style="border: 1px solid #30302e;">
                    <h2 class="mb-4 text-3xl font-semibold" style="color: #ffffff; line-height: 1.2;">
                        Siap untuk Memulai?
                    </h2>
                    <p class="mx-auto mb-8 max-w-2xl text-base sm:text-lg" style="color: #b0aea5; line-height: 1.6;">
                        Bergabunglah dengan ribuan pelanggan yang puas yang mempercayai kebutuhan hosting mereka kepada kami.
                    </p>
                    <div class="flex flex-col gap-3 sm:flex-row sm:justify-center sm:gap-4">
                        <Button asChild size="lg" class="text-lg px-6 py-4" style="background-color: var(--primary); color: #ffffff; border-radius: 16px;">
                            <Link href="/customer/register">Buat Akun</Link>
                        </Button>
                        <Button asChild variant="outline" size="lg" class="text-lg px-6 py-4" style="background-color: #ffffff; color: #000000; border: 1px solid #30302e; border-radius: 16px;">
                            <Link href="/domains">Jelajahi Domain</Link>
                        </Button>
                    </div>
                </div>
            </div>
        </section>
    </CustomerPublicLayout>
</template>
