/**
 * Tema grafik WSCRM — satu sumber gaya untuk SEMUA grafik (Chart.js / vue-chartjs).
 *
 * PENTING: canvas tidak mengenal `var(--primary)`. Token warna aplikasi disimpan sebagai hex
 * (`--primary: #c96442`), sehingga penulisan lama `hsl(var(--primary))` menghasilkan string warna
 * TIDAK valid → Chart.js mengabaikannya dan grafik tampil hitam/pucat. Modul ini membaca nilai
 * asli token lewat `getComputedStyle` sehingga warna (dan branding) ikut benar, plus menyediakan
 * gaya dasar yang seragam: grid tipis hanya horizontal, sudut batang membulat, tooltip kartu gelap,
 * legenda titik bulat, animasi halus.
 */

const FONT = 'Inter, ui-sans-serif, system-ui, -apple-system, sans-serif';

function readVar(name: string, fallback: string): string {
    if (typeof document === 'undefined') return fallback;
    const raw = getComputedStyle(document.documentElement).getPropertyValue(name).trim();
    return raw || fallback;
}

export const chartColors = {
    primary: () => readVar('--primary', '#c96442'),
    border: () => readVar('--border', '#eceae2'),
    text: () => readVar('--muted-foreground', '#5e5d59'),
    card: () => readVar('--card', '#ffffff'),
    foreground: () => readVar('--foreground', '#1c1c1a'),
};

function toRgb(color: string): [number, number, number] | null {
    const c = (color || '').trim();
    const hex = c.match(/^#([0-9a-f]{3}|[0-9a-f]{6})$/i);
    if (hex) {
        const h = hex[1];
        if (h.length === 3) {
            return [parseInt(h[0] + h[0], 16), parseInt(h[1] + h[1], 16), parseInt(h[2] + h[2], 16)];
        }
        return [parseInt(h.slice(0, 2), 16), parseInt(h.slice(2, 4), 16), parseInt(h.slice(4, 6), 16)];
    }
    const rgb = c.match(/^rgba?\(([^)]+)\)$/i);
    if (rgb) {
        const p = rgb[1].split(',').map((v) => parseFloat(v));
        return [p[0] || 0, p[1] || 0, p[2] || 0];
    }
    return null;
}

/** Warna + alpha (canvas aman). Kalau warna tak dikenali, kembalikan apa adanya. */
export function alpha(color: string, a: number): string {
    const rgb = toRgb(color);
    if (!rgb) return color;
    return `rgba(${rgb[0]}, ${rgb[1]}, ${rgb[2]}, ${a})`;
}

/** Palet seragam: turunan warna brand dulu, baru aksen pendukung. */
export function chartPalette(): string[] {
    const p = chartColors.primary();
    return [
        p,
        alpha(p, 0.72),
        alpha(p, 0.45),
        alpha(p, 0.25),
        '#10b981',
        '#f59e0b',
        '#6366f1',
        '#0ea5e9',
        '#a855f7',
        '#ef4444',
    ];
}

export function legendOptions(display = false, position: 'top' | 'bottom' = 'top') {
    return {
        display,
        position,
        align: 'end' as const,
        labels: {
            boxWidth: 8,
            boxHeight: 8,
            usePointStyle: true,
            pointStyle: 'circle' as const,
            padding: 14,
            color: chartColors.text(),
            font: { family: FONT, size: 11 },
        },
    };
}

export function tooltipOptions(overrides: Record<string, unknown> = {}) {
    return {
        mode: 'index' as const,
        intersect: false,
        backgroundColor: 'rgba(26, 25, 23, 0.95)',
        titleColor: '#ffffff',
        bodyColor: 'rgba(255, 255, 255, 0.85)',
        titleFont: { family: FONT, size: 11, weight: 600 as const },
        bodyFont: { family: FONT, size: 11 },
        padding: 10,
        cornerRadius: 10,
        boxPadding: 5,
        caretSize: 5,
        caretPadding: 8,
        usePointStyle: true,
        borderWidth: 0,
        ...overrides,
    };
}

/** Sumbu gaya bersih: tanpa garis sumbu, grid horizontal tipis, tick abu-abu kecil. */
export function cartesianScales(opts: { stacked?: boolean; maxTicksLimit?: number; yTicks?: number; xTicks?: number } = {}) {
    const stacked = opts.stacked ?? false;
    const text = chartColors.text();
    return {
        x: {
            stacked,
            grid: { display: false },
            border: { display: false },
            ticks: {
                color: text,
                font: { family: FONT, size: 10 },
                maxRotation: 0,
                autoSkip: true,
                maxTicksLimit: opts.xTicks ?? 10,
                padding: 6,
            },
        },
        y: {
            stacked,
            beginAtZero: true,
            border: { display: false },
            grid: { color: alpha(chartColors.text(), 0.18), drawTicks: false },
            ticks: {
                color: text,
                font: { family: FONT, size: 10 },
                padding: 8,
                maxTicksLimit: opts.yTicks ?? 5,
                precision: 0,
            },
        },
    };
}

export function baseChartOptions(overrides: Record<string, unknown> = {}) {
    return {
        responsive: true,
        maintainAspectRatio: false,
        animation: { duration: 550, easing: 'easeOutQuart' as const },
        layout: { padding: { top: 4, right: 4, bottom: 0, left: 0 } },
        interaction: { mode: 'index' as const, intersect: false },
        plugins: { legend: legendOptions(false), tooltip: tooltipOptions() },
        ...overrides,
    };
}

export function doughnutOptions(opts: { legend?: boolean } = {}) {
    return {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '68%',
        animation: { duration: 600, easing: 'easeOutQuart' as const },
        layout: { padding: 6 },
        plugins: {
            legend: legendOptions(opts.legend ?? true, 'bottom'),
            tooltip: tooltipOptions({ mode: 'nearest' as const, intersect: true }),
        },
    };
}

/** Batang modern: sudut membulat penuh, ada jeda antar kategori, hover lembut. */
export function barDataset(label: string, data: number[], extra: Record<string, unknown> = {}) {
    const color = (extra.backgroundColor as string | string[] | undefined) ?? chartColors.primary();
    // Warna per-batang (array) tidak bisa diturunkan alpha-nya → biarkan Chart.js mengatur hover.
    const hover = typeof color === 'string' ? { hoverBackgroundColor: alpha(color, 0.85) } : {};
    return {
        label,
        data,
        backgroundColor: color,
        borderRadius: 6,
        borderSkipped: false as const,
        maxBarThickness: 34,
        categoryPercentage: 0.72,
        barPercentage: 0.86,
        ...hover,
        ...extra,
    };
}

/** Gradien area untuk grafik garis (chart area belum ada saat frame pertama → pakai warna solid). */
export function areaGradient(color?: string) {
    const base = color ?? chartColors.primary();
    return (context: { chart: { ctx: CanvasRenderingContext2D; chartArea?: { top: number; bottom: number } } }) => {
        const { ctx, chartArea } = context.chart;
        if (!chartArea) return alpha(base, 0.18);
        const g = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
        g.addColorStop(0, alpha(base, 0.3));
        g.addColorStop(1, alpha(base, 0));
        return g;
    };
}

/** Titik garis modern: kecil, ada cincin warna kartu. */
export function lineDataset(label: string, data: number[], extra: Record<string, unknown> = {}) {
    const color = chartColors.primary();
    return {
        label,
        data,
        borderColor: color,
        backgroundColor: areaGradient(color),
        fill: true,
        tension: 0.38,
        borderWidth: 2,
        pointRadius: 0,
        pointHoverRadius: 5,
        pointBackgroundColor: color,
        pointBorderColor: chartColors.card(),
        pointBorderWidth: 2,
        ...extra,
    };
}
