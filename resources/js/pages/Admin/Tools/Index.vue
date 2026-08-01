<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import {
    CheckCircle, Database, FileText, HardDrive, Monitor,
    RefreshCw, RotateCcw, Server, Shield, Terminal,
    Wrench, Zap,
} from 'lucide-vue-next';
import { ref } from 'vue';
import { usePage } from '@inertiajs/vue3';

interface Props {
    systemInfo: {
        php_version: string;
        laravel_root: string;
        env_exists: boolean;
        storage_link: boolean;
        memory_limit: string;
        max_execution_time: string;
    };
}

defineProps<Props>();

const $page = usePage();

const output = ref('');
const isRunning = ref(false);

// Read result from flash on page load
const flashResult = ($page.props.flash as any)?.tool_result;
if (flashResult) {
    output.value = flashResult.output || 'Done (no output)';
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Settings', href: '#' },
    { title: 'App Manager', href: '/admin/tools' },
];

interface ToolAction {
    key: string;
    label: string;
    desc: string;
    variant: 'default' | 'secondary' | 'destructive';
    confirm?: boolean;
}

const toolGroups: { title: string; icon: any; actions: ToolAction[] }[] = [
    {
        title: 'Cache Management',
        icon: Zap,
        actions: [
            { key: 'clear_cache', label: 'Clear All Cache', desc: 'Clear cache, config, routes, views', variant: 'default' as const },
            { key: 'optimize_clear', label: 'Clear Optimization', desc: 'Clear all optimized files', variant: 'secondary' as const },
        ],
    },
    {
        title: 'Optimization',
        icon: RotateCcw,
        actions: [
            { key: 'optimize', label: 'Optimize App', desc: 'Optimize routes, config, views', variant: 'default' as const },
            { key: 'config_cache', label: 'Cache Config', desc: 'Cache configuration files', variant: 'secondary' as const },
            { key: 'route_cache', label: 'Cache Routes', desc: 'Cache route definitions', variant: 'secondary' as const },
        ],
    },
    {
        title: 'Storage',
        icon: HardDrive,
        actions: [
            { key: 'storage_link', label: 'Create Storage Link', desc: 'Create symbolic link for storage', variant: 'default' as const },
            { key: 'fix_storage_permissions', label: 'Fix Storage Permissions', desc: 'Set proper storage permissions', variant: 'secondary' as const },
            { key: 'clear_logs', label: 'Clear Log Files', desc: 'Delete all log files', variant: 'destructive' as const },
        ],
    },
    {
        title: 'Database',
        icon: Database,
        actions: [
            { key: 'migrate', label: 'Run Migrations', desc: 'Execute database migrations', variant: 'default' as const },
            { key: 'migrate_optimize', label: 'Migrate + Optimize', desc: 'Run migrations then optimize', variant: 'default' as const },
            { key: 'db_seed', label: 'Run Seeder', desc: 'Seed database', variant: 'secondary' as const },
            { key: 'db_seed_users', label: 'Seed Users', desc: 'Seed Super Admin', variant: 'secondary' as const },
            { key: 'db_seed_domain', label: 'Seed Domain Prices', desc: 'Data harga domain', variant: 'secondary' as const },
            { key: 'db_seed_layanan', label: 'Seed Service Plans', desc: 'Data paket layanan', variant: 'secondary' as const },
            { key: 'db_seed_hosting', label: 'Seed Hosting Plans', desc: 'Data paket hosting', variant: 'secondary' as const },
            { key: 'db_seed_demo', label: 'Seed Demo Websites', desc: 'Demo website dari API', variant: 'secondary' as const },
            { key: 'db_seed_websites', label: 'Seed Manage Website', desc: '6 website + jurnal sample', variant: 'secondary' as const },
            { key: 'migrate_fresh', label: 'Fresh Migration', desc: '⚠️ Drop all tables & re-migrate', variant: 'destructive' as const, confirm: true },
        ],
    },
    {
        title: 'Maintenance Mode',
        icon: Monitor,
        actions: [
            { key: 'maintenance_down', label: 'Enable Maintenance', desc: 'Put app in maintenance mode', variant: 'secondary' as const },
            { key: 'maintenance_up', label: 'Disable Maintenance', desc: 'Bring app back online', variant: 'default' as const },
        ],
    },
    {
        title: 'Security',
        icon: Shield,
        actions: [
            { key: 'key_generate', label: 'Generate App Key', desc: '⚠️ Generate new APP_KEY', variant: 'destructive' as const, confirm: true },
        ],
    },
    {
        title: 'Environment',
        icon: FileText,
        actions: [
            { key: 'check_env', label: 'Check .env', desc: 'Validate env configuration', variant: 'secondary' as const },
            { key: 'show_env', label: 'Show .env Content', desc: 'Display env (masked)', variant: 'secondary' as const },
            { key: 'backup_env', label: 'Backup .env', desc: 'Create backup of .env', variant: 'secondary' as const },
        ],
    },
    {
        title: 'Diagnostics',
        icon: Wrench,
        actions: [
            { key: 'health_check', label: 'System Health Check', desc: 'Complete system diagnosis', variant: 'default' as const },
            { key: 'debug_500_error', label: 'Debug 500 Error', desc: 'Diagnose HTTP 500 errors', variant: 'secondary' as const },
            { key: 'debug_hosting_structure', label: 'Debug Hosting Structure', desc: 'Analyze hosting directory', variant: 'secondary' as const },
            { key: 'disk_space', label: 'Disk Space Usage', desc: 'Check disk space & file sizes', variant: 'secondary' as const },
        ],
    },
];

const getVariantClass = (v: string) => {
    return {
        default: 'bg-primary text-primary-foreground hover:bg-primary/90',
        secondary: 'bg-secondary text-secondary-foreground hover:bg-secondary/80 border',
        destructive: 'bg-destructive text-destructive-foreground hover:bg-destructive/90',
    }[v] || '';
};

const executeAction = (key: string, confirmMsg?: string) => {
    if (confirmMsg && !confirm('⚠️ ' + confirmMsg + ' Lanjutkan?')) return;

    isRunning.value = true;
    output.value = 'Running...';

    router.post('/admin/tools/execute', { action: key }, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: (page: any) => {
            const result = page.props.flash?.result || {};
            output.value = result.output || 'Done (no output)';
            isRunning.value = false;
        },
        onError: () => {
            output.value = 'Error executing action';
            isRunning.value = false;
        },
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="App Manager" />

        <div class="space-y-6">
            <div>
                <h1 class="text-3xl font-medium tracking-tight" style="font-family: Georgia, serif;">App Manager</h1>
                <p class="text-muted-foreground">Server & application management utilities</p>
            </div>

            <!-- System Info -->
            <Card>
                <CardHeader><CardTitle class="text-base flex items-center gap-2"><Server class="h-4 w-4" /> System Info</CardTitle></CardHeader>
                <CardContent>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                        <div><span class="text-muted-foreground">PHP Version:</span> <span class="font-mono">{{ systemInfo.php_version }}</span></div>
                        <div><span class="text-muted-foreground">Memory Limit:</span> <span class="font-mono">{{ systemInfo.memory_limit }}</span></div>
                        <div><span class="text-muted-foreground">.env:</span> <Badge :variant="systemInfo.env_exists ? 'default' : 'destructive'" class="ml-1">{{ systemInfo.env_exists ? 'Exists' : 'Missing' }}</Badge></div>
                        <div><span class="text-muted-foreground">Storage Link:</span> <Badge :variant="systemInfo.storage_link ? 'default' : 'secondary'" class="ml-1">{{ systemInfo.storage_link ? 'OK' : 'Missing' }}</Badge></div>
                    </div>
                </CardContent>
            </Card>

            <!-- Output -->
            <Card v-if="output">
                <CardHeader class="pb-2">
                    <CardTitle class="text-base flex items-center gap-2">
                        <Terminal class="h-4 w-4" />
                        Output
                        <span v-if="isRunning" class="text-xs text-muted-foreground animate-pulse">running...</span>
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <pre class="bg-muted rounded-md p-4 text-xs font-mono whitespace-pre-wrap max-h-96 overflow-y-auto">{{ output }}</pre>
                    <Button variant="ghost" size="sm" class="mt-2 cursor-pointer" @click="output = ''">Clear</Button>
                </CardContent>
            </Card>

            <!-- Tool Groups -->
            <div class="grid gap-6 md:grid-cols-2">
                <Card v-for="group in toolGroups" :key="group.title">
                    <CardHeader class="pb-3">
                        <CardTitle class="text-base flex items-center gap-2">
                            <component :is="group.icon" class="h-4 w-4 text-muted-foreground" />
                            {{ group.title }}
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-2">
                            <div v-for="action in group.actions" :key="action.key" class="flex items-center justify-between gap-2">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium truncate">{{ action.label }}</p>
                                    <p class="text-xs text-muted-foreground truncate">{{ action.desc }}</p>
                                </div>
                                <Button
                                    :variant="action.variant === 'destructive' ? 'destructive' : action.variant === 'secondary' ? 'outline' : 'default'"
                                    size="sm"
                                    :disabled="isRunning"
                                    @click="executeAction(action.key, action.confirm ? action.desc : undefined)"
                                    class="cursor-pointer whitespace-nowrap flex-shrink-0"
                                >
                                    <CheckCircle class="mr-1 h-3.5 w-3.5" />
                                    Run
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
