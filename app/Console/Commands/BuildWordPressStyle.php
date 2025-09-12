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
    protected $signature = 'build:package {--output=dist/wscrm-package.zip}';

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
            'index.php' => base_path('index.php'),
        ];

        foreach ($criticalFiles as $target => $source) {
            $targetPath = $tempDir . '/' . $target;
            if (!File::exists($targetPath) && File::exists($source)) {
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
        
        // Move all files from public/ to root, except special directories
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($publicDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $relativePath = str_replace($publicDir . DIRECTORY_SEPARATOR, '', $item->getPathname());
            $relativePath = str_replace('\\', '/', $relativePath);
            
            // Don't skip anything - we want install directory in the flat structure

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
                if (!File::exists($targetPath) || str_starts_with($relativePath, 'build/')) {
                    File::copy($item->getPathname(), $targetPath);
                }
            }
        }
        
        // Remove the public directory after flattening
        File::deleteDirectory($publicDir);
        
        $this->line('✅ Flattened public directory structure');
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
