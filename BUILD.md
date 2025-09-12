# Production Build Setup

This document explains how to set up the production build system used in this Laravel + Vue + Inertia.js project. You can copy these configurations to other projects.

## Overview

The production build system creates a deployment-ready structure with:
- `dist/laravel/` - Laravel application files (backend)
- `dist/public_html/` - Public web files (frontend)
- `dist/production.zip` - Complete deployment package

This structure is ideal for shared hosting where you need to separate the Laravel app from the web root.

## Setup Instructions

### 1. Composer Scripts

Add these scripts to your `composer.json`:

```json
{
  "scripts": {
    "build:production": [
      "powershell -Command \"if (Test-Path 'dist') { Remove-Item -Recurse -Force 'dist' }\"",
      "npm run build:clean",
      "@php artisan config:cache",
      "@php artisan route:cache",
      "@php artisan view:cache",
      "@php artisan build:production-structure"
    ]
  }
}
```

### 2. NPM Scripts

Add these scripts to your `package.json`:

```json
{
  "scripts": {
    "build": "vite build",
    "build:clean": "rimraf public/build && vite build"
  },
  "devDependencies": {
    "rimraf": "^6.0.1"
  }
}
```

Install rimraf if not already installed:
```bash
npm install --save-dev rimraf
```

### 3. Artisan Command

Create the file `app/Console/Commands/BuildProductionStructure.php`:

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

class BuildProductionStructure extends Command
{
    protected $signature = 'build:production-structure';
    protected $description = 'Create production build structure with Laravel and public_html folders';

    public function handle(): int
    {
        $this->info('Building production structure...');

        $distPath = base_path('dist');
        $laravelPath = $distPath.'/laravel';
        $publicHtmlPath = $distPath.'/public_html';

        // Create directories
        File::makeDirectory($laravelPath, 0755, true, true);
        File::makeDirectory($publicHtmlPath, 0755, true, true);

        $this->info('Created dist directories');

        // Copy Laravel files (excluding public directory)
        $this->copyLaravelFiles($laravelPath);
        $this->info('Copied Laravel files');

        // Copy public files to public_html
        $this->copyPublicFiles($publicHtmlPath);
        $this->info('Copied public files to public_html');

        // Create public directory in Laravel folder and copy build assets
        $this->createLaravelPublicBuild($laravelPath, $publicHtmlPath);
        $this->info('Created Laravel public/build directory');

        // Modify index.php for production structure
        $this->modifyIndexPhpForProduction($publicHtmlPath);
        $this->info('Modified index.php for production structure');

        // Create production.zip
        $this->createZip($distPath);
        $this->info('Created production.zip');

        $this->info('✅ Production build completed successfully!');
        $this->info('📁 Files created in: '.$distPath);
        $this->info('📦 Production archive: '.$distPath.'/production.zip');

        return self::SUCCESS;
    }

    private function copyLaravelFiles(string $destination): void
    {
        $excludePaths = [
            'public',
            'node_modules',
            '.git',
            '.env',
            '.env.example',
            'dist',
            'storage/logs',
            'storage/framework/cache',
            'storage/framework/sessions',
            'storage/framework/views',
            'tests',
            'phpunit.xml',
            'vite.config.js',
            'package.json',
            'package-lock.json',
            '.gitignore',
            '.editorconfig',
            '.styleci.yml',
            'README.md',
        ];

        $basePath = base_path();

        foreach (File::allFiles($basePath) as $file) {
            $relativePath = str_replace($basePath.DIRECTORY_SEPARATOR, '', $file->getPathname());
            $relativePath = str_replace('\\', '/', $relativePath);

            // Skip excluded paths
            $shouldSkip = false;
            foreach ($excludePaths as $excludePath) {
                if (str_starts_with($relativePath, $excludePath.'/') || $relativePath === $excludePath) {
                    $shouldSkip = true;
                    break;
                }
            }

            if ($shouldSkip) {
                continue;
            }

            $destinationFile = $destination.DIRECTORY_SEPARATOR.$relativePath;
            $destinationDir = dirname($destinationFile);

            if (! File::exists($destinationDir)) {
                File::makeDirectory($destinationDir, 0755, true);
            }

            File::copy($file->getPathname(), $destinationFile);
        }

        // Copy .env.production as .env if it exists
        $envProduction = base_path('.env.production');
        if (File::exists($envProduction)) {
            File::copy($envProduction, $destination.'/.env');
        }

        // Create necessary storage directories
        $storageDirs = [
            'app/public',
            'framework/cache/data',
            'framework/sessions',
            'framework/views',
            'logs',
        ];

        foreach ($storageDirs as $dir) {
            File::makeDirectory($destination.'/storage/'.$dir, 0755, true, true);
        }
    }

    private function copyPublicFiles(string $destination): void
    {
        $publicPath = base_path('public');

        if (! File::exists($publicPath)) {
            return;
        }

        // Use system commands for efficiency
        if (PHP_OS_FAMILY === 'Windows') {
            // Windows - use xcopy for better hidden file support
            exec("xcopy \"{$publicPath}\" \"{$destination}\" /E /H /Y /Q > nul 2>&1");
        } else {
            // Unix/Linux - use cp with recursive and hidden file flags
            exec("cp -r {$publicPath}/. {$destination}/ 2>/dev/null");
        }

        // Fallback to PHP if system commands fail
        if (! File::exists($destination.'/index.php')) {
            foreach (File::allFiles($publicPath) as $file) {
                $relativePath = str_replace($publicPath.DIRECTORY_SEPARATOR, '', $file->getPathname());
                $destinationFile = $destination.DIRECTORY_SEPARATOR.$relativePath;
                $destinationDir = dirname($destinationFile);

                if (! File::exists($destinationDir)) {
                    File::makeDirectory($destinationDir, 0755, true);
                }

                File::copy($file->getPathname(), $destinationFile);
            }

            // Copy .htaccess explicitly
            $htaccess = $publicPath.'/.htaccess';
            if (File::exists($htaccess)) {
                File::copy($htaccess, $destination.'/.htaccess');
            }
        }

        // Remove development files from production build
        $devFiles = ['hot', 'mix-manifest.json'];
        foreach ($devFiles as $devFile) {
            $devFilePath = $destination.'/'.$devFile;
            if (File::exists($devFilePath)) {
                File::delete($devFilePath);
            }
        }
    }

    private function modifyIndexPhpForProduction(string $publicHtmlPath): void
    {
        $indexPhpPath = $publicHtmlPath.'/index.php';
        
        if (!File::exists($indexPhpPath)) {
            $this->error('index.php not found in public_html directory');
            return;
        }
        
        $content = File::get($indexPhpPath);
        
        // Replace paths to point to ../laravel/ instead of ../
        $content = str_replace(
            "require __DIR__.'/../vendor/autoload.php';",
            "require __DIR__.'/../laravel/vendor/autoload.php';",
            $content
        );
        
        $content = str_replace(
            "require_once __DIR__.'/../bootstrap/app.php';",
            "require_once __DIR__.'/../laravel/bootstrap/app.php';",
            $content
        );
        
        $content = str_replace(
            "file_exists(\$maintenance = __DIR__.'/../storage/framework/maintenance.php')",
            "file_exists(\$maintenance = __DIR__.'/../laravel/storage/framework/maintenance.php')",
            $content
        );
        
        File::put($indexPhpPath, $content);
    }

    private function createLaravelPublicBuild(string $laravelPath, string $publicHtmlPath): void
    {
        $laravelPublicPath = $laravelPath.'/public';
        $laravelBuildPath = $laravelPublicPath.'/build';
        $publicHtmlBuildPath = $publicHtmlPath.'/build';

        // Create public directory in Laravel folder
        File::makeDirectory($laravelPublicPath, 0755, true, true);
        File::makeDirectory($laravelBuildPath, 0755, true, true);

        // Copy build directory from public_html to laravel/public
        if (File::exists($publicHtmlBuildPath)) {
            // Copy manifest.json
            if (File::exists($publicHtmlBuildPath.'/manifest.json')) {
                File::copy($publicHtmlBuildPath.'/manifest.json', $laravelBuildPath.'/manifest.json');
            }

            // Copy assets directory
            $assetsSource = $publicHtmlBuildPath.'/assets';
            $assetsDestination = $laravelBuildPath.'/assets';
            
            if (File::exists($assetsSource)) {
                File::makeDirectory($assetsDestination, 0755, true, true);
                
                // Copy all files in assets directory
                foreach (File::allFiles($assetsSource) as $file) {
                    $relativePath = str_replace($assetsSource.DIRECTORY_SEPARATOR, '', $file->getPathname());
                    $destinationFile = $assetsDestination.DIRECTORY_SEPARATOR.$relativePath;
                    $destinationDir = dirname($destinationFile);
                    
                    if (!File::exists($destinationDir)) {
                        File::makeDirectory($destinationDir, 0755, true);
                    }
                    
                    File::copy($file->getPathname(), $destinationFile);
                }
            }
        }
    }

    private function createZip(string $distPath): void
    {
        $zipFile = $distPath.'/production.zip';

        if (File::exists($zipFile)) {
            File::delete($zipFile);
        }

        $zip = new ZipArchive;
        if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            // Add laravel directory
            $this->addDirectoryToZip($zip, realpath($distPath.'/laravel'), 'laravel');

            // Add public_html directory
            $this->addDirectoryToZip($zip, realpath($distPath.'/public_html'), 'public_html');

            $zip->close();
        }
    }

    private function addDirectoryToZip(ZipArchive $zip, string $dir, string $zipDir): void
    {
        if (! $dir || ! is_dir($dir)) {
            return;
        }

        $realDir = realpath($dir);
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($realDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            $filePath = $file->getRealPath();

            // Calculate relative path from base directory
            $relativePath = str_replace($realDir, '', $filePath);
            $relativePath = ltrim($relativePath, DIRECTORY_SEPARATOR);
            $relativePath = str_replace('\\', '/', $relativePath);

            // Create zip path
            $zipPath = $relativePath ? $zipDir.'/'.$relativePath : $zipDir;

            if ($file->isDir()) {
                if ($zipPath !== $zipDir) { // Don't add the root directory
                    $zip->addEmptyDir($zipPath);
                }
            } elseif ($file->isFile()) {
                $zip->addFile($filePath, $zipPath);
            }
        }
    }
}
```

### 4. Environment Configuration

Create `.env.production` file with production settings:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database settings
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Add other production-specific settings
```

## Usage

### Build for Production

Run the complete production build:

```bash
composer run build:production
```

This will:
1. Clean the `dist` directory
2. Build optimized frontend assets
3. Cache Laravel configurations
4. Create production folder structure
5. Generate `production.zip`

### Individual Commands

You can also run individual steps:

```bash
# Build frontend only
npm run build:clean

# Cache Laravel configs
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create production structure
php artisan build:production-structure
```

## Output Structure

After running the build, you'll get:

```
dist/
├── laravel/                 # Laravel application files
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── public/
│   │   └── build/          # Build assets for Laravel
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   ├── vendor/
│   └── .env                # Production environment
├── public_html/            # Web root files
│   ├── build/              # Optimized assets
│   ├── index.php           # Modified to point to ../laravel/
│   ├── .htaccess
│   └── ...
└── production.zip          # Complete deployment package
```

## Deployment

1. Extract `production.zip` on your server
2. Place `laravel/` folder outside web root
3. Place `public_html/` contents in web root
4. Set up database and configure web server
5. Run initial setup:
   ```bash
   php artisan storage:link
   php artisan migrate --force
   ```

## Customization

### Exclude Additional Files

Edit the `$excludePaths` array in `BuildProductionStructure.php`:

```php
$excludePaths = [
    'public',
    'node_modules',
    '.git',
    'tests',
    // Add your custom exclusions here
    'custom-dev-folder',
    '*.log',
];
```

### Production Environment Variables

The build automatically copies `.env.production` to `.env` in the production build if it exists. Create this file with your production-specific settings.

## Requirements

- PHP 8.2+
- Laravel 12+
- Node.js & NPM
- ZipArchive PHP extension
- rimraf NPM package

## Package-Style Deployment System

This project also includes a Package deployment system for maximum ease of installation, similar to WordPress, WHMCS, and other popular PHP applications.

### Package-Style Build

Create a single ZIP file that can be extracted directly to public_html with a simple installer:

```bash
# Build package
composer run build:package

# Output: dist/wscrm-package.zip
```

### Implementation Components

#### 1. Package Build Command

Create `app/Console/Commands/BuildPackage.php`:

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

class BuildPackage extends Command
{
    protected $signature = 'build:package {--output=dist/wscrm-package.zip}';
    protected $description = 'Build aplikasi menjadi package siap deploy (extract dan install)';

    public function handle(): int
    {
        $this->info('🚀 Memulai build package deployment...');
        
        $outputPath = $this->option('output');
        $distDir = dirname($outputPath);
        $tempDir = $distDir . '/temp-package';

        // Clean dan create directories
        if (File::exists($distDir)) {
            File::deleteDirectory($distDir);
        }
        File::makeDirectory($distDir, 0755, true);
        File::makeDirectory($tempDir, 0755, true);

        // Step 1: Run npm build
        $this->info('📦 Building frontend assets...');
        $this->runCommand('npm run build');

        // Step 2: Copy files untuk deployment
        $this->info('📁 Copying application files...');
        $this->copyApplicationFiles($tempDir);

        // Step 3: Setup installer
        $this->info('🔧 Setting up installer...');
        $this->setupInstaller($tempDir);

        // Step 4: Create distributable package
        $this->info('📦 Creating zip package...');
        $this->createZipPackage($tempDir, $outputPath);

        // Cleanup
        File::deleteDirectory($tempDir);

        $this->info("✅ Package build completed: {$outputPath}");
        $this->newLine();
        $this->line("📖 Cara install:");
        $this->line("1. Extract zip ke public_html/domain folder");
        $this->line("2. Buka {domain}/install/");
        $this->line("3. Ikuti panduan instalasi");

        return self::SUCCESS;
    }

    private function copyApplicationFiles(string $tempDir): void
    {
        $excludes = [
            '.git', 'node_modules', 'tests', 'storage/logs',
            'dist', '.env', 'package-lock.json', 'composer.lock',
            'BUILD.md', 'README.md'
        ];

        $this->copyDirectory(base_path(), $tempDir, $excludes);

        // Copy public files ke root level untuk flat deployment
        if (File::exists($tempDir . '/public')) {
            foreach (File::allFiles($tempDir . '/public') as $file) {
                $relativePath = str_replace($tempDir . '/public/', '', $file->getPathname());
                File::copy(
                    $file->getPathname(),
                    $tempDir . '/' . $relativePath
                );
            }
        }

        // Create empty storage directories dengan permissions
        $storageDirs = [
            'storage/app/public',
            'storage/framework/cache',
            'storage/framework/sessions',
            'storage/framework/testing',
            'storage/framework/views',
            'storage/logs'
        ];

        foreach ($storageDirs as $dir) {
            File::makeDirectory($tempDir . '/' . $dir, 0755, true);
            File::put($tempDir . '/' . $dir . '/.gitkeep', '');
        }

        // Create bootstrap/cache
        File::makeDirectory($tempDir . '/bootstrap/cache', 0755, true);
        File::put($tempDir . '/bootstrap/cache/.gitkeep', '');
    }

    private function setupInstaller(string $tempDir): void
    {
        // Pastikan installer sudah ada
        if (!File::exists($tempDir . '/install')) {
            File::copyDirectory(
                base_path('public/install'),
                $tempDir . '/install'
            );
        }

        // Create .env.example untuk installer
        if (File::exists($tempDir . '/.env.example')) {
            $envContent = File::get($tempDir . '/.env.example');
            $envContent = str_replace([
                'APP_DEBUG=true',
                'APP_ENV=local'
            ], [
                'APP_DEBUG=false',
                'APP_ENV=production'
            ], $envContent);
            File::put($tempDir . '/.env.example', $envContent);
        }

        // Create README.txt untuk user
        $readmeContent = "WSCRM - WordPress Style Installation

INSTALASI:
1. Extract semua file ke folder public_html atau domain folder
2. Pastikan permissions folder storage/ dan bootstrap/cache/ 755
3. Buka http://yourdomain.com/install/
4. Ikuti panduan instalasi

REQUIREMENTS:
- PHP 8.2 atau higher
- Extensions: PDO, SQLite, OpenSSL, Mbstring, Tokenizer, XML, Ctype, JSON
- Permissions: storage/ dan bootstrap/cache/ harus writable

SUPPORT:
Untuk bantuan lebih lanjut, silakan hubungi developer.

---
Generated: " . date('Y-m-d H:i:s');

        File::put($tempDir . '/README.txt', $readmeContent);
    }

    private function copyDirectory(string $source, string $dest, array $excludes = []): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $relativePath = str_replace($source . DIRECTORY_SEPARATOR, '', $item);
            
            // Skip excluded paths
            foreach ($excludes as $exclude) {
                if (str_starts_with($relativePath, $exclude)) {
                    continue 2;
                }
            }

            $target = $dest . DIRECTORY_SEPARATOR . $relativePath;

            if ($item->isDir()) {
                File::makeDirectory($target, 0755, true);
            } else {
                File::copy($item, $target);
            }
        }
    }

    private function createZipPackage(string $tempDir, string $outputPath): void
    {
        $zip = new ZipArchive();
        
        if ($zip->open($outputPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($tempDir),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($tempDir) + 1);
                    $zip->addFile($filePath, $relativePath);
                }
            }

            $zip->close();
        } else {
            $this->error('Failed to create zip package');
        }
    }

    private function runCommand(string $command): void
    {
        $process = proc_open(
            $command,
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
            base_path()
        );

        if (is_resource($process)) {
            fclose($pipes[0]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($process);
        }
    }
}
```

#### 2. Modified Index.php for Flexible Deployment

Update `public/index.php` to auto-detect Laravel path:

```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Check if installer exists and installation is not completed
if (is_dir(__DIR__ . '/install') && !file_exists(__DIR__ . '/../storage/installer.lock')) {
    // Redirect to installer for non-install URLs
    $requestUri = $_SERVER['REQUEST_URI'];
    if (strpos($requestUri, '/install') !== 0) {
        header('Location: /install/');
        exit;
    }
}

// Auto-detect Laravel path for flexible deployment
$laravel_root = __DIR__;

// Check if we're in standard Laravel structure (public folder)
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    $laravel_root = __DIR__ . '/..';
} 
// Check if we're in flat deployment (all files in same level)
elseif (file_exists(__DIR__ . '/vendor/autoload.php')) {
    $laravel_root = __DIR__;
} 
// Check if Laravel is in parent directory 
elseif (file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
    $laravel_root = dirname(__DIR__);
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $laravel_root.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $laravel_root.'/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once $laravel_root.'/bootstrap/app.php';

$app->handleRequest(Request::capture());
```

#### 3. Package-Style Installer

Create `public/install/index.php` with visual installer interface:

```php
<?php
/**
 * Laravel Installer - WordPress Style
 * Simple installation system untuk kemudahan deployment
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if already installed
if (file_exists(__DIR__ . '/../../.env') && !file_exists(__DIR__ . '/../../storage/installer.lock')) {
    // Check if database connection works
    try {
        $env = parse_ini_file(__DIR__ . '/../../.env');
        if (isset($env['DB_CONNECTION']) && $env['DB_CONNECTION'] === 'sqlite') {
            $dbPath = __DIR__ . '/../../database/database.sqlite';
            if (file_exists($dbPath)) {
                $pdo = new PDO('sqlite:' . $dbPath);
                $result = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users' LIMIT 1");
                if ($result && $result->fetch()) {
                    header('Location: /');
                    exit('Installation sudah selesai. <a href="/">Akses aplikasi</a>');
                }
            }
        }
    } catch (Exception $e) {
        // Continue with installation if database check fails
    }
}

// Handle installation process
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $step = $_POST['step'] ?? 1;
    
    switch ($step) {
        case 2:
            handleEnvironmentSetup();
            break;
        case 3:
            handleDatabaseSetup();
            break;
        case 4:
            handleFinalSetup();
            break;
    }
}

function handleEnvironmentSetup() {
    $appName = $_POST['app_name'] ?? 'WSCRM';
    $appUrl = $_POST['app_url'] ?? 'http://localhost';
    $dbConnection = $_POST['db_connection'] ?? 'sqlite';
    
    $envTemplate = file_get_contents(__DIR__ . '/../../.env.example');
    
    $envContent = str_replace([
        'APP_NAME=Laravel',
        'APP_URL=http://localhost',
        'DB_CONNECTION=sqlite',
    ], [
        "APP_NAME=\"$appName\"",
        "APP_URL=$appUrl",
        "DB_CONNECTION=$dbConnection",
    ], $envTemplate);
    
    // Generate app key
    $appKey = 'base64:' . base64_encode(random_bytes(32));
    $envContent = str_replace('APP_KEY=', "APP_KEY=$appKey", $envContent);
    
    file_put_contents(__DIR__ . '/../../.env', $envContent);
    
    // Create SQLite database file
    if ($dbConnection === 'sqlite') {
        $dbPath = __DIR__ . '/../../database/database.sqlite';
        if (!file_exists($dbPath)) {
            touch($dbPath);
        }
    }
    
    showStep(3, 'Environment berhasil dikonfigurasi');
}

function handleDatabaseSetup() {
    // Run migrations
    $output = [];
    $return = 0;
    
    chdir(__DIR__ . '/../..');
    exec('php artisan migrate --force 2>&1', $output, $return);
    
    if ($return !== 0) {
        showStep(3, 'Error saat migrasi database: ' . implode('\n', $output), true);
        return;
    }
    
    // Run seeders if exists
    exec('php artisan db:seed --force 2>&1', $output, $return);
    
    showStep(4, 'Database berhasil disetup');
}

function handleFinalSetup() {
    // Clear caches
    chdir(__DIR__ . '/../..');
    exec('php artisan config:clear 2>&1');
    exec('php artisan route:clear 2>&1');
    exec('php artisan view:clear 2>&1');
    
    // Create lock file
    file_put_contents(__DIR__ . '/../../storage/installer.lock', date('Y-m-d H:i:s'));
    
    // Create admin user if requested
    $adminEmail = $_POST['admin_email'] ?? '';
    $adminPassword = $_POST['admin_password'] ?? '';
    
    if ($adminEmail && $adminPassword) {
        // Create admin user via artisan command
        $command = "php artisan tinker --execute=\"
            \\App\\Models\\User::create([
                'name' => 'Administrator',
                'email' => '$adminEmail',
                'password' => bcrypt('$adminPassword'),
                'email_verified_at' => now(),
            ]);
        \"";
        exec($command . ' 2>&1');
    }
    
    showSuccess();
}

// Include installer UI functions here...
```

#### 4. Enhanced Security .htaccess

Update `public/.htaccess` with security protection for public_html deployment:

```apache
# Laravel .htaccess dengan Security Protection untuk WordPress-style Deployment
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Handle X-XSRF-Token Header
    RewriteCond %{HTTP:x-xsrf-token} .
    RewriteRule .* - [E=HTTP_X_XSRF_TOKEN:%{HTTP:X-XSRF-Token}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>

# Security Protection Rules untuk Deployment di Public HTML
# Block access to sensitive Laravel files dan directories
<FilesMatch "^(\.env|\.env\..*|\.git.*|composer\.(json|lock)|package\.(json|lock)|artisan|phpunit\.xml)$">
    Require all denied
</FilesMatch>

# Block akses ke Laravel directories jika deployed di public_html
<IfModule mod_rewrite.c>
    # Block app, config, database, storage, tests, vendor directories
    RewriteRule ^(app|bootstrap|config|database|resources|routes|storage|tests|vendor)(/.*)?$ - [F,L]
    
    # Block PHP files di root kecuali index.php dan installer
    RewriteCond %{REQUEST_FILENAME} -f
    RewriteCond %{REQUEST_URI} ^/[^/]+\.php$
    RewriteCond %{REQUEST_URI} !^/(index\.php|install/.*)$
    RewriteRule .* - [F,L]
    
    # Block access to node_modules dan dist folders
    RewriteRule ^(node_modules|dist)(/.*)?$ - [F,L]
</IfModule>

# Block access ke specific file types yang sensitive
<FilesMatch "\.(md|txt|log|ini|conf|cfg|sql|sqlite|lock|yaml|yml)$">
    Require all denied
</FilesMatch>

# Security Headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY  
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    Header always set Permissions-Policy "geolocation=(), microphone=(), camera=()"
</IfModule>

# Disable server signature untuk security
ServerTokens Prod
ServerSignature Off

# Disable directory browsing
Options -Indexes

# Disable access ke version control directories
<DirectoryMatch "\.(git|svn)">
    Require all denied
</DirectoryMatch>

# Set proper MIME types
<IfModule mod_mime.c>
    AddType application/javascript .js
    AddType text/css .css
</IfModule>

# Gzip compression untuk better performance
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>

# Cache static files
<IfModule mod_expires.c>
    ExpiresActive on
    ExpiresByType text/css "access plus 1 year"
    ExpiresByType application/javascript "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/svg+xml "access plus 1 year"
    ExpiresByType image/x-icon "access plus 1 year"
    ExpiresByType image/webp "access plus 1 year"
</IfModule>
```

#### 5. Auto-Update System

Add GitHub releases integration for automatic updates:

**Update Controller (`app/Http/Controllers/UpdateController.php`):**

```php
<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class UpdateController extends Controller
{
    private const GITHUB_REPO = 'your-username/wscrm'; // Ganti dengan repository GitHub Anda
    private const UPDATE_TEMP_DIR = 'storage/app/updates';
    private const BACKUP_DIR = 'storage/app/backups';

    /**
     * Check for available updates from GitHub releases
     */
    public function checkUpdates(): JsonResponse
    {
        try {
            $response = Http::timeout(10)->get("https://api.github.com/repos/" . self::GITHUB_REPO . "/releases/latest");
            
            if (!$response->successful()) {
                return response()->json(['error' => 'Tidak dapat mengecek update'], 500);
            }

            $latestRelease = $response->json();
            $currentVersion = $this->getCurrentVersion();
            
            $hasUpdate = version_compare($latestRelease['tag_name'], $currentVersion, '>');
            
            return response()->json([
                'has_update' => $hasUpdate,
                'current_version' => $currentVersion,
                'latest_version' => $latestRelease['tag_name'],
                'release_notes' => $latestRelease['body'] ?? '',
                'download_url' => $this->getDownloadUrl($latestRelease),
                'published_at' => $latestRelease['published_at']
            ]);
        } catch (Exception $e) {
            Log::error('Failed to check updates: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal mengecek update: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Download and install update
     */
    public function performUpdate(Request $request): JsonResponse
    {
        try {
            set_time_limit(300); // 5 minutes timeout
            
            $downloadUrl = $request->input('download_url');
            $version = $request->input('version');
            
            if (!$downloadUrl || !$version) {
                return response()->json(['error' => 'Parameter tidak lengkap'], 400);
            }

            // Step 1: Create backup
            $this->createBackup();
            
            // Step 2: Download update
            $updateFile = $this->downloadUpdate($downloadUrl, $version);
            
            // Step 3: Extract and install
            $this->extractAndInstall($updateFile, $version);
            
            // Step 4: Run post-update tasks
            $this->runPostUpdateTasks();
            
            // Step 5: Cleanup
            $this->cleanupUpdate($updateFile);
            
            return response()->json([
                'success' => true,
                'message' => 'Update berhasil diinstall ke versi ' . $version,
                'version' => $version
            ]);
            
        } catch (Exception $e) {
            Log::error('Update failed: ' . $e->getMessage());
            
            // Attempt to restore backup on failure
            $this->restoreBackup();
            
            return response()->json(['error' => 'Update gagal: ' . $e->getMessage()], 500);
        }
    }

    private function getCurrentVersion(): string
    {
        $composerPath = base_path('composer.json');
        
        if (file_exists($composerPath)) {
            $composer = json_decode(file_get_contents($composerPath), true);
            return $composer['version'] ?? '1.0.0';
        }
        
        return '1.0.0';
    }

    // Additional methods for backup, download, extract, etc...
}
```

#### 6. Composer Scripts

Add these scripts to `composer.json`:

```json
{
  "scripts": {
    "build:package": [
      "powershell -Command \"if (Test-Path 'dist') { Remove-Item -Recurse -Force 'dist' }\"",
      "@php artisan build:package"
    ],
    "build:flat-deployment": [
      "powershell -Command \"if (Test-Path 'dist') { Remove-Item -Recurse -Force 'dist' }\"",
      "npm run build",
      "@php artisan config:cache",
      "@php artisan route:cache", 
      "@php artisan view:cache",
      "@php artisan build:flat-deployment"
    ]
  }
}
```

#### 7. Version Tracking

Add version to `composer.json`:

```json
{
  "name": "your-app/name",
  "version": "1.0.0",
  "description": "Your application description"
}
```

### Package-Style Usage

#### For End Users (Installation)
1. Download ZIP dari GitHub releases atau build
2. Extract ke `public_html/` atau domain folder  
3. Buka `http://yourdomain.com/install/`
4. Ikuti wizard instalasi:
   - System requirements check
   - App configuration (name, URL, database)
   - Database setup (auto-migration)  
   - Admin account creation (optional)
5. Done! Aplikasi siap digunakan

#### For Developers (Building)
```bash
# Build package
composer run build:package

# Output: dist/wscrm-package.zip
# Upload to GitHub releases
```

#### For Updates
1. Admin buka `/admin/system/update`
2. Click "Cek Update" 
3. Jika ada update, click "Install Update"
4. System otomatis backup, download, extract, dan install
5. Auto-rollback jika gagal

### Security Features

**Package deployment tetap secure dengan:**

- ✅ `.htaccess` protection untuk block sensitive files
- ✅ Auto-detect deployment structure  
- ✅ Block akses ke Laravel directories
- ✅ Security headers
- ✅ Directory browsing disabled
- ✅ File type restrictions
- ✅ Performance optimization (Gzip, caching)

### Deployment Comparison

| Feature | Traditional Laravel | Package-Style |
|---------|-------------------|-----------------|
| Installation | Requires SSH/command line | Extract + web interface |
| User Skill | Technical (developer) | Non-technical (end user) |
| Security | Maximum (files outside web) | Good (protected via .htaccess) |
| Updates | Manual/CI/CD | One-click from admin |
| Shared Hosting | Complex setup | Simple extract |
| Distribution | Multiple files | Single ZIP |

### Best Practices

1. **Always test** builds in clean environment before release
2. **Use HTTPS** untuk download updates dan security  
3. **Regular backups** sebelum update
4. **Monitor logs** untuk security issues
5. **Version control** releases dengan semantic versioning
6. **Test installer** di berbagai hosting environments
7. **Document requirements** dengan jelas untuk users

This system provides the best of both worlds: Laravel's power with WordPress-style ease of use.

## Notes

- The build process excludes development files automatically
- Storage directories are created with proper permissions
- The `index.php` is modified to work with the separated structure
- Build assets are duplicated for both web root and Laravel compatibility
- Windows and Unix systems are both supported
- Package deployment maintains security through .htaccess protection
- Auto-update system provides safe, one-click updates with backup/restore
- Installation wizard handles all technical setup automatically