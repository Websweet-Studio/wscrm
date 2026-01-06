<script setup lang="ts">
import NavUser from '@/components/NavUser.vue';
import { useSidebar } from '@/composables/useSidebar';
// import { dashboard } from '@/routes';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import {
    BookOpen,
    Building,
    Building2,
    Calculator,
    CheckSquare,
    ChevronDown,
    ChevronRight,
    CreditCard,
    DollarSign,
    FileText,
    Folder,
    Globe,
    LayoutGrid,
    Package,
    Palette,
    PenTool,
    Server,
    ShoppingCart,
    Users,
} from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import AppLogo from './AppLogo.vue';
import AppLogoIcon from './AppLogoIcon.vue';

interface Props {
    forceExpanded?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    forceExpanded: false,
});

const $page = usePage();
const { isMinimized, isMobileOpen, closeMobileSidebar } = useSidebar();

// For mobile and desktop: determine when to show icons only
const shouldShowIconsOnly = computed(() => {
    // If forceExpanded is true (mobile), never show icons only
    if (props.forceExpanded) return false;

    // Otherwise use isMinimized state (desktop)
    return isMinimized.value;
});

// Function to handle link clicks on mobile
const handleLinkClick = () => {
    // Close mobile sidebar when link is clicked
    closeMobileSidebar();
};

// Get current user for role-based access
const user = $page.props.auth.user;

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

// Define financial children based on user role
const getFinancialChildren = () => {
    const baseChildren = [
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
    ];

    // Only add expenses for super_admin
    if (user.role === 'super_admin') {
        baseChildren.push({
            title: 'Data Keuangan',
            href: '/admin/expenses',
            icon: CreditCard,
        });
    }

    return baseChildren;
};

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Tasks',
        href: '/admin/tasks?calendar_date=2025-12-31&view_mode=calendar',
        icon: CheckSquare,
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
            {
                title: 'Services',
                href: '/admin/orders?view=services&status=active',
                icon: Package,
            },
        ],
    },
    {
        title: 'Karyawan',
        href: '/admin/employees',
        icon: Building2,
    },
    {
        title: 'Hosting',
        href: '#',
        icon: Server,
        children: [
            {
                title: 'Hosting Plans',
                href: '/admin/hosting-plans',
                icon: Server,
            },
            {
                title: 'Bulk Pricing',
                href: '/admin/bulk-pricing',
                icon: Calculator,
            },
            {
                title: 'Service Plans',
                href: '/admin/service-plans',
                icon: Package,
            },
        ],
    },
    {
        title: 'Blog',
        href: '/admin/blog',
        icon: PenTool,
    },
    {
        title: 'Financial',
        href: '#',
        icon: DollarSign,
        children: getFinancialChildren(),
    },
    {
        title: 'Branding',
        href: '/admin/branding',
        icon: Palette,
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
    <aside
        :class="[
            'fixed inset-y-0 left-0 flex flex-col border-r border-border bg-sidebar transition-all duration-300',
            props.forceExpanded ? 'w-full' : shouldShowIconsOnly ? 'w-16' : 'w-64',
        ]"
    >
        <!-- Header -->
        <div class="border-b border-border p-4">
            <Link v-if="!shouldShowIconsOnly" href="/dashboard" class="flex cursor-pointer items-center gap-2" @click="handleLinkClick">
                <AppLogo :show-text="!shouldShowIconsOnly" />
            </Link>
            <Link v-else href="/dashboard" class="flex cursor-pointer items-center justify-center" @click="handleLinkClick">
                <div class="flex aspect-square size-8 items-center justify-center rounded-md">
                    <AppLogoIcon class="size-6 h-6 w-6" />
                </div>
            </Link>
        </div>

        <!-- Navigation -->
        <nav class="custom-scrollbar flex-1 overflow-y-auto p-0">
            <div class="space-y-1">
                <template v-for="item in mainNavItems" :key="item.title">
                    <!-- Single menu item -->
                    <Link
                        v-if="!item.children"
                        :href="item.href"
                        @click="handleLinkClick"
                        :class="[
                            'flex cursor-pointer items-center gap-2 rounded-md px-3 py-2 text-sm transition-colors hover:bg-sidebar-accent',
                            item.href === $page.url ? 'bg-sidebar-accent font-medium' : '',
                            shouldShowIconsOnly ? 'justify-center' : '',
                        ]"
                        :title="shouldShowIconsOnly ? item.title : ''"
                    >
                        <component :is="item.icon" class="h-4 w-4 flex-shrink-0" />
                        <span v-if="!shouldShowIconsOnly" class="truncate">{{ item.title }}</span>
                    </Link>

                    <!-- Group with children -->
                    <div v-else>
                        <button
                            v-if="!shouldShowIconsOnly"
                            @click="toggleGroup(item.title.toLowerCase().replace(' ', '-'))"
                            :class="[
                                'flex w-full cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm transition-colors hover:bg-sidebar-accent',
                                'text-left',
                            ]"
                        >
                            <div class="flex items-center gap-2">
                                <component :is="item.icon" class="h-4 w-4 flex-shrink-0" />
                                <span class="truncate">{{ item.title }}</span>
                            </div>
                            <component
                                :is="expandedGroups.has(item.title.toLowerCase().replace(' ', '-')) ? ChevronDown : ChevronRight"
                                class="h-4 w-4 flex-shrink-0"
                            />
                        </button>

                        <!-- Minimized group header (clickable) -->
                        <button
                            v-else
                            @click="toggleGroup(item.title.toLowerCase().replace(' ', '-'))"
                            :class="[
                                'w-full flex cursor-pointer items-center justify-center rounded-md px-3 py-2 text-sm transition-colors hover:bg-sidebar-accent',
                                expandedGroups.has(item.title.toLowerCase().replace(' ', '-')) ? 'bg-sidebar-accent' : '',
                            ]"
                            :title="item.title"
                        >
                            <component :is="item.icon" class="h-4 w-4 flex-shrink-0" />
                        </button>

                        <!-- Minimized submenu items -->
                        <div v-if="shouldShowIconsOnly && expandedGroups.has(item.title.toLowerCase().replace(' ', '-'))" class="mt-1 space-y-1">
                            <template v-for="child in item.children" :key="child.title">
                                <!-- Child without nested children -->
                                <Link
                                    v-if="!child.children"
                                    :href="child.href"
                                    @click="handleLinkClick"
                                    :class="[
                                        'flex cursor-pointer items-center justify-center rounded-md px-3 py-2 text-sm transition-colors hover:bg-sidebar-accent/50',
                                        'text-sidebar-foreground/60',
                                        child.href === $page.url ? 'bg-sidebar-accent font-medium text-sidebar-accent-foreground' : '',
                                    ]"
                                    :title="child.title"
                                >
                                    <component :is="child.icon" class="h-3.5 w-3.5 flex-shrink-0" />
                                </Link>

                                <!-- Child with nested children (show nested children directly in minimized mode) -->
                                <div v-else class="space-y-1">
                                    <button
                                        @click="toggleGroup(child.title.toLowerCase().replace(' ', '-'))"
                                        :class="[
                                            'flex cursor-pointer items-center justify-center rounded-md px-3 py-2 text-sm transition-colors hover:bg-sidebar-accent/50',
                                            'text-sidebar-foreground/60',
                                            expandedGroups.has(child.title.toLowerCase().replace(' ', '-')) ? 'bg-sidebar-accent/30' : '',
                                        ]"
                                        :title="child.title"
                                    >
                                        <component :is="child.icon" class="h-3.5 w-3.5 flex-shrink-0" />
                                    </button>

                                    <!-- Nested children in minimized mode -->
                                    <div v-if="expandedGroups.has(child.title.toLowerCase().replace(' ', '-'))" class="space-y-1 pl-2">
                                        <Link
                                            v-for="nestedChild in child.children"
                                            :key="nestedChild.title"
                                            :href="nestedChild.href"
                                            @click="handleLinkClick"
                                            :class="[
                                                'flex cursor-pointer items-center justify-center rounded-md px-2 py-1.5 text-xs transition-colors hover:bg-sidebar-accent/30',
                                                'text-sidebar-foreground/50',
                                                nestedChild.href === $page.url ? 'bg-sidebar-accent font-medium text-sidebar-accent-foreground' : '',
                                            ]"
                                            :title="nestedChild.title"
                                        >
                                            <component :is="nestedChild.icon" class="h-3 w-3 flex-shrink-0" />
                                        </Link>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Submenu items (expanded) -->
                        <div
                            v-if="!shouldShowIconsOnly && expandedGroups.has(item.title.toLowerCase().replace(' ', '-'))"
                            class="mt-1 ml-2 space-y-1 border-l-2 border-sidebar-accent/20 pl-3"
                        >
                            <template v-for="child in item.children" :key="child.title">
                                <!-- Child without nested children -->
                                <Link
                                    v-if="!child.children"
                                    :href="child.href"
                                    @click="handleLinkClick"
                                    :class="[
                                        'flex cursor-pointer items-center gap-2 rounded-md px-3 py-2 text-sm transition-colors hover:bg-sidebar-accent',
                                        child.href === $page.url ? 'bg-sidebar-accent font-medium' : '',
                                    ]"
                                >
                                    <component :is="child.icon" class="h-4 w-4 flex-shrink-0" />
                                    <span class="truncate">{{ child.title }}</span>
                                </Link>

                                <!-- Child with nested children (nested submenu) -->
                                <div v-else>
                                    <button
                                        @click="toggleGroup(child.title.toLowerCase().replace(' ', '-'))"
                                        :class="[
                                            'flex w-full cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm transition-colors hover:bg-sidebar-accent',
                                            'text-left',
                                        ]"
                                    >
                                        <div class="flex items-center gap-2">
                                            <component :is="child.icon" class="h-4 w-4 flex-shrink-0" />
                                            <span class="truncate">{{ child.title }}</span>
                                        </div>
                                        <component
                                            :is="expandedGroups.has(child.title.toLowerCase().replace(' ', '-')) ? ChevronDown : ChevronRight"
                                            class="h-3 w-3 flex-shrink-0"
                                        />
                                    </button>

                                    <!-- Nested submenu items -->
                                    <div
                                        v-if="expandedGroups.has(child.title.toLowerCase().replace(' ', '-'))"
                                        class="mt-1 ml-4 space-y-1 border-l-2 border-sidebar-accent/10 pl-3"
                                    >
                                        <Link
                                            v-for="nestedChild in child.children"
                                            :key="nestedChild.title"
                                            :href="nestedChild.href"
                                            @click="handleLinkClick"
                                            :class="[
                                                'flex cursor-pointer items-center gap-2 rounded-md px-3 py-1.5 text-xs transition-colors hover:bg-sidebar-accent/50',
                                                'text-sidebar-foreground/70',
                                                nestedChild.href === $page.url ? 'bg-sidebar-accent font-medium text-sidebar-accent-foreground' : '',
                                            ]"
                                        >
                                            <component :is="nestedChild.icon" class="h-3.5 w-3.5 flex-shrink-0" />
                                            <span class="truncate">{{ nestedChild.title }}</span>
                                        </Link>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </nav>

        <!-- Footer -->
        <div class="border-t border-border p-4">
            <NavUser :minimized="shouldShowIconsOnly" />
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
