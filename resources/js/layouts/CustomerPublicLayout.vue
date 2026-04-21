<script setup lang="ts">
import CustomerFooter from '@/components/CustomerFooter.vue';
import CustomerHeader from '@/components/CustomerHeader.vue';
import { Head } from '@inertiajs/vue3';

interface Props {
    title?: string;
}

withDefaults(defineProps<Props>(), {
    title: 'Layanan Hosting & Domain',
});
</script>

<template>
    <Head :title="title" />

    <div class="min-h-screen" style="background-color: #f5f4ed;">
        <CustomerHeader />

        <main class="flex-1">
            <slot />
        </main>

        <CustomerFooter />
    </div>
</template>
