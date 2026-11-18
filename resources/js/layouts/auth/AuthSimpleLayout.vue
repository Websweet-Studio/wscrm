<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const brandingSettings = computed(() => (page.props.brandingSettings as Record<string, string>) || {});
const appLogo = computed(() => brandingSettings.value.app_logo);
const appName = computed(() => brandingSettings.value.app_name || 'WSCRM');

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <!-- Sama dengan bahasa visual halaman publik: putih, garis rambut, hijau untuk aksi. -->
    <div class="kpub relative flex min-h-svh flex-col items-center justify-center overflow-hidden p-4 md:p-8">
        <!-- Pita hijau tipis di atas, seperti aksen halaman publik. -->
        <div class="pointer-events-none absolute inset-x-0 top-0 h-1" style="background-color: var(--kpub-green);"></div>

        <div class="w-full max-w-md">
            <div class="mb-8 flex items-center justify-center">
                <Link :href="home()" class="flex items-center">
                    <span
                        class="flex h-11 w-11 items-center justify-center rounded-xl"
                        style="background-color: var(--kpub-deep);"
                    >
                        <img v-if="appLogo" :src="appLogo" :alt="appName" class="h-6 w-6 object-contain" />
                        <AppLogoIcon v-else class="h-6 w-6" style="color: var(--kpub-green-ink);" />
                    </span>
                </Link>
            </div>

            <div class="kcard p-6 sm:p-8" style="border-radius: var(--kpub-radius-lg); padding: 2rem 1.75rem;">
                <div class="mb-7 space-y-2 text-center">
                    <h1 class="text-3xl" style="color: var(--kpub-ink);">
                        {{ title }}
                    </h1>
                    <p class="text-[0.9375rem]" style="color: var(--kpub-muted);">
                        {{ description }}
                    </p>
                </div>

                <slot />
            </div>

            <p class="mt-6 text-center text-sm" style="color: var(--kpub-faint);">
                <Link :href="home()" class="hover:underline" style="color: var(--kpub-muted);">
                    {{ appName }}
                </Link>
                — hosting, domain, dan pembuatan website untuk bisnis di Indonesia.
            </p>
        </div>
    </div>
</template>
