<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, DollarSign, Globe, Settings } from 'lucide-vue-next';
import { computed } from 'vue';

interface DomainPrice {
    id: number;
    extension: string;
    base_cost: number;
    renewal_cost: number;
    selling_price: number;
    renewal_price_with_tax: number;
    is_active: boolean;
    created_at: string;
    updated_at: string;
    promo_active?: boolean;
    effective_selling_price?: number;
    promo_price?: number | null;
    promo_base_cost?: number | null;
    promo_selling_price?: number | null;
    promo_savings?: number | null;
    promo_days_left?: number | null;
    promo_starts_at?: string | null;
    promo_ends_at?: string | null;
    promo_note?: string | null;
}

interface Props {
    domainPrice: DomainPrice;
}

const props = defineProps<Props>();

/** Ekstensi selalu ditampilkan dengan satu titik di depan (.com), apa pun format tersimpannya. */
const extLabel = (extension?: string) => {
    const ext = (extension || '').trim();
    if (ext === '') return '';

    return ext.startsWith('.') ? ext : `.${ext}`;
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Domain Prices', href: '/admin/domain-prices' },
    { title: extLabel(props.domainPrice.extension), href: `/admin/domain-prices/${props.domainPrice.id}` },
];

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(price);
};

const formatDate = (date: string) => {
    return new Intl.DateTimeFormat('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(new Date(date));
};

/** Status promo registrasi dari RDash (null bila ekstensi ini tidak punya promo). */
const promoStatus = computed(() => {
    if (props.domainPrice.promo_selling_price === null || props.domainPrice.promo_selling_price === undefined) return null;
    if (props.domainPrice.promo_active) return { label: 'AKTIF', variant: 'default' as const };

    const startsAt = props.domainPrice.promo_starts_at ? new Date(props.domainPrice.promo_starts_at).getTime() : null;
    if (startsAt !== null && startsAt > Date.now()) return { label: 'Akan Datang', variant: 'secondary' as const };

    return { label: 'Berakhir', variant: 'secondary' as const };
});

/** Margin promo = harga jual promo - modal promo (null bila data tidak lengkap). */
const promoMargin = computed(() => {
    const selling = props.domainPrice.promo_selling_price;
    const cost = props.domainPrice.promo_base_cost;
    if (selling === null || selling === undefined || cost === null || cost === undefined) return null;

    return Number(selling) - Number(cost);
});
</script>

<template>
    <Head :title="`Domain Price - ${extLabel(domainPrice.extension)}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <Link href="/admin/domain-prices">
                        <Button variant="outline" size="sm">
                            <ArrowLeft class="mr-2 h-4 w-4" />
                            Back to Domain Prices
                        </Button>
                    </Link>
                    <div>
                        <h1 class="flex items-center text-3xl font-bold tracking-tight">
                            <Globe class="mr-3 h-8 w-8 text-blue-500" />
                            {{ extLabel(domainPrice.extension) }}
                        </h1>
                        <p class="text-muted-foreground">Domain extension pricing details</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <Badge :variant="domainPrice.is_active ? 'default' : 'secondary'" class="px-3 py-1 text-sm">
                        {{ domainPrice.is_active ? 'Active' : 'Inactive' }}
                    </Badge>
                    <Link :href="`/admin/domain-prices/${domainPrice.id}/edit`">
                        <Button>
                            <Settings class="mr-2 h-4 w-4" />
                            Edit Price
                        </Button>
                    </Link>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <!-- Pricing Information -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center">
                            <DollarSign class="mr-2 h-5 w-5" />
                            Pricing Information
                        </CardTitle>
                        <CardDescription> Current pricing structure for {{ extLabel(domainPrice.extension) }} domain </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid grid-cols-1 gap-4">
                            <div class="flex items-center justify-between rounded-lg bg-muted p-4">
                                <div>
                                    <h3 class="text-lg font-semibold">Base Cost</h3>
                                    <p class="text-sm text-muted-foreground">Internal cost for domain</p>
                                </div>
                                <div class="text-right">
                                    <div class="text-2xl font-bold text-red-600">{{ formatPrice(domainPrice.base_cost) }}</div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between rounded-lg bg-muted p-4">
                                <div>
                                    <h3 class="text-lg font-semibold">Renewal Cost</h3>
                                    <p class="text-sm text-muted-foreground">Internal renewal cost</p>
                                </div>
                                <div class="text-right">
                                    <div class="text-2xl font-bold text-orange-600">{{ formatPrice(domainPrice.renewal_cost) }}</div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between rounded-lg bg-muted p-4">
                                <div>
                                    <h3 class="text-lg font-semibold">Selling Price</h3>
                                    <p class="text-sm text-muted-foreground">Customer selling price</p>
                                </div>
                                <div class="text-right">
                                    <div class="text-2xl font-bold text-green-600">{{ formatPrice(domainPrice.selling_price) }}</div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between rounded-lg bg-muted p-4">
                                <div>
                                    <h3 class="text-lg font-semibold">Renewal w/ Tax</h3>
                                    <p class="text-sm text-muted-foreground">Renewal price including tax</p>
                                </div>
                                <div class="text-right">
                                    <div class="text-2xl font-bold text-blue-600">{{ formatPrice(domainPrice.renewal_price_with_tax) }}</div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Domain Information -->
                <Card>
                    <CardHeader>
                        <CardTitle>Domain Information</CardTitle>
                        <CardDescription> General information about this domain extension </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="font-medium">Extension:</span>
                                <span class="font-mono text-lg">{{ extLabel(domainPrice.extension) }}</span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="font-medium">Status:</span>
                                <Badge :variant="domainPrice.is_active ? 'default' : 'secondary'">
                                    {{ domainPrice.is_active ? 'Active' : 'Inactive' }}
                                </Badge>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="font-medium">ID:</span>
                                <span class="font-mono">#{{ domainPrice.id }}</span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="font-medium">Created:</span>
                                <span class="text-sm">{{ formatDate(domainPrice.created_at) }}</span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="font-medium">Last Updated:</span>
                                <span class="text-sm">{{ formatDate(domainPrice.updated_at) }}</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Price Comparison Card -->
            <Card>
                <CardHeader>
                    <CardTitle>Price Comparison</CardTitle>
                    <CardDescription> Compare different pricing options for {{ extLabel(domainPrice.extension) }} </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-4 gap-4">
                        <div class="rounded-lg border p-4 text-center">
                            <div class="mb-1 text-sm text-muted-foreground">Base Cost</div>
                            <div class="text-xl font-bold text-red-600">{{ formatPrice(domainPrice.base_cost) }}</div>
                            <div class="mt-1 text-xs text-muted-foreground">Internal</div>
                        </div>
                        <div class="rounded-lg border p-4 text-center">
                            <div class="mb-1 text-sm text-muted-foreground">Renewal Cost</div>
                            <div class="text-xl font-bold text-orange-600">{{ formatPrice(domainPrice.renewal_cost) }}</div>
                            <div class="mt-1 text-xs text-muted-foreground">Internal</div>
                        </div>
                        <div class="rounded-lg border p-4 text-center">
                            <div class="mb-1 text-sm text-muted-foreground">Selling Price</div>
                            <div class="text-xl font-bold text-green-600">{{ formatPrice(domainPrice.selling_price) }}</div>
                            <div class="mt-1 text-xs text-muted-foreground">Customer</div>
                        </div>
                        <div class="rounded-lg border p-4 text-center">
                            <div class="mb-1 text-sm text-muted-foreground">Renewal w/ Tax</div>
                            <div class="text-xl font-bold text-blue-600">{{ formatPrice(domainPrice.renewal_price_with_tax) }}</div>
                            <div class="mt-1 text-xs text-muted-foreground">Customer</div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Promo Registrasi Card (RDash) -->
            <Card v-if="promoStatus">
                <CardHeader>
                    <CardTitle class="flex items-center">
                        <DollarSign class="mr-2 h-5 w-5" />
                        Promo Registrasi (RDash)
                        <Badge :variant="promoStatus.variant" class="ml-2">{{ promoStatus.label }}</Badge>
                    </CardTitle>
                    <CardDescription> Harga promo registrasi yang disinkronkan dari RDash (tidak berlaku untuk perpanjangan) </CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid grid-cols-2 gap-4 md:grid-cols-3">
                        <div class="rounded-lg border p-4 text-center">
                            <div class="mb-1 text-sm text-muted-foreground">Harga Promo RDash</div>
                            <div class="text-xl font-bold text-blue-600">{{ formatPrice(domainPrice.promo_price ?? 0) }}</div>
                            <div class="mt-1 text-xs text-muted-foreground">Excl. PPN</div>
                        </div>
                        <div class="rounded-lg border p-4 text-center">
                            <div class="mb-1 text-sm text-muted-foreground">Modal Promo</div>
                            <div class="text-xl font-bold text-red-600">{{ formatPrice(domainPrice.promo_base_cost ?? 0) }}</div>
                            <div class="mt-1 text-xs text-muted-foreground">Incl. PPN</div>
                        </div>
                        <div class="rounded-lg border p-4 text-center">
                            <div class="mb-1 text-sm text-muted-foreground">Harga Jual Promo</div>
                            <div class="text-xl font-bold text-green-600">{{ formatPrice(domainPrice.promo_selling_price ?? 0) }}</div>
                            <div class="mt-1 text-xs text-muted-foreground">Ke klien</div>
                        </div>
                        <div class="rounded-lg border p-4 text-center">
                            <div class="mb-1 text-sm text-muted-foreground">Penghematan Klien</div>
                            <div class="text-xl font-bold text-emerald-600">{{ formatPrice(domainPrice.promo_savings ?? 0) }}</div>
                            <div class="mt-1 text-xs text-muted-foreground">vs harga normal</div>
                        </div>
                        <div class="rounded-lg border p-4 text-center">
                            <div class="mb-1 text-sm text-muted-foreground">Margin Promo</div>
                            <div class="text-xl font-bold" :class="promoMargin !== null && promoMargin < 0 ? 'text-red-600' : 'text-emerald-600'">
                                {{ formatPrice(promoMargin ?? 0) }}
                            </div>
                            <div class="mt-1 text-xs text-muted-foreground">Jual - modal</div>
                        </div>
                        <div class="rounded-lg border p-4 text-center">
                            <div class="mb-1 text-sm text-muted-foreground">Sisa Hari</div>
                            <div class="text-xl font-bold">{{ domainPrice.promo_days_left ?? '—' }}</div>
                            <div class="mt-1 text-xs text-muted-foreground">hari</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                        <div class="flex items-center justify-between rounded-lg bg-muted p-3">
                            <span class="text-muted-foreground">Mulai</span>
                            <span>{{ domainPrice.promo_starts_at ? formatDate(domainPrice.promo_starts_at) : '—' }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-muted p-3">
                            <span class="text-muted-foreground">Berakhir</span>
                            <span>{{ domainPrice.promo_ends_at ? formatDate(domainPrice.promo_ends_at) : '—' }}</span>
                        </div>
                    </div>

                    <div v-if="domainPrice.promo_note" class="whitespace-pre-line rounded-lg bg-muted p-3 text-xs text-muted-foreground">
                        {{ domainPrice.promo_note }}
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
