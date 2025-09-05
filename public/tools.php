<?php
/**
 * Emergency Admin Tools
 * 
 * This file provides basic admin functions when the main application is not accessible.
 * Access: http://yoursite.com/admin-tools.php
 * 
 * Security: This file should be removed or secured in production.
 * You can add HTTP basic auth or IP restrictions for additional security.
 */

// Security check - only allow admin access
session_start();
$admin_password = 'admin123'; // Change this password!

if (!isset($_SESSION['admin_authenticated'])) {
    if (isset($_POST['password']) && $_POST['password'] === $admin_password) {
        $_SESSION['admin_authenticated'] = true;
    } else {
        showLoginForm();
        exit;
    }
}

// Get the Laravel root directory - adjust for server structure
$currentDir = __DIR__;

// Try multiple approaches to find Laravel directory
$possiblePaths = [
    // Production: public_html/ and laravel/ are siblings
    dirname($currentDir) . '/laravel',
    // Alternative production structure
    dirname(dirname($currentDir)) . '/laravel', 
    // Development: normal Laravel structure
    dirname(__DIR__),
    // If admin-tools.php is in domain root
    $currentDir . '/laravel',
];

$laravelRoot = null;
foreach ($possiblePaths as $path) {
    if (is_dir($path) && file_exists($path . '/artisan')) {
        $laravelRoot = $path;
        break;
    }
}

// If still not found, use default assumption
if (!$laravelRoot) {
    $laravelRoot = dirname($currentDir) . '/laravel';
}

// Verify Laravel directory exists
if (!is_dir($laravelRoot) || !file_exists($laravelRoot . '/artisan')) {
    $debugInfo = "Laravel directory not found. Debug info:\n";
    $debugInfo .= "Current directory: " . $currentDir . "\n";
    $debugInfo .= "Tried paths:\n";
    foreach ($possiblePaths as $i => $path) {
        $debugInfo .= "  " . ($i + 1) . ". " . $path . " - " . (is_dir($path) ? "Directory exists" : "Directory missing") . " - " . (file_exists($path . '/artisan') ? "Artisan found" : "Artisan missing") . "\n";
    }
    $debugInfo .= "Final Laravel root: " . $laravelRoot . "\n";
    $debugInfo .= "Directory listing of parent: " . print_r(scandir(dirname($currentDir)), true);
    die('<pre>' . $debugInfo . '</pre>');
}

chdir($laravelRoot);

$output = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    try {
        // Add debug info and clean up any wrong storage links
        if ($action === 'storage_link') {
            $output .= "Debug Info:\n";
            $output .= "Current Dir: " . $currentDir . "\n";
            $output .= "Laravel Root: " . $laravelRoot . "\n";
            $output .= "Working Directory: " . getcwd() . "\n";
            $output .= "Server Structure: Production (admin-tools.php in public_html)\n";
            $output .= "PHP Version: " . PHP_VERSION . "\n";
            $output .= "PHP Binary: " . (defined('PHP_BINARY') ? PHP_BINARY : 'Not defined') . "\n";
            $output .= "shell_exec available: " . (function_exists('shell_exec') ? 'Yes' : 'No') . "\n";
            
            // Clean up wrong storage link in laravel/public if it exists
            $wrongStoragePath = $laravelRoot . '/public/storage';
            if (file_exists($wrongStoragePath)) {
                $output .= "\n⚠️ Removing wrong storage link at: " . $wrongStoragePath . "\n";
                if (is_link($wrongStoragePath)) {
                    unlink($wrongStoragePath);
                    $output .= "✅ Wrong symlink removed\n";
                } else {
                    $output .= "⚠️ Wrong storage is not a symlink, manual cleanup needed\n";
                }
            }
            
            $checkPublicPath = $currentDir; // public_html directory
            $checkStoragePath = $checkPublicPath . '/storage';
            
            $output .= "Public Path: " . $checkPublicPath . "\n";
            $output .= "Storage Target: " . $laravelRoot . '/storage/app/public' . "\n";
            $output .= "Storage Link Path: " . $checkStoragePath . "\n";
            $output .= "Storage app/public exists: " . (is_dir($laravelRoot . '/storage/app/public') ? 'Yes' : 'No') . "\n";
            $output .= "Public/storage exists: " . (file_exists($checkStoragePath) ? 'Yes' : 'No') . "\n\n";
        }
        
        switch ($action) {
            case 'clear_cache':
                $output = executeCommand('php artisan cache:clear');
                $output .= "\n" . executeCommand('php artisan config:clear');
                $output .= "\n" . executeCommand('php artisan route:clear');
                $output .= "\n" . executeCommand('php artisan view:clear');
                break;
                
            case 'optimize_clear':
                $output = executeCommand('php artisan optimize:clear');
                break;
                
            case 'optimize':
                $output = executeCommand('php artisan optimize');
                break;
                
            case 'storage_link':
                // Determine the correct public path structure
                // admin-tools.php is in public_html, so storage link should be created here
                $publicPath = $currentDir; // Same directory as admin-tools.php
                $storagePath = $publicPath . '/storage';
                $storageTarget = $laravelRoot . '/storage/app/public';
                
                $output .= "Debug Storage Link Info:\n";
                $output .= "Public Path: " . $publicPath . "\n";
                $output .= "Storage Link Path: " . $storagePath . "\n";
                $output .= "Storage Target: " . $storageTarget . "\n";
                $output .= "Target exists: " . (is_dir($storageTarget) ? 'Yes' : 'No') . "\n";
                $output .= "Link exists: " . (file_exists($storagePath) ? 'Yes' : 'No') . "\n";
                
                if (file_exists($storagePath)) {
                    if (is_link($storagePath)) {
                        $target = readlink($storagePath);
                        $output .= "Current link target: " . $target . "\n";
                        $output .= "Target accessible: " . (is_readable($target) ? 'Yes' : 'No') . "\n";
                        
                        // Check if link points to wrong location
                        if ($target !== $storageTarget) {
                            $output .= "\n❌ Link points to wrong location. Recreating...\n";
                            unlink($storagePath);
                            if (symlink($storageTarget, $storagePath)) {
                                $output .= "✅ Link recreated successfully!\n";
                            } else {
                                $output .= "❌ Failed to recreate link\n";
                            }
                        } else {
                            $output .= "\n✅ Storage link exists and points to correct location\n";
                            
                            // Check permissions
                            $perms = fileperms($storageTarget);
                            $output .= "Target permissions: " . decoct($perms & 0777) . "\n";
                        }
                    } else {
                        $output .= "\n⚠️ Storage exists but is not a symlink (directory/file)\n";
                        $output .= "Consider removing and recreating as symlink\n";
                    }
                } else {
                    // Create new storage link
                    if (!is_dir($storageTarget)) {
                        $output .= "\n❌ Target directory does not exist: " . $storageTarget;
                    } else {
                        $output .= "\n🔨 Creating storage link...\n";
                        
                        // Try artisan command first
                        $artisanOutput = executeCommand('php artisan storage:link');
                        $output .= "Artisan output: " . $artisanOutput . "\n";
                        
                        // Check if it worked
                        if (file_exists($storagePath)) {
                            $output .= "✅ Storage link created via artisan!\n";
                        } else {
                            // Try manual symlink
                            $output .= "Artisan failed, trying manual symlink...\n";
                            if (function_exists('symlink')) {
                                if (symlink($storageTarget, $storagePath)) {
                                    $output .= "✅ Manual symlink created!\n";
                                } else {
                                    $output .= "❌ Manual symlink failed\n";
                                }
                            } else {
                                $output .= "❌ symlink() function not available\n";
                            }
                        }
                    }
                }
                break;
                
            case 'migrate':
                $output = executeCommand('php artisan migrate --force');
                break;
                
            case 'migrate_fresh':
                $output = executeCommand('php artisan migrate:fresh --force');
                break;
                
            case 'config_cache':
                $output = executeCommand('php artisan config:cache');
                break;
                
            case 'route_cache':
                $output = executeCommand('php artisan route:cache');
                break;
                
            case 'maintenance_down':
                $output = executeCommand('php artisan down --secret=admin-secret');
                break;
                
            case 'maintenance_up':
                $output = executeCommand('php artisan up');
                break;
                
            case 'key_generate':
                $output = executeCommand('php artisan key:generate --force');
                break;
                
            case 'clear_logs':
                $logPath = $laravelRoot . '/storage/logs';
                if (is_dir($logPath)) {
                    $files = glob($logPath . '/*.log');
                    $count = 0;
                    foreach ($files as $file) {
                        if (unlink($file)) {
                            $count++;
                        }
                    }
                    $output = "Deleted {$count} log files";
                } else {
                    $output = "Log directory not found";
                }
                break;
                
            case 'cleanup_storage':
                $wrongStoragePath = $laravelRoot . '/public/storage';
                $correctStoragePath = $currentDir . '/storage';
                $storageTarget = $laravelRoot . '/storage/app/public';
                
                $output = "Storage Cleanup:\n";
                
                // Remove wrong storage link in laravel/public
                if (file_exists($wrongStoragePath)) {
                    $output .= "Removing wrong storage link: " . $wrongStoragePath . "\n";
                    if (is_link($wrongStoragePath)) {
                        unlink($wrongStoragePath);
                        $output .= "✅ Wrong symlink removed\n";
                    } else {
                        $output .= "⚠️ Not a symlink, skipping\n";
                    }
                }
                
                // Handle correct storage path in public_html
                if (file_exists($correctStoragePath)) {
                    if (is_link($correctStoragePath)) {
                        $target = readlink($correctStoragePath);
                        $output .= "✅ Storage is already a symlink pointing to: " . $target . "\n";
                        if ($target !== $storageTarget) {
                            $output .= "⚠️ Symlink points to wrong target, fixing...\n";
                            unlink($correctStoragePath);
                            symlink($storageTarget, $correctStoragePath);
                            $output .= "✅ Symlink recreated with correct target\n";
                        }
                    } else {
                        // Remove directory and create symlink
                        $output .= "⚠️ Storage exists as directory/file, not symlink. Removing...\n";
                        if (is_dir($correctStoragePath)) {
                            // Remove directory recursively
                            removeDirectory($correctStoragePath);
                            $output .= "✅ Directory removed\n";
                        } else {
                            unlink($correctStoragePath);
                            $output .= "✅ File removed\n";
                        }
                        
                        // Create symlink
                        if (symlink($storageTarget, $correctStoragePath)) {
                            $output .= "✅ Created correct storage symlink\n";
                        } else {
                            $output .= "❌ Failed to create storage symlink\n";
                        }
                    }
                } else {
                    // Create new symlink
                    if (symlink($storageTarget, $correctStoragePath)) {
                        $output .= "✅ Created new storage symlink at: " . $correctStoragePath . "\n";
                    } else {
                        $output .= "❌ Failed to create storage symlink\n";
                    }
                }
                
                // Verify the symlink works
                if (is_link($correctStoragePath) && is_readable($correctStoragePath)) {
                    $output .= "✅ Storage symlink is working and readable\n";
                } else {
                    $output .= "❌ Storage symlink may have issues\n";
                }
                break;
                
            case 'fix_storage_permissions':
                $publicPath = $currentDir;
                $storagePath = $publicPath . '/storage';
                $storageTarget = $laravelRoot . '/storage/app/public';
                
                $output = "Fixing Storage Permissions:\n";
                
                // Set proper permissions for storage target
                if (is_dir($storageTarget)) {
                    chmod($storageTarget, 0755);
                    $output .= "✅ Set target directory permissions to 755\n";
                    
                    // Set permissions for files inside
                    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($storageTarget));
                    $count = 0;
                    foreach ($iterator as $file) {
                        if ($file->isFile()) {
                            chmod($file->getPathname(), 0644);
                            $count++;
                        } elseif ($file->isDir() && !in_array($file->getFilename(), ['.', '..'])) {
                            chmod($file->getPathname(), 0755);
                        }
                    }
                    $output .= "✅ Fixed permissions for {$count} files\n";
                } else {
                    $output .= "❌ Storage target directory not found\n";
                }
                break;
                
            case 'backup_env':
                $envPath = $laravelRoot . '/.env';
                $backupPath = $laravelRoot . '/.env.backup.' . date('Y-m-d_H-i-s');
                
                if (file_exists($envPath)) {
                    if (copy($envPath, $backupPath)) {
                        $output = "✅ Environment file backed up to: " . basename($backupPath);
                    } else {
                        $output = "❌ Failed to backup environment file";
                    }
                } else {
                    $output = "❌ Environment file not found";
                }
                break;
                
            case 'check_env':
                $envPath = $laravelRoot . '/.env';
                $envExamplePath = $laravelRoot . '/.env.example';
                
                $output = "Environment File Check:\n";
                $output .= ".env exists: " . (file_exists($envPath) ? 'Yes' : 'No') . "\n";
                $output .= ".env.example exists: " . (file_exists($envExamplePath) ? 'Yes' : 'No') . "\n";
                
                if (file_exists($envPath)) {
                    $envSize = filesize($envPath);
                    $output .= ".env size: " . $envSize . " bytes\n";
                    $output .= ".env modified: " . date('Y-m-d H:i:s', filemtime($envPath)) . "\n";
                    
                    // Check key variables
                    $envContent = file_get_contents($envPath);
                    $requiredVars = ['APP_KEY', 'DB_CONNECTION', 'DB_DATABASE'];
                    foreach ($requiredVars as $var) {
                        $exists = strpos($envContent, $var . '=') !== false;
                        $output .= "{$var}: " . ($exists ? 'Set' : 'Missing') . "\n";
                    }
                }
                break;
                
            case 'health_check':
                $output = "System Health Check:\n\n";
                
                // PHP Info
                $output .= "🔧 PHP Information:\n";
                $output .= "PHP Version: " . PHP_VERSION . "\n";
                $output .= "Memory Limit: " . ini_get('memory_limit') . "\n";
                $output .= "Max Execution Time: " . ini_get('max_execution_time') . "s\n";
                $output .= "Upload Max Size: " . ini_get('upload_max_filesize') . "\n";
                $output .= "Post Max Size: " . ini_get('post_max_size') . "\n";
                
                // Extensions
                $output .= "\n🔌 Extensions:\n";
                $requiredExtensions = ['pdo', 'mbstring', 'tokenizer', 'json', 'openssl', 'curl'];
                foreach ($requiredExtensions as $ext) {
                    $loaded = extension_loaded($ext);
                    $output .= "{$ext}: " . ($loaded ? '✅ Loaded' : '❌ Missing') . "\n";
                }
                
                // Laravel Files
                $output .= "\n📁 Laravel Files:\n";
                $files = ['artisan', 'composer.json', '.env', 'bootstrap/app.php'];
                foreach ($files as $file) {
                    $exists = file_exists($laravelRoot . '/' . $file);
                    $output .= "{$file}: " . ($exists ? '✅ Exists' : '❌ Missing') . "\n";
                }
                
                // Directories
                $output .= "\n📂 Directories:\n";
                $dirs = ['storage', 'storage/app', 'storage/logs', 'storage/framework', 'bootstrap/cache'];
                foreach ($dirs as $dir) {
                    $path = $laravelRoot . '/' . $dir;
                    $exists = is_dir($path);
                    $writable = $exists ? is_writable($path) : false;
                    $output .= "{$dir}: " . ($exists ? '✅ Exists' : '❌ Missing') . 
                              ($writable ? ' (Writable)' : ($exists ? ' (Not Writable)' : '')) . "\n";
                }
                break;
                
            case 'composer_status':
                $output = "Composer Status:\n";
                $output .= executeCommand('composer --version');
                $output .= "\n\nComposer Dependencies:\n";
                $output .= executeCommand('composer show --installed');
                break;
                
            case 'queue_status':
                $output = "Queue Status:\n";
                $output .= executeCommand('php artisan queue:work --stop-when-empty --timeout=10');
                break;
                
            case 'create_symlinks':
                $output = "Creating Symlinks:\n";
                
                // Storage link
                $publicPath = $currentDir;
                $storagePath = $publicPath . '/storage';
                $storageTarget = $laravelRoot . '/storage/app/public';
                
                if (!file_exists($storagePath) && is_dir($storageTarget)) {
                    if (symlink($storageTarget, $storagePath)) {
                        $output .= "✅ Storage symlink created\n";
                    } else {
                        $output .= "❌ Failed to create storage symlink\n";
                    }
                } else {
                    $output .= "⚠️ Storage symlink already exists or target missing\n";
                }
                
                // Check for other common symlinks needed
                $output .= "\nSymlink Status:\n";
                $output .= "Storage: " . (is_link($storagePath) ? '✅ Symlink' : '❌ Not a symlink') . "\n";
                break;
                
            case 'clear_all_cache':
                $output = "Clearing All Cache and Optimization:\n";
                $output .= executeCommand('php artisan cache:clear');
                $output .= "\n" . executeCommand('php artisan config:clear');
                $output .= "\n" . executeCommand('php artisan route:clear');
                $output .= "\n" . executeCommand('php artisan view:clear');
                $output .= "\n" . executeCommand('php artisan optimize:clear');
                break;
                
            case 'show_env':
                $envPath = $laravelRoot . '/.env';
                if (file_exists($envPath)) {
                    $envContent = file_get_contents($envPath);
                    // Mask sensitive values
                    $maskedContent = preg_replace('/(APP_KEY|DB_PASSWORD|.*_SECRET|.*_TOKEN|.*_KEY)=(.+)/i', '$1=***MASKED***', $envContent);
                    $output = "Environment File Content (sensitive values masked):\n\n" . $maskedContent;
                } else {
                    $output = "❌ Environment file not found";
                }
                break;
                
            case 'disk_space':
                $output = "Disk Space Information:\n";
                
                // Laravel directory size
                $laravelSize = getDirSize($laravelRoot);
                $output .= "Laravel directory: " . formatBytes($laravelSize) . "\n";
                
                // Storage directory size
                $storageSize = getDirSize($laravelRoot . '/storage');
                $output .= "Storage directory: " . formatBytes($storageSize) . "\n";
                
                // Log files size
                $logsSize = getDirSize($laravelRoot . '/storage/logs');
                $output .= "Log files: " . formatBytes($logsSize) . "\n";
                
                // Available disk space
                $freeSpace = disk_free_space($laravelRoot);
                $totalSpace = disk_total_space($laravelRoot);
                $usedSpace = $totalSpace - $freeSpace;
                
                $output .= "\nDisk Usage:\n";
                $output .= "Used: " . formatBytes($usedSpace) . "\n";
                $output .= "Free: " . formatBytes($freeSpace) . "\n";
                $output .= "Total: " . formatBytes($totalSpace) . "\n";
                $output .= "Usage: " . round(($usedSpace / $totalSpace) * 100, 2) . "%\n";
                break;
                
            case 'build_production':
                $output = "Building Production Package:\n\n";
                
                // Create dist directory if it doesn't exist
                $distDir = $laravelRoot . '/dist';
                if (!is_dir($distDir)) {
                    mkdir($distDir, 0755, true);
                    $output .= "✅ Created dist directory\n";
                } else {
                    $output .= "📁 Dist directory exists\n";
                }
                
                // Clean up previous build
                $zipPath = $distDir . '/naokipos.zip';
                if (file_exists($zipPath)) {
                    unlink($zipPath);
                    $output .= "🗑️ Removed previous naokipos.zip\n";
                }
                
                // Run npm build first
                $output .= "🔨 Building frontend assets...\n";
                $buildOutput = executeCommand('npm run build');
                $output .= "Frontend build output:\n" . $buildOutput . "\n\n";
                
                // Clear and optimize Laravel
                $output .= "🧹 Clearing Laravel cache...\n";
                $output .= executeCommand('php artisan cache:clear') . "\n";
                $output .= executeCommand('php artisan config:clear') . "\n";
                $output .= executeCommand('php artisan route:clear') . "\n";
                $output .= executeCommand('php artisan view:clear') . "\n";
                
                $output .= "\n🚀 Optimizing Laravel...\n";
                $output .= executeCommand('php artisan config:cache') . "\n";
                $output .= executeCommand('php artisan route:cache') . "\n";
                $output .= executeCommand('php artisan view:cache') . "\n";
                
                // Create production zip
                $output .= "\n📦 Creating production package...\n";
                
                // Initialize zip
                $zip = new ZipArchive();
                if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
                    $output .= "❌ Cannot create zip file\n";
                    break;
                }
                
                // Files and directories to exclude from production build
                $excludePaths = [
                    'storage/app',
                    'storage/logs',
                    'storage/framework/cache',
                    'storage/framework/sessions',
                    'storage/framework/views',
                    'node_modules',
                    '.git',
                    '.env',
                    '.env.example',
                    'tests',
                    'phpunit.xml',
                    'vite.config.js',
                    'package.json',
                    'package-lock.json',
                    'composer.json',
                    'composer.lock',
                    'dist'
                ];
                
                // Keep essential storage structure but empty
                $keepEmptyDirs = [
                    'storage/app/public',
                    'storage/framework/cache/data',
                    'storage/framework/sessions',
                    'storage/framework/views',
                    'storage/logs'
                ];
                
                // Add files to zip
                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($laravelRoot, RecursiveDirectoryIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::SELF_FIRST
                );
                
                $filesAdded = 0;
                foreach ($iterator as $file) {
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($laravelRoot) + 1);
                    
                    // Skip excluded paths
                    $skip = false;
                    foreach ($excludePaths as $excludePath) {
                        if (strpos($relativePath, $excludePath) === 0) {
                            $skip = true;
                            break;
                        }
                    }
                    
                    if ($skip) continue;
                    
                    if ($file->isDir()) {
                        $zip->addEmptyDir($relativePath);
                    } else {
                        $zip->addFile($filePath, $relativePath);
                        $filesAdded++;
                    }
                }
                
                // Add empty storage directories
                foreach ($keepEmptyDirs as $emptyDir) {
                    $zip->addEmptyDir($emptyDir);
                    // Add .gitkeep to preserve directory
                    $zip->addFromString($emptyDir . '/.gitkeep', '');
                }
                
                // Add production .env template
                $prodEnvContent = "APP_NAME=NaokiPOS\nAPP_ENV=production\nAPP_KEY=\nAPP_DEBUG=false\nAPP_URL=http://localhost\n\nLOG_CHANNEL=stack\nLOG_DEPRECATIONS_CHANNEL=null\nLOG_LEVEL=error\n\nDB_CONNECTION=sqlite\nDB_DATABASE=database/database.sqlite\n\nBROADCAST_DRIVER=log\nCACHE_DRIVER=file\nFILESYSTEM_DISK=local\nQUEUE_CONNECTION=sync\nSESSION_DRIVER=file\nSESSION_LIFETIME=120\n\nREDIS_HOST=127.0.0.1\nREDIS_PASSWORD=null\nREDIS_PORT=6379\n\nMAIL_MAILER=smtp\nMAIL_HOST=mailpit\nMAIL_PORT=1025\nMAIL_USERNAME=null\nMAIL_PASSWORD=null\nMAIL_ENCRYPTION=null\nMAIL_FROM_ADDRESS=\"hello@example.com\"\nMAIL_FROM_NAME=\"\${APP_NAME}\"\n";
                
                $zip->addFromString('.env.production', $prodEnvContent);
                
                $zip->close();
                
                $zipSize = filesize($zipPath);
                $output .= "✅ Production package created successfully!\n";
                $output .= "📍 Location: dist/naokipos.zip\n";
                $output .= "📊 Size: " . formatBytes($zipSize) . "\n";
                $output .= "📁 Files included: " . $filesAdded . "\n";
                $output .= "🚫 Storage directory excluded from build\n";
                $output .= "\n📋 Deployment Instructions:\n";
                $output .= "1. Extract naokipos.zip to production server\n";
                $output .= "2. Rename .env.production to .env and configure\n";
                $output .= "3. Run: php artisan key:generate\n";
                $output .= "4. Run: php artisan storage:link\n";
                $output .= "5. Set proper permissions on storage and bootstrap/cache\n";
                break;
                
            case 'logout':
                session_destroy();
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit;
                break;
                
            default:
                $error = "Unknown action: " . htmlspecialchars($action);
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

function executeCommand($command) {
    // Check if shell_exec is available
    if (!function_exists('shell_exec')) {
        return 'Error: shell_exec function is disabled on this server';
    }
    
    // Add full path to PHP if needed
    if (strpos($command, 'php ') === 0) {
        // Try multiple methods to find PHP executable
        $phpPath = null;
        
        // Method 1: Use PHP_BINARY constant (most reliable)
        if (defined('PHP_BINARY') && is_executable(PHP_BINARY)) {
            $phpPath = PHP_BINARY;
        }
        // Method 2: Try common paths
        elseif (is_executable('/usr/bin/php')) {
            $phpPath = '/usr/bin/php';
        }
        elseif (is_executable('/usr/local/bin/php')) {
            $phpPath = '/usr/local/bin/php';
        }
        // Method 3: Use which/where command (if available)
        else {
            $which = trim(shell_exec('which php 2>/dev/null') ?: '');
            if ($which && is_executable($which)) {
                $phpPath = $which;
            }
        }
        
        if ($phpPath) {
            $command = str_replace('php ', escapeshellarg($phpPath) . ' ', $command);
        } else {
            return 'Error: Could not find PHP executable. Tried PHP_BINARY, /usr/bin/php, /usr/local/bin/php';
        }
    }
    
    $output = shell_exec($command . ' 2>&1');
    return $output ?: 'Command executed (no output)';
}

function removeDirectory($dir) {
    if (!is_dir($dir)) {
        return false;
    }
    
    $files = array_diff(scandir($dir), array('.', '..'));
    foreach ($files as $file) {
        $path = $dir . '/' . $file;
        if (is_dir($path)) {
            removeDirectory($path);
        } else {
            unlink($path);
        }
    }
    return rmdir($dir);
}

function getDirSize($dir) {
    if (!is_dir($dir)) {
        return 0;
    }
    
    $size = 0;
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS));
    foreach ($files as $file) {
        $size += $file->getSize();
    }
    return $size;
}

function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}

function showLoginForm() {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Neural Admin Tools - Authentication</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Inter:wght@400;500;600&display=swap');
            
            :root {
                --bg-primary: #0f172a;
                --bg-secondary: #1e293b;
                --text-primary: #e2e8f0;
                --text-secondary: #94a3b8;
                --accent-primary: #8b5cf6;
                --accent-secondary: #a855f7;
                --warning: #f59e0b;
                --border: rgba(139, 92, 246, 0.3);
                --shadow: rgba(139, 92, 246, 0.1);
            }

            * {
                box-sizing: border-box;
                margin: 0;
                padding: 0;
            }

            body { 
                font-family: 'Inter', system-ui, -apple-system, sans-serif;
                background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
                color: var(--text-primary);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
                position: relative;
                overflow: hidden;
            }

            body::before {
                content: '';
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-image: 
                    radial-gradient(circle at 25% 25%, rgba(139, 92, 246, 0.15) 0%, transparent 50%),
                    radial-gradient(circle at 75% 75%, rgba(168, 85, 247, 0.15) 0%, transparent 50%);
                pointer-events: none;
                z-index: -1;
            }

            .login-container {
                background: rgba(15, 23, 42, 0.95);
                border: 1px solid var(--border);
                border-radius: 20px;
                backdrop-filter: blur(20px);
                box-shadow: 0 20px 40px var(--shadow);
                padding: 3rem;
                width: 100%;
                max-width: 450px;
                position: relative;
                overflow: hidden;
            }

            .login-container::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 2px;
                background: linear-gradient(90deg, var(--accent-primary), var(--accent-secondary));
                animation: headerGlow 3s ease-in-out infinite alternate;
            }

            @keyframes headerGlow {
                0% { opacity: 0.3; }
                100% { opacity: 1; }
            }

            .login-header {
                text-align: center;
                margin-bottom: 2rem;
            }

            .login-title { 
                font-family: 'Orbitron', monospace;
                font-weight: 900;
                font-size: 1.8rem;
                margin-bottom: 0.5rem;
                background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
                text-shadow: 0 0 30px rgba(139, 92, 246, 0.3);
            }

            .login-subtitle {
                font-size: 0.9rem;
                color: var(--text-secondary);
                text-transform: uppercase;
                letter-spacing: 0.1em;
                margin-bottom: 1rem;
            }

            .security-badge {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                background: rgba(139, 92, 246, 0.1);
                border: 1px solid rgba(139, 92, 246, 0.3);
                border-radius: 20px;
                padding: 0.5rem 1rem;
                font-size: 0.8rem;
                color: var(--accent-primary);
                margin-bottom: 2rem;
            }

            .pulse-dot {
                width: 8px;
                height: 8px;
                background: var(--accent-primary);
                border-radius: 50%;
                animation: pulse 2s infinite;
            }

            @keyframes pulse {
                0%, 100% { box-shadow: 0 0 5px var(--accent-primary); }
                50% { box-shadow: 0 0 15px var(--accent-primary), 0 0 25px var(--accent-primary); }
            }

            .warning { 
                background: rgba(245, 158, 11, 0.1);
                border: 1px solid rgba(245, 158, 11, 0.3);
                color: #fbbf24;
                padding: 1rem;
                border-radius: 12px;
                margin-bottom: 2rem;
                font-size: 0.9rem;
                position: relative;
                padding-left: 3rem;
            }

            .warning::before {
                content: '⚠';
                position: absolute;
                left: 1rem;
                top: 50%;
                transform: translateY(-50%);
                font-size: 1.2rem;
            }

            .form-group { 
                margin-bottom: 1.5rem; 
                position: relative;
            }

            label { 
                display: block;
                margin-bottom: 0.5rem;
                font-weight: 600;
                color: var(--text-primary);
                text-transform: uppercase;
                letter-spacing: 0.05em;
                font-size: 0.9rem;
            }

            .input-wrapper {
                position: relative;
            }

            .input-icon {
                position: absolute;
                left: 1rem;
                top: 50%;
                transform: translateY(-50%);
                font-size: 1.1rem;
                color: var(--text-secondary);
                z-index: 2;
            }

            input[type="password"] { 
                width: 100%;
                padding: 1rem 1rem 1rem 3rem;
                background: rgba(30, 41, 59, 0.8);
                border: 1px solid var(--border);
                border-radius: 12px;
                color: var(--text-primary);
                font-size: 1rem;
                transition: all 0.3s ease;
                backdrop-filter: blur(10px);
            }

            input[type="password"]:focus { 
                outline: none;
                border-color: rgba(139, 92, 246, 0.6);
                box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
                background: rgba(30, 41, 59, 1);
            }

            input[type="password"]::placeholder {
                color: var(--text-secondary);
                font-style: italic;
            }

            .login-button { 
                width: 100%;
                background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
                color: white;
                padding: 1rem 2rem;
                border: none;
                border-radius: 12px;
                cursor: pointer;
                font-weight: 600;
                font-size: 1rem;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                transition: all 0.3s ease;
                position: relative;
                overflow: hidden;
            }

            .login-button::before {
                content: '';
                position: absolute;
                top: 50%;
                left: 50%;
                width: 0;
                height: 0;
                background: rgba(255, 255, 255, 0.2);
                border-radius: 50%;
                transform: translate(-50%, -50%);
                transition: width 0.6s, height 0.6s;
            }

            .login-button:hover::before {
                width: 300px;
                height: 300px;
            }

            .login-button:hover { 
                transform: translateY(-2px);
                box-shadow: 0 8px 30px rgba(139, 92, 246, 0.4);
            }

            .login-footer {
                text-align: center;
                margin-top: 2rem;
                font-size: 0.8rem;
                color: var(--text-secondary);
                line-height: 1.5;
            }

            /* Mobile Responsiveness */
            @media (max-width: 480px) {
                .login-container {
                    padding: 2rem;
                    margin: 1rem;
                }
                
                .login-title {
                    font-size: 1.5rem;
                }
            }

            /* Matrix rain effect */
            .matrix-char {
                position: fixed;
                color: rgba(139, 92, 246, 0.1);
                font-family: 'Orbitron', monospace;
                font-size: 14px;
                pointer-events: none;
                z-index: -1;
                animation: matrixFall 3s linear infinite;
            }

            @keyframes matrixFall {
                0% {
                    transform: translateY(-20px);
                    opacity: 0;
                }
                10% {
                    opacity: 1;
                }
                90% {
                    opacity: 1;
                }
                100% {
                    transform: translateY(100vh);
                    opacity: 0;
                }
            }
        </style>
    </head>
    <body>
        <div class="login-container">
            <div class="login-header">
                <h1 class="login-title">NEURAL ACCESS</h1>
                <div class="login-subtitle">Security Protocol Activated</div>
                <div class="security-badge">
                    <div class="pulse-dot"></div>
                    AUTHENTICATION REQUIRED
                </div>
            </div>

            <div class="warning">
                <strong>Security Notice:</strong> This is an emergency administration interface. Remove this file in production environment.
            </div>

            <form method="post">
                <div class="form-group">
                    <label for="password">🔐 Access Key</label>
                    <div class="input-wrapper">
                        <div class="input-icon">🔑</div>
                        <input type="password" name="password" id="password" placeholder="Enter neural access key" required autocomplete="current-password">
                    </div>
                </div>
                <button type="submit" class="login-button">
                    🚀 INITIALIZE SYSTEM ACCESS
                </button>
            </form>

            <div class="login-footer">
                <p>🔒 Encrypted connection established</p>
                <p>⚡ System monitoring active</p>
            </div>
        </div>

        <script>
            // Matrix rain effect for login
            function createMatrixChar() {
                const chars = '01234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                const char = document.createElement('div');
                char.className = 'matrix-char';
                char.textContent = chars[Math.floor(Math.random() * chars.length)];
                char.style.left = Math.random() * 100 + '%';
                char.style.animationDelay = Math.random() * 2 + 's';
                char.style.animationDuration = (3 + Math.random() * 2) + 's';
                
                document.body.appendChild(char);
                
                setTimeout(() => {
                    char.remove();
                }, 5000);
            }

            // Create matrix effect
            setInterval(createMatrixChar, 200);

            // Focus on password field when page loads
            document.addEventListener('DOMContentLoaded', function() {
                const passwordField = document.getElementById('password');
                if (passwordField) {
                    passwordField.focus();
                }

                // Add keyboard shortcut (Enter to submit)
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' && e.target !== passwordField) {
                        passwordField.focus();
                    }
                });
            });

            // Add loading state on form submission
            document.querySelector('form').addEventListener('submit', function() {
                const button = document.querySelector('.login-button');
                button.textContent = '🔄 AUTHENTICATING...';
                button.disabled = true;
            });
        </script>
    </body>
    </html>
    <?php
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Emergency Admin Tools</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Inter:wght@400;500;600&display=swap');
        
        * {
            box-sizing: border-box;
        }

        :root {
            --bg-primary: #0f172a;
            --bg-secondary: #1e293b;
            --bg-tertiary: #334155;
            --text-primary: #e2e8f0;
            --text-secondary: #94a3b8;
            --accent-primary: #8b5cf6;
            --accent-secondary: #a855f7;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --border: rgba(139, 92, 246, 0.3);
            --shadow: rgba(139, 92, 246, 0.1);
        }

        body { 
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: var(--text-primary);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: 
                radial-gradient(circle at 25% 25%, rgba(139, 92, 246, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 75% 75%, rgba(168, 85, 247, 0.1) 0%, transparent 50%);
            pointer-events: none;
            z-index: -1;
        }

        .container { 
            max-width: 1400px;
            margin: 0 auto;
            background: rgba(15, 23, 42, 0.95);
            border: 1px solid var(--border);
            border-radius: 16px;
            backdrop-filter: blur(20px);
            box-shadow: 0 20px 40px var(--shadow);
            overflow: hidden;
        }

        .header {
            padding: 2rem;
            background: linear-gradient(135deg, rgba(139, 92, 246, 0.1) 0%, rgba(168, 85, 247, 0.05) 100%);
            border-bottom: 1px solid var(--border);
            position: relative;
        }

        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--accent-primary), transparent);
            animation: headerGlow 3s ease-in-out infinite alternate;
        }

        @keyframes headerGlow {
            0% { opacity: 0.3; }
            100% { opacity: 1; }
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .header h1 { 
            font-family: 'Orbitron', monospace;
            font-weight: 900;
            font-size: 2rem;
            margin: 0;
            background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 30px rgba(139, 92, 246, 0.3);
            position: relative;
        }

        .header h1::after {
            content: 'SYSTEM MATRIX';
            display: block;
            font-size: 0.4em;
            font-weight: 400;
            color: var(--text-secondary);
            margin-top: 0.5rem;
            letter-spacing: 0.2em;
            text-transform: uppercase;
        }

        .system-status {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-family: 'Orbitron', monospace;
            font-size: 0.9rem;
            color: var(--success);
        }

        .status-indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--success);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { box-shadow: 0 0 5px var(--success); }
            50% { box-shadow: 0 0 20px var(--success), 0 0 30px var(--success); }
        }

        .grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); 
            gap: 1.5rem; 
            padding: 2rem;
        }

        .card { 
            background: rgba(30, 41, 59, 0.8);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1.5rem;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, var(--accent-primary), var(--accent-secondary));
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .card:hover {
            border-color: rgba(139, 92, 246, 0.6);
            box-shadow: 0 10px 30px rgba(139, 92, 246, 0.2);
            transform: translateY(-2px);
        }

        .card:hover::before {
            transform: scaleX(1);
        }

        .card-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .card-icon {
            width: 2rem;
            height: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
            border-radius: 8px;
            font-size: 1rem;
        }

        .card h3 { 
            font-family: 'Orbitron', monospace;
            font-weight: 700;
            font-size: 1.1rem;
            margin: 0;
            color: var(--text-primary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .card-description {
            font-size: 0.85rem;
            color: var(--text-secondary);
            margin-bottom: 1rem;
            line-height: 1.4;
        }

        .button-group {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        button { 
            background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
            color: white;
            padding: 0.75rem 1rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            min-width: 120px;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }

        button::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        button:hover::before {
            width: 300px;
            height: 300px;
        }

        button:hover { 
            transform: translateY(-1px);
            box-shadow: 0 5px 20px rgba(139, 92, 246, 0.4);
        }

        button.danger { 
            background: linear-gradient(135deg, var(--danger), #dc2626);
        }

        button.danger:hover { 
            box-shadow: 0 5px 20px rgba(239, 68, 68, 0.4);
        }

        button.warning { 
            background: linear-gradient(135deg, var(--warning), #d97706);
            color: white;
        }

        button.warning:hover { 
            box-shadow: 0 5px 20px rgba(245, 158, 11, 0.4);
        }

        button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        .output { 
            background: rgba(15, 23, 42, 0.9);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1.5rem;
            white-space: pre-wrap;
            font-family: 'Fira Code', 'Courier New', monospace;
            margin: 1.5rem 0;
            max-height: 400px;
            overflow-y: auto;
            font-size: 0.9rem;
            line-height: 1.5;
            color: var(--text-primary);
            position: relative;
        }

        .output::before {
            content: 'SYSTEM OUTPUT';
            position: absolute;
            top: -0.5rem;
            left: 1rem;
            background: var(--bg-primary);
            padding: 0 0.5rem;
            font-size: 0.7rem;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        .error { 
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
        }

        .success { 
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #6ee7b7;
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
        }

        .warning-banner { 
            background: rgba(245, 158, 11, 0.1);
            border: 1px solid rgba(245, 158, 11, 0.3);
            color: #fbbf24;
            padding: 1rem;
            border-radius: 8px;
            margin: 1.5rem 2rem;
            position: relative;
        }

        .warning-banner::before {
            content: '⚠';
            position: absolute;
            left: -0.5rem;
            top: -0.5rem;
            width: 2rem;
            height: 2rem;
            background: var(--warning);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .system-info { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); 
            gap: 1rem; 
            margin: 1.5rem 2rem;
            background: rgba(30, 41, 59, 0.5);
            padding: 1.5rem;
            border-radius: 12px;
            border: 1px solid var(--border);
        }

        .info-item { 
            font-size: 0.9rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid rgba(139, 92, 246, 0.1);
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-item strong { 
            color: var(--text-primary);
            font-weight: 600;
        }

        .info-value {
            color: var(--accent-primary);
            font-weight: 500;
        }

        .logout { 
            background: linear-gradient(135deg, var(--danger), #dc2626);
            margin: 0;
        }

        /* Loading Animation */
        .loading {
            position: relative;
        }

        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 1rem;
            height: 1rem;
            margin: -0.5rem 0 0 -0.5rem;
            border: 2px solid transparent;
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .footer {
            padding: 2rem;
            border-top: 1px solid var(--border);
            background: rgba(15, 23, 42, 0.5);
            font-size: 0.85rem;
            color: var(--text-secondary);
            line-height: 1.6;
        }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            
            .grid {
                grid-template-columns: 1fr;
                padding: 1rem;
            }
            
            .header {
                padding: 1.5rem;
            }
            
            .header h1 {
                font-size: 1.5rem;
            }
            
            .button-group {
                justify-content: stretch;
            }
            
            button {
                flex: 1;
                min-width: auto;
            }
            
            .system-info {
                grid-template-columns: 1fr;
                margin: 1rem;
            }
        }

        /* Scrollbar Styling */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(30, 41, 59, 0.3);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--accent-primary);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--accent-secondary);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-content">
                <h1>NEURAL ADMIN TOOLS</h1>
                <div class="system-status">
                    <div class="status-indicator"></div>
                    SYSTEM ONLINE
                </div>
                <form method="post" style="margin: 0;">
                    <button type="submit" name="action" value="logout" class="danger logout">🚪 LOGOUT</button>
                </form>
            </div>
        </div>
        
        <div class="warning-banner">
            <strong>Warning:</strong> This is an emergency administration tool. Remove this file in production or restrict access via IP/authentication.
        </div>

        <div class="system-info">
            <div class="info-item">
                <strong>🔧 PHP Version:</strong> 
                <span class="info-value"><?php echo PHP_VERSION; ?></span>
            </div>
            <div class="info-item">
                <strong>📁 Laravel Root:</strong> 
                <span class="info-value"><?php echo basename($laravelRoot); ?></span>
            </div>
            <div class="info-item">
                <strong>🔗 Storage Link:</strong> 
                <span class="info-value"><?php echo file_exists($laravelRoot . '/public/storage') ? '✅ Active' : '❌ Missing'; ?></span>
            </div>
            <div class="info-item">
                <strong>⚙️ Environment:</strong> 
                <span class="info-value"><?php echo file_exists($laravelRoot . '/.env') ? '✅ Configured' : '❌ Missing'; ?></span>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($output): ?>
            <div class="success">Command executed successfully!</div>
            <div class="output"><?php echo htmlspecialchars($output); ?></div>
        <?php endif; ?>

        <div class="grid">
            <div class="card">
                <div class="card-header">
                    <div class="card-icon">🧹</div>
                    <h3>Cache Matrix</h3>
                </div>
                <div class="card-description">Clear system cache and optimization files to resolve performance issues</div>
                <form method="post" class="button-group">
                    <button type="submit" name="action" value="clear_cache">🗑️ Clear Cache</button>
                    <button type="submit" name="action" value="optimize_clear">⚡ Clear Optimization</button>
                </form>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-icon">⚡</div>
                    <h3>Performance Engine</h3>
                </div>
                <div class="card-description">Optimize application performance and caching strategies</div>
                <form method="post" class="button-group">
                    <button type="submit" name="action" value="optimize">🚀 Optimize App</button>
                    <button type="submit" name="action" value="config_cache">⚙️ Cache Config</button>
                    <button type="submit" name="action" value="route_cache">🛤️ Cache Routes</button>
                </form>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-icon">💾</div>
                    <h3>Storage Matrix</h3>
                </div>
                <div class="card-description">Manage storage links, permissions, and file system operations</div>
                <form method="post" class="button-group">
                    <button type="submit" name="action" value="storage_link">🔍 Debug Storage</button>
                    <button type="submit" name="action" value="cleanup_storage" class="warning">🧽 Cleanup Storage</button>
                    <button type="submit" name="action" value="fix_storage_permissions" class="warning">🔒 Fix Permissions</button>
                    <button type="submit" name="action" value="clear_logs" class="warning">📝 Clear Logs</button>
                </form>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-icon">🗄️</div>
                    <h3>Database Core</h3>
                </div>
                <div class="card-description">Execute database migrations and schema operations</div>
                <form method="post" class="button-group">
                    <button type="submit" name="action" value="migrate">▶️ Run Migrations</button>
                    <button type="submit" name="action" value="migrate_fresh" class="danger" onclick="return confirm('⚠️ This will delete ALL data. Continue?')">🔄 Fresh Migration</button>
                </form>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-icon">🔧</div>
                    <h3>Maintenance Mode</h3>
                </div>
                <div class="card-description">Control application availability and maintenance status</div>
                <form method="post" class="button-group">
                    <button type="submit" name="action" value="maintenance_down" class="warning">🚫 Enable Maintenance</button>
                    <button type="submit" name="action" value="maintenance_up">✅ Disable Maintenance</button>
                </form>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-icon">🔐</div>
                    <h3>Security Protocol</h3>
                </div>
                <div class="card-description">Manage encryption keys and security configurations</div>
                <form method="post" class="button-group">
                    <button type="submit" name="action" value="key_generate" class="danger" onclick="return confirm('🔑 Generate new APP_KEY? This may log out all users.')">🔑 Generate Key</button>
                </form>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-icon">🌍</div>
                    <h3>Environment Config</h3>
                </div>
                <div class="card-description">Inspect and manage environment configuration files</div>
                <form method="post" class="button-group">
                    <button type="submit" name="action" value="check_env">🔍 Check Config</button>
                    <button type="submit" name="action" value="show_env">👁️ Show Content</button>
                    <button type="submit" name="action" value="backup_env" class="warning">💾 Backup Config</button>
                </form>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-icon">💊</div>
                    <h3>System Health</h3>
                </div>
                <div class="card-description">Monitor system status, dependencies, and resource usage</div>
                <form method="post" class="button-group">
                    <button type="submit" name="action" value="health_check">🩺 Health Check</button>
                    <button type="submit" name="action" value="composer_status">📦 Composer Status</button>
                    <button type="submit" name="action" value="disk_space">💽 Disk Usage</button>
                </form>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-icon">⚙️</div>
                    <h3>Advanced Tools</h3>
                </div>
                <div class="card-description">Advanced system operations and queue management</div>
                <form method="post" class="button-group">
                    <button type="submit" name="action" value="clear_all_cache" class="warning">🧹 Full Cache Clear</button>
                    <button type="submit" name="action" value="create_symlinks">🔗 Create Symlinks</button>
                    <button type="submit" name="action" value="queue_status">⏳ Queue Status</button>
                </form>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <div class="card-icon">📦</div>
                    <h3>Production Build</h3>
                </div>
                <div class="card-description">Create optimized production package ready for deployment</div>
                <form method="post" class="button-group">
                    <button type="submit" name="action" value="build_production" class="warning" onclick="return confirm('📦 Create production build?')">🚀 Build Package</button>
                </form>
                <div class="card-description" style="margin-top: 0.5rem; font-size: 0.75rem;">
                    Creates dist/naokipos.zip excluding storage directory
                </div>
            </div>
        </div>

        <div class="footer">
            <p><strong>🚨 Emergency Access:</strong> If the main application is broken, you can use this tool to clear cache and fix common issues.</p>
            <p><strong>🔒 Security Note:</strong> This file should be removed or secured in production. Consider adding HTTP basic authentication or IP restrictions.</p>
            <p><strong>💡 Usage:</strong> Click any button to execute the corresponding command. Dangerous operations will ask for confirmation.</p>
        </div>
    </div>

    <script>
        // Add loading states and interactive feedback
        document.addEventListener('DOMContentLoaded', function() {
            // Add loading state to buttons on form submission
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    const submitButton = e.submitter;
                    if (submitButton && !submitButton.classList.contains('danger')) {
                        submitButton.classList.add('loading');
                        submitButton.disabled = true;
                        
                        // Add a timeout to prevent infinite loading
                        setTimeout(() => {
                            submitButton.classList.remove('loading');
                            submitButton.disabled = false;
                        }, 30000); // 30 seconds timeout
                    }
                });
            });

            // Add enhanced confirm dialogs for dangerous actions
            const dangerButtons = document.querySelectorAll('button.danger');
            dangerButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    const action = this.name === 'action' ? this.value : 'this action';
                    const confirmMsg = `⚠️ WARNING: You are about to execute "${action}". This operation cannot be undone. Continue?`;
                    
                    if (!confirm(confirmMsg)) {
                        e.preventDefault();
                        return false;
                    }
                });
            });

            // Add enhanced confirm for warning buttons
            const warningButtons = document.querySelectorAll('button.warning');
            warningButtons.forEach(button => {
                if (!button.hasAttribute('onclick')) {
                    button.addEventListener('click', function(e) {
                        const action = this.name === 'action' ? this.value : 'this action';
                        const confirmMsg = `🔄 You are about to execute "${action}". Continue?`;
                        
                        if (!confirm(confirmMsg)) {
                            e.preventDefault();
                            return false;
                        }
                    });
                }
            });

            // Real-time status updates
            function updateSystemStatus() {
                const statusIndicator = document.querySelector('.status-indicator');
                const statusText = document.querySelector('.system-status');
                
                if (statusIndicator && statusText) {
                    // Simulate checking system status
                    const isOnline = true; // In real implementation, this would be an AJAX check
                    
                    if (isOnline) {
                        statusIndicator.style.background = 'var(--success)';
                        statusText.innerHTML = '<div class="status-indicator"></div>SYSTEM ONLINE';
                    } else {
                        statusIndicator.style.background = 'var(--danger)';
                        statusText.innerHTML = '<div class="status-indicator"></div>SYSTEM ERROR';
                    }
                }
            }

            // Update status every 30 seconds
            setInterval(updateSystemStatus, 30000);

            // Add keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                // Ctrl+L for logout
                if (e.ctrlKey && e.key === 'l') {
                    e.preventDefault();
                    const logoutButton = document.querySelector('button[value="logout"]');
                    if (logoutButton) {
                        logoutButton.click();
                    }
                }

                // Ctrl+H for health check
                if (e.ctrlKey && e.key === 'h') {
                    e.preventDefault();
                    const healthButton = document.querySelector('button[value="health_check"]');
                    if (healthButton) {
                        healthButton.click();
                    }
                }

                // Ctrl+C for clear cache
                if (e.ctrlKey && e.key === 'c' && !e.shiftKey) {
                    e.preventDefault();
                    const cacheButton = document.querySelector('button[value="clear_cache"]');
                    if (cacheButton) {
                        cacheButton.click();
                    }
                }
            });

            // Add tooltips for buttons
            const buttons = document.querySelectorAll('button[name="action"]');
            buttons.forEach(button => {
                const action = button.value;
                let tooltip = '';
                
                switch(action) {
                    case 'clear_cache':
                        tooltip = 'Clear all Laravel cache files (Ctrl+C)';
                        break;
                    case 'health_check':
                        tooltip = 'Run comprehensive system health check (Ctrl+H)';
                        break;
                    case 'storage_link':
                        tooltip = 'Debug and fix storage symbolic links';
                        break;
                    case 'migrate_fresh':
                        tooltip = 'WARNING: This will delete all database data';
                        break;
                    case 'key_generate':
                        tooltip = 'WARNING: This will log out all users';
                        break;
                    case 'logout':
                        tooltip = 'Logout from admin tools (Ctrl+L)';
                        break;
                    default:
                        tooltip = `Execute ${action.replace(/_/g, ' ')}`;
                }
                
                button.title = tooltip;
            });

            // Auto-scroll to output section when command is executed
            const output = document.querySelector('.output');
            if (output) {
                output.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });

        // Matrix rain effect (subtle background animation)
        function createMatrixRain() {
            const chars = '01234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            const container = document.body;
            
            for (let i = 0; i < 3; i++) {
                const drop = document.createElement('div');
                drop.style.position = 'fixed';
                drop.style.top = '-20px';
                drop.style.left = Math.random() * 100 + '%';
                drop.style.color = 'rgba(139, 92, 246, 0.1)';
                drop.style.fontSize = '12px';
                drop.style.fontFamily = 'monospace';
                drop.style.zIndex = '-1';
                drop.style.pointerEvents = 'none';
                drop.textContent = chars[Math.floor(Math.random() * chars.length)];
                
                container.appendChild(drop);
                
                const animation = drop.animate([
                    { transform: 'translateY(-20px)', opacity: 0 },
                    { transform: 'translateY(20px)', opacity: 0.3 },
                    { transform: 'translateY(' + (window.innerHeight + 20) + 'px)', opacity: 0 }
                ], {
                    duration: 3000 + Math.random() * 2000,
                    easing: 'linear'
                });
                
                animation.onfinish = () => drop.remove();
            }
        }

        // Create matrix rain every few seconds
        setInterval(createMatrixRain, 2000);
    </script>
</body>
</html>