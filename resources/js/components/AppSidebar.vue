<script setup lang="ts">
import NavUser from '@/components/NavUser.vue';
import { dashboard } from '@/routes';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid, Server, Globe, Users, ShoppingCart, Settings, DollarSign, Package, Calculator } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const $page = usePage();

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Order Simulator',
        href: '/order-simulator',
        icon: Calculator,
    },
    {
        title: 'Customers',
        href: '/admin/customers',
        icon: Users,
    },
    {
        title: 'Orders',
        href: '/admin/orders',
        icon: ShoppingCart,
    },
    {
        title: 'Services',
        href: '/admin/services',
        icon: Settings,
    },
    {
        title: 'Service Plans',
        href: '/admin/service-plans',
        icon: Package,
    },
    {
        title: 'Hosting Plans',
        href: '/admin/hosting-plans',
        icon: Server,
    },
    {
        title: 'Domain Prices',
        href: '/admin/domain-prices',
        icon: DollarSign,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <aside class="fixed inset-y-0 left-0 w-64 bg-sidebar border-r border-border flex flex-col">
        <!-- Header -->
        <div class="p-4 border-b border-border">
            <Link :href="dashboard()" class="flex items-center gap-2">
                <AppLogo />
            </Link>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 p-4">
            <div class="space-y-1">
                <Link 
                    v-for="item in mainNavItems" 
                    :key="item.title"
                    :href="item.href"
                    class="flex items-center gap-2 px-3 py-2 text-sm rounded-md hover:bg-sidebar-accent transition-colors"
                    :class="item.href === $page.url ? 'bg-sidebar-accent font-medium' : ''"
                >
                    <component :is="item.icon" class="w-4 h-4" />
                    {{ item.title }}
                </Link>
            </div>
        </nav>

        <!-- Footer -->
        <div class="p-4 border-t border-border">
            <NavUser />
        </div>
    </aside>
</template>
