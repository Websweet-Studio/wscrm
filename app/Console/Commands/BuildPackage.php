<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

class BuildPackage extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'build:package {--output=dist/wscrm-package.zip} {--force}';

    /**
     * The console command description.
     */
    protected $description = 'Build aplikasi menjadi package siap deploy (extract dan install)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🚀 Memulai build package deployment...');

        // SAFETY CHECKS - Mencegah penghapusan file yang tidak diinginkan
        $this->performSafetyChecks();

        $outputPath = $this->option('output');
        $distDir = dirname($outputPath);
        $tempDir = $distDir.DIRECTORY_SEPARATOR.'temp-package';

        // Validasi path untuk mencegah penghapusan folder project
        if (! $this->validatePaths($distDir, $tempDir)) {
            $this->error('❌ Path tidak aman. Build dibatalkan untuk keamanan.');

            return self::FAILURE;
        }

        // Clean dan create directories dengan validasi keamanan
        if (File::exists($distDir)) {
            // Pastikan hanya menghapus folder dist, bukan folder lain
            // Gunakan path absolut untuk perbandingan
            $absoluteDistDir = realpath($distDir);
            $absoluteBasePath = realpath(base_path());

            if ($absoluteDistDir && $absoluteBasePath) {
                // Normalize path separators untuk Windows
                $normalizedDistDir = str_replace('\\', '/', $absoluteDistDir);
                $normalizedBasePath = str_replace('\\', '/', $absoluteBasePath);

                if (basename($distDir) === 'dist' && str_starts_with($normalizedDistDir, $normalizedBasePath)) {
                    $this->info("🗑️ Menghapus folder dist: {$distDir}");
                    File::deleteDirectory($distDir);
                } else {
                    $this->error("❌ Tidak aman menghapus folder: {$distDir}");

                    return self::FAILURE;
                }
            } else {
                // Fallback jika realpath gagal
                if (basename($distDir) === 'dist' && str_contains($distDir, base_path())) {
                    $this->info("🗑️ Menghapus folder dist: {$distDir}");
                    File::deleteDirectory($distDir);
                } else {
                    $this->error("❌ Tidak aman menghapus folder: {$distDir}");

                    return self::FAILURE;
                }
            }
        }

        File::makeDirectory($distDir, 0755, true);
        File::makeDirectory($tempDir, 0755, true);

        // Step 1: Run npm build
        $this->info('📦 Building frontend assets...');
        $this->executeCommand('npm run build');

        // Step 2: Copy files untuk deployment
        $this->info('📁 Copying application files...');
        $this->copyApplicationFiles($tempDir);

        // Step 3: Flatten public directory structure
        $this->info('🔄 Flattening directory structure...');
        $this->flattenPublicStructure($tempDir);

        // Step 4: Setup installer
        $this->info('🔧 Setting up installer...');
        $this->setupInstaller($tempDir);

        // Step 5: Create distributable package
        $this->info('📦 Creating zip package...');
        $this->createZipPackage($tempDir, $outputPath);

        // Cleanup
        File::deleteDirectory($tempDir);

        $this->info("✅ Package build completed: {$outputPath}");
        $this->newLine();
        $this->line('📖 Cara install:');
        $this->line('1. Extract zip ke public_html/domain folder');
        $this->line('2. Buka {domain}/install/');
        $this->line('3. Ikuti panduan instalasi');

        return self::SUCCESS;
    }

    /**
     * Perform safety checks sebelum menjalankan build
     */
    private function performSafetyChecks(): void
    {
        // Check 1: Pastikan kita berada di root project
        if (! File::exists(base_path('artisan'))) {
            $this->error('❌ File artisan tidak ditemukan. Pastikan Anda menjalankan command dari root project.');
            exit(1);
        }

        // Check 2: Pastikan ada file composer.json
        if (! File::exists(base_path('composer.json'))) {
            $this->error('❌ File composer.json tidak ditemukan.');
            exit(1);
        }

        // Check 3: Periksa git status - warn jika ada uncommitted changes
        if (File::exists(base_path('.git'))) {
            $this->warn('⚠️  PERINGATAN: Pastikan semua perubahan sudah di-commit ke git.');
            $this->warn('   Command ini akan memodifikasi file dan folder.');

            // Gunakan option untuk bypass konfirmasi, atau fallback ke input standar
            if ($this->option('no-interaction') || $this->option('force')) {
                $this->info('🚀 Auto-continuing karena --no-interaction atau --force flag.');
            } else {
                // Coba confirm, tapi dengan handling untuk PowerShell
                try {
                    $continue = $this->confirm('Lanjutkan build package?', true); // default true untuk PowerShell
                    if (! $continue) {
                        $this->info('Build dibatalkan oleh user.');
                        exit(0);
                    }
                } catch (\Exception $e) {
                    // Fallback jika confirm tidak bekerja
                    $this->warn('Input tidak dapat dibaca, melanjutkan build...');
                    $this->info('Gunakan --no-interaction untuk menonaktifkan prompt ini.');
                }
            }
        }

        // Check 4: Pastikan frontend assets sudah ter-build atau akan di-build
        $this->info('✅ Safety checks passed.');
    }

    /**
     * Validasi path untuk mencegah operasi berbahaya
     */
    private function validatePaths(string $distDir, string $tempDir): bool
    {
        $basePath = base_path();

        // Normalize paths ke absolute dan normalize separators untuk Windows
        $distDir = realpath($distDir) ?: $distDir;
        $tempDir = realpath(dirname($tempDir)) ? (realpath(dirname($tempDir)).'/'.basename($tempDir)) : $tempDir;

        // Jika path relatif, convert ke absolute
        if (! str_starts_with($distDir, '/') && ! preg_match('/^[A-Z]:/', $distDir)) {
            $distDir = $basePath.'/'.ltrim($distDir, '/');
        }
        if (! str_starts_with($tempDir, '/') && ! preg_match('/^[A-Z]:/', $tempDir)) {
            $tempDir = $basePath.'/'.ltrim($tempDir, '/');
        }

        // Normalize path separators untuk Windows compatibility
        $normalizedBasePath = str_replace('\\', '/', $basePath);
        $normalizedDistDir = str_replace('\\', '/', $distDir);
        $normalizedTempDir = str_replace('\\', '/', $tempDir);

        $this->info('🔍 Validating paths:');
        $this->line("   Base path: {$basePath}");
        $this->line("   Dist dir: {$distDir}");
        $this->line("   Temp dir: {$tempDir}");

        // Pastikan dist directory berada di dalam project
        if (! str_starts_with($normalizedDistDir, $normalizedBasePath)) {
            $this->error("❌ Dist directory harus berada di dalam project: {$distDir}");

            return false;
        }

        // Pastikan dist directory adalah folder 'dist'
        if (basename($distDir) !== 'dist') {
            $this->error("❌ Directory output harus bernama 'dist': {$distDir}");

            return false;
        }

        // Pastikan temp directory aman
        if (! str_starts_with($normalizedTempDir, $normalizedBasePath)) {
            $this->error("❌ Temp directory harus berada di dalam project: {$tempDir}");

            return false;
        }

        // Pastikan tidak menimpa folder project penting
        $protectedDirs = ['app', 'config', 'database', 'routes', 'resources', '.git', 'vendor'];
        foreach ($protectedDirs as $protected) {
            $protectedPath = str_replace('\\', '/', $basePath.DIRECTORY_SEPARATOR.$protected);
            if (str_starts_with($normalizedDistDir, $protectedPath) || str_starts_with($normalizedTempDir, $protectedPath)) {
                $this->error("❌ Tidak boleh menggunakan folder protected: {$protected}");

                return false;
            }
        }

        return true;
    }

    private function copyApplicationFiles(string $tempDir): void
    {
        $excludes = [
            '.git', 'node_modules', 'tests', 'storage/logs',
            'dist', 'temp-package', '.env', 'package-lock.json',
            'composer.lock', 'BUILD.md', 'README.md', '.claude',
            'public/.htaccess', 'deployment-htaccess', 'install-htaccess-template.txt',
        ];

        $this->copyDirectory(base_path(), $tempDir, $excludes);

        // Ensure critical files exist
        $criticalFiles = [
            '.env.example' => base_path('.env.example'),
            'artisan' => base_path('artisan'),
            'index.php' => base_path('deployment-index.php'), // Use deployment version
            'debug.php' => base_path('debug-deployment.php'), // Debug script
            'install/htaccess-template.txt' => base_path('install-htaccess-template.txt'), // Template untuk installer
            // NOTE: .htaccess akan di-generate oleh installer setelah instalasi selesai
        ];

        foreach ($criticalFiles as $target => $source) {
            $targetPath = $tempDir.'/'.$target;
            if (File::exists($source)) {
                // Ensure target directory exists
                $targetDir = dirname($targetPath);
                if (! File::exists($targetDir)) {
                    File::makeDirectory($targetDir, 0755, true);
                }

                File::copy($source, $targetPath);
                $this->line("✅ Copied critical file: $target");
            }
        }

        // Create empty storage directories dengan permissions
        $storageDirs = [
            'storage/app/public',
            'storage/framework/cache',
            'storage/framework/sessions',
            'storage/framework/testing',
            'storage/framework/views',
            'storage/logs',
        ];

        foreach ($storageDirs as $dir) {
            if (! File::exists($tempDir.'/'.$dir)) {
                File::makeDirectory($tempDir.'/'.$dir, 0755, true);
            }
            File::put($tempDir.'/'.$dir.'/.gitkeep', '');
        }

        // Create bootstrap/cache
        if (! File::exists($tempDir.'/bootstrap/cache')) {
            File::makeDirectory($tempDir.'/bootstrap/cache', 0755, true);
        }
        File::put($tempDir.'/bootstrap/cache/.gitkeep', '');
    }

    private function flattenPublicStructure(string $tempDir): void
    {
        $publicDir = $tempDir.'/public';

        if (! File::exists($publicDir)) {
            $this->warn('Public directory not found, skipping flatten');

            return;
        }

        // Move all files from public/ to root first
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($publicDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $relativePath = str_replace($publicDir.DIRECTORY_SEPARATOR, '', $item->getPathname());
            $relativePath = str_replace('\\', '/', $relativePath);

            $targetPath = $tempDir.DIRECTORY_SEPARATOR.$relativePath;

            if ($item->isDir()) {
                if (! File::exists($targetPath)) {
                    File::makeDirectory($targetPath, 0755, true);
                }
            } else {
                // Ensure target directory exists
                $targetDir = dirname($targetPath);
                if (! File::exists($targetDir)) {
                    File::makeDirectory($targetDir, 0755, true);
                }

                // Copy file if it doesn't exist in root or if it's an asset file
                // Skip copying public/index.php and .htaccess - we'll use deployment versions
                if (! in_array($relativePath, ['index.php', '.htaccess']) && (! File::exists($targetPath) || str_starts_with($relativePath, 'build/'))) {
                    File::copy($item->getPathname(), $targetPath);
                }
            }
        }

        // Remove the public directory after flattening (HANYA di temp directory!)
        if (str_contains($publicDir, 'temp-package') && File::exists($publicDir)) {
            File::deleteDirectory($publicDir);
            $this->line('✅ Flattened public directory structure');
        } else {
            $this->error('❌ BAHAYA: Tidak akan menghapus public directory yang bukan di temp folder');
        }

        // Create wscrm directory for backend files
        $wscrmDir = $tempDir.'/wscrm';
        File::makeDirectory($wscrmDir, 0755, true);

        // Copy all backend files (except public) to wscrm directory
        $this->copyWscrmFiles($tempDir, $wscrmDir);

        // Clean up backend files from root (they're now in wscrm/)
        $this->cleanupWscrmFilesFromRoot($tempDir);

        // Remove .htaccess from root - it will be generated by installer
        if (File::exists($tempDir.'/.htaccess')) {
            File::delete($tempDir.'/.htaccess');
            $this->line('🗑️ Removed .htaccess - will be generated by installer');
        }

        $this->line('✅ Created wscrm backend folder and moved backend files');
    }

    private function copyWscrmFiles(string $tempDir, string $wscrmDir): void
    {
        $backendDirs = ['app', 'bootstrap', 'config', 'database', 'resources', 'routes', 'storage', 'vendor'];
        $backendFiles = ['artisan', '.env.example', 'composer.json', 'composer.lock'];

        // Recreate public directory in wscrm dengan build assets
        $this->createWscrmPublicDirectory($tempDir, $wscrmDir);

        // Copy backend directories menggunakan system commands untuk kecepatan
        $this->copyDirectoriesFast($tempDir, $wscrmDir, $backendDirs);

        // Copy backend files
        foreach ($backendFiles as $file) {
            $sourceFile = $tempDir.'/'.$file;
            $targetFile = $wscrmDir.'/'.$file;

            if (File::exists($sourceFile)) {
                File::copy($sourceFile, $targetFile);
                $this->line("✅ Copied {$file} to wscrm/");
            }
        }
    }

    private function cleanupWscrmFilesFromRoot(string $tempDir): void
    {
        $backendDirs = ['app', 'bootstrap', 'config', 'database', 'resources', 'routes', 'storage', 'vendor'];
        $backendFiles = ['artisan', '.env.example', 'composer.json', 'composer.lock'];

        // Remove backend directories from root (ONLY if they exist and we're in temp-package)
        foreach ($backendDirs as $dir) {
            $dirPath = $tempDir.'/'.$dir;
            if (File::exists($dirPath) && str_contains($dirPath, 'temp-package')) {
                File::deleteDirectory($dirPath);
                $this->line("🗑️ Removed backend dir from root: {$dir}");
            }
        }

        // Remove backend files from root (ONLY backend files, not public files)
        foreach ($backendFiles as $file) {
            $filePath = $tempDir.'/'.$file;
            if (File::exists($filePath)) {
                File::delete($filePath);
                $this->line("🗑️ Removed backend file from root: {$file}");
            }
        }

        $this->line('✅ Cleaned up backend files from root directory');
    }

    private function createWscrmPublicDirectory(string $tempDir, string $wscrmDir): void
    {
        $wscrmPublicDir = $wscrmDir.'/public';
        File::makeDirectory($wscrmPublicDir, 0755, true);

        // Copy build assets dari root ke wscrm/public/
        if (File::exists($tempDir.'/build')) {
            File::copyDirectory($tempDir.'/build', $wscrmPublicDir.'/build');
            $this->line('✅ Copied build assets to wscrm/public/build/');
        }

        // Buat index.php di wscrm/public/ (Laravel standard)
        $publicIndexContent = '<?php

use Illuminate\\Http\\Request;

define(\'LARAVEL_START\', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.\'/../storage/framework/maintenance.php\')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.\'/../vendor/autoload.php\';

// Bootstrap Laravel and handle the request...
(require_once __DIR__.\'/../bootstrap/app.php\')
    ->handleRequest(Request::capture());
';

        File::put($wscrmPublicDir.'/index.php', $publicIndexContent);
        $this->line('✅ Created wscrm/public/index.php');

        // Copy .htaccess ke wscrm/public/ (Laravel public standard)
        $publicHtaccessContent = '<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
';

        File::put($wscrmPublicDir.'/.htaccess', $publicHtaccessContent);
        $this->line('✅ Created wscrm/public/.htaccess');
    }

    private function copyDirectoriesFast(string $tempDir, string $wscrmDir, array $dirs): void
    {
        foreach ($dirs as $dir) {
            $sourceDir = $tempDir.'/'.$dir;
            $targetDir = $wscrmDir.'/'.$dir;

            if (! File::exists($sourceDir)) {
                continue;
            }

            // Ensure target parent directory exists
            if (! File::exists(dirname($targetDir))) {
                File::makeDirectory(dirname($targetDir), 0755, true);
            }

            // Use system commands for much faster copying
            if (PHP_OS_FAMILY === 'Windows') {
                // Windows - use robocopy for faster copying
                $cmd = "robocopy \"$sourceDir\" \"$targetDir\" /E /NFL /NDL /NJH /NJS /NP /MT:8 >nul 2>&1";
                exec($cmd, $output, $returnCode);

                // Robocopy return codes 0-7 are success, 8+ are errors
                if ($returnCode >= 8) {
                    // Fallback to PHP method
                    File::copyDirectory($sourceDir, $targetDir);
                }
            } else {
                // Unix/Linux - use cp with parallel processing
                $cmd = "cp -r \"$sourceDir\" \"".dirname($targetDir).'/" 2>/dev/null';
                exec($cmd, $output, $returnCode);

                if ($returnCode !== 0) {
                    // Fallback to PHP method
                    File::copyDirectory($sourceDir, $targetDir);
                }
            }

            $this->line("✅ Copied {$dir} to wscrm/ (fast)");
        }
    }

    private function setupInstaller(string $tempDir): void
    {
        // Pastikan installer sudah ada (seharusnya sudah di-copy dari flattening)
        if (! File::exists($tempDir.'/install')) {
            // Fallback: copy dari source jika belum ada
            if (File::exists(base_path('public/install'))) {
                File::copyDirectory(
                    base_path('public/install'),
                    $tempDir.'/install'
                );
            } else {
                $this->warn('Install directory not found in source');
            }
        }

        // Create .env.example untuk installer
        if (File::exists($tempDir.'/.env.example')) {
            $envContent = File::get($tempDir.'/.env.example');
            $envContent = str_replace([
                'APP_DEBUG=true',
                'APP_ENV=local',
            ], [
                'APP_DEBUG=false',
                'APP_ENV=production',
            ], $envContent);
            File::put($tempDir.'/.env.example', $envContent);
        }

        // Create README.txt untuk user
        $readmeContent = 'WSCRM - Application Package Installation

INSTALASI:
1. Extract semua file ke folder public_html atau domain folder
2. Pastikan permissions folder storage/ dan bootstrap/cache/ 755
3. Buka http://yourdomain.com/install/
4. Ikuti panduan instalasi

REQUIREMENTS:
- PHP 8.2 atau higher
- Extensions: PDO, MySQL, OpenSSL, Mbstring, Tokenizer, XML, Ctype, JSON
- Permissions: storage/ dan bootstrap/cache/ harus writable
- Database: MySQL/MariaDB dengan user dan database yang sudah dibuat

SUPPORT:
Untuk bantuan lebih lanjut, silakan hubungi developer.

---
Generated: '.date('Y-m-d H:i:s');

        File::put($tempDir.'/README.txt', $readmeContent);
    }

    private function copyDirectory(string $source, string $dest, array $excludes = []): void
    {
        // Normalize paths
        $source = rtrim($source, DIRECTORY_SEPARATOR);
        $dest = rtrim($dest, DIRECTORY_SEPARATOR);

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $itemPath = $item->getPathname();
            $relativePath = str_replace($source.DIRECTORY_SEPARATOR, '', $itemPath);
            $relativePath = str_replace('\\', '/', $relativePath); // Normalize separators

            // Skip excluded paths
            $skip = false;
            foreach ($excludes as $exclude) {
                if (str_starts_with($relativePath, $exclude)) {
                    $skip = true;
                    break;
                }
            }
            if ($skip) {
                continue;
            }

            $target = $dest.DIRECTORY_SEPARATOR.$relativePath;

            if ($item->isDir()) {
                if (! File::exists($target)) {
                    File::makeDirectory($target, 0755, true);
                }
            } else {
                $targetDir = dirname($target);
                if (! File::exists($targetDir)) {
                    File::makeDirectory($targetDir, 0755, true);
                }
                File::copy($itemPath, $target);
            }
        }
    }

    private function createZipPackage(string $tempDir, string $outputPath): void
    {
        // Try system commands first for better performance
        if (PHP_OS_FAMILY === 'Windows') {
            // Use PowerShell Compress-Archive for Windows
            $normalizedTempDir = str_replace('/', '\\', $tempDir);
            $normalizedOutputPath = str_replace('/', '\\', $outputPath);

            $cmd = "powershell -Command \"Compress-Archive -Path '$normalizedTempDir\\*' -DestinationPath '$normalizedOutputPath' -Force\"";
            exec($cmd, $output, $returnCode);

            if ($returnCode === 0 && file_exists($outputPath)) {
                $this->line('✅ Created zip package using PowerShell (fast)');

                return;
            }
        } else {
            // Use zip command for Unix/Linux
            $cmd = 'cd "'.dirname($tempDir)."\" && zip -r \"$outputPath\" \"".basename($tempDir).'" >/dev/null 2>&1';
            exec($cmd, $output, $returnCode);

            if ($returnCode === 0 && file_exists($outputPath)) {
                $this->line('✅ Created zip package using system zip (fast)');

                return;
            }
        }

        // Fallback to PHP ZipArchive with batch processing
        $zip = new ZipArchive;

        if ($zip->open($outputPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            $tempDir = realpath($tempDir);

            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($tempDir, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            $fileCount = 0;
            foreach ($files as $file) {
                if (! $file->isDir()) {
                    $filePath = $file->getRealPath();
                    if ($filePath && file_exists($filePath)) {
                        $relativePath = substr($filePath, strlen($tempDir) + 1);
                        $relativePath = str_replace('\\', '/', $relativePath);

                        if (! empty($relativePath)) {
                            $zip->addFile($filePath, $relativePath);
                            $fileCount++;

                            // Batch close/reopen every 1000 files to avoid memory issues
                            if ($fileCount % 1000 === 0) {
                                $zip->close();
                                $zip->open($outputPath, ZipArchive::CREATE);
                            }
                        }
                    }
                }
            }

            $zip->close();
            $this->line('✅ Created zip package using PHP ZipArchive (fallback)');
        } else {
            $this->error('Failed to create zip package');
        }
    }

    protected function executeCommand(string $command): void
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
