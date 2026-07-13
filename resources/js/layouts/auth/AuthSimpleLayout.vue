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
    <div
        class="relative flex min-h-svh flex-col items-center justify-center overflow-hidden p-4 md:p-8 lg:p-12"
        style="background-color: #f5f4ed;"
    >
        <!-- Subtle dot pattern overlay -->
        <div
            class="pointer-events-none absolute inset-0"
            style="background-image: radial-gradient(circle, rgba(201,100,66,0.04) 1px, transparent 1px); background-size: 20px 20px;"
        ></div>

        <!-- Warm ambient glow -->
        <div
            class="pointer-events-none absolute -top-48 left-1/2 h-96 w-[36rem] -translate-x-1/2 opacity-30"
            style="background: radial-gradient(ellipse at center, rgba(201,100,66,0.12) 0%, transparent 70%);"
        ></div>

        <div class="relative w-full max-w-sm">
            <div class="flex flex-col gap-8">
                <!-- Logo area -->
                <div class="flex flex-col items-center gap-4">
                    <Link :href="home()" class="group flex flex-col items-center gap-3">
                        <div
                            class="flex h-14 w-14 items-center justify-center rounded-2xl transition-shadow duration-300"
                            style="background-color: #e8e6dc;"
                        >
                            <img
                                v-if="appLogo"
                                :src="appLogo"
                                :alt="appName"
                                class="h-8 w-8 object-contain"
                            />
                            <AppLogoIcon v-else class="h-8 w-8" style="color: var(--primary);" />
                        </div>
                    </Link>
                    <div class="space-y-2 text-center">
                        <h1
                            class="text-2xl font-medium tracking-tight"
                            style="color: #141413; font-family: Georgia, serif; line-height: 1.25;"
                        >
                            {{ title }}
                        </h1>
                        <p
                            class="text-sm"
                            style="color: #5e5d59;"
                        >
                            {{ description }}
                        </p>
                    </div>
                </div>

                <!-- Card -->
                <div
                    class="animate-in fade-in slide-in-from-bottom-4 duration-700 ease-out"
                    style="background-color: #faf9f5; border: 1px solid #f0eee6; border-radius: 18px; box-shadow: 0 4px 24px rgba(0,0,0,0.04), 0 1px 4px rgba(0,0,0,0.02); padding: 2rem;"
                >
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>
