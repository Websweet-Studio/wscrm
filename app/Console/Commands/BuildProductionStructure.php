<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

class BuildProductionStructure extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'build:production-structure';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create production build structure with Laravel and public_html folders';

    /**
     * Execute the console command.
     */
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
