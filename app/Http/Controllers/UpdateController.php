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

    /**
     * Restore from backup
     */
    public function restoreBackup(): JsonResponse
    {
        try {
            $backupPath = base_path(self::BACKUP_DIR . '/backup-' . date('Y-m-d') . '.zip');
            
            if (!file_exists($backupPath)) {
                return response()->json(['error' => 'Backup tidak ditemukan'], 404);
            }
            
            $this->extractBackup($backupPath);
            
            return response()->json([
                'success' => true,
                'message' => 'Backup berhasil direstore'
            ]);
            
        } catch (Exception $e) {
            Log::error('Restore backup failed: ' . $e->getMessage());
            return response()->json(['error' => 'Restore backup gagal: ' . $e->getMessage()], 500);
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

    private function getDownloadUrl(array $release): ?string
    {
        foreach ($release['assets'] ?? [] as $asset) {
            if (str_contains($asset['name'], 'package') && str_ends_with($asset['name'], '.zip')) {
                return $asset['browser_download_url'];
            }
        }
        
        // Fallback to zipball
        return $release['zipball_url'] ?? null;
    }

    private function createBackup(): void
    {
        $backupDir = base_path(self::BACKUP_DIR);
        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }
        
        $backupFile = $backupDir . '/backup-' . date('Y-m-d-H-i-s') . '.zip';
        $zip = new ZipArchive();
        
        if ($zip->open($backupFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            $excludes = ['node_modules', 'vendor', 'storage/logs', 'storage/app/updates', 'storage/app/backups'];
            
            $this->addDirectoryToZip($zip, base_path(), '', $excludes);
            $zip->close();
        } else {
            throw new Exception('Gagal membuat backup');
        }
    }

    private function downloadUpdate(string $url, string $version): string
    {
        $updateDir = base_path(self::UPDATE_TEMP_DIR);
        if (!File::exists($updateDir)) {
            File::makeDirectory($updateDir, 0755, true);
        }
        
        $updateFile = $updateDir . '/update-' . $version . '.zip';
        
        $response = Http::timeout(120)->get($url);
        
        if (!$response->successful()) {
            throw new Exception('Gagal download update');
        }
        
        file_put_contents($updateFile, $response->body());
        
        return $updateFile;
    }

    private function extractAndInstall(string $updateFile, string $version): void
    {
        $zip = new ZipArchive();
        $extractDir = base_path(self::UPDATE_TEMP_DIR . '/extracted-' . $version);
        
        if ($zip->open($updateFile) === TRUE) {
            $zip->extractTo($extractDir);
            $zip->close();
        } else {
            throw new Exception('Gagal extract update file');
        }
        
        // Find actual content directory (GitHub releases often have a wrapper folder)
        $contentDir = $this->findContentDirectory($extractDir);
        
        // Copy files, excluding certain directories
        $excludes = ['storage/app', 'storage/logs', '.env', 'composer.lock', 'package-lock.json'];
        $this->copyUpdateFiles($contentDir, base_path(), $excludes);
        
        // Clean up extraction directory
        File::deleteDirectory($extractDir);
    }

    private function findContentDirectory(string $extractDir): string
    {
        $items = File::directories($extractDir);
        
        // If there's only one directory, it's likely the wrapper
        if (count($items) === 1) {
            $subDir = $items[0];
            // Check if it contains Laravel structure
            if (File::exists($subDir . '/artisan')) {
                return $subDir;
            }
        }
        
        // Check if current directory has Laravel structure
        if (File::exists($extractDir . '/artisan')) {
            return $extractDir;
        }
        
        throw new Exception('Tidak dapat menemukan struktur Laravel di update file');
    }

    private function copyUpdateFiles(string $source, string $dest, array $excludes): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
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
                if (!File::exists($target)) {
                    File::makeDirectory($target, 0755, true);
                }
            } else {
                File::copy($item, $target);
            }
        }
    }

    private function runPostUpdateTasks(): void
    {
        // Clear caches
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
        
        // Run Laravel commands
        $commands = [
            'config:clear',
            'route:clear', 
            'view:clear',
            'migrate --force'
        ];
        
        foreach ($commands as $command) {
            try {
                \Artisan::call($command);
            } catch (Exception $e) {
                Log::warning("Post-update command failed: {$command} - " . $e->getMessage());
            }
        }
    }

    private function cleanupUpdate(string $updateFile): void
    {
        if (file_exists($updateFile)) {
            unlink($updateFile);
        }
        
        // Keep only 3 most recent backups
        $backupDir = base_path(self::BACKUP_DIR);
        if (File::exists($backupDir)) {
            $backups = collect(File::files($backupDir))
                ->sortByDesc(function ($file) {
                    return $file->getMTime();
                })
                ->skip(3);
                
            foreach ($backups as $backup) {
                File::delete($backup);
            }
        }
    }

    private function extractBackup(string $backupFile): void
    {
        $zip = new ZipArchive();
        
        if ($zip->open($backupFile) === TRUE) {
            $zip->extractTo(base_path());
            $zip->close();
        } else {
            throw new Exception('Gagal extract backup file');
        }
    }

    private function addDirectoryToZip(ZipArchive $zip, string $source, string $prefix = '', array $excludes = []): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $relativePath = str_replace($source . DIRECTORY_SEPARATOR, '', $item);
            
            // Skip excluded paths
            foreach ($excludes as $exclude) {
                if (str_starts_with($relativePath, $exclude)) {
                    continue 2;
                }
            }

            if ($item->isFile()) {
                $zip->addFile($item, $prefix . $relativePath);
            }
        }
    }
}