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
    <footer class="text-sm" style="background-color: var(--kpub-deep); color: var(--kpub-deep-muted); padding-top: 3rem; padding-bottom: 2rem;">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="flex flex-col items-center justify-between gap-6 md:flex-row">
                <div class="flex items-center space-x-2">
                    <img v-if="appLogo" :src="appLogo" :alt="appName" class="h-8 w-8 object-contain" />
                    <span class="text-lg font-semibold tracking-tight" style="color: var(--kpub-deep-ink);">{{ appName }}</span>
                </div>
                <div class="flex flex-wrap justify-center gap-x-6 gap-y-2">
                    <Link href="/hosting" class="transition-colors hover:opacity-80">Hosting &amp; VPS</Link>
                    <Link href="/domains" class="transition-colors hover:opacity-80">Domain</Link>
                    <Link href="/demo-web" class="transition-colors hover:opacity-80">Demo Web</Link>
                    <Link href="/customer/login" class="transition-colors hover:opacity-80">Login Pelanggan</Link>
                </div>
            </div>
            <div class="mt-6 pt-5 text-center text-xs" style="border-top: 1px solid var(--kpub-deep-line); color: var(--kpub-deep-muted);">
                <p>{{ footerText }}</p>
            </div>
        </div>
    </footer>
</template>
