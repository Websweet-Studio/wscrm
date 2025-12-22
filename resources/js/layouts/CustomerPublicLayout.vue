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

    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-gray-900 dark:to-gray-800">
        <CustomerHeader />

        <main class="flex-1">
            <slot />
        </main>

        <CustomerFooter />
    </div>
</template>
