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
