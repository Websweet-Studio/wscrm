<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Props {
    showText?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    showText: true,
});

interface BrandingSettings {
    app_name?: string;
}

const page = usePage();
const brandingSettings = computed<BrandingSettings>(() => (page.props.brandingSettings as BrandingSettings) || {});

const appName = computed(() => {
    return brandingSettings.value.app_name || 'WebSweetStudio';
});
</script>

<template>
    <div class="flex items-center">
        <div class="flex aspect-square size-8 items-center justify-center rounded-md">
            <AppLogoIcon class="size-8 h-8 w-8" />
        </div>
        <div v-if="props.showText" class="ml-2 grid flex-1 text-left text-sm transition-all duration-200">
            <span class="mb-0.5 truncate leading-tight font-semibold">{{ appName }}</span>
        </div>
    </div>
</template>
