<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const brandingSettings = computed(() => page.props.brandingSettings || {});
const appName = computed(() => brandingSettings.value.app_name || 'WSCRM');
const appLogo = computed(() => brandingSettings.value.app_logo);
const appLogoDark = computed(() => brandingSettings.value.app_logo_dark || brandingSettings.value.app_logo);
const footerText = computed(() => brandingSettings.value.footer_text || '© 2024 WebSweetStudio. All rights reserved.');
</script>

<template>
    <!-- Footer -->
    <footer class="bg-gray-900 py-6 text-gray-300 sm:py-8">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="flex flex-col items-center justify-between space-y-4 md:flex-row md:space-y-0">
                <div class="flex items-center space-x-2">
                    <img v-if="appLogo" :src="appLogo" :alt="appName" class="h-8 w-8 object-contain dark:hidden" />
                    <img v-if="appLogoDark" :src="appLogoDark" :alt="appName" class="hidden h-8 w-8 object-contain dark:block" />
                    <span class="text-lg font-bold text-white sm:text-xl">{{ appName }}</span>
                </div>
                <div class="flex flex-wrap justify-center gap-4 text-sm sm:gap-6">
                    <Link href="/hosting" class="transition-colors hover:text-white">Hosting</Link>
                    <Link href="/domains" class="transition-colors hover:text-white">Domain</Link>
                    <Link href="/customer/login" class="transition-colors hover:text-white">Login Pelanggan</Link>
                    <Link href="/login" class="transition-colors hover:text-white">Login Admin</Link>
                </div>
            </div>
            <div class="mt-4 border-t border-gray-700 pt-4 text-center text-xs sm:mt-6 sm:pt-6 sm:text-sm">
                <p>{{ footerText }}</p>
            </div>
        </div>
    </footer>
</template>
