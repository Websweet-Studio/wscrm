<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Inertia\Inertia;
use Inertia\Response;

class AdminToolsController extends Controller
{
    private function checkAdmin(): void
    {
        if (!auth()->user()?->isAdmin()) {
            abort(403, 'Unauthorized access.');
        }
    }

    public function index(): Response
    {
        $this->checkAdmin();

        $laravelRoot = base_path();
        $publicPath = public_path();

        $systemInfo = [
            'php_version' => PHP_VERSION,
            'laravel_root' => $laravelRoot,
            'env_exists' => file_exists($laravelRoot . '/.env'),
            'storage_link' => file_exists($publicPath . '/storage'),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
        ];

        return Inertia::render('Admin/Tools/Index', [
            'systemInfo' => $systemInfo,
        ]);
    }

    public function execute(Request $request)
    {
        $this->checkAdmin();

        $action = $request->input('action');

        try {
            $output = match ($action) {
                // Cache
                'clear_cache' => $this->runCommands([
                    'cache:clear', 'config:clear', 'route:clear', 'view:clear',
                ]),
                'optimize_clear' => $this->runCommands(['optimize:clear']),

                // Optimization
                'optimize' => $this->runCommands(['optimize --no-interaction']),
                'config_cache' => $this->runCommands(['config:cache --no-interaction']),
                'route_cache' => $this->runCommands(['route:cache --no-interaction']),

                // Storage
                'storage_link' => $this->handleStorageLink(),
                'fix_storage_permissions' => $this->handleStoragePermissions(),
                'clear_logs' => $this->handleClearLogs(),

                // Database
                'migrate' => $this->runCommands(['migrate --force --no-interaction']),
                'migrate_repair' => $this->runCommands(['migrate:repair --no-interaction']),
                'migrate_optimize' => $this->runCommands(['migrate --force --no-interaction', 'optimize --no-interaction']),
                'db_seed' => $this->runCommands(['db:seed --force --no-interaction']),
                'db_seed_users' => $this->runSeeder('Database\\Seeders\\SuperAdminSeeder'),
                'db_seed_domain' => $this->runSeeder('Database\\Seeders\\DomainPriceSeeder'),
                'db_seed_layanan' => $this->runSeeder('Database\\Seeders\\ServicePlanSeeder'),
                'db_seed_hosting' => $this->runSeeder('Database\\Seeders\\HostingPlanSeeder'),
                'db_seed_demo' => $this->runSeeder('Database\\Seeders\\DemoWebsiteSeeder'),
                'db_seed_websites' => $this->runSeeder('Database\\Seeders\\ManageWebsiteSeeder'),
                'migrate_fresh' => $this->runCommands(['migrate:fresh --force --no-interaction']),

                // Maintenance
                'maintenance_down' => $this->runCommands(['down --secret=admin-secret']),
                'maintenance_up' => $this->runCommands(['up']),

                // Security
                'key_generate' => $this->runCommands(['key:generate --force']),

                // Environment
                'check_env' => $this->handleCheckEnv(),
                'show_env' => $this->handleShowEnv(),
                'backup_env' => $this->handleBackupEnv(),

                // Diagnostics
                'health_check' => $this->handleHealthCheck(),
                'debug_500_error' => $this->handleDebug500(),
                'debug_hosting_structure' => $this->handleDebugHosting(),
                'disk_space' => $this->handleDiskSpace(),

                default => throw new \Exception("Unknown action: {$action}"),
            };

            return redirect()->back()->with('tool_result', [
                'success' => true,
                'output' => $output,
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('tool_result', [
                'success' => false,
                'output' => 'Error: ' . $e->getMessage(),
            ]);
        }
    }

    private function runCommands(array $commands): string
    {
        $outputs = [];
        foreach ($commands as $command) {
            Artisan::call($command);
            $result = trim(Artisan::output());
            $outputs[] = "\$ php artisan {$command}\n" . ($result ?: '(no output)');
        }
        return implode("\n\n", $outputs);
    }

    private function runSeeder(string $class): string
    {
        Artisan::call('db:seed', [
            '--class' => $class,
            '--force' => true,
        ]);

        return "\$ php artisan db:seed --class={$class}\n" . trim(Artisan::output());
    }

    private function handleStorageLink(): string
    {
        $publicPath = public_path();
        $target = base_path('storage/app/public');
        $link = $publicPath . '/storage';

        $output = "Storage Link Management:\n";
        $output .= "Public Directory: {$publicPath}\n";
        $output .= "Link Path: {$link}\n";
        $output .= "Target: {$target}\n";

        if (!is_dir($target)) {
            return $output . "\n❌ Target directory does not exist: {$target}";
        }

        if (file_exists($link)) {
            if (is_link($link)) {
                unlink($link);
            } elseif (is_dir($link)) {
                $this->rmdirRecursive($link);
            }
            $output .= "Removed existing link/directory.\n";
        }

        try {
            Artisan::call('storage:link');
            $output .= "\n✅ " . trim(Artisan::output());
        } catch (\Exception $e) {
            $output .= "\n❌ Failed: " . $e->getMessage();
        }

        return $output;
    }

    private function handleStoragePermissions(): string
    {
        $laravelRoot = base_path();
        $dirs = ['storage', 'storage/app', 'storage/logs', 'storage/framework', 'bootstrap/cache'];
        $output = "Fixing Storage Permissions:\n";
        $fixed = 0;

        foreach ($dirs as $dir) {
            $path = $laravelRoot . '/' . $dir;
            if (is_dir($path)) {
                if (@chmod($path, 0755)) {
                    $output .= "✅ {$dir}\n";
                    $fixed++;
                } else {
                    $output .= "⚠️ Cannot chmod {$dir} (may need manual fix)\n";
                }
            }
        }

        $output .= "\nFixed {$fixed} directories.";
        return $output;
    }

    private function handleClearLogs(): string
    {
        $logPath = base_path('storage/logs');
        if (!is_dir($logPath)) {
            return 'Log directory not found.';
        }

        $files = glob($logPath . '/*.log');
        $count = 0;
        foreach ($files as $file) {
            if (@unlink($file)) {
                $count++;
            }
        }

        return "Deleted {$count} log files.";
    }

    private function handleCheckEnv(): string
    {
        $envPath = base_path('.env');
        $examplePath = base_path('.env.example');

        $output = "Environment File Check:\n";
        $output .= '.env exists: ' . (file_exists($envPath) ? 'Yes' : 'No') . "\n";
        $output .= '.env.example exists: ' . (file_exists($examplePath) ? 'Yes' : 'No') . "\n";

        if (file_exists($envPath)) {
            $envContent = file_get_contents($envPath);
            $requiredVars = ['APP_KEY', 'DB_CONNECTION', 'DB_DATABASE'];
            foreach ($requiredVars as $var) {
                $output .= "{$var}: " . (strpos($envContent, $var . '=') !== false ? 'Set' : 'Missing') . "\n";
            }
        }

        return $output;
    }

    private function handleShowEnv(): string
    {
        $envPath = base_path('.env');
        if (!file_exists($envPath)) {
            return '❌ Environment file not found';
        }

        $envContent = file_get_contents($envPath);
        $masked = preg_replace('/(APP_KEY|DB_PASSWORD|.*_SECRET|.*_TOKEN|.*_KEY)=(.+)/i', '$1=***MASKED***', $envContent);
        return "Environment File (sensitive values masked):\n\n" . $masked;
    }

    private function handleBackupEnv(): string
    {
        $envPath = base_path('.env');
        $backupPath = base_path('.env.backup.' . date('Y-m-d_H-i-s'));

        if (!file_exists($envPath)) {
            return '❌ Environment file not found';
        }

        if (copy($envPath, $backupPath)) {
            return '✅ Backed up to: ' . basename($backupPath);
        }

        return '❌ Failed to backup';
    }

    private function handleHealthCheck(): string
    {
        $output = "System Health Check:\n\n";

        $output .= "🔧 PHP:\n";
        $output .= 'Version: ' . PHP_VERSION . "\n";
        $output .= 'Memory Limit: ' . ini_get('memory_limit') . "\n";
        $output .= 'Max Execution: ' . ini_get('max_execution_time') . "s\n";

        $output .= "\n🔌 Extensions:\n";
        foreach (['pdo', 'mbstring', 'tokenizer', 'json', 'openssl', 'curl'] as $ext) {
            $output .= "{$ext}: " . (extension_loaded($ext) ? '✅' : '❌') . "\n";
        }

        $output .= "\n📁 Critical Files:\n";
        foreach (['artisan', 'composer.json', '.env', 'bootstrap/app.php'] as $f) {
            $output .= "{$f}: " . (file_exists(base_path($f)) ? '✅' : '❌') . "\n";
        }

        $output .= "\n📂 Directories:\n";
        foreach (['storage', 'storage/logs', 'storage/framework', 'bootstrap/cache'] as $d) {
            $path = base_path($d);
            $exists = is_dir($path);
            $writable = $exists && is_writable($path);
            $output .= "{$d}: " . ($exists ? '✅' : '❌');
            if ($exists) {
                $output .= $writable ? ' (Writable)' : ' (Not Writable)';
            }
            $output .= "\n";
        }

        return $output;
    }

    private function handleDebug500(): string
    {
        $output = "HTTP 500 Error Diagnostic:\n\n";

        $output .= "1. PHP Environment:\n";
        $output .= '   Version: ' . PHP_VERSION . "\n";

        foreach (['openssl', 'pdo', 'mbstring', 'tokenizer', 'json', 'curl'] as $ext) {
            $output .= "   {$ext}: " . (extension_loaded($ext) ? '✅' : '❌ Missing') . "\n";
        }

        $output .= "\n2. Quick Fixes:\n";
        $output .= "   1. Generate APP_KEY\n";
        $output .= "   2. Fix Storage Permissions\n";
        $output .= "   3. Clear All Cache\n";

        return $output;
    }

    private function handleDebugHosting(): string
    {
        $output = "Hosting Structure:\n";
        $output .= 'Base Path: ' . base_path() . "\n";
        $output .= 'Public Path: ' . public_path() . "\n";
        $output .= 'Storage Path: ' . storage_path() . "\n";
        $output .= 'Document Root: ' . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "\n";

        return $output;
    }

    private function handleDiskSpace(): string
    {
        $laravelRoot = base_path();

        $output = "Disk Space:\n\n";
        $output .= "Directories:\n";
        $output .= 'Base: ' . $this->formatBytes($this->getDirSize($laravelRoot)) . "\n";
        $output .= 'Storage: ' . $this->formatBytes($this->getDirSize($laravelRoot . '/storage')) . "\n";

        $free = disk_free_space($laravelRoot);
        $total = disk_total_space($laravelRoot);
        $used = $total - $free;

        $output .= "\nDisk:\n";
        $output .= 'Used: ' . $this->formatBytes($used) . "\n";
        $output .= 'Free: ' . $this->formatBytes($free) . "\n";
        $output .= 'Total: ' . $this->formatBytes($total) . "\n";
        $output .= 'Usage: ' . round(($used / $total) * 100, 2) . "%\n";

        return $output;
    }

    private function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        return round($bytes / (1024 ** $pow), $precision) . ' ' . $units[$pow];
    }

    private function getDirSize($dir): int
    {
        if (!is_dir($dir)) return 0;
        $size = 0;
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)) as $file) {
            $size += $file->getSize();
        }
        return $size;
    }

    private function rmdirRecursive($dir): void
    {
        if (!is_dir($dir)) return;
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->rmdirRecursive($path) : unlink($path);
        }
        rmdir($dir);
    }
}
