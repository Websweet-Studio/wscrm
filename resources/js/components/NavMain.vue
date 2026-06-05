<script setup lang="ts">
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuBadge, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps<{
    items: NavItem[];
}>();

const page = usePage();

const currentPath = computed(() => {
    const url = page.url || '';
    return url.split('?')[0] || url;
});

const normalizePath = (value: string) => {
    const path = (value || '').split('?')[0];
    if (!path) return '/';
    return path.length > 1 ? path.replace(/\/+$/, '') : path;
};

const isActive = (href: string) => {
    const current = normalizePath(currentPath.value);
    const target = normalizePath(href);
    if (target === '/') return current === '/';
    return current === target || current.startsWith(`${target}/`);
};
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>Menu</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <SidebarMenuButton as-child :is-active="isActive(item.href)" :tooltip="item.title">
                    <Link :href="item.href">
                        <component :is="item.icon" class="h-4 w-4" />
                        <span>{{ item.title }}</span>
                        <SidebarMenuBadge v-if="item.badge && item.badge > 0">{{ item.badge }}</SidebarMenuBadge>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
