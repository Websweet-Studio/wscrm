<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Card, CardContent } from '@/components/ui/card';
import { home } from '@/routes';
import { Link } from '@inertiajs/vue3';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="flex min-h-svh flex-col items-center justify-center bg-gradient-to-br from-muted/30 via-background to-muted/50 p-4 md:p-8 lg:p-12">
        <div class="w-full max-w-sm space-y-6 sm:max-w-md sm:space-y-8 lg:max-w-lg">
            <!-- Logo Section with enhanced styling -->
            <div class="text-center">
                <Link
                    :href="home()"
                    class="group inline-flex items-center justify-center rounded-xl p-3 transition-all duration-300 hover:scale-105 hover:bg-primary/5"
                >
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 transition-colors group-hover:bg-primary/15">
                        <AppLogoIcon class="size-8 fill-current text-primary transition-transform group-hover:scale-110" />
                    </div>
                </Link>
                <div class="mt-4 space-y-1"></div>
            </div>

            <!-- Main Card with modern styling -->
            <Card class="border-0 bg-card/80 shadow-2xl shadow-primary/5 backdrop-blur-sm">
                <CardContent class="p-6 sm:p-8">
                    <slot />
                </CardContent>
            </Card>
        </div>
    </div>
</template>
