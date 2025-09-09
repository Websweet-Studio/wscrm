<script setup lang="ts">
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import { useSidebar } from '@/composables/useSidebar';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const { isMinimized } = useSidebar();
</script>

<template>
    <div class="flex min-h-screen">
        <AppSidebar />
        <div :class="['flex flex-1 flex-col transition-all duration-300', isMinimized ? 'ml-16' : 'ml-64']">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <main class="flex-1 p-6">
                <slot />
            </main>
        </div>
    </div>
</template>
