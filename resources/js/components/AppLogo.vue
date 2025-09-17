<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { useSidebar } from '@/components/ui/sidebar';

const { state } = useSidebar();
</script>

<template>
    <div class="flex aspect-square size-8 items-center justify-center rounded-md">
        <AppLogoIcon class="size-8 h-8 w-8" />
    </div>
    <div class="ml-1 grid flex-1 text-left text-sm transition-all duration-200 hidden md:block">
        <span class="mb-0.5 truncate leading-tight font-semibold">WebSweetStudio</span>
    </div>
</template>
