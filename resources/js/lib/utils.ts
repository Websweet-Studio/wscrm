import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function formatPrice(price: number | string, currency: string = 'IDR'): string {
    const numPrice = typeof price === 'string' ? parseFloat(price) : price;

    if (isNaN(numPrice)) {
        return `${currency} 0`;
    }

    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: currency === 'IDR' ? 'IDR' : 'USD',
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    }).format(numPrice);
}

export function getHostingPlanFinalPrice(plan: {
    selling_price: number | string;
    discount_percent?: number | string | null;
    use_bulk_pricing?: boolean | null;
}): number {
    const base = typeof plan.selling_price === 'string' ? parseFloat(plan.selling_price) : plan.selling_price;
    if (isNaN(base)) return 0;

    if (plan.use_bulk_pricing) {
        return base;
    }

    const discount = plan.discount_percent ?? 0;
    const discountNumber = typeof discount === 'string' ? parseFloat(discount) : discount;
    const safeDiscount = isNaN(discountNumber) ? 0 : discountNumber;

    return base * (1 - safeDiscount / 100);
}

/** Jumlah bulan setiap periode/siklus tagihan. */
export const BILLING_CYCLE_MONTHS: Record<string, number> = {
    monthly: 1,
    quarterly: 3,
    semi_annually: 6,
    semi_annual: 6,
    annually: 12,
    annual: 12,
};

/** Label periode tagihan paket, mis. "/bulan" atau "/tahun". */
export const BILLING_PERIOD_LABELS: Record<string, string> = {
    monthly: '/bulan',
    quarterly: '/3 bulan',
    semi_annually: '/6 bulan',
    annually: '/tahun',
};

/**
 * Harga jual paket hosting/VPS untuk satu siklus tagihan tertentu.
 *
 * Basis diambil dari `billing_period` paket: VPS dijual per BULAN (Rp180.000/bulan
 * → 6 bulan = Rp1.080.000), shared hosting per TAHUN. Jangan asumsikan tahunan.
 */
export function getHostingPlanPriceForCycle(
    plan: {
        selling_price: number | string;
        discount_percent?: number | string | null;
        use_bulk_pricing?: boolean | null;
        billing_period?: string | null;
    },
    cycle?: string | null,
): number {
    const base = getHostingPlanFinalPrice(plan);
    const baseMonths = BILLING_CYCLE_MONTHS[plan.billing_period ?? 'annually'] ?? 12;
    const cycleMonths = BILLING_CYCLE_MONTHS[cycle ?? ''] ?? baseMonths;

    return Math.round((base * cycleMonths) / baseMonths * 100) / 100;
}

export function formatDate(date: string | Date, format: string = 'short'): string {
    const dateObj = typeof date === 'string' ? new Date(date) : date;

    if (isNaN(dateObj.getTime())) {
        return 'Invalid Date';
    }

    const options: Intl.DateTimeFormatOptions = {
        year: 'numeric',
        month: format === 'long' ? 'long' : 'short',
        day: 'numeric',
    };

    return new Intl.DateTimeFormat('id-ID', options).format(dateObj);
}

// Remove unused function - not needed with Reka UI approach
