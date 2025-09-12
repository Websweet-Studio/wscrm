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
        $tempDir = $distDir . '/temp-wordpress-style';

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
            'dist', 'temp-wordpress-style', '.env', 'package-lock.json', 
            'composer.lock', 'BUILD.md', 'README.md'
        ];

        $this->copyDirectory(base_path(), $tempDir, $excludes);

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
