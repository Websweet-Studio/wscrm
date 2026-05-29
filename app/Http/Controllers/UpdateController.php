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
    private const GITHUB_REPO = 'Websweet-Studio/wscrm';

    private const UPDATE_TEMP_DIR = 'storage/app/updates';

    private const BACKUP_DIR = 'storage/app/backups';

    /**
     * Check for available updates from GitHub releases
     */
    public function checkUpdates(): JsonResponse
    {
        try {
            $response = Http::withHeaders([
                'Accept' => 'application/vnd.github+json',
                'User-Agent' => 'wscrm-updater',
            ])->timeout(15)->get('https://api.github.com/repos/'.self::GITHUB_REPO.'/releases/latest');

            if (! $response->successful()) {
                return response()->json(['error' => 'Tidak dapat mengecek update'], 500);
            }

            $latestRelease = $response->json();
            $currentVersion = $this->getCurrentVersion();

            $latestVersion = $this->normalizeVersion((string) ($latestRelease['tag_name'] ?? '0.0.0'));
            $hasUpdate = version_compare($latestVersion, $currentVersion, '>');

            return response()->json([
                'has_update' => $hasUpdate,
                'current_version' => $currentVersion,
                'latest_version' => $latestVersion,
                'release_notes' => $latestRelease['body'] ?? '',
                'download_url' => $this->getDownloadUrl($latestRelease),
                'published_at' => $latestRelease['published_at'],
            ]);
        } catch (Exception $e) {
            Log::error('Failed to check updates: '.$e->getMessage());

            return response()->json(['error' => 'Gagal mengecek update: '.$e->getMessage()], 500);
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

            if (! $downloadUrl || ! $version) {
                return response()->json(['error' => 'Parameter tidak lengkap'], 400);
            }

            $version = $this->normalizeVersion((string) $version);
            $publicRoot = $this->normalizePath((string) ($request->server('DOCUMENT_ROOT') ?? ''));
            if ($publicRoot === '' || ! is_dir($publicRoot)) {
                return response()->json(['error' => 'Document root tidak valid'], 500);
            }

            $wscrmRoot = $this->normalizePath(base_path());
            if (! file_exists($wscrmRoot.'/artisan')) {
                return response()->json(['error' => 'Struktur WSCRM tidak ditemukan (artisan hilang)'], 500);
            }

            $updateFile = $this->downloadUpdate($downloadUrl, $version);
            $extractDir = $this->extractUpdate($updateFile, $version);

            try {
                $this->installUpdateFromExtracted($extractDir, $publicRoot, $version);
            } finally {
                if (File::exists($extractDir)) {
                    File::deleteDirectory($extractDir);
                }
            }

            $this->runPostUpdateTasks();
            $this->cleanupUpdate($updateFile);

            return response()->json([
                'success' => true,
                'message' => 'Update berhasil diinstall ke versi '.$version,
                'version' => $version,
            ]);

        } catch (Exception $e) {
            Log::error('Update failed: '.$e->getMessage());

            $this->restoreLatestBackupFromSnapshot();

            return response()->json(['error' => 'Update gagal: '.$e->getMessage()], 500);
        }
    }

    /**
     * Restore from backup
     */
    public function restoreBackup(): JsonResponse
    {
        try {
            $backupDir = $this->findLatestBackupDir();
            if (! $backupDir) {
                return response()->json(['error' => 'Backup tidak ditemukan'], 404);
            }

            $this->restoreFromSnapshot($backupDir);

            return response()->json([
                'success' => true,
                'message' => 'Backup berhasil direstore',
            ]);

        } catch (Exception $e) {
            Log::error('Restore backup failed: '.$e->getMessage());

            return response()->json(['error' => 'Restore backup gagal: '.$e->getMessage()], 500);
        }
    }

    private function getCurrentVersion(): string
    {
        $composerPath = base_path('composer.json');

        if (file_exists($composerPath)) {
            $composer = json_decode(file_get_contents($composerPath), true);

            return $this->normalizeVersion((string) ($composer['version'] ?? '0.0.0'));
        }

        return '0.0.0';
    }

    private function getDownloadUrl(array $release): ?string
    {
        $assets = $release['assets'] ?? [];
        foreach ($assets as $asset) {
            $name = (string) ($asset['name'] ?? '');
            if (preg_match('/^wscrm[.-].+\\.zip$/i', $name)) {
                return $asset['browser_download_url'] ?? null;
            }
        }

        foreach ($assets as $asset) {
            $name = (string) ($asset['name'] ?? '');
            if (str_ends_with(strtolower($name), '.zip')) {
                return $asset['browser_download_url'] ?? null;
            }
        }

        // Fallback to zipball
        return $release['zipball_url'] ?? null;
    }

    private function downloadUpdate(string $url, string $version): string
    {
        $updateDir = base_path(self::UPDATE_TEMP_DIR);
        if (! File::exists($updateDir)) {
            File::makeDirectory($updateDir, 0755, true);
        }

        $updateFile = $updateDir.'/update-'.$version.'.zip';

        $response = Http::withHeaders([
            'User-Agent' => 'wscrm-updater',
        ])->timeout(180)->sink($updateFile)->get($url);

        if (! $response->successful() || ! file_exists($updateFile) || filesize($updateFile) < 1024) {
            if (file_exists($updateFile)) {
                @unlink($updateFile);
            }

            throw new Exception('Gagal download update');
        }

        return $updateFile;
    }

    private function extractUpdate(string $updateFile, string $version): string
    {
        $zip = new ZipArchive;
        $extractDir = base_path(self::UPDATE_TEMP_DIR.'/extracted-'.$version);

        if ($zip->open($updateFile) === true) {
            $zip->extractTo($extractDir);
            $zip->close();
        } else {
            throw new Exception('Gagal extract update file');
        }

        return $extractDir;
    }

    private function findPackageContentDirectory(string $extractDir): string
    {
        if (File::exists($extractDir.'/wscrm/artisan')) {
            return $extractDir;
        }

        $dirs = File::directories($extractDir);
        if (count($dirs) === 1) {
            $candidate = $dirs[0];
            if (File::exists($candidate.'/wscrm/artisan')) {
                return $candidate;
            }
        }

        throw new Exception('Struktur package tidak valid (folder wscrm tidak ditemukan)');
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
            'migrate --force',
        ];

        foreach ($commands as $command) {
            try {
                \Artisan::call($command);
            } catch (Exception $e) {
                Log::warning("Post-update command failed: {$command} - ".$e->getMessage());
            }
        }
    }

    private function cleanupUpdate(string $updateFile): void
    {
        if (file_exists($updateFile)) {
            unlink($updateFile);
        }

        $backupDir = base_path(self::BACKUP_DIR);
        if (! File::exists($backupDir)) {
            return;
        }

        $backups = collect(File::directories($backupDir))
            ->sortByDesc(function ($dir) {
                return filemtime($dir) ?: 0;
            })
            ->skip(3);

        foreach ($backups as $dir) {
            File::deleteDirectory($dir);
        }
    }

    private function restoreLatestBackupFromSnapshot(): void
    {
        $backupDir = $this->findLatestBackupDir();
        if (! $backupDir) {
            return;
        }

        $this->restoreFromSnapshot($backupDir);
    }

    private function installUpdateFromExtracted(string $extractDir, string $publicRoot, string $version): void
    {
        $extractDir = $this->normalizePath($extractDir);
        $publicRoot = $this->normalizePath($publicRoot);

        $contentDir = $this->findPackageContentDirectory($extractDir);
        $sourcePublicRoot = $this->normalizePath($contentDir);
        $sourceWscrmRoot = $this->normalizePath($contentDir.'/wscrm');

        if (! File::exists($sourceWscrmRoot.'/artisan') || ! File::exists($sourceWscrmRoot.'/vendor/autoload.php')) {
            throw new Exception('Package wscrm tidak valid (artisan/vendor tidak ditemukan)');
        }

        $backupDir = $this->createSnapshotBackupDir($version);

        try {
            try {
                \Artisan::call('down');
            } catch (Exception $e) {
                Log::warning('Failed to enable maintenance mode: '.$e->getMessage());
            }

            $this->backupAndReplaceWscrm($sourceWscrmRoot, base_path(), $backupDir.'/wscrm');
            $this->backupAndReplacePublicRoot($sourcePublicRoot, $publicRoot, $backupDir.'/public_html');

            try {
                \Artisan::call('up');
            } catch (Exception $e) {
                Log::warning('Failed to disable maintenance mode: '.$e->getMessage());
            }
        } catch (Exception $e) {
            $this->restoreFromSnapshot($backupDir);
            throw $e;
        }
    }

    private function backupAndReplaceWscrm(string $sourceWscrmRoot, string $targetWscrmRoot, string $backupWscrmDir): void
    {
        $sourceWscrmRoot = $this->normalizePath($sourceWscrmRoot);
        $targetWscrmRoot = $this->normalizePath($targetWscrmRoot);
        $backupWscrmDir = $this->normalizePath($backupWscrmDir);

        if (! File::exists($backupWscrmDir)) {
            File::makeDirectory($backupWscrmDir, 0755, true);
        }

        $preserveNames = [
            'storage',
            '.env',
            '.env.production',
            '.env.backup',
        ];

        foreach (File::directories($targetWscrmRoot) as $dir) {
            $name = basename($dir);
            if (in_array($name, $preserveNames, true)) {
                continue;
            }
            File::move($dir, $backupWscrmDir.'/'.$name);
        }

        foreach (File::files($targetWscrmRoot) as $file) {
            $name = $file->getFilename();
            if (in_array($name, $preserveNames, true)) {
                continue;
            }
            File::move($file->getPathname(), $backupWscrmDir.'/'.$name);
        }

        $this->copyDirectoryContents($sourceWscrmRoot, $targetWscrmRoot, [
            'storage',
            '.env',
        ]);

        $this->clearBootstrapCache($targetWscrmRoot);
    }

    private function backupAndReplacePublicRoot(string $sourcePublicRoot, string $targetPublicRoot, string $backupPublicDir): void
    {
        $sourcePublicRoot = $this->normalizePath($sourcePublicRoot);
        $targetPublicRoot = $this->normalizePath($targetPublicRoot);
        $backupPublicDir = $this->normalizePath($backupPublicDir);

        if (! File::exists($backupPublicDir)) {
            File::makeDirectory($backupPublicDir, 0755, true);
        }

        $skipNames = [
            'wscrm',
            'install',
            'storage',
            '.env',
            '.env.production',
            '.env.backup',
            'storage',
        ];

        $sourceItems = array_merge(File::directories($sourcePublicRoot), File::files($sourcePublicRoot));
        foreach ($sourceItems as $item) {
            $name = basename((string) $item);
            if (in_array($name, $skipNames, true)) {
                continue;
            }

            $targetPath = $targetPublicRoot.'/'.$name;
            if (File::exists($targetPath)) {
                File::move($targetPath, $backupPublicDir.'/'.$name);
            }

            if (is_dir($item)) {
                File::copyDirectory((string) $item, $targetPath);
            } else {
                File::copy((string) $item, $targetPath);
            }
        }
    }

    private function createSnapshotBackupDir(string $version): string
    {
        $backupBase = base_path(self::BACKUP_DIR);
        if (! File::exists($backupBase)) {
            File::makeDirectory($backupBase, 0755, true);
        }

        $dir = $backupBase.'/backup-'.date('Y-m-d-H-i-s').'-'.$version;
        File::makeDirectory($dir, 0755, true);

        return $dir;
    }

    private function findLatestBackupDir(): ?string
    {
        $backupBase = base_path(self::BACKUP_DIR);
        if (! File::exists($backupBase)) {
            return null;
        }

        $dirs = File::directories($backupBase);
        if (empty($dirs)) {
            return null;
        }

        usort($dirs, function ($a, $b) {
            return (filemtime($b) ?: 0) <=> (filemtime($a) ?: 0);
        });

        return $dirs[0] ?? null;
    }

    private function restoreFromSnapshot(string $backupDir): void
    {
        $backupDir = $this->normalizePath($backupDir);

        $backupWscrm = $backupDir.'/wscrm';
        if (File::exists($backupWscrm)) {
            $this->restoreSnapshotInto($backupWscrm, base_path(), [
                'storage',
                '.env',
                '.env.production',
                '.env.backup',
            ]);
            $this->clearBootstrapCache(base_path());
        }

        $publicRoot = $this->normalizePath((string) (request()->server('DOCUMENT_ROOT') ?? ''));
        if ($publicRoot !== '' && is_dir($publicRoot)) {
            $backupPublic = $backupDir.'/public_html';
            if (File::exists($backupPublic)) {
                $this->restoreSnapshotInto($backupPublic, $publicRoot, [
                    'storage',
                ]);
            }
        }
    }

    private function restoreSnapshotInto(string $backupRoot, string $targetRoot, array $preserveNames): void
    {
        $backupRoot = $this->normalizePath($backupRoot);
        $targetRoot = $this->normalizePath($targetRoot);

        foreach (File::directories($backupRoot) as $dir) {
            $name = basename($dir);
            if (in_array($name, $preserveNames, true)) {
                continue;
            }

            $targetPath = $targetRoot.'/'.$name;
            if (File::exists($targetPath)) {
                File::deleteDirectory($targetPath);
            }
            File::move($dir, $targetPath);
        }

        foreach (File::files($backupRoot) as $file) {
            $name = $file->getFilename();
            if (in_array($name, $preserveNames, true)) {
                continue;
            }

            $targetPath = $targetRoot.'/'.$name;
            if (File::exists($targetPath)) {
                File::delete($targetPath);
            }
            File::move($file->getPathname(), $targetPath);
        }
    }

    private function copyDirectoryContents(string $source, string $dest, array $excludeBasenames): void
    {
        $source = $this->normalizePath($source);
        $dest = $this->normalizePath($dest);

        if (! File::exists($dest)) {
            File::makeDirectory($dest, 0755, true);
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $itemPath = (string) $item;
            $relative = ltrim(str_replace($source, '', $this->normalizePath($itemPath)), '/');
            if ($relative === '') {
                continue;
            }

            $top = explode('/', $relative)[0] ?? $relative;
            if (in_array($top, $excludeBasenames, true)) {
                continue;
            }

            $target = $dest.'/'.$relative;
            if ($item->isDir()) {
                if (! File::exists($target)) {
                    File::makeDirectory($target, 0755, true);
                }
                continue;
            }

            $targetDir = dirname($target);
            if (! File::exists($targetDir)) {
                File::makeDirectory($targetDir, 0755, true);
            }

            File::copy($itemPath, $target);
        }
    }

    private function clearBootstrapCache(string $wscrmRoot): void
    {
        $wscrmRoot = $this->normalizePath($wscrmRoot);
        $cacheDir = $wscrmRoot.'/bootstrap/cache';
        if (! File::exists($cacheDir)) {
            return;
        }

        foreach (File::glob($cacheDir.'/*.php') as $file) {
            File::delete($file);
        }
    }

    private function normalizeVersion(string $version): string
    {
        $version = trim($version);
        $version = ltrim($version, "vV \t\n\r\0\x0B");

        return $version === '' ? '0.0.0' : $version;
    }

    private function normalizePath(string $path): string
    {
        return rtrim(str_replace('\\', '/', $path), '/');
    }
}
