<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import type { HTMLAttributes } from 'vue';
import { computed } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
    darkMode?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    darkMode: false,
});

interface BrandingSettings {
    app_logo?: string;
    app_logo_dark?: string;
    app_name?: string;
}

const page = usePage();
const brandingSettings = computed<BrandingSettings>(() => (page.props.brandingSettings as BrandingSettings) || {});

// Get the appropriate logo based on dark mode setting
const logoSrc = computed(() => {
    if (props.darkMode && brandingSettings.value.app_logo_dark) {
        return brandingSettings.value.app_logo_dark;
    }
    return brandingSettings.value.app_logo || '/logo.png';
});

const altText = computed(() => {
    return brandingSettings.value.app_name || 'WebSweetStudio Logo';
});
</script>

<template>
    <img :src="logoSrc" :alt="altText" :class="className" v-bind="$attrs" class="object-contain" />
</template>
