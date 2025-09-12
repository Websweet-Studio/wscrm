<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

class BuildWordPressStyle extends Command
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
        $tempDir = $distDir . DIRECTORY_SEPARATOR . 'temp-package';

        // Validasi path untuk mencegah penghapusan folder project
        if (!$this->validatePaths($distDir, $tempDir)) {
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
        $this->line("📖 Cara install:");
        $this->line("1. Extract zip ke public_html/domain folder");
        $this->line("2. Buka {domain}/install/");
        $this->line("3. Ikuti panduan instalasi");

        return self::SUCCESS;
    }

    /**
     * Perform safety checks sebelum menjalankan build
     */
    private function performSafetyChecks(): void
    {
        // Check 1: Pastikan kita berada di root project Laravel
        if (!File::exists(base_path('artisan'))) {
            $this->error('❌ File artisan tidak ditemukan. Pastikan Anda menjalankan command dari root project Laravel.');
            exit(1);
        }

        // Check 2: Pastikan ada file composer.json
        if (!File::exists(base_path('composer.json'))) {
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
                    if (!$continue) {
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
        $tempDir = realpath(dirname($tempDir)) ? (realpath(dirname($tempDir)) . '/' . basename($tempDir)) : $tempDir;
        
        // Jika path relatif, convert ke absolute
        if (!str_starts_with($distDir, '/') && !preg_match('/^[A-Z]:/', $distDir)) {
            $distDir = $basePath . '/' . ltrim($distDir, '/');
        }
        if (!str_starts_with($tempDir, '/') && !preg_match('/^[A-Z]:/', $tempDir)) {
            $tempDir = $basePath . '/' . ltrim($tempDir, '/');
        }
        
        // Normalize path separators untuk Windows compatibility
        $normalizedBasePath = str_replace('\\', '/', $basePath);
        $normalizedDistDir = str_replace('\\', '/', $distDir);
        $normalizedTempDir = str_replace('\\', '/', $tempDir);
        
        $this->info("🔍 Validating paths:");
        $this->line("   Base path: {$basePath}");
        $this->line("   Dist dir: {$distDir}");
        $this->line("   Temp dir: {$tempDir}");
        
        // Pastikan dist directory berada di dalam project
        if (!str_starts_with($normalizedDistDir, $normalizedBasePath)) {
            $this->error("❌ Dist directory harus berada di dalam project: {$distDir}");
            return false;
        }

        // Pastikan dist directory adalah folder 'dist'
        if (basename($distDir) !== 'dist') {
            $this->error("❌ Directory output harus bernama 'dist': {$distDir}");
            return false;
        }

        // Pastikan temp directory aman
        if (!str_starts_with($normalizedTempDir, $normalizedBasePath)) {
            $this->error("❌ Temp directory harus berada di dalam project: {$tempDir}");
            return false;
        }

        // Pastikan tidak menimpa folder project penting
        $protectedDirs = ['app', 'config', 'database', 'routes', 'resources', '.git', 'vendor'];
        foreach ($protectedDirs as $protected) {
            $protectedPath = str_replace('\\', '/', $basePath . DIRECTORY_SEPARATOR . $protected);
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
            'composer.lock', 'BUILD.md', 'README.md', '.claude'
        ];

        $this->copyDirectory(base_path(), $tempDir, $excludes);

        // Ensure critical files exist
        $criticalFiles = [
            '.env.example' => base_path('.env.example'),
            'artisan' => base_path('artisan'),
            'index.php' => base_path('deployment-index.php'), // Use deployment version
            'debug.php' => base_path('debug-deployment.php'), // Debug script
        ];

        foreach ($criticalFiles as $target => $source) {
            $targetPath = $tempDir . '/' . $target;
            if (File::exists($source)) {
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
            'storage/logs'
        ];

        foreach ($storageDirs as $dir) {
            if (!File::exists($tempDir . '/' . $dir)) {
                File::makeDirectory($tempDir . '/' . $dir, 0755, true);
            }
            File::put($tempDir . '/' . $dir . '/.gitkeep', '');
        }

        // Create bootstrap/cache
        if (!File::exists($tempDir . '/bootstrap/cache')) {
            File::makeDirectory($tempDir . '/bootstrap/cache', 0755, true);
        }
        File::put($tempDir . '/bootstrap/cache/.gitkeep', '');
    }

    private function flattenPublicStructure(string $tempDir): void
    {
        $publicDir = $tempDir . '/public';
        
        if (!File::exists($publicDir)) {
            $this->warn('Public directory not found, skipping flatten');
            return;
        }
        
        // Move all files from public/ to root first
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($publicDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $relativePath = str_replace($publicDir . DIRECTORY_SEPARATOR, '', $item->getPathname());
            $relativePath = str_replace('\\', '/', $relativePath);
            
            $targetPath = $tempDir . DIRECTORY_SEPARATOR . $relativePath;

            if ($item->isDir()) {
                if (!File::exists($targetPath)) {
                    File::makeDirectory($targetPath, 0755, true);
                }
            } else {
                // Ensure target directory exists
                $targetDir = dirname($targetPath);
                if (!File::exists($targetDir)) {
                    File::makeDirectory($targetDir, 0755, true);
                }
                
                // Copy file if it doesn't exist in root or if it's an asset file
                // Skip copying public/index.php - we'll use deployment version instead
                if ($relativePath !== 'index.php' && (!File::exists($targetPath) || str_starts_with($relativePath, 'build/'))) {
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
        
        // Create wscrm directory for Laravel backend files
        $wscrmDir = $tempDir . '/wscrm';
        File::makeDirectory($wscrmDir, 0755, true);
        
        // Copy all Laravel files (except public) to wscrm directory
        $this->copyLaravelFilesToWscrm($tempDir, $wscrmDir);
        
        // Clean up Laravel files from root (they're now in wscrm/)
        $this->cleanupLaravelFilesFromRoot($tempDir);
        
        $this->line('✅ Created wscrm backend folder and moved Laravel files');
    }

    private function copyLaravelFilesToWscrm(string $tempDir, string $wscrmDir): void
    {
        $laravelDirs = ['app', 'bootstrap', 'config', 'database', 'resources', 'routes', 'storage', 'vendor'];
        $laravelFiles = ['artisan', '.env.example', 'composer.json', 'composer.lock'];
        
        // Copy Laravel directories
        foreach ($laravelDirs as $dir) {
            $sourceDir = $tempDir . '/' . $dir;
            $targetDir = $wscrmDir . '/' . $dir;
            
            if (File::exists($sourceDir)) {
                File::copyDirectory($sourceDir, $targetDir);
                $this->line("✅ Copied {$dir} to wscrm/");
            }
        }
        
        // Copy Laravel files
        foreach ($laravelFiles as $file) {
            $sourceFile = $tempDir . '/' . $file;
            $targetFile = $wscrmDir . '/' . $file;
            
            if (File::exists($sourceFile)) {
                File::copy($sourceFile, $targetFile);
                $this->line("✅ Copied {$file} to wscrm/");
            }
        }
    }
    
    private function cleanupLaravelFilesFromRoot(string $tempDir): void
    {
        $laravelDirs = ['app', 'bootstrap', 'config', 'database', 'resources', 'routes', 'storage', 'vendor'];
        $laravelFiles = ['artisan', '.env.example', 'composer.json', 'composer.lock'];
        
        // Remove Laravel directories from root (ONLY if they exist and we're in temp-package)
        foreach ($laravelDirs as $dir) {
            $dirPath = $tempDir . '/' . $dir;
            if (File::exists($dirPath) && str_contains($dirPath, 'temp-package')) {
                File::deleteDirectory($dirPath);
                $this->line("🗑️ Removed Laravel dir from root: {$dir}");
            }
        }
        
        // Remove Laravel files from root (ONLY Laravel files, not public files)
        foreach ($laravelFiles as $file) {
            $filePath = $tempDir . '/' . $file;
            if (File::exists($filePath)) {
                File::delete($filePath);
                $this->line("🗑️ Removed Laravel file from root: {$file}");
            }
        }
        
        $this->line('✅ Cleaned up Laravel files from root directory');
    }

    private function setupInstaller(string $tempDir): void
    {
        // Pastikan installer sudah ada (seharusnya sudah di-copy dari flattening)
        if (!File::exists($tempDir . '/install')) {
            // Fallback: copy dari source jika belum ada
            if (File::exists(base_path('public/install'))) {
                File::copyDirectory(
                    base_path('public/install'),
                    $tempDir . '/install'
                );
            } else {
                $this->warn('Install directory not found in source');
            }
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
        $readmeContent = "WSCRM - Laravel Package Installation

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
Generated: " . date('Y-m-d H:i:s');

        File::put($tempDir . '/README.txt', $readmeContent);
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
            $relativePath = str_replace($source . DIRECTORY_SEPARATOR, '', $itemPath);
            $relativePath = str_replace('\\', '/', $relativePath); // Normalize separators
            
            // Skip excluded paths
            $skip = false;
            foreach ($excludes as $exclude) {
                if (str_starts_with($relativePath, $exclude)) {
                    $skip = true;
                    break;
                }
            }
            if ($skip) continue;

            $target = $dest . DIRECTORY_SEPARATOR . $relativePath;

            if ($item->isDir()) {
                if (!File::exists($target)) {
                    File::makeDirectory($target, 0755, true);
                }
            } else {
                $targetDir = dirname($target);
                if (!File::exists($targetDir)) {
                    File::makeDirectory($targetDir, 0755, true);
                }
                File::copy($itemPath, $target);
            }
        }
    }

    private function createZipPackage(string $tempDir, string $outputPath): void
    {
        $zip = new ZipArchive();
        
        if ($zip->open($outputPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            // Normalize temp directory path
            $tempDir = realpath($tempDir);
            
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($tempDir, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    if ($filePath && file_exists($filePath)) {
                        // Create relative path from temp directory
                        $relativePath = substr($filePath, strlen($tempDir) + 1);
                        $relativePath = str_replace('\\', '/', $relativePath);
                        
                        if (!empty($relativePath)) {
                            $zip->addFile($filePath, $relativePath);
                        }
                    }
                }
            }

            $zip->close();
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
