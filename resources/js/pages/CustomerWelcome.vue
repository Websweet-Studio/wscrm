<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import CustomerPublicLayout from '@/layouts/CustomerPublicLayout.vue';
import { ArrowRight } from 'lucide-vue-next';
import { computed } from 'vue';
import { formatPrice, getHostingPlanFinalPrice } from '@/lib/utils';

/** Angka gaya Indonesia tanpa nol desimal berlebih: 1 GB, 1,5 GB. */
const num = (value: number | string) => String(value).replace(/\.0+$/, '').replace('.', ',');

const periodeLabel: Record<string, string> = {
    monthly: '/bulan',
    quarterly: '/3 bulan',
    semi_annually: '/6 bulan',
    annually: '/tahun',
};

interface DomainPrice {
    id: number;
    extension: string;
    selling_price: number;
    renewal_price_with_tax: number;
    is_active: boolean;
    promo_active?: boolean;
    effective_selling_price?: number;
    promo_savings?: number | null;
    promo_ends_at?: string | null;
}

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
    features: Record<string, any> | string[];
    is_active: boolean;
}

interface ServicePlan {
    id: number;
    name: string;
    category: string;
    description: string;
    price: number;
    features: Record<string, any>;
    is_active: boolean;
}

interface DemoKategori {
    name: string;
    slug: string;
    jumlah: number;
}

interface Props {
    domainPrices: DomainPrice[];
    hostingPlans: HostingPlan[];
    servicePlans: ServicePlan[];
    companyWhatsapp: string | null;
    demoKategori: DemoKategori[];
}

const props = defineProps<Props>();

/** Ekstensi selalu satu titik di depan (.com), apa pun format tersimpannya. */
const extLabel = (extension?: string) => {
    const ext = (extension || '').trim();
    if (ext === '') return '';
    return ext.startsWith('.') ? ext.toLowerCase() : `.${ext.toLowerCase()}`;
};

/** Harga jual sekarang: promo bila aktif, kalau tidak harga normal. */
const hargaDomain = (domain: DomainPrice): number =>
    domain.effective_selling_price ?? domain.selling_price;

/** Papan harga domain: urut termurah, maksimal delapan supaya papan tetap keterbacaan. */
const papanDomain = computed(() =>
    [...props.domainPrices].sort((a, b) => hargaDomain(a) - hargaDomain(b)).slice(0, 8),
);

/** Paket hosting: satu termurah per kelas penyimpanan, jadi baris tabel pembanding. */
const kelasHosting = computed(() => {
    const shared = props.hostingPlans.filter(
        (p) =>
            p.service_type === 'hosting' &&
            !p.use_bulk_pricing &&
            !p.plan_name.toLowerCase().includes('lite'),
    );
    return [1, 5, 10]
        .map((gb) => {
            const kelas = shared
                .filter((p) => parseFloat(String(p.storage_gb)) === gb)
                .sort((a, b) => getHostingPlanFinalPrice(a) - getHostingPlanFinalPrice(b));
            return kelas[0] ?? null;
        })
        .filter((p): p is HostingPlan => p !== null);
});

const fiturHosting = (plan: HostingPlan): string[] => {
    const f = plan.features;
    const dict: Record<string, any> =
        typeof f === 'string' ? JSON.parse(f || '{}') : (f ?? {});
    const email = dict.email_accounts;
    const db = dict.databases;
    const backup = dict.backup === 'daily' ? 'Backup harian' : dict.backup === 'weekly' ? 'Backup mingguan' : null;
    const bandwidth = /^unlimited$/i.test(String(plan.bandwidth)) ? 'Bandwidth tanpa batas' : `Bandwidth ${plan.bandwidth}`;
    return [
        `${num(plan.storage_gb)} GB SSD`,
        bandwidth,
        email === 'unlimited' ? 'Email tanpa batas' : `${email} akun email`,
        db === 'unlimited' ? 'Database tanpa batas' : `${db} database`,
        ...(dict.ssl_certificate ? ['SSL gratis'] : []),
        ...(backup ? [backup] : []),
    ].filter(Boolean) as string[];
};

const vpsTermurah = computed(() =>
    props.hostingPlans
        .filter((p) => p.service_type === 'vps')
        .sort((a, b) => getHostingPlanFinalPrice(a) - getHostingPlanFinalPrice(b))[0] ?? null,
);

const paketWeb = computed(() =>
    props.servicePlans.filter((s) => s.is_active && s.category === 'web_package'),
);

const promoTersedia = computed(() =>
    props.domainPrices.filter((d) => d.promo_active).length,
);

const waUrl = computed(() => {
    const nomor = (props.companyWhatsapp || '').replace(/^0/, '62').replace(/\D/g, '');
    const teks = encodeURIComponent('Halo WebSweetStudio! Saya ingin bertanya tentang layanan hosting.');
    return `https://wa.me/${nomor}?text=${teks}`;
});
</script>

<template>
    <CustomerPublicLayout title="Hosting, Domain & Website untuk Bisnis Anda">
        <!-- Hero: satu klaim, satu pasang aksi -->
        <section class="kcontainer pt-20 pb-14 sm:pt-24 md:pt-28 md:pb-20">
            <h1 class="mx-auto max-w-3xl text-center text-4xl sm:text-5xl md:text-[3.5rem] md:leading-[1.08]">
                Website bisnis Anda, dari domain sampai online.
            </h1>
            <p class="mx-auto mt-5 max-w-xl text-center text-base sm:text-lg" style="color: var(--kpub-muted);">
                Hosting dan domain dengan harga tertera di muka, plus jasa pembuatan website kalau Anda tidak mau repot. Semua bisa dipesan lewat WhatsApp.
            </p>
            <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
                <Link href="/hosting" class="kbtn kbtn-primary w-full sm:w-auto">
                    Lihat paket hosting
                    <ArrowRight class="h-4 w-4" />
                </Link>
                <Link href="/domains" class="kbtn kbtn-outline w-full sm:w-auto">Cek harga domain</Link>
            </div>
        </section>

        <!-- Papan harga domain: data nyata dari DB -->
        <section v-if="papanDomain.length" class="kcontainer pb-20 md:pb-24">
            <div class="kcard overflow-hidden">
                <div class="flex flex-wrap items-baseline justify-between gap-2 border-b px-6 py-5" style="border-color: var(--kpub-line);">
                    <h2 class="text-xl sm:text-2xl">Papan harga domain</h2>
                    <p class="knum text-sm" style="color: var(--kpub-muted);">per tahun, tanpa biaya tersembunyi</p>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4">
                    <div
                        v-for="d in papanDomain"
                        :key="d.id"
                        class="border-r border-b px-6 py-5"
                        style="border-color: var(--kpub-line);"
                    >
                        <div class="kdisplay text-xl">{{ extLabel(d.extension) }}</div>
                        <div class="knum mt-1.5 flex flex-wrap items-baseline gap-x-2 gap-y-0.5 text-sm">
                            <span v-if="d.promo_active" class="text-xs line-through" style="color: var(--kpub-faint);">{{ formatPrice(d.selling_price) }}</span>
                            <span class="font-medium" :style="d.promo_active ? 'color: var(--kpub-green);' : ''">{{ formatPrice(hargaDomain(d)) }}</span>
                        </div>
                    </div>
                </div>
                <div class="border-t px-6 py-4" style="border-color: var(--kpub-line);">
                    <Link href="/domains" class="klink text-sm">Semua ekstensi &amp; cek ketersediaan</Link>
                </div>
            </div>
        </section>

        <!-- Hosting: tiga paket sebaris, garis rambut, harga di depan -->
        <section v-if="kelasHosting.length" class="border-y py-16 md:py-24" style="border-color: var(--kpub-line); background-color: var(--kpub-surface);">
            <div class="kcontainer">
                <div class="mb-10 flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <h2 class="text-2xl sm:text-3xl">Paket hosting, per tahun</h2>
                        <p class="mt-2 max-w-xl text-sm" style="color: var(--kpub-muted);">Termasuk SSL dan backup. Naik kelas kapan saja tanpa pindah server.</p>
                    </div>
                    <Link href="/hosting" class="klink text-sm">Semua paket hosting &amp; VPS</Link>
                </div>

                <div class="grid gap-4 md:grid-cols-3 md:items-start">
                    <div v-for="plan in kelasHosting" :key="plan.id" class="kcard flex flex-col p-6">
                        <h3 class="text-lg">{{ num(plan.storage_gb) }} GB</h3>
                        <p class="text-sm" style="color: var(--kpub-muted);">paket {{ plan.plan_name }}</p>
                        <div class="knum mt-4 flex items-baseline gap-2">
                            <span class="text-3xl font-medium tracking-tight">{{ formatPrice(getHostingPlanFinalPrice(plan)) }}</span>
                            <span class="text-sm" style="color: var(--kpub-muted);">/tahun</span>
                        </div>
                        <p v-if="!plan.use_bulk_pricing && plan.discount_percent > 0" class="knum mt-1 text-xs" style="color: var(--kpub-green);">
                            hemat {{ plan.discount_percent }}% dari {{ formatPrice(plan.selling_price) }}
                        </p>
                        <ul class="mt-6 flex-1 space-y-2 border-t pt-5 text-sm" style="border-color: var(--kpub-line); color: var(--kpub-muted);">
                            <li v-for="f in fiturHosting(plan)" :key="f">{{ f }}</li>
                        </ul>
                        <Link href="/hosting" class="kbtn kbtn-outline mt-6 w-full">Pilih {{ num(plan.storage_gb) }} GB</Link>
                    </div>
                </div>

                <!-- VPS sebagai baris lanjutan, bukan kartu keempat -->
                <div v-if="vpsTermurah" class="kcard mt-4 flex flex-col items-start justify-between gap-4 p-6 sm:flex-row sm:items-center">
                    <div>
                        <h3 class="text-lg">Butuh server sendiri? VPS mulai <span class="knum">{{ formatPrice(getHostingPlanFinalPrice(vpsTermurah)) }}</span>{{ periodeLabel[vpsTermurah.billing_period] || '/bulan' }}</h3>
                        <p class="mt-1 text-sm" style="color: var(--kpub-muted);">
                            {{ vpsTermurah.plan_name }}: {{ num(vpsTermurah.cpu_cores) }} vCPU, {{ num(vpsTermurah.ram_gb) }} GB RAM, {{ num(vpsTermurah.storage_gb) }} GB SSD.
                        </p>
                    </div>
                    <Link href="/hosting" class="kbtn kbtn-outline shrink-0">Bandingkan VPS</Link>
                </div>
            </div>
        </section>

        <!-- Jasa pembuatan website: satu daftar harga, bukan grid kartu -->
        <section v-if="paketWeb.length" class="kcontainer py-16 md:py-24">
            <div class="grid gap-10 lg:grid-cols-[0.9fr_1.1fr] lg:gap-16">
                <div>
                    <h2 class="text-2xl sm:text-3xl">Tidak mau ribet? Kami buatkan.</h2>
                    <p class="mt-3 max-w-md text-sm leading-relaxed" style="color: var(--kpub-muted);">
                        Anda kasih materi, kami kerjakan sampai online. Hosting dan domain bisa sekalian di sini supaya semua satu tagihan.
                    </p>
                    <a :href="waUrl" target="_blank" rel="noopener noreferrer" class="klink mt-5 inline-block text-sm">Konsultasi gratis lewat WhatsApp</a>
                </div>
                <ul class="border-t" style="border-color: var(--kpub-line);">
                    <li v-for="s in paketWeb" :key="s.id" class="flex items-start justify-between gap-6 border-b py-4" style="border-color: var(--kpub-line);">
                        <div>
                            <div class="font-medium">{{ s.name }}</div>
                            <div v-if="s.description" class="mt-0.5 text-sm" style="color: var(--kpub-muted);">{{ s.description }}</div>
                        </div>
                        <div class="knum shrink-0 text-right font-medium">{{ formatPrice(s.price) }}</div>
                    </li>
                </ul>
            </div>
        </section>

        <!-- Bukti kerja: jumlah kategori nyata dari database -->
        <section v-if="demoKategori.length" class="border-y py-16 md:py-24" style="border-color: var(--kpub-line); background-color: var(--kpub-surface);">
            <div class="kcontainer">
                <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <h2 class="text-2xl sm:text-3xl">Kerja kami bisa dilihat</h2>
                        <p class="mt-2 max-w-xl text-sm" style="color: var(--kpub-muted);">Contoh website yang sudah jadi. Klik, buka, dan nilai sendiri.</p>
                    </div>
                    <Link href="/demo-web" class="klink text-sm">Lihat semua demo</Link>
                </div>
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <Link
                        v-for="k in demoKategori"
                        :key="k.slug"
                        :href="`/demo-web?category=${k.slug}`"
                        class="kcard group flex flex-col justify-between gap-6 p-5 transition-colors"
                    >
                        <div class="kdisplay text-xl">{{ k.name }}</div>
                        <div class="knum flex items-center justify-between text-sm" style="color: var(--kpub-muted);">
                            <span>{{ k.jumlah }} contoh</span>
                            <ArrowRight class="h-4 w-4 transition-transform group-hover:translate-x-0.5" style="color: var(--kpub-green);" />
                        </div>
                    </Link>
                </div>
            </div>
        </section>

        <!-- CTA penutup: satu panel gelap -->
        <section class="kcontainer py-16 md:py-24">
            <div class="overflow-hidden rounded-[25px] px-6 py-12 sm:px-12 sm:py-16" style="background-color: var(--kpub-deep);">
                <div class="max-w-xl">
                    <h2 class="text-3xl sm:text-4xl" style="color: #ffffff;">Mulai dari domain, atau tanya dulu juga boleh.</h2>
                    <p class="mt-4 text-sm sm:text-base" style="color: var(--kpub-deep-ink);">
                        <template v-if="promoTersedia > 0">{{ promoTersedia }} ekstensi domain sedang promo. </template>
                        Cek ketersediaan nama yang Anda mau, atau chat kami kalau masih bingung pilih yang mana.
                    </p>
                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <Link href="/domains" class="kbtn" style="background-color: #ffffff; color: var(--kpub-deep);">Cari domain sekarang</Link>
                        <a :href="waUrl" target="_blank" rel="noopener noreferrer" class="kbtn" style="border: 1px solid rgba(255, 255, 255, 0.28); color: #ffffff;">Chat WhatsApp</a>
                    </div>
                </div>
            </div>
        </section>
    </CustomerPublicLayout>
</template>
