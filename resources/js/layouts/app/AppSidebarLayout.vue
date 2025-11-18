<script setup lang="ts">
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import ToastContainer from '@/components/ToastContainer.vue';
import { useSidebar } from '@/composables/useSidebar';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const { isMinimized, isMobileOpen, toggleSidebar, toggleMobileSidebar, closeMobileSidebar } = useSidebar();
</script>

<template>
    <div class="relative flex min-h-screen overflow-hidden">
        <!-- Desktop Sidebar -->
        <AppSidebar class="hidden lg:flex" />

        <!-- Mobile Overlay -->
        <div v-if="isMobileOpen" class="fixed inset-0 z-40 lg:hidden" @click="closeMobileSidebar">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
        </div>

        <!-- Mobile Sidebar - Always expanded when opened -->
        <div
            :class="[
                'fixed inset-y-0 left-0 z-50 flex w-[72%] transform transition-transform duration-300 ease-in-out lg:hidden',
                isMobileOpen ? 'translate-x-0' : '-translate-x-full',
            ]"
        >
            <AppSidebar :force-expanded="true" />
        </div>

        <!-- Main Content -->
        <div :class="['flex min-w-0 flex-1 flex-col transition-all duration-300', isMinimized ? 'lg:ml-16' : 'lg:ml-64']">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" @toggle-sidebar="toggleSidebar" @toggle-mobile-sidebar="toggleMobileSidebar" />
            <main class="flex-1 overflow-hidden">
                <div class="h-full overflow-y-auto">
                    <div class="p-6">
                        <slot />
                    </div>
                </div>
            </main>
        </div>

        <!-- Toast Container -->
        <ToastContainer />
    </div>
</template>
