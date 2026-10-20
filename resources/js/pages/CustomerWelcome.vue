<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import CustomerPublicLayout from '@/layouts/CustomerPublicLayout.vue';
import { computed } from 'vue';
import { formatPrice, getHostingPlanFinalPrice } from '@/lib/utils';

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

const cariDomain = (ext: string): DomainPrice | undefined =>
    props.domainPrices.find((d) => extLabel(d.extension) === ext);

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
    return [
        `${String(plan.storage_gb).replace('.', ',')} GB SSD`,
        `Bandwidth ${plan.bandwidth}`,
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
    <CustomerPublicLayout title="WebSweetStudio — Hosting, Domain & Website untuk Bisnis Anda">
        <!-- Hero: klaim pendek, lalu papan harga domain sebagai pembuktian -->
        <section class="px-4 pt-16 pb-14 sm:px-6 sm:pt-24">
            <div class="mx-auto max-w-6xl">
                <h1 class="kdisplay max-w-3xl text-4xl sm:text-5xl md:text-6xl">
                    Website bisnis Anda, dari domain sampai online.
                </h1>
                <p class="mt-5 max-w-2xl text-base sm:text-lg" style="color: var(--kpub-muted); line-height: 1.65;">
                    Hosting dan domain dengan harga tertera di muka, plus jasa pembuatan website kalau Anda tidak mau repot. Semua bisa dipesan lewat WhatsApp, dibantu sampai jalan.
                </p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <Link href="/hosting" class="kbtn kbtn-primary rounded-lg px-6 py-3.5 text-base">
                        Lihat paket hosting &amp; VPS
                    </Link>
                    <Link href="/domains" class="kbtn kbtn-ghost rounded-lg px-6 py-3.5 text-base">
                        Cek harga domain
                    </Link>
                </div>
            </div>

            <!-- Papan harga domain: elemen khas halaman ini, data nyata dari DB -->
            <div v-if="papanDomain.length" class="mx-auto mt-14 max-w-6xl sm:px-6">
                <div class="overflow-hidden rounded-xl" style="background-color: var(--kpub-deep); color: var(--kpub-deep-ink);">
                    <div class="flex flex-wrap items-baseline justify-between gap-2 px-6 pt-6">
                        <h2 class="text-lg font-semibold tracking-tight sm:text-xl">Papan harga domain</h2>
                        <p class="text-sm" style="color: var(--kpub-deep-muted);">per tahun, terpasang di sini langsung dari daftar kami</p>
                    </div>
                    <div class="grid grid-cols-2 gap-x-6 px-6 pt-5 sm:grid-cols-4">
                        <div
                            v-for="d in papanDomain"
                            :key="d.id"
                            class="border-t py-4"
                            style="border-color: var(--kpub-deep-line);"
                        >
                            <div class="kdisplay text-xl sm:text-2xl">{{ extLabel(d.extension) }}</div>
                            <div class="knum mt-1 text-sm">
                                <span v-if="d.promo_active" class="mr-1.5 line-through" style="color: var(--kpub-deep-muted);">{{ formatPrice(d.selling_price) }}</span>
                                <span :style="d.promo_active ? 'color: var(--kpub-highlight); font-weight: 600;' : 'font-weight: 600;'">{{ formatPrice(hargaDomain(d)) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="px-6 pb-5">
                        <Link href="/domains" class="klink klink-on-deep text-sm">
                            Semua ekstensi &amp; cek ketersediaan
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Hosting: tabel pembanding, bukan tiga kartu kembar -->
        <section v-if="kelasHosting.length" class="border-y px-4 py-16 sm:px-6" style="background-color: var(--kpub-card); border-color: var(--kpub-line);">
            <div class="mx-auto max-w-6xl">
                <div class="mb-10 flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <h2 class="kdisplay text-2xl sm:text-3xl">Paket hosting, per tahun</h2>
                        <p class="mt-2 max-w-xl text-sm" style="color: var(--kpub-muted);">Termasuk SSL dan backup. Naik kelas kapan saja tanpa pindah server.</p>
                    </div>
                    <Link href="/hosting" class="klink text-sm">Semua paket hosting &amp; VPS</Link>
                </div>

                <div class="grid gap-4 md:grid-cols-3 md:items-start">
                    <div
                        v-for="(plan, i) in kelasHosting"
                        :key="plan.id"
                        class="flex flex-col rounded-xl"
                        :style="i === 1
                            ? 'background-color: var(--kpub-deep); color: var(--kpub-deep-ink); padding: 1.5rem;'
                            : 'border: 1px solid var(--kpub-line); padding: 1.5rem;'"
                    >
                        <div class="flex items-baseline justify-between gap-3">
                            <h3 class="text-lg font-semibold tracking-tight">{{ plan.plan_name }} {{ String(plan.storage_gb).replace('.', ',') }} GB</h3>
                            <span v-if="i === 1" class="rounded-full px-2.5 py-0.5 text-xs font-semibold" style="background-color: var(--kpub-deep-line);">paling sering dipilih</span>
                            <span v-else-if="!plan.use_bulk_pricing && plan.discount_percent > 0" class="text-xs font-semibold" style="color: var(--kpub-primary);">hemat {{ plan.discount_percent }}%</span>
                        </div>
                        <div class="knum mt-4 flex items-baseline gap-2">
                            <span v-if="!plan.use_bulk_pricing && plan.discount_percent > 0" class="text-sm line-through" :style="i === 1 ? 'color: var(--kpub-deep-muted);' : 'color: var(--kpub-muted);'">{{ formatPrice(plan.selling_price) }}</span>
                            <span class="text-2xl font-bold">{{ formatPrice(getHostingPlanFinalPrice(plan)) }}</span>
                            <span class="text-sm" :style="i === 1 ? 'color: var(--kpub-deep-muted);' : 'color: var(--kpub-muted);'">/tahun</span>
                        </div>
                        <ul class="mt-5 flex-1 space-y-2 text-sm" :style="i === 1 ? 'color: var(--kpub-deep-text);' : 'color: var(--kpub-muted);'">
                            <li v-for="f in fiturHosting(plan)" :key="f" class="flex items-start gap-2">
                                <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full" :style="i === 1 ? 'background-color: var(--kpub-highlight);' : 'background-color: var(--kpub-primary);'" />
                                <span>{{ f }}</span>
                            </li>
                        </ul>
                        <div class="mt-6">
                            <Link
                                href="/hosting"
                                class="kbtn w-full rounded-lg px-4 py-2.5 text-sm"
                                :class="i === 1 ? 'kbtn-primary' : 'kbtn-ghost'"
                            >
                                Pilih {{ plan.plan_name }}
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- VPS sebagai baris lanjutan, bukan kartu keempat -->
                <div v-if="vpsTermurah" class="mt-8 flex flex-col items-start justify-between gap-4 rounded-xl border p-5 sm:flex-row sm:items-center" style="border-color: var(--kpub-line);">
                    <div>
                        <h3 class="font-semibold tracking-tight">Butuh server sendiri? VPS mulai <span class="knum">{{ formatPrice(getHostingPlanFinalPrice(vpsTermurah)) }}</span>/tahun</h3>
                        <p class="mt-1 text-sm" style="color: var(--kpub-muted);">{{ vpsTermurah.plan_name }}: {{ String(vpsTermurah.cpu_cores).replace('.', ',') }} vCPU, {{ String(vpsTermurah.ram_gb).replace('.', ',') }} GB RAM, {{ String(vpsTermurah.storage_gb).replace('.', ',') }} GB SSD. Ada 10 konfigurasi lain.</p>
                    </div>
                    <Link href="/hosting" class="kbtn kbtn-ghost shrink-0 rounded-lg px-4 py-2.5 text-sm">
                        Bandingkan VPS
                    </Link>
                </div>
            </div>
        </section>

        <!-- Jasa pembuatan website: satu daftar harga, bukan grid kartu -->
        <section v-if="paketWeb.length" class="px-4 py-16 sm:px-6">
            <div class="mx-auto grid max-w-6xl gap-10 lg:grid-cols-[1fr_1.2fr] lg:gap-16">
                <div>
                    <h2 class="kdisplay text-2xl sm:text-3xl">Tidak mau ribet? Kami buatkan.</h2>
                    <p class="mt-3 text-sm leading-relaxed" style="color: var(--kpub-muted);">
                        Anda kasih materi, kami kerjakan sampai online. Hosting dan domain bisa sekalian di sini supaya semua satu tagihan.
                    </p>
                    <a :href="waUrl" target="_blank" rel="noopener noreferrer" class="klink mt-6 inline-block text-sm">
                        Konsultasi gratis lewat WhatsApp
                    </a>
                </div>
                <ul class="divide-y" style="border-color: var(--kpub-line);">
                    <li v-for="s in paketWeb" :key="s.id" class="flex items-start justify-between gap-6 py-4">
                        <div>
                            <div class="font-semibold tracking-tight">{{ s.name }}</div>
                            <div v-if="s.description" class="mt-0.5 text-sm" style="color: var(--kpub-muted);">{{ s.description }}</div>
                        </div>
                        <div class="knum shrink-0 text-right font-semibold">{{ formatPrice(s.price) }}</div>
                    </li>
                </ul>
            </div>
        </section>

        <!-- Bukti kerja: jumlah kategori nyata dari database -->
        <section v-if="demoKategori.length" class="border-y" style="background-color: var(--kpub-deep);">
            <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
                <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <h2 class="kdisplay text-2xl sm:text-3xl" style="color: var(--kpub-deep-ink);">Kerja kami bisa dilihat</h2>
                        <p class="mt-2 max-w-xl text-sm" style="color: var(--kpub-deep-muted);">Contoh website yang sudah jadi. Klik, buka, dan nilai sendiri.</p>
                    </div>
                    <Link href="/demo-web" class="klink klink-on-deep text-sm">Lihat semua demo</Link>
                </div>
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <Link
                        v-for="k in demoKategori"
                        :key="k.slug"
                        :href="`/demo-web?category=${k.slug}`"
                        class="group rounded-lg border p-4 transition-colors hover:bg-white/5"
                        style="border-color: var(--kpub-deep-line);"
                    >
                        <div class="kdisplay text-2xl" style="color: var(--kpub-deep-ink);">{{ k.name }}</div>
                        <div class="knum mt-1 text-sm" style="color: var(--kpub-deep-muted);">{{ k.jumlah }} contoh siap dilihat</div>
                    </Link>
                </div>
            </div>
        </section>

        <!-- CTA penutup -->
        <section class="px-4 py-16 sm:px-6">
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="kdisplay text-2xl sm:text-3xl">Mulai dari domain, atau tanya dulu juga boleh.</h2>
                <p class="mx-auto mt-3 max-w-lg text-sm" style="color: var(--kpub-muted);">
                    <template v-if="promoTersedia > 0">{{ promoTersedia }} ekstensi domain sedang promo. </template>
                    Cek ketersediaan nama yang Anda mau, atau chat kami kalau masih bingung pilih yang mana.
                </p>
                <div class="mt-7 flex flex-col justify-center gap-3 sm:flex-row">
                    <Link href="/domains" class="kbtn kbtn-primary rounded-lg px-6 py-3 text-base">
                        Cari domain sekarang
                    </Link>
                    <a :href="waUrl" target="_blank" rel="noopener noreferrer" class="kbtn kbtn-ghost rounded-lg px-6 py-3 text-base">
                        Chat WhatsApp
                    </a>
                </div>
            </div>
        </section>
    </CustomerPublicLayout>
</template>
