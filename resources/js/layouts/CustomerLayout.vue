<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import CustomerSidebar from '@/components/CustomerSidebar.vue';
import { updateTheme } from '@/composables/useAppearance';
import type { BreadcrumbItemType } from '@/types';
import { onMounted } from 'vue';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

onMounted(() => {
    try {
        localStorage.setItem('appearance', 'light');
    } catch {}

    updateTheme('light');
});
</script>

<template>
    <AppShell variant="sidebar">
        <CustomerSidebar />
        <AppContent variant="sidebar" class="overflow-x-hidden">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" :show-appearance-tabs="false" />
            <slot />
        </AppContent>
    </AppShell>
</template>
