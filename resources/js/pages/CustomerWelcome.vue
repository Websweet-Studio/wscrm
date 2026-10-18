<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import CustomerPublicLayout from '@/layouts/CustomerPublicLayout.vue';
import { Link } from '@inertiajs/vue3';
import { Check } from 'lucide-vue-next';
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

interface Props {
    domainPrices: DomainPrice[];
    hostingPlans: HostingPlan[];
    servicePlans: ServicePlan[];
    companyWhatsapp: string | null;
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

/** Domain unggulan untuk strip harga hero — hanya yang benar-benar ada di DB. */
const domainUnggulan = computed(() =>
    ['.my.id', '.web.id', '.com', '.id']
        .map((ext) => cariDomain(ext))
        .filter((d): d is DomainPrice => d !== undefined),
);

/** Paket hosting unggulan: satu per kelas penyimpanan, harga final termurah di kelasnya. */
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
        <!-- Hero: harga nyata sebagai pembuka, bukan klaim -->
        <section class="mx-auto max-w-6xl px-4 pt-16 pb-14 sm:px-6 sm:pt-24">
            <div class="max-w-3xl">
                <h1 class="text-4xl leading-tight font-medium sm:text-5xl md:text-6xl" style="color: #141413; line-height: 1.08; font-family: Georgia, serif;">
                    Website bisnis Anda, dari domain sampai online.
                </h1>
                <p class="mt-5 max-w-2xl text-base sm:text-lg" style="color: #5e5d59; line-height: 1.65;">
                    Hosting dan domain dengan harga tertera di muka, plus jasa pembuatan website kalau Anda tidak mau repot. Semua bisa dipesan lewat WhatsApp, dibantu sampai jalan.
                </p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <Button asChild class="px-6 py-4 text-base" style="background-color: var(--primary); color: var(--primary-foreground); border-radius: 10px;">
                        <Link href="/hosting">Lihat Paket Hosting &amp; VPS</Link>
                    </Button>
                    <Button asChild variant="outline" class="px-6 py-4 text-base" style="background-color: var(--primary-foreground); color: #4d4c48; border: 1px solid #e8e6dc; border-radius: 10px;">
                        <Link href="/domains">Cek Harga Domain</Link>
                    </Button>
                </div>

                <!-- Strip harga domain nyata dari database -->
                <div v-if="domainUnggulan.length" class="mt-12 border-t pt-6" style="border-color: #e8e6dc;">
                    <p class="mb-4 text-sm" style="color: #87867f;">Harga domain per tahun, langsung dari daftar kami:</p>
                    <div class="flex flex-wrap gap-x-10 gap-y-4">
                        <div v-for="d in domainUnggulan" :key="d.id">
                            <div class="font-medium" style="color: #141413; font-family: Georgia, serif;">{{ extLabel(d.extension) }}</div>
                            <div class="text-sm">
                                <span v-if="d.promo_active" class="mr-1.5 text-xs line-through" style="color: #91918c;">{{ formatPrice(d.selling_price) }}</span>
                                <span :style="d.promo_active ? 'color: #c96442; font-weight: 600;' : 'color: #141413; font-weight: 600;'">{{ formatPrice(hargaDomain(d)) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Hosting: tiga paket nyata, kolom beda bobot sesuai kelasnya -->
        <section v-if="kelasHosting.length" class="border-y" style="background-color: #faf9f5; border-color: #f0eee6;">
            <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
                <div class="mb-10 flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-medium sm:text-3xl" style="color: #141413; font-family: Georgia, serif;">Paket hosting, per tahun</h2>
                        <p class="mt-2 max-w-xl text-sm" style="color: #5e5d59;">Termasuk SSL dan backup. Naik kelas kapan saja tanpa pindah server.</p>
                    </div>
                    <Link href="/hosting" class="text-sm font-medium underline underline-offset-4" style="color: #a04a2e;">Semua paket hosting &amp; VPS</Link>
                </div>

                <div class="grid gap-4 md:grid-cols-3 md:items-start">
                    <Card
                        v-for="(plan, i) in kelasHosting"
                        :key="plan.id"
                        class="flex flex-col"
                        :style="i === 1
                            ? 'background-color: #ffffff; border: 1px solid #e8cfc2; border-radius: 12px; box-shadow: rgba(0,0,0,0.06) 0 4px 20px; margin-top: -12px; padding-top: 12px;'
                            : 'background-color: #ffffff; border: 1px solid #f0eee6; border-radius: 12px;'"
                    >
                        <CardContent class="flex flex-1 flex-col p-6">
                            <div class="flex items-baseline justify-between">
                                <h3 class="text-lg font-medium" style="color: #141413; font-family: Georgia, serif;">{{ plan.plan_name }} {{ String(plan.storage_gb).replace('.', ',') }} GB</h3>
                                <span v-if="!plan.use_bulk_pricing && plan.discount_percent > 0" class="text-xs font-medium" style="color: #c96442;">hemat {{ plan.discount_percent }}%</span>
                            </div>
                            <div class="mt-4 flex items-baseline gap-2">
                                <span v-if="!plan.use_bulk_pricing && plan.discount_percent > 0" class="text-sm line-through" style="color: #91918c;">{{ formatPrice(plan.selling_price) }}</span>
                                <span class="text-2xl font-bold" style="color: #141413;">{{ formatPrice(getHostingPlanFinalPrice(plan)) }}</span>
                                <span class="text-sm" style="color: #87867f;">/tahun</span>
                            </div>
                            <ul class="mt-5 space-y-2 text-sm" style="color: #4d4c48;">
                                <li v-for="f in fiturHosting(plan)" :key="f" class="flex items-start gap-2">
                                    <Check class="mt-0.5 h-4 w-4 shrink-0" style="color: #c96442;" />
                                    <span>{{ f }}</span>
                                </li>
                            </ul>
                            <div class="mt-6 pt-2">
                                <Button asChild class="w-full" :variant="i === 1 ? 'default' : 'outline'"
                                    :style="i === 1
                                        ? 'background-color: var(--primary); color: var(--primary-foreground); border-radius: 8px;'
                                        : 'background-color: var(--primary-foreground); color: #4d4c48; border: 1px solid #e8e6dc; border-radius: 8px;'">
                                    <Link href="/hosting">Pilih {{ plan.plan_name }}</Link>
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- VPS nyata sebagai baris lanjutan, bukan kartu keempat yang sama -->
                <div v-if="vpsTermurah" class="mt-8 flex flex-col items-start justify-between gap-4 rounded-xl border p-5 sm:flex-row sm:items-center" style="background-color: #f5f4ed; border-color: #e8e6dc;">
                    <div>
                        <h3 class="font-medium" style="color: #141413;">Butuh server sendiri? VPS mulai {{ formatPrice(getHostingPlanFinalPrice(vpsTermurah)) }}/tahun</h3>
                        <p class="mt-1 text-sm" style="color: #5e5d59;">{{ vpsTermurah.plan_name }}: {{ String(vpsTermurah.cpu_cores).replace('.', ',') }} vCPU, {{ String(vpsTermurah.ram_gb).replace('.', ',') }} GB RAM, {{ String(vpsTermurah.storage_gb).replace('.', ',') }} GB SSD. Ada 10 konfigurasi lain.</p>
                    </div>
                    <Button asChild variant="outline" class="shrink-0" style="background-color: var(--primary-foreground); color: #4d4c48; border: 1px solid #e8e6dc; border-radius: 8px;">
                        <Link href="/hosting">Bandingkan VPS</Link>
                    </Button>
                </div>
            </div>
        </section>

        <!-- Jasa pembuatan website: harga nyata, satu daftar bukan grid kartu -->
        <section v-if="paketWeb.length" class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
            <div class="grid gap-10 lg:grid-cols-[1fr_1.2fr] lg:gap-16">
                <div>
                    <h2 class="text-2xl font-medium sm:text-3xl" style="color: #141413; font-family: Georgia, serif;">Tidak mau ribet? Kami buatkan.</h2>
                    <p class="mt-3 text-sm leading-relaxed" style="color: #5e5d59;">
                        Anda kasih materi, kami kerjakan sampai online. Hosting dan domain bisa sekalian di sini supaya semua satu tagihan.
                    </p>
                    <a :href="waUrl" target="_blank" rel="noopener noreferrer" class="mt-6 inline-block text-sm font-medium underline underline-offset-4" style="color: #a04a2e;">
                        Konsultasi gratis lewat WhatsApp
                    </a>
                </div>
                <ul class="divide-y" style="border-color: #f0eee6;">
                    <li v-for="s in paketWeb" :key="s.id" class="flex items-start justify-between gap-6 py-4">
                        <div>
                            <div class="font-medium" style="color: #141413;">{{ s.name }}</div>
                            <div v-if="s.description" class="mt-0.5 text-sm" style="color: #5e5d59;">{{ s.description }}</div>
                        </div>
                        <div class="shrink-0 text-right">
                            <div class="font-semibold" style="color: #141413;">{{ formatPrice(s.price) }}</div>
                        </div>
                    </li>
                </ul>
            </div>
        </section>

        <!-- Bukti kerja: demo nyata yang bisa diklik, bukan logo bar -->
        <section class="border-y" style="background-color: #141413; border-color: #30302e;">
            <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
                <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-medium sm:text-3xl" style="color: #faf9f5; font-family: Georgia, serif;">Kerja kami bisa dilihat</h2>
                        <p class="mt-2 max-w-xl text-sm" style="color: #b0aea5;">Contoh website yang sudah jadi. Klik, buka, dan nilai sendiri.</p>
                    </div>
                    <Link href="/demo-web" class="text-sm font-medium underline underline-offset-4" style="color: #a04a2e;">Lihat semua demo</Link>
                </div>
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <a href="/demo-web" class="group rounded-lg border p-4 transition-colors hover:bg-[#1e1e1c]" style="border-color: #30302e;">
                        <div class="text-2xl font-medium" style="color: #faf9f5; font-family: Georgia, serif;">Profil Perusahaan</div>
                        <div class="mt-1 text-sm" style="color: #b0aea5;">22 contoh siap dilihat</div>
                    </a>
                    <a href="/demo-web" class="group rounded-lg border p-4 transition-colors hover:bg-[#1e1e1c]" style="border-color: #30302e;">
                        <div class="text-2xl font-medium" style="color: #faf9f5; font-family: Georgia, serif;">Sekolah &amp; Edukasi</div>
                        <div class="mt-1 text-sm" style="color: #b0aea5;">17 contoh siap dilihat</div>
                    </a>
                    <a href="/demo-web" class="group rounded-lg border p-4 transition-colors hover:bg-[#1e1e1c]" style="border-color: #30302e;">
                        <div class="text-2xl font-medium" style="color: #faf9f5; font-family: Georgia, serif;">Toko Online</div>
                        <div class="mt-1 text-sm" style="color: #b0aea5;">13 contoh siap dilihat</div>
                    </a>
                    <a href="/demo-web" class="group rounded-lg border p-4 transition-colors hover:bg-[#1e1e1c]" style="border-color: #30302e;">
                        <div class="text-2xl font-medium" style="color: #faf9f5; font-family: Georgia, serif;">Dealer</div>
                        <div class="mt-1 text-sm" style="color: #b0aea5;">12 contoh siap dilihat</div>
                    </a>
                </div>
            </div>
        </section>

        <!-- CTA penutup: satu aksi, jelas -->
        <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="text-2xl font-medium sm:text-3xl" style="color: #141413; font-family: Georgia, serif;">Mulai dari domain, atau tanya dulu juga boleh.</h2>
                <p class="mx-auto mt-3 max-w-lg text-sm" style="color: #5e5d59;">
                    <template v-if="promoTersedia > 0">{{ promoTersedia }} ekstensi domain sedang promo.</template>
                    Cek ketersediaan nama yang Anda mau, atau chat kami kalau masih bingung pilih yang mana.
                </p>
                <div class="mt-7 flex flex-col justify-center gap-3 sm:flex-row">
                    <Button asChild class="px-6 py-3" style="background-color: var(--primary); color: var(--primary-foreground); border-radius: 10px;">
                        <Link href="/domains">Cari Domain Sekarang</Link>
                    </Button>
                    <a :href="waUrl" target="_blank" rel="noopener noreferrer">
                        <Button variant="outline" class="w-full px-6 py-3 sm:w-auto" style="background-color: var(--primary-foreground); color: #4d4c48; border: 1px solid #e8e6dc; border-radius: 10px;">
                            Chat WhatsApp
                        </Button>
                    </a>
                </div>
            </div>
        </section>
    </CustomerPublicLayout>
</template>
