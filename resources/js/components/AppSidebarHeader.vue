<script setup lang="ts">
import AppearanceTabs from '@/components/AppearanceTabs.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { Button } from '@/components/ui/button';
import { useSidebar as useRekaSidebar } from '@/components/ui/sidebar';
import type { BreadcrumbItemType } from '@/types';
import { Menu } from 'lucide-vue-next';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
    useCustomSidebar?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
    useCustomSidebar: false,
});

let hasRekaUISidebar = true;
let rekaSidebar: ReturnType<typeof useRekaSidebar> | null = null;
try {
    rekaSidebar = useRekaSidebar();
} catch {
    hasRekaUISidebar = false;
}

// For custom sidebar (AppSidebar), we need to handle toggle manually
const emit = defineEmits<{
    toggleSidebar: [];
    toggleMobileSidebar: [];
}>();

const toggleSidebar = () => {
    rekaSidebar?.toggleSidebar();
};
</script>

<template>
    <header
        class="sticky top-0 z-40 flex h-16 items-center gap-4 border-b border-border bg-background/95 px-4 backdrop-blur supports-[backdrop-filter]:bg-background/60 sm:px-6"
    >
        <!-- Reka UI Sidebar Trigger (for CustomerLayout) -->
        <Button v-if="hasRekaUISidebar" variant="ghost" size="icon" @click="toggleSidebar" class="h-11 w-11">
            <Menu class="h-5 w-5" />
        </Button>

        <!-- Custom Sidebar Triggers (for AppSidebarLayout) -->
        <template v-else>
            <!-- Mobile Menu Button -->
            <Button variant="ghost" size="icon" @click="emit('toggleMobileSidebar')" class="h-11 w-11 lg:hidden">
                <Menu class="h-5 w-5" />
            </Button>

            <!-- Desktop Sidebar Toggle -->
            <Button variant="ghost" size="icon" @click="emit('toggleSidebar')" class="hidden h-11 w-11 lg:flex">
                <Menu class="h-5 w-5" />
            </Button>
        </template>

        <template v-if="breadcrumbs && breadcrumbs.length > 0">
            <Breadcrumbs :breadcrumbs="breadcrumbs" />
        </template>

        <div class="ml-auto flex items-center gap-2">
            <AppearanceTabs />
        </div>
    </header>
</template>
