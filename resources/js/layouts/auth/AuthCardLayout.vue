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
    <div class="flex min-h-svh flex-col items-center justify-center p-4 md:p-8 lg:p-12" style="background-color: #f5f4ed;">
        <div class="w-full max-w-sm space-y-6 sm:max-w-md sm:space-y-8 lg:max-w-lg">
            <div class="text-center">
                <Link :href="home()" class="group inline-flex items-center justify-center">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full" style="background-color: #e8e6dc;">
                        <AppLogoIcon class="h-8 w-8" style="color: #c96442;" />
                    </div>
                </Link>
            </div>

            <Card style="background-color: #faf9f5; border: 1px solid #f0eee6; border-radius: 16px; box-shadow: rgba(0,0,0,0.05) 0px 4px 24px;">
                <CardContent class="p-6 sm:p-8">
                    <slot />
                </CardContent>
            </Card>
        </div>
    </div>
</template>
