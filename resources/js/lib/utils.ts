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
