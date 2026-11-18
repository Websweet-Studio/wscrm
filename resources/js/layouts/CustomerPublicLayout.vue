<script setup lang="ts">
import CustomerFooter from '@/components/CustomerFooter.vue';
import CustomerHeader from '@/components/CustomerHeader.vue';
import PublicFloatingChat from '@/components/PublicFloatingChat.vue';
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

    <div class="kpub min-h-screen">
        <CustomerHeader />

        <main class="flex-1">
            <slot />
        </main>

        <CustomerFooter />

        <!-- Chatbot customer service untuk pengunjung -->
        <PublicFloatingChat />
    </div>
</template>

