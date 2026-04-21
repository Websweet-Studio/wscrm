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
    <footer style="background-color: #141413; border-top: 1px solid #30302e; padding-top: 1.5rem; padding-bottom: 1.5rem; color: #b0aea5;">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="flex flex-col items-center justify-between space-y-4 md:flex-row md:space-y-0">
                <div class="flex items-center space-x-2">
                    <img v-if="appLogo" :src="appLogo" :alt="appName" class="h-8 w-8 object-contain dark:hidden" />
                    <img v-if="appLogoDark" :src="appLogoDark" :alt="appName" class="hidden h-8 w-8 object-contain dark:block" />
                    <span class="text-lg font-bold sm:text-xl" style="color: #faf9f5; font-family: Georgia, serif;">{{ appName }}</span>
                </div>
                <div class="flex flex-wrap justify-center gap-4 text-sm sm:gap-6">
                    <Link href="/hosting" class="transition-colors" style="color: #b0aea5;">Hosting</Link>
                    <Link href="/domains" class="transition-colors" style="color: #b0aea5;">Domain</Link>
                    <Link href="/customer/login" class="transition-colors" style="color: #b0aea5;">Login Pelanggan</Link>
                    <Link href="/login" class="transition-colors" style="color: #b0aea5;">Login Admin</Link>
                </div>
            </div>
            <div class="mt-4 pt-4 text-center text-xs sm:mt-6 sm:pt-6 sm:text-sm" style="border-top: 1px solid #30302e; color: #87867f;">
                <p>{{ footerText }}</p>
            </div>
        </div>
    </footer>
</template>
