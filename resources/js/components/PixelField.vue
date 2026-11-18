<script setup lang="ts">
/*
  Animasi piksel di background: kanvas berisi mosaic kotak kecil yang
  menyala-pudar acak. Digambar per-tick (tanpa rAF loop) supaya murah.
  Seluruh area transparan; warna mengikuti palet hijau halaman publik.
*/
import { onBeforeUnmount, onMounted, ref } from 'vue';

const props = withDefaults(defineProps<{
    /** Ukuran satu piksel dalam px CSS. */
    cell?: number;
    /** Peluang sel baru menyala per tick (0..1), relatif jumlah sel. */
    flip?: number;
    /** Tick per detik. */
    fps?: number;
}>(), {
    cell: 10,
    flip: 0.025,
    fps: 7,
});

const canvasRef = ref<HTMLCanvasElement | null>(null);

let ctx: CanvasRenderingContext2D | null = null;
let states: Float32Array = new Float32Array(0);
let cols = 0;
let rows = 0;
let timer: ReturnType<typeof setInterval> | null = null;
let observer: ResizeObserver | null = null;
let cssWidth = 0;
let cssHeight = 0;

function draw(): void {
    if (!ctx) return;
    ctx.clearRect(0, 0, cssWidth, cssHeight);
    for (let r = 0; r < rows; r++) {
        for (let c = 0; c < cols; c++) {
            const a = states[r * cols + c];
            if (a <= 0.01) continue;
            // Sebagian kecil piksel hijau tua, sisanya hijau normal.
            const deep = ((r * 31 + c * 17) % 23) === 0;
            ctx.fillStyle = deep
                ? `rgba(12, 42, 31, ${(a * 0.35).toFixed(3)})`
                : `rgba(10, 108, 77, ${(a * 0.22).toFixed(3)})`;
            ctx.fillRect(c * props.cell + 1, r * props.cell + 1, props.cell - 2, props.cell - 2);
        }
    }
}

function tick(): void {
    const total = cols * rows;
    if (!total) return;
    // Seluruhan bidang meluruh pelan, lalu sebagian kecil piksel menyala ulang.
    for (let i = 0; i < total; i++) {
        states[i] *= 0.72;
    }
    const nyala = Math.max(1, Math.round(total * props.flip));
    for (let n = 0; n < nyala; n++) {
        const i = (Math.random() * total) | 0;
        states[i] = 0.25 + Math.random() * 0.75;
    }
    draw();
}

function resize(): void {
    const canvas = canvasRef.value;
    if (!canvas) return;
    const parent = canvas.parentElement;
    if (!parent) return;
    cssWidth = parent.clientWidth;
    cssHeight = parent.clientHeight;
    if (!cssWidth || !cssHeight) return;

    const dpr = Math.min(window.devicePixelRatio || 1, 2);
    canvas.width = Math.round(cssWidth * dpr);
    canvas.height = Math.round(cssHeight * dpr);
    canvas.style.width = `${cssWidth}px`;
    canvas.style.height = `${cssHeight}px`;
    ctx = canvas.getContext('2d');
    ctx?.setTransform(dpr, 0, 0, dpr, 0, 0);

    cols = Math.ceil(cssWidth / props.cell);
    rows = Math.ceil(cssHeight / props.cell);
    states = new Float32Array(cols * rows);
    draw();
}

onMounted(() => {
    resize();
    const reduksiGerak = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (!reduksiGerak) {
        timer = setInterval(tick, 1000 / props.fps);
    }
    observer = new ResizeObserver(resize);
    if (canvasRef.value?.parentElement) {
        observer.observe(canvasRef.value.parentElement);
    }
});

onBeforeUnmount(() => {
    if (timer) clearInterval(timer);
    observer?.disconnect();
});
</script>

<template>
    <canvas
        ref="canvasRef"
        aria-hidden="true"
        class="pointer-events-none absolute inset-0"
        style="
            mask-image: linear-gradient(to bottom, black 55%, transparent 96%);
            -webkit-mask-image: linear-gradient(to bottom, black 55%, transparent 96%);
        "
    ></canvas>
</template>
