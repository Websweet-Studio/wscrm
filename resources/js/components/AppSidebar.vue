<script setup lang="ts">
import NavUser from '@/components/NavUser.vue';
import { dashboard } from '@/routes';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid, Server, Users, ShoppingCart, Settings, DollarSign, Package, Calculator, ChevronDown, ChevronRight, FileText, Building, Globe } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import AppLogoIcon from './AppLogoIcon.vue';
import { useSidebar } from '@/composables/useSidebar';
import { ref, onMounted } from 'vue';

const $page = usePage();
const { isMinimized } = useSidebar();

// Track expanded state for menu groups with persistence
const expandedGroups = ref(new Set<string>());

// Load expanded state from localStorage on mount
onMounted(() => {
    const saved = localStorage.getItem('sidebar-expanded-groups');
    if (saved) {
        try {
            const savedGroups = JSON.parse(saved);
            expandedGroups.value = new Set(savedGroups);
        } catch (e) {
            console.warn('Failed to parse saved sidebar state');
        }
    }
});

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
        title: 'Customer',
        href: '#',
        icon: Users,
        children: [
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
        ]
    },
    {
        title: 'Service',
        href: '#',
        icon: Settings,
        children: [
            {
                title: 'Active Services',
                href: '/admin/services',
                icon: Server,
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
        ]
    },
    {
        title: 'Financial',
        href: '#',
        icon: DollarSign,
        children: [
            {
                title: 'Invoices',
                href: '/admin/invoices',
                icon: FileText,
            },
            {
                title: 'Domain Prices',
                href: '/admin/domain-prices',
                icon: Globe,
            },
            {
                title: 'Bank Management',
                href: '/admin/banks',
                icon: Building,
            },
        ]
    },
];

const toggleGroup = (groupTitle: string) => {
    if (expandedGroups.value.has(groupTitle)) {
        expandedGroups.value.delete(groupTitle);
    } else {
        expandedGroups.value.add(groupTitle);
    }
    
    // Save to localStorage
    localStorage.setItem('sidebar-expanded-groups', JSON.stringify([...expandedGroups.value]));
};

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
                class="flex items-center gap-2 cursor-pointer"
            >
                <AppLogo />
            </Link>
            <Link 
                v-else 
                :href="dashboard()" 
                class="flex items-center justify-center cursor-pointer"
            >
                <div class="flex aspect-square size-8 items-center justify-center rounded-md">
                    <AppLogoIcon class="size-6 w-6 h-6" />
                </div>
            </Link>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 p-4 overflow-y-auto custom-scrollbar">
            <div class="space-y-1">
                <template v-for="item in mainNavItems" :key="item.title">
                    <!-- Single menu item -->
                    <Link 
                        v-if="!item.children"
                        :href="item.href"
                        :class="[
                            'flex items-center gap-2 px-3 py-2 text-sm rounded-md hover:bg-sidebar-accent transition-colors cursor-pointer',
                            item.href === $page.url ? 'bg-sidebar-accent font-medium' : '',
                            isMinimized ? 'justify-center' : ''
                        ]"
                        :title="isMinimized ? item.title : ''"
                    >
                        <component :is="item.icon" class="w-4 h-4 flex-shrink-0" />
                        <span v-if="!isMinimized" class="truncate">{{ item.title }}</span>
                    </Link>

                    <!-- Group with children -->
                    <div v-else>
                        <button
                            v-if="!isMinimized"
                            @click="toggleGroup(item.title.toLowerCase().replace(' ', '-'))"
                            :class="[
                                'flex items-center justify-between w-full px-3 py-2 text-sm rounded-md hover:bg-sidebar-accent transition-colors cursor-pointer',
                                'text-left'
                            ]"
                        >
                            <div class="flex items-center gap-2">
                                <component :is="item.icon" class="w-4 h-4 flex-shrink-0" />
                                <span class="truncate">{{ item.title }}</span>
                            </div>
                            <component 
                                :is="expandedGroups.has(item.title.toLowerCase().replace(' ', '-')) ? ChevronDown : ChevronRight"
                                class="w-4 h-4 flex-shrink-0"
                            />
                        </button>

                        <!-- Minimized group header (clickable) -->
                        <button 
                            v-else
                            @click="toggleGroup(item.title.toLowerCase().replace(' ', '-'))"
                            :class="[
                                'flex items-center justify-center px-3 py-2 text-sm rounded-md hover:bg-sidebar-accent transition-colors cursor-pointer',
                                expandedGroups.has(item.title.toLowerCase().replace(' ', '-')) ? 'bg-sidebar-accent' : ''
                            ]"
                            :title="item.title"
                        >
                            <component :is="item.icon" class="w-4 h-4 flex-shrink-0" />
                        </button>

                        <!-- Minimized submenu items -->
                        <div 
                            v-if="isMinimized && expandedGroups.has(item.title.toLowerCase().replace(' ', '-'))"
                            class="space-y-1 mt-1"
                        >
                            <Link
                                v-for="child in item.children"
                                :key="child.title"
                                :href="child.href"
                                :class="[
                                    'flex items-center justify-center px-3 py-2 text-sm rounded-md hover:bg-sidebar-accent/50 transition-colors cursor-pointer',
                                    'text-sidebar-foreground/60',
                                    child.href === $page.url ? 'bg-sidebar-accent text-sidebar-accent-foreground font-medium' : ''
                                ]"
                                :title="child.title"
                            >
                                <component :is="child.icon" class="w-3.5 h-3.5 flex-shrink-0" />
                            </Link>
                        </div>

                        <!-- Submenu items (expanded) -->
                        <div 
                            v-if="!isMinimized && expandedGroups.has(item.title.toLowerCase().replace(' ', '-'))"
                            class="ml-2 mt-1 space-y-1 border-l-2 border-sidebar-accent/20 pl-3"
                        >
                            <Link
                                v-for="child in item.children"
                                :key="child.title"
                                :href="child.href"
                                :class="[
                                    'flex items-center gap-2 px-3 py-2 text-sm rounded-md hover:bg-sidebar-accent transition-colors cursor-pointer',
                                    child.href === $page.url ? 'bg-sidebar-accent font-medium' : ''
                                ]"
                            >
                                <component :is="child.icon" class="w-4 h-4 flex-shrink-0" />
                                <span class="truncate">{{ child.title }}</span>
                            </Link>
                        </div>
                    </div>
                </template>
            </div>
        </nav>

        <!-- Footer -->
        <div class="p-4 border-t border-border">
            <NavUser :minimized="isMinimized" />
        </div>
    </aside>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 2px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
}

/* Firefox */
.custom-scrollbar {
    scrollbar-width: thin;
    scrollbar-color: #d1d5db transparent;
}
</style>
