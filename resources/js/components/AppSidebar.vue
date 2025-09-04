<script setup lang="ts">
import NavUser from '@/components/NavUser.vue';
import { dashboard } from '@/routes';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid, Server, Globe, Users, ShoppingCart, Settings, DollarSign, Package, Calculator, CreditCard } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import AppLogoIcon from './AppLogoIcon.vue';
import { useSidebar } from '@/composables/useSidebar';

const $page = usePage();
const { isMinimized } = useSidebar();

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
    {
        title: 'Bank Management',
        href: '/admin/banks',
        icon: CreditCard,
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
    <aside :class="[
        'fixed inset-y-0 left-0 bg-sidebar border-r border-border flex flex-col transition-all duration-300',
        isMinimized ? 'w-16' : 'w-64'
    ]">
        <!-- Header -->
        <div class="p-4 border-b border-border">
            <Link 
                v-if="!isMinimized" 
                :href="dashboard()" 
                class="flex items-center gap-2"
            >
                <AppLogo />
            </Link>
            <Link 
                v-else 
                :href="dashboard()" 
                class="flex items-center justify-center"
            >
                <div class="flex aspect-square size-8 items-center justify-center rounded-md">
                    <AppLogoIcon class="size-6 w-6 h-6" />
                </div>
            </Link>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 p-4 overflow-y-auto">
            <div class="space-y-1">
                <Link 
                    v-for="item in mainNavItems" 
                    :key="item.title"
                    :href="item.href"
                    :class="[
                        'flex items-center gap-2 px-3 py-2 text-sm rounded-md hover:bg-sidebar-accent transition-colors',
                        item.href === $page.url ? 'bg-sidebar-accent font-medium' : '',
                        isMinimized ? 'justify-center' : ''
                    ]"
                    :title="isMinimized ? item.title : ''"
                >
                    <component :is="item.icon" class="w-4 h-4 flex-shrink-0" />
                    <span v-if="!isMinimized" class="truncate">{{ item.title }}</span>
                </Link>
            </div>
        </nav>

        <!-- Footer -->
        <div class="p-4 border-t border-border">
            <NavUser :minimized="isMinimized" />
        </div>
    </aside>
</template>
