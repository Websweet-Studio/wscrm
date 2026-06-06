<?php

$debugInstaller = isset($_GET['debug']) && $_GET['debug'] === '1';
if ($debugInstaller) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

function parseEnvFile($envPath)
{
    if (! file_exists($envPath)) {
        return [];
    }

    $env = [];
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        if (strpos($line, '=') !== false) {
            [$key, $value] = explode('=', $line, 2);
            $env[trim($key)] = trim($value, '"\' ');
        }
    }

    return $env;
}

function formatEnvValue($value)
{
    if ($value === null) {
        return '';
    }

    $value = (string) $value;
    if ($value === '') {
        return '';
    }

    if (preg_match('/[\\s#"\'\\\\]/', $value)) {
        return '"' . str_replace('"', '\\"', $value) . '"';
    }

    return $value;
}

function setEnvValue($envContent, $key, $value)
{
    $formatted = formatEnvValue($value);
    $pattern = '/^' . preg_quote($key, '/') . '=.*$/m';
    $replacement = $key . '=' . $formatted;

    if (preg_match($pattern, $envContent)) {
        return preg_replace($pattern, $replacement, $envContent);
    }

    $envContent = rtrim((string) $envContent, "\r\n");

    return $envContent . "\n" . $replacement . "\n";
}

function clearBootstrapCache($wscrmPath)
{
    $cacheDir = rtrim(str_replace('\\', '/', $wscrmPath), '/') . '/bootstrap/cache';
    if (! is_dir($cacheDir)) {
        return;
    }

    $files = glob($cacheDir . '/*.php') ?: [];
    foreach ($files as $file) {
        @unlink($file);
    }
}

function executeSimpleCommand($command)
{
    // Simple command execution without exec() complexity
    if (! function_exists('shell_exec')) {
        return 'Error: shell_exec function is disabled on this server';
    }

    // Try to use php directly since we're on shared hosting
    if (strpos($command, 'php ') === 0) {
        // Try the most common hosting-compatible approach
        $testPhp = @shell_exec('php --version 2>/dev/null');
        if ($testPhp && strpos($testPhp, 'PHP') !== false) {
            // php command works directly
            $output = @shell_exec($command . ' 2>&1');
        } else {
            // Try common cPanel paths
            $phpPaths = [
                '/opt/cpanel/ea-php83/root/usr/bin/php',
                '/opt/cpanel/ea-php82/root/usr/bin/php',
                '/opt/cpanel/ea-php81/root/usr/bin/php',
                '/usr/local/bin/php',
                '/usr/bin/php',
            ];

            $phpFound = false;
            foreach ($phpPaths as $phpPath) {
                if (is_executable($phpPath)) {
                    $command = str_replace('php ', $phpPath . ' ', $command);
                    $output = @shell_exec($command . ' 2>&1');
                    $phpFound = true;
                    break;
                }
            }

            if (! $phpFound) {
                return 'Error: Could not find PHP executable on this server';
            }
        }
    } else {
        $output = @shell_exec($command . ' 2>&1');
    }

    return $output ?: 'Command executed (no output)';
}

function detectWscrmFolder()
{
    $currentDir = rtrim(str_replace('\\', '/', __DIR__), '/');  // Normalize path
    $publicHtmlDir = dirname($currentDir);
    $documentRoot = rtrim(str_replace('\\', '/', (string) ($_SERVER['DOCUMENT_ROOT'] ?? '')), '/');
    $publicHtmlParent = dirname($documentRoot);

    // Normalize all paths to use forward slashes
    $publicHtmlDir = str_replace('\\', '/', $publicHtmlDir);
    $publicHtmlParent = str_replace('\\', '/', $publicHtmlParent);

    // Debug info for troubleshooting
    $debugInfo = [
        'currentDir' => $currentDir,
        'publicHtmlDir' => $publicHtmlDir,
        'DOCUMENT_ROOT' => $_SERVER['DOCUMENT_ROOT'],
        'publicHtmlParent' => $publicHtmlParent,
        'checks' => [],
    ];

    // Priority 1: Check if wscrm is already moved to parent of public_html (already installed)
    $path1 = $publicHtmlParent . '/wscrm';
    $exists1 = is_dir($path1);
    $debugInfo['checks'][] = ['path' => $path1, 'exists' => $exists1, 'priority' => 1];
    if ($exists1) {
        return rtrim($path1, '/'); // Remove trailing slash
    }

    $path2a = $documentRoot !== '' ? ($documentRoot . '/wscrm') : '';
    $exists2a = $path2a !== '' ? is_dir($path2a) : false;
    $debugInfo['checks'][] = ['path' => $path2a, 'exists' => $exists2a, 'priority' => '2a'];
    if ($exists2a) {
        return rtrim($path2a, '/');
    }

    $path2b = $documentRoot !== '' ? ($documentRoot . '/public/wscrm') : '';
    $exists2b = $path2b !== '' ? is_dir($path2b) : false;
    $debugInfo['checks'][] = ['path' => $path2b, 'exists' => $exists2b, 'priority' => '2b'];
    if ($exists2b) {
        return rtrim($path2b, '/');
    }

    $path2c = $publicHtmlDir . '/wscrm';
    $exists2c = is_dir($path2c);
    $debugInfo['checks'][] = ['path' => $path2c, 'exists' => $exists2c, 'priority' => '2c'];
    if ($exists2c) {
        return rtrim($path2c, '/');
    }

    // Priority 3: Check relative to install directory
    $path3 = $currentDir . '/../wscrm';
    $path3 = realpath($path3); // Resolve relative path
    if ($path3) {
        $path3 = str_replace('\\', '/', $path3); // Normalize
        $exists3 = is_dir($path3);
        $debugInfo['checks'][] = ['path' => $path3, 'exists' => $exists3, 'priority' => 3];
        if ($exists3) {
            return rtrim($path3, '/'); // Remove trailing slash
        }
    }

    // Store debug info in session/global for troubleshooting
    $GLOBALS['wscrm_debug'] = $debugInfo;


    function detectIncomingWscrmFolder($targetWscrmPath)
    {
        $targetWscrmPath = rtrim(str_replace('\\', '/', (string) $targetWscrmPath), '/');
        $targetReal = ($targetWscrmPath !== '' && is_dir($targetWscrmPath)) ? realpath($targetWscrmPath) : null;

        $currentDir = rtrim(str_replace('\\', '/', __DIR__), '/');
        $publicDir = dirname($currentDir);
        $publicDir = rtrim(str_replace('\\', '/', (string) $publicDir), '/');
        $documentRoot = rtrim(str_replace('\\', '/', (string) ($_SERVER['DOCUMENT_ROOT'] ?? '')), '/');

        $candidates = array_unique(array_filter([
            ($documentRoot !== '' ? $documentRoot . '/wscrm' : null),
            ($documentRoot !== '' ? $documentRoot . '/public/wscrm' : null),
            ($publicDir !== '' ? $publicDir . '/wscrm' : null),
        ]));

        foreach ($candidates as $candidate) {
            if (! is_dir($candidate)) {
                continue;
            }
            $candidateReal = realpath($candidate);
            if ($targetReal && $candidateReal && $candidateReal === $targetReal) {
                continue;
            }
            if ($candidate === $targetWscrmPath) {
                continue;
            }
            return rtrim(str_replace('\\', '/', (string) $candidate), '/');
        }

        return '';
    }
    return false;
}

function moveWscrmFolder($wscrmPath, $targetPath)
{
    // Normalize paths
    $wscrmPath = rtrim(str_replace('\\', '/', $wscrmPath), '/');
    $targetPath = rtrim(str_replace('\\', '/', $targetPath), '/');

    if (! is_dir($wscrmPath)) {
        return ['success' => false, 'message' => 'Folder wscrm tidak ditemukan di: ' . $wscrmPath];
    }

    if (is_dir($targetPath)) {
        return ['success' => false, 'message' => 'Folder target sudah ada di: ' . $targetPath];
    }

    // Create target directory if parent doesn't exist
    $targetParent = dirname($targetPath);
    if (! is_dir($targetParent)) {
        if (! mkdir($targetParent, 0755, true)) {
            return ['success' => false, 'message' => 'Gagal membuat direktori parent: ' . $targetParent];
        }
    }

    // Move the folder
    if (rename($wscrmPath, $targetPath)) {
        return ['success' => true, 'message' => 'Folder wscrm berhasil dipindahkan ke: ' . $targetPath];
    }

    $copied = copyDirectory($wscrmPath, $targetPath);
    if (! $copied) {
        return ['success' => false, 'message' => 'Gagal memindahkan folder wscrm'];
    }

    $deleted = deleteDirectoryRecursive($wscrmPath);
    if (! $deleted) {
        return ['success' => false, 'message' => 'Folder wscrm berhasil disalin, tapi gagal menghapus folder sumber. Silakan hapus manual: ' . $wscrmPath];
    }

    return ['success' => true, 'message' => 'Folder wscrm berhasil dipindahkan ke: ' . $targetPath];
}

function copyWscrmFolder($wscrmPath, $targetPath)
{
    // Normalize paths
    $wscrmPath = rtrim(str_replace('\\', '/', $wscrmPath), '/');
    $targetPath = rtrim(str_replace('\\', '/', $targetPath), '/');

    if (! is_dir($wscrmPath)) {
        return ['success' => false, 'message' => 'Folder wscrm tidak ditemukan di: ' . $wscrmPath];
    }

    if (is_dir($targetPath)) {
        return ['success' => false, 'message' => 'Folder target sudah ada di: ' . $targetPath];
    }

    // Create target directory
    if (! mkdir($targetPath, 0755, true)) {
        return ['success' => false, 'message' => 'Gagal membuat direktori target: ' . $targetPath];
    }

    // Copy directory recursively
    $result = copyDirectory($wscrmPath, $targetPath);

    if ($result) {
        return ['success' => true, 'message' => 'Folder wscrm berhasil disalin ke: ' . $targetPath];
    } else {
        return ['success' => false, 'message' => 'Gagal menyalin folder wscrm'];
    }
}

function copyDirectory($src, $dst)
{
    $dir = opendir($src);
    if (! $dir) {
        return false;
    }

    while (($file = readdir($dir)) !== false) {
        if ($file != '.' && $file != '..') {
            $srcFile = $src . '/' . $file;
            $dstFile = $dst . '/' . $file;

            if (is_dir($srcFile)) {
                if (! mkdir($dstFile, 0755, true)) {
                    closedir($dir);

                    return false;
                }
                if (! copyDirectory($srcFile, $dstFile)) {
                    closedir($dir);

                    return false;
                }
            } else {
                if (! copy($srcFile, $dstFile)) {
                    closedir($dir);

                    return false;
                }
            }
        }
    }

    closedir($dir);

    return true;
}

function deleteDirectoryRecursive($dir)
{
    if (! is_dir($dir)) {
        return false;
    }

    $files = array_diff(scandir($dir), ['.', '..']);

    foreach ($files as $file) {
        $filePath = $dir . '/' . $file;
        if (is_dir($filePath)) {
            deleteDirectoryRecursive($filePath);
        } else {
            unlink($filePath);
        }
    }

    return rmdir($dir);
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'skip_installer':
            $host = $_SERVER['HTTP_HOST'] ?? '';
            $hostNoPort = explode(':', $host)[0];
            $isLocalHost = in_array($hostNoPort, ['localhost', '127.0.0.1', '::1'], true);

            if (! $isLocalHost) {
                echo json_encode(['success' => false, 'message' => 'Skip installer hanya tersedia untuk local']);
                exit;
            }

            $written = [];

            $wscrmPath = detectWscrmFolder();
            if ($wscrmPath && is_dir($wscrmPath)) {
                $wscrmStorage = rtrim(str_replace('\\', '/', $wscrmPath), '/') . '/storage';
                if (! is_dir($wscrmStorage)) {
                    @mkdir($wscrmStorage, 0755, true);
                }
                if (is_dir($wscrmStorage) && @file_put_contents($wscrmStorage . '/installer.skip', date('Y-m-d H:i:s'))) {
                    $written[] = $wscrmStorage . '/installer.skip';
                }
            }

            $laravelRoot = realpath(__DIR__ . '/../..');
            if ($laravelRoot) {
                $laravelStorage = rtrim(str_replace('\\', '/', $laravelRoot), '/') . '/storage';
                if (! is_dir($laravelStorage)) {
                    @mkdir($laravelStorage, 0755, true);
                }
                if (is_dir($laravelStorage) && @file_put_contents($laravelStorage . '/installer.skip', date('Y-m-d H:i:s'))) {
                    $written[] = $laravelStorage . '/installer.skip';
                }
            }

            if (empty($written)) {
                echo json_encode(['success' => false, 'message' => 'Gagal membuat marker skip. Pastikan folder storage writable.']);
                exit;
            }

            echo json_encode([
                'success' => true,
                'message' => 'Installer berhasil di-skip untuk local. Redirect installer akan dinonaktifkan.',
            ]);
            exit;

        case 'detect_wscrm':
            $wscrmPath = detectWscrmFolder();
            if ($wscrmPath) {
                // Check if wscrm is already in target location
                $publicHtmlParent = dirname($_SERVER['DOCUMENT_ROOT']);
                $targetPath = $publicHtmlParent . '/wscrm';
                $isAlreadyInTargetLocation = (realpath($wscrmPath) === realpath($targetPath));

                echo json_encode([
                    'success' => true,
                    'path' => $wscrmPath,
                    'message' => 'Folder wscrm ditemukan di: ' . $wscrmPath,
                    'already_in_target_location' => $isAlreadyInTargetLocation,
                    'target_path' => $targetPath,
                    'debug' => $GLOBALS['wscrm_debug'] ?? null,
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Folder wscrm tidak ditemukan',
                    'debug' => $GLOBALS['wscrm_debug'] ?? null,
                ]);
            }
            exit;

        case 'move_wscrm':
            // Debug: Log semua data POST yang diterima
            error_log('POST data received: ' . print_r($_POST, true));

            $wscrmPath = $_POST['wscrm_path'] ?? '';

            // Debug: Log nilai yang diambil
            error_log('wscrm_path: ' . $wscrmPath);

            if (empty($wscrmPath)) {
                $errorMsg = 'Path wscrm tidak valid. Received: ' . var_export($wscrmPath, true);
                error_log('ERROR ' . $errorMsg);
                echo json_encode(['success' => false, 'message' => $errorMsg, 'debug_post' => $_POST]);
                exit;
            }

            // Normalize the received path
            $wscrmPath = rtrim(str_replace('\\', '/', $wscrmPath), '/');

            // Determine target path (sejajar dengan public_html)
            $publicHtmlParent = str_replace('\\', '/', dirname($_SERVER['DOCUMENT_ROOT']));
            $targetPath = $publicHtmlParent . '/wscrm';

            $result = moveWscrmFolder($wscrmPath, $targetPath);

            echo json_encode($result);
            exit;

        case 'configure_env':
            // Log received data for debugging
            error_log('configure_env action started');
            error_log('POST data: ' . print_r($_POST, true));

            $appUrl = $_POST['app_url'] ?? '';
            $appName = $_POST['app_name'] ?? 'WSCRM';
            $dbType = $_POST['db_type'] ?? 'sqlite';

            // Validate inputs
            if (empty($appUrl)) {
                echo json_encode(['success' => false, 'message' => 'Application URL harus diisi']);
                exit;
            }

            // Pastikan folder wscrm berada di lokasi target (sejajar dengan public_html).
            // Pindah folder dilakukan otomatis saat klik install (configure_env), tidak ada step terpisah.
            $detectedWscrmPath = detectWscrmFolder();
            $publicHtmlParent = str_replace('\\', '/', dirname($_SERVER['DOCUMENT_ROOT']));
            $targetWscrmPath = $publicHtmlParent . '/wscrm';

            if ($detectedWscrmPath) {
                $normalizedDetected = rtrim(str_replace('\\', '/', $detectedWscrmPath), '/');
                $normalizedTarget = rtrim(str_replace('\\', '/', $targetWscrmPath), '/');

                $detectedReal = realpath($normalizedDetected);
                $targetReal = realpath($normalizedTarget);

                $isSameLocation = $detectedReal && $targetReal ? ($detectedReal === $targetReal) : ($normalizedDetected === $normalizedTarget);

                if (! $isSameLocation && ! is_dir($normalizedTarget)) {
                    $moveResult = moveWscrmFolder($normalizedDetected, $normalizedTarget);
                    if (! ($moveResult['success'] ?? false)) {
                        echo json_encode([
                            'success' => false,
                            'message' => 'Gagal memindahkan folder wscrm: ' . ($moveResult['message'] ?? 'Unknown error'),
                            'details' => $moveResult,
                        ]);
                        exit;
                    }
                }
            }

            error_log('Target wscrm path: ' . $targetWscrmPath);
            error_log('Directory exists: ' . (is_dir($targetWscrmPath) ? 'YES' : 'NO'));

            if (! is_dir($targetWscrmPath)) {
                $fallback = $detectedWscrmPath ?: $targetWscrmPath;
                error_log('ERROR Target wscrm directory not found: ' . $fallback);
                echo json_encode(['success' => false, 'message' => 'Folder wscrm tidak ditemukan. Pastikan file package sudah diextract dengan benar.']);
                exit;
            }

            // Create .env file
            $envPath = $targetWscrmPath . '/.env';
            $envTemplate = $targetWscrmPath . '/.env.example';

            error_log('Env template path: ' . $envTemplate);
            error_log('Template exists: ' . (file_exists($envTemplate) ? 'YES' : 'NO'));
            error_log('Target env path: ' . $envPath);

            if (! file_exists($envTemplate)) {
                error_log('ERROR .env.example not found: ' . $envTemplate);
                echo json_encode(['success' => false, 'message' => 'File .env.example tidak ditemukan: ' . $envTemplate]);
                exit;
            }

            // Read .env.example and modify values
            $envContent = file_get_contents($envTemplate);

            if ($dbType === 'mysql') {
                $dbHost = $_POST['db_host'] ?? 'localhost';
                $dbPort = $_POST['db_port'] ?? '3306';
                $dbName = $_POST['db_name'] ?? '';
                $dbUsername = $_POST['db_username'] ?? '';
                $dbPassword = $_POST['db_password'] ?? '';

                if (empty($dbName) || empty($dbUsername)) {
                    echo json_encode(['success' => false, 'message' => 'Database name dan username harus diisi untuk MySQL']);
                    exit;
                }

                try {
                    $dsn = "mysql:host={$dbHost};port={$dbPort};charset=utf8mb4";
                    $pdo = new PDO($dsn, $dbUsername, $dbPassword, [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_TIMEOUT => 5,
                    ]);

                    $stmt = $pdo->query("SHOW DATABASES LIKE '{$dbName}'");
                    if ($stmt->rowCount() === 0) {
                        $pdo->exec("CREATE DATABASE `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                    }
                    $pdo->exec("USE `{$dbName}`");
                    error_log('Database connection test successful');
                } catch (PDOException $e) {
                    error_log('ERROR Database connection test failed: ' . $e->getMessage());
                    echo json_encode(['success' => false, 'message' => 'Koneksi database gagal: ' . $e->getMessage()]);
                    exit;
                }
            } else {
                $dbHost = null;
                $dbPort = null;
                $dbName = null;
                $dbUsername = null;
                $dbPassword = null;
            }

            $envContent = setEnvValue($envContent, 'APP_NAME', $appName);
            $envContent = setEnvValue($envContent, 'APP_URL', $appUrl);
            $envContent = setEnvValue($envContent, 'APP_ENV', 'production');
            $envContent = setEnvValue($envContent, 'APP_DEBUG', 'false');

            if ($dbType === 'mysql') {
                $envContent = setEnvValue($envContent, 'DB_CONNECTION', 'mysql');
                $envContent = setEnvValue($envContent, 'DB_HOST', $dbHost);
                $envContent = setEnvValue($envContent, 'DB_PORT', (string) $dbPort);
                $envContent = setEnvValue($envContent, 'DB_DATABASE', $dbName);
                $envContent = setEnvValue($envContent, 'DB_USERNAME', $dbUsername);
                $envContent = setEnvValue($envContent, 'DB_PASSWORD', $dbPassword);
            } else {
                $envContent = setEnvValue($envContent, 'DB_CONNECTION', 'sqlite');
                $envContent = setEnvValue($envContent, 'DB_DATABASE', 'database/database.sqlite');
            }

            $envContent = setEnvValue($envContent, 'CACHE_STORE', 'file');
            $envContent = setEnvValue($envContent, 'SESSION_DRIVER', 'file');
            $envContent = setEnvValue($envContent, 'QUEUE_CONNECTION', 'sync');

            // Write .env file
            error_log('Writing .env file to: ' . $envPath);
            error_log('Content length: ' . strlen($envContent) . ' bytes');

            if (! file_put_contents($envPath, $envContent)) {
                error_log('ERROR Failed to write .env file to: ' . $envPath);
                $parentDir = dirname($envPath);
                error_log('Parent directory writable: ' . (is_writable($parentDir) ? 'YES' : 'NO'));
                echo json_encode(['success' => false, 'message' => 'Gagal menulis file .env ke: ' . $envPath]);
                exit;
            }

            error_log('.env file written successfully');

            clearBootstrapCache($targetWscrmPath);

            // Generate APP_KEY manually (exec() disabled on hosting)
            $keyGenerated = false;
            $appKey = '';

            // Generate key manually since exec() is disabled
            error_log('Generating APP_KEY manually (exec disabled)');
            $appKey = 'base64:' . base64_encode(random_bytes(32));

            // Update .env file with generated key
            $envContent = file_get_contents($envPath);
            if (strpos($envContent, 'APP_KEY=') !== false) {
                $envContent = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY=' . $appKey, $envContent);
            } else {
                $envContent .= "\nAPP_KEY=" . $appKey;
            }

            if (file_put_contents($envPath, $envContent)) {
                $keyGenerated = true;
                error_log('APP_KEY generated and saved: ' . substr($appKey, 0, 20) . '...');
            } else {
                error_log('ERROR Failed to save APP_KEY to .env file');
            }

            // Create installer lock file
            $lockPath = $targetWscrmPath . '/storage/installer.lock';
            if (! file_put_contents($lockPath, date('Y-m-d H:i:s'))) {
                echo json_encode(['success' => false, 'message' => 'Gagal membuat installer lock file']);
                exit;
            }

            // Generate .htaccess from template if exists
            $htaccessTemplatePath = __DIR__ . '/htaccess-template.txt';
            if (file_exists($htaccessTemplatePath)) {
                $htaccessContent = file_get_contents($htaccessTemplatePath);
                $htaccessContent = str_replace('{{DATE}}', date('Y-m-d H:i:s'), $htaccessContent);

                $scriptFilename = (string) ($_SERVER['SCRIPT_FILENAME'] ?? '');
                $installDir = $scriptFilename !== '' ? dirname($scriptFilename) : __DIR__;
                $appWebRootDir = dirname($installDir);
                $publicHtmlDir = (string) ($_SERVER['DOCUMENT_ROOT'] ?? '');

                $candidateDirs = array_values(array_unique(array_filter([
                    $appWebRootDir,
                    $publicHtmlDir,
                ])));

                $htaccessPath = $candidateDirs[0] . '/.htaccess';

                error_log('Creating .htaccess at: ' . $htaccessPath);

                $written = false;
                foreach ($candidateDirs as $dir) {
                    $path = rtrim($dir, '/\\') . '/.htaccess';
                    if (@file_put_contents($path, $htaccessContent) !== false) {
                        error_log('.htaccess created successfully at: ' . $path);
                        $written = true;
                        break;
                    }
                }

                if (! $written) {
                    error_log('ERROR Failed to create .htaccess in: ' . implode(', ', $candidateDirs));
                }
            }

            $successMessage = 'Environment berhasil dikonfigurasi. Installer lock file telah dibuat.';
            if ($keyGenerated) {
                $successMessage .= ' Application key telah digenerate untuk keamanan.';
            }

            echo json_encode([
                'success' => true,
                'message' => $successMessage,
            ]);
            exit;

        case 'delete_install_folder':
            // Security check - only allow deletion if installation is complete
            $targetWscrmPath = str_replace('\\', '/', dirname($_SERVER['DOCUMENT_ROOT'])) . '/wscrm';

            // Debug: Check multiple possible paths including the one from UI
            $detectedPath = detectWscrmFolder();
            $possiblePaths = [
                $targetWscrmPath,
                $detectedPath,
            ];

            // Remove duplicates and empty values
            $possiblePaths = array_unique(array_filter($possiblePaths));

            $isInstallComplete = false;
            $actualWscrmPath = '';

            // Generate APP_KEY if not exists to prevent errors
            foreach ($possiblePaths as $path) {
                if ($path && is_dir($path)) {
                    $envFile = $path . '/.env';
                    if (file_exists($envFile)) {
                        $envContent = file_get_contents($envFile);
                        // Check if APP_KEY is empty or not set
                        if (preg_match('/^APP_KEY=\s*$/m', $envContent) || ! preg_match('/^APP_KEY=/m', $envContent)) {
                            $appKey = 'base64:' . base64_encode(random_bytes(32));

                            if (preg_match('/^APP_KEY=/m', $envContent)) {
                                $envContent = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY=' . $appKey, $envContent);
                            } else {
                                $envContent .= "\nAPP_KEY=" . $appKey;
                            }

                            file_put_contents($envFile, $envContent);
                            error_log("APP_KEY generated for: $path");

                            // Note: Cache clearing skipped (exec() disabled on hosting)
                            error_log('Cache clearing skipped (exec disabled on shared hosting)');
                        }
                        break;
                    }
                }
            }

            foreach ($possiblePaths as $path) {
                if ($path && is_dir($path)) {
                    $envFile = $path . '/.env';
                    $lockFile = $path . '/storage/installer.lock';
                    $storageDir = $path . '/storage';

                    // Log detailed check for debugging
                    error_log("Checking path: $path");
                    error_log('Directory exists: ' . (is_dir($path) ? 'YES' : 'NO'));
                    error_log('.env exists: ' . (file_exists($envFile) ? 'YES' : 'NO') . " at $envFile");
                    error_log('installer.lock exists: ' . (file_exists($lockFile) ? 'YES' : 'NO') . " at $lockFile");
                    error_log('storage dir exists: ' . (is_dir($storageDir) ? 'YES' : 'NO') . " at $storageDir");

                    // Check if .env exists (primary requirement)
                    if (file_exists($envFile)) {
                        // Check if installer.lock exists OR if storage directory exists (fallback)
                        if (file_exists($lockFile) || is_dir($storageDir)) {
                            $isInstallComplete = true;
                            $actualWscrmPath = $path;
                            error_log("Installation complete found at: $path");
                            error_log('.env: YES, installer.lock: ' . (file_exists($lockFile) ? 'YES' : 'NO') . ', storage: ' . (is_dir($storageDir) ? 'YES' : 'NO'));
                            break;
                        }
                    }
                }
            }

            if (! $isInstallComplete) {
                // Debug info for troubleshooting
                $debugInfo = [
                    'DOCUMENT_ROOT' => $_SERVER['DOCUMENT_ROOT'],
                    'dirname_DOCUMENT_ROOT' => dirname($_SERVER['DOCUMENT_ROOT']),
                    'calculated_target' => $targetWscrmPath,
                    'detected_wscrm' => $detectedPath,
                    'possible_paths' => $possiblePaths,
                    'checks' => [],
                ];

                foreach ($possiblePaths as $path) {
                    if ($path) {
                        $envPath = $path . '/.env';
                        $lockPath = $path . '/storage/installer.lock';
                        $debugInfo['checks'][] = [
                            'path' => $path,
                            'is_dir' => is_dir($path),
                            'has_env' => file_exists($envPath),
                            'env_path' => $envPath,
                            'has_lock' => file_exists($lockPath),
                            'lock_path' => $lockPath,
                            'storage_dir_exists' => is_dir($path . '/storage'),
                        ];
                    }
                }

                echo json_encode([
                    'success' => false,
                    'message' => 'Instalasi belum selesai. Folder install tidak dapat dihapus. Debug info tersedia di console.',
                    'debug' => $debugInfo,
                ]);
                exit;
            }

            $maintenanceDetail = null;
            if ($actualWscrmPath && is_dir($actualWscrmPath)) {
                $maintenancePath = $actualWscrmPath . '/storage/framework/maintenance.php';
                if (file_exists($maintenancePath)) {
                    if (@unlink($maintenancePath)) {
                        $maintenanceDetail = 'Maintenance mode dinonaktifkan';
                    } else {
                        $maintenanceDetail = 'Maintenance mode terdeteksi, tapi gagal menghapus maintenance.php. Silakan hapus manual: ' . $maintenancePath;
                    }
                }
            }

            $upDetail = null;
            if ($actualWscrmPath && is_dir($actualWscrmPath)) {
                $currentDir = getcwd();
                chdir($actualWscrmPath);
                $upOutput = executeSimpleCommand('php artisan up');
                chdir($currentDir);

                if (is_string($upOutput) && str_starts_with($upOutput, 'Error:')) {
                    $upDetail = 'Artisan up dilewati: ' . $upOutput;
                } else {
                    $upDetail = 'Artisan up dijalankan';
                }
            }

            $optimizeDetail = null;
            if ($actualWscrmPath && is_dir($actualWscrmPath)) {
                $currentDir = getcwd();
                chdir($actualWscrmPath);
                $optimizeOutput = executeSimpleCommand('php artisan optimize');
                chdir($currentDir);

                if (is_string($optimizeOutput) && str_starts_with($optimizeOutput, 'Error:')) {
                    $optimizeDetail = 'Optimize dilewati: ' . $optimizeOutput;
                } else {
                    $optimizeDetail = 'Optimize berhasil dijalankan';
                }
            }

            // Delete install folder recursively
            $installPath = __DIR__;

            function deleteDirectory($dir)
            {
                if (! is_dir($dir)) {
                    return false;
                }

                $files = array_diff(scandir($dir), ['.', '..']);

                foreach ($files as $file) {
                    $filePath = $dir . '/' . $file;
                    if (is_dir($filePath)) {
                        deleteDirectory($filePath);
                    } else {
                        unlink($filePath);
                    }
                }

                return rmdir($dir);
            }

            if (deleteDirectory($installPath)) {
                // Prepare success message with details
                $successMessage = 'Folder install berhasil dihapus.';
                $details = [];

                // Check if APP_KEY was generated (look for log entries)
                if ($actualWscrmPath) {
                    $envFile = $actualWscrmPath . '/.env';
                    if (file_exists($envFile)) {
                        $envContent = file_get_contents($envFile);
                        if (preg_match('/^APP_KEY=base64:/m', $envContent)) {
                            $details[] = 'APP_KEY sudah terpasang';
                        }
                    }
                }

                if ($maintenanceDetail) {
                    $details[] = $maintenanceDetail;
                }

                if ($upDetail) {
                    $details[] = $upDetail;
                }

                if ($optimizeDetail) {
                    $details[] = $optimizeDetail;
                } else {
                    $details[] = 'Optimize tidak dijalankan (path wscrm tidak terdeteksi atau tidak bisa diakses)';
                }

                if (! empty($details)) {
                    $successMessage .= "\n\n" . implode("\n", $details);
                }

                echo json_encode([
                    'success' => true,
                    'message' => $successMessage,
                    'details' => $details,
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal menghapus folder install. Periksa permission folder.']);
            }
            exit;

        case 'cleanup_after_install':
            $targetWscrmPath = str_replace('\\', '/', dirname($_SERVER['DOCUMENT_ROOT'])) . '/wscrm';
            $detectedPath = detectWscrmFolder();
            $possiblePaths = array_unique(array_filter([$targetWscrmPath, $detectedPath]));

            $actualWscrmPath = '';
            foreach ($possiblePaths as $path) {
                if (! $path || ! is_dir($path)) {
                    continue;
                }

                if (file_exists($path . '/.env')) {
                    $actualWscrmPath = $path;
                    break;
                }
            }

            if (! $actualWscrmPath) {
                echo json_encode(['success' => false, 'message' => 'Folder wscrm tidak ditemukan atau file .env belum ada.']);
                exit;
            }

            $outputs = [];
            $runInWscrm = function ($label, $command) use ($actualWscrmPath, &$outputs) {
                $currentDir = getcwd();
                chdir($actualWscrmPath);
                $output = executeSimpleCommand($command);
                chdir($currentDir);
                $outputs[] = "== {$label} ==\n{$command}\n{$output}";
            };

            $envFile = $actualWscrmPath . '/.env';
            if (file_exists($envFile)) {
                $envContent = file_get_contents($envFile);
                if (preg_match('/^APP_KEY=\s*$/m', $envContent) || ! preg_match('/^APP_KEY=/m', $envContent)) {
                    $appKey = 'base64:' . base64_encode(random_bytes(32));

                    if (preg_match('/^APP_KEY=/m', $envContent)) {
                        $envContent = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY=' . $appKey, $envContent);
                    } else {
                        $envContent .= "\nAPP_KEY=" . $appKey;
                    }

                    file_put_contents($envFile, $envContent);
                    $outputs[] = "== APP_KEY ==\nAPP_KEY dibuat otomatis (fallback)";
                }
            }

            $runInWscrm('Artisan Up', 'php artisan up');

            $lockPath = $actualWscrmPath . '/storage/installer.lock';
            if (! is_dir(dirname($lockPath))) {
                @mkdir(dirname($lockPath), 0755, true);
            }
            if (! file_exists($lockPath)) {
                @file_put_contents($lockPath, date('Y-m-d H:i:s'));
            }
            $outputs[] = "== Installer Lock ==\ninstaller.lock dibuat di: {$lockPath}";

            $actualReal = realpath($actualWscrmPath);
            $targetLooksValid = is_file($actualWscrmPath . '/artisan') && (is_file($actualWscrmPath . '/vendor/autoload.php') || is_file($actualWscrmPath . '/bootstrap/app.php'));
            $documentRoot = rtrim(str_replace('\\', '/', (string) ($_SERVER['DOCUMENT_ROOT'] ?? '')), '/');
            $publicDir = rtrim(str_replace('\\', '/', dirname(__DIR__)), '/');
            $cleanupCandidates = array_unique(array_filter([
                ($documentRoot !== '' ? $documentRoot . '/wscrm' : null),
                ($documentRoot !== '' ? $documentRoot . '/public/wscrm' : null),
                ($publicDir !== '' ? $publicDir . '/wscrm' : null),
            ]));

            if ($targetLooksValid && $actualReal) {
                foreach ($cleanupCandidates as $candidate) {
                    if (! is_dir($candidate)) {
                        continue;
                    }
                    $candidateReal = realpath($candidate);
                    if ($candidateReal && $candidateReal === $actualReal) {
                        continue;
                    }
                    $deletedSource = deleteDirectoryRecursive($candidate);
                    $outputs[] = "== Cleanup wscrm (public_html) ==\nPath: {$candidate}\nResult: " . ($deletedSource ? 'DELETED' : 'FAILED');
                }
            } else {
                $outputs[] = "== Cleanup wscrm (public_html) ==\nDilewati demi keamanan (target wscrm tidak tervalidasi).";
            }

            $installPath = __DIR__;
            $deleted = false;

            if (function_exists('deleteDirectory')) {
                $deleted = (bool) deleteDirectory($installPath);
            }

            if (! $deleted) {
                $deleteDirectoryLocal = function ($dir) use (&$deleteDirectoryLocal) {
                    if (! is_dir($dir)) {
                        return false;
                    }
                    $files = array_diff(scandir($dir), ['.', '..']);
                    foreach ($files as $file) {
                        $filePath = $dir . '/' . $file;
                        if (is_dir($filePath)) {
                            $deleteDirectoryLocal($filePath);
                        } else {
                            @unlink($filePath);
                        }
                    }
                    return @rmdir($dir);
                };

                $deleted = (bool) $deleteDirectoryLocal($installPath);
            }

            echo json_encode([
                'success' => (bool) $deleted,
                'message' => $deleted ? 'Cleanup selesai. Folder install berhasil dihapus.' : 'Cleanup selesai, tapi gagal menghapus folder install. Silakan hapus manual.',
                'output' => implode("\n\n", $outputs),
            ]);
            exit;

        case 'detect_update':
            $documentRoot = rtrim(str_replace('\\', '/', (string) ($_SERVER['DOCUMENT_ROOT'] ?? '')), '/');
            $publicHtmlParent = str_replace('\\', '/', dirname($documentRoot));
            $targetWscrmPath = $publicHtmlParent . '/wscrm';
            $incomingWscrmPath = detectIncomingWscrmFolder($targetWscrmPath);

            $installed = is_dir($targetWscrmPath) && file_exists($targetWscrmPath . '/.env');
            $incoming = $incomingWscrmPath !== '' && is_dir($incomingWscrmPath);

            echo json_encode([
                'success' => true,
                'installed' => $installed,
                'target' => $targetWscrmPath,
                'incoming' => $incoming,
                'incoming_path' => $incomingWscrmPath,
            ]);
            exit;

        case 'update_app':
            $documentRoot = rtrim(str_replace('\\', '/', (string) ($_SERVER['DOCUMENT_ROOT'] ?? '')), '/');
            $publicHtmlParent = str_replace('\\', '/', dirname($documentRoot));
            $targetWscrmPath = $publicHtmlParent . '/wscrm';

            if (! is_dir($targetWscrmPath) || ! file_exists($targetWscrmPath . '/.env')) {
                echo json_encode(['success' => false, 'message' => 'Target wscrm tidak ditemukan atau .env belum ada. Update hanya bisa dijalankan jika aplikasi sudah terinstall.']);
                exit;
            }

            $incomingWscrmPath = detectIncomingWscrmFolder($targetWscrmPath);
            if (! $incomingWscrmPath || ! is_dir($incomingWscrmPath)) {
                echo json_encode(['success' => false, 'message' => 'Folder wscrm update tidak ditemukan di public_html. Silakan upload dan extract package update terlebih dahulu.']);
                exit;
            }

            $outputs = [];
            $timestamp = date('Ymd_His');
            $backupPath = $publicHtmlParent . '/wscrm_backup_' . $timestamp;

            $outputs[] = "== Update Info ==\nTarget: {$targetWscrmPath}\nIncoming: {$incomingWscrmPath}\nBackup: {$backupPath}";

            $currentDir = getcwd();
            chdir($targetWscrmPath);
            $outputs[] = "== Maintenance Down ==\nphp artisan down\n" . executeSimpleCommand('php artisan down');
            chdir($currentDir);

            $moveExisting = moveWscrmFolder($targetWscrmPath, $backupPath);
            if (! ($moveExisting['success'] ?? false)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Gagal membuat backup target wscrm: ' . ($moveExisting['message'] ?? 'Unknown error'),
                    'output' => implode("\n\n", $outputs),
                ]);
                exit;
            }
            $outputs[] = "== Backup ==\n" . ($moveExisting['message'] ?? 'Backup done');

            $moveIncoming = moveWscrmFolder($incomingWscrmPath, $targetWscrmPath);
            if (! ($moveIncoming['success'] ?? false)) {
                $rollback = moveWscrmFolder($backupPath, $targetWscrmPath);
                $outputs[] = "== Rollback ==\n" . (($rollback['success'] ?? false) ? 'Rollback berhasil' : 'Rollback gagal: ' . ($rollback['message'] ?? 'Unknown error'));

                echo json_encode([
                    'success' => false,
                    'message' => 'Gagal memindahkan wscrm update ke target: ' . ($moveIncoming['message'] ?? 'Unknown error'),
                    'output' => implode("\n\n", $outputs),
                ]);
                exit;
            }
            $outputs[] = "== Deploy New Code ==\n" . ($moveIncoming['message'] ?? 'Deploy done');

            $backupEnv = $backupPath . '/.env';
            if (is_file($backupEnv)) {
                @copy($backupEnv, $targetWscrmPath . '/.env');
                $outputs[] = "== Restore .env ==\n.env dipulihkan dari backup";
            }

            $backupSqlite = $backupPath . '/database/database.sqlite';
            if (is_file($backupSqlite)) {
                @mkdir($targetWscrmPath . '/database', 0755, true);
                @copy($backupSqlite, $targetWscrmPath . '/database/database.sqlite');
                $outputs[] = "== Restore SQLite ==\ndatabase.sqlite dipulihkan dari backup";
            }

            $backupStorage = $backupPath . '/storage';
            if (is_dir($backupStorage)) {
                deleteDirectoryRecursive($targetWscrmPath . '/storage');
                copyDirectory($backupStorage, $targetWscrmPath . '/storage');
                $outputs[] = "== Restore Storage ==\nstorage dipulihkan dari backup";
            }

            clearBootstrapCache($targetWscrmPath);

            $runInTarget = function ($label, $command) use ($targetWscrmPath, &$outputs) {
                $currentDir = getcwd();
                chdir($targetWscrmPath);
                $output = executeSimpleCommand($command);
                chdir($currentDir);
                $outputs[] = "== {$label} ==\n{$command}\n{$output}";
            };

            $runInTarget('Migrations', 'php artisan migrate --force');
            $runInTarget('Storage Link', 'php artisan storage:link');
            $runInTarget('Clear Cache', 'php artisan optimize:clear');
            $runInTarget('Config Cache', 'php artisan config:cache');
            $runInTarget('Artisan Up', 'php artisan up');

            echo json_encode([
                'success' => true,
                'message' => 'Update selesai. Lanjutkan cleanup untuk menghapus folder install.',
                'output' => implode("\n\n", $outputs),
                'backup' => $backupPath,
            ]);
            exit;

        case 'finalize_install':
            $targetWscrmPath = str_replace('\\', '/', dirname($_SERVER['DOCUMENT_ROOT'])) . '/wscrm';
            $detectedPath = detectWscrmFolder();
            $possiblePaths = array_unique(array_filter([$targetWscrmPath, $detectedPath]));

            $actualWscrmPath = '';
            foreach ($possiblePaths as $path) {
                if (! $path || ! is_dir($path)) {
                    continue;
                }

                if (file_exists($path . '/.env')) {
                    $actualWscrmPath = $path;
                    break;
                }
            }

            if (! $actualWscrmPath) {
                echo json_encode(['success' => false, 'message' => 'Folder wscrm tidak ditemukan atau file .env belum ada.']);
                exit;
            }

            $envFile = $actualWscrmPath . '/.env';
            if (file_exists($envFile)) {
                $envContent = file_get_contents($envFile);
                if (preg_match('/^APP_KEY=\s*$/m', $envContent) || ! preg_match('/^APP_KEY=/m', $envContent)) {
                    $appKey = 'base64:' . base64_encode(random_bytes(32));

                    if (preg_match('/^APP_KEY=/m', $envContent)) {
                        $envContent = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY=' . $appKey, $envContent);
                    } else {
                        $envContent .= "\nAPP_KEY=" . $appKey;
                    }

                    file_put_contents($envFile, $envContent);
                }
            }

            $outputs = [];
            $runInWscrm = function ($label, $command) use ($actualWscrmPath, &$outputs) {
                $currentDir = getcwd();
                chdir($actualWscrmPath);
                $output = executeSimpleCommand($command);
                chdir($currentDir);
                $outputs[] = "== {$label} ==\n{$command}\n{$output}";
            };

            $runInWscrm('Migrations', 'php artisan migrate --force');
            $runInWscrm('Storage Link', 'php artisan storage:link');
            $runInWscrm('Clear Cache', 'php artisan optimize:clear');
            $runInWscrm('Optimize', 'php artisan optimize');
            $runInWscrm('Config Cache', 'php artisan config:cache');
            $runInWscrm('Artisan Up', 'php artisan up');

            $lockPath = $actualWscrmPath . '/storage/installer.lock';
            if (! is_dir(dirname($lockPath))) {
                @mkdir(dirname($lockPath), 0755, true);
            }
            if (! file_exists($lockPath)) {
                @file_put_contents($lockPath, date('Y-m-d H:i:s'));
            }

            $actualReal = realpath($actualWscrmPath);
            $targetLooksValid = is_file($actualWscrmPath . '/artisan') && (is_file($actualWscrmPath . '/vendor/autoload.php') || is_file($actualWscrmPath . '/bootstrap/app.php'));
            $documentRoot = rtrim(str_replace('\\', '/', (string) ($_SERVER['DOCUMENT_ROOT'] ?? '')), '/');
            $publicDir = rtrim(str_replace('\\', '/', dirname(__DIR__)), '/');
            $cleanupCandidates = array_unique(array_filter([
                ($documentRoot !== '' ? $documentRoot . '/wscrm' : null),
                ($documentRoot !== '' ? $documentRoot . '/public/wscrm' : null),
                ($publicDir !== '' ? $publicDir . '/wscrm' : null),
            ]));

            if ($targetLooksValid && $actualReal) {
                foreach ($cleanupCandidates as $candidate) {
                    if (! is_dir($candidate)) {
                        continue;
                    }
                    $candidateReal = realpath($candidate);
                    if ($candidateReal && $candidateReal === $actualReal) {
                        continue;
                    }
                    $deletedSource = deleteDirectoryRecursive($candidate);
                    $outputs[] = "== Cleanup wscrm (public_html) ==\nPath: {$candidate}\nResult: " . ($deletedSource ? 'DELETED' : 'FAILED');
                }
            } else {
                $outputs[] = "== Cleanup wscrm (public_html) ==\nDilewati demi keamanan (target wscrm tidak tervalidasi).";
            }

            $installPath = __DIR__;

            if (function_exists('deleteDirectory') && deleteDirectory($installPath)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Install selesai. Folder install berhasil dihapus. Mengalihkan ke aplikasi...',
                    'output' => implode("\n\n", $outputs),
                ]);
                exit;
            }

            // Fallback: define local deleteDirectory if not available in this scope
            $deleteDirectoryLocal = function ($dir) use (&$deleteDirectoryLocal) {
                if (! is_dir($dir)) {
                    return false;
                }
                $files = array_diff(scandir($dir), ['.', '..']);
                foreach ($files as $file) {
                    $filePath = $dir . '/' . $file;
                    if (is_dir($filePath)) {
                        $deleteDirectoryLocal($filePath);
                    } else {
                        @unlink($filePath);
                    }
                }
                return @rmdir($dir);
            };

            $deleted = $deleteDirectoryLocal($installPath);
            echo json_encode([
                'success' => (bool) $deleted,
                'message' => $deleted ? 'Install selesai. Folder install berhasil dihapus. Mengalihkan ke aplikasi...' : 'Install selesai, tapi gagal menghapus folder install. Silakan hapus manual.',
                'output' => implode("\n\n", $outputs),
            ]);
            exit;

        case 'test_database':
            $dbHost = $_POST['db_host'] ?? 'localhost';
            $dbPort = $_POST['db_port'] ?? '3306';
            $dbName = $_POST['db_name'] ?? '';
            $dbUsername = $_POST['db_username'] ?? '';
            $dbPassword = $_POST['db_password'] ?? '';

            if (empty($dbName) || empty($dbUsername)) {
                echo json_encode(['success' => false, 'message' => 'Database name dan username harus diisi']);
                exit;
            }

            try {
                // Test connection
                $dsn = "mysql:host={$dbHost};port={$dbPort};charset=utf8mb4";
                $pdo = new PDO($dsn, $dbUsername, $dbPassword, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_TIMEOUT => 5,
                ]);

                // Check if database exists, create if not
                $stmt = $pdo->query("SHOW DATABASES LIKE '{$dbName}'");
                if ($stmt->rowCount() === 0) {
                    $pdo->exec("CREATE DATABASE `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                    $message = "Database '{$dbName}' berhasil dibuat dan koneksi berhasil!";
                } else {
                    $message = "Koneksi ke database '{$dbName}' berhasil!";
                }

                // Test database selection
                $pdo->exec("USE `{$dbName}`");

                echo json_encode([
                    'success' => true,
                    'message' => $message,
                ]);
            } catch (PDOException $e) {
                $errorMessage = $e->getMessage();

                // Provide user-friendly error messages
                if (strpos($errorMessage, 'Access denied') !== false) {
                    $friendlyMessage = 'Username atau password salah';
                } elseif (strpos($errorMessage, 'Connection refused') !== false || strpos($errorMessage, 'Connection timed out') !== false) {
                    $friendlyMessage = 'Tidak dapat terhubung ke server database. Periksa host dan port.';
                } elseif (strpos($errorMessage, 'Unknown database') !== false) {
                    $friendlyMessage = 'Database tidak ditemukan. Pastikan database sudah dibuat atau akan dibuat otomatis.';
                } else {
                    $friendlyMessage = 'Error: ' . $errorMessage;
                }

                echo json_encode([
                    'success' => false,
                    'message' => $friendlyMessage,
                ]);
            }
            exit;

        case 'laravel_command':
            $command = $_POST['command'] ?? '';
            $detectedWscrmPath = detectWscrmFolder();
            $targetWscrmPath = $detectedWscrmPath ?: (str_replace('\\', '/', dirname($_SERVER['DOCUMENT_ROOT'])) . '/wscrm');

            if (! is_dir($targetWscrmPath)) {
                echo json_encode(['success' => false, 'message' => 'Laravel directory not found', 'debug' => $GLOBALS['wscrm_debug'] ?? null]);
                exit;
            }

            clearBootstrapCache($targetWscrmPath);

            $currentDir = getcwd();
            chdir($targetWscrmPath);

            try {
                $env = parseEnvFile('.env');
                $cacheStore = $env['CACHE_STORE'] ?? ($env['CACHE_DRIVER'] ?? 'database');

                switch ($command) {
                    case 'migrate':
                        $output = executeSimpleCommand('php artisan config:clear');
                        $output .= "\n" . executeSimpleCommand('php artisan migrate --force');
                        break;
                    case 'db_seed':
                        $output = 'Seeder dinonaktifkan di installer untuk penggunaan production.';
                        break;
                    case 'storage_link':
                        $output = executeSimpleCommand('php artisan config:clear');
                        $output .= "\n" . executeSimpleCommand('php artisan storage:link');
                        break;
                    case 'key_generate':
                        $output = executeSimpleCommand('php artisan config:clear');
                        $output .= "\n" . executeSimpleCommand('php artisan key:generate --force');
                        break;
                    case 'clear_cache':
                        $output = executeSimpleCommand('php artisan config:clear');
                        $output .= "\n" . executeSimpleCommand('php artisan route:clear');
                        $output .= "\n" . executeSimpleCommand('php artisan view:clear');
                        if ($cacheStore !== 'database') {
                            $output .= "\n" . executeSimpleCommand('php artisan cache:clear');
                        } else {
                            $output .= "\n" . 'cache:clear dilewati (CACHE_STORE=database membutuhkan koneksi DB)';
                        }
                        break;
                    case 'optimize':
                        $output = executeSimpleCommand('php artisan optimize');
                        break;
                    case 'config_cache':
                        $output = executeSimpleCommand('php artisan config:cache');
                        break;
                    case 'up':
                        $output = executeSimpleCommand('php artisan up');
                        break;
                    case 'check_env':
                        $envPath = '.env';
                        if (file_exists($envPath)) {
                            $envSize = filesize($envPath);
                            $envModified = date('Y-m-d H:i:s', filemtime($envPath));
                            $envContent = file_get_contents($envPath);
                            $hasAppKey = strpos($envContent, 'APP_KEY=') !== false && strpos($envContent, 'APP_KEY=base64:') !== false;
                            $envData = parseEnvFile($envPath);

                            $output = "Environment File Check:\n";
                            $output .= ".env exists: Yes\n";
                            $output .= ".env size: {$envSize} bytes\n";
                            $output .= ".env modified: {$envModified}\n";
                            $output .= 'APP_KEY set: ' . ($hasAppKey ? 'Yes' : 'No') . "\n";

                            // Check database connection
                            $dbConnection = $envData['DB_CONNECTION'] ?? 'not set';
                            $output .= "DB_CONNECTION: {$dbConnection}\n";

                            $dbHost = $envData['DB_HOST'] ?? 'not set';
                            $output .= "DB_HOST: {$dbHost}\n";

                            $dbPort = $envData['DB_PORT'] ?? 'not set';
                            $output .= "DB_PORT: {$dbPort}\n";

                            $dbDatabase = $envData['DB_DATABASE'] ?? 'not set';
                            $output .= "DB_DATABASE: {$dbDatabase}\n";

                            $dbUsername = $envData['DB_USERNAME'] ?? 'not set';
                            $output .= "DB_USERNAME: {$dbUsername}\n";

                            $dbPasswordRaw = $envData['DB_PASSWORD'] ?? '';
                            $output .= 'DB_PASSWORD set: ' . ($dbPasswordRaw !== '' ? 'Yes' : 'No') . "\n";

                            $cacheStoreValue = $envData['CACHE_STORE'] ?? ($envData['CACHE_DRIVER'] ?? 'database');
                            $output .= "CACHE_STORE: {$cacheStoreValue}\n";

                            if ($dbConnection === 'mysql' && $dbHost !== 'not set' && $dbDatabase !== 'not set' && $dbUsername !== 'not set') {
                                try {
                                    $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbDatabase};charset=utf8mb4";
                                    new PDO($dsn, $dbUsername, $dbPasswordRaw, [
                                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                                        PDO::ATTR_TIMEOUT => 5,
                                    ]);
                                    $output .= "DB connect: OK\n";
                                } catch (PDOException $e) {
                                    $output .= "DB connect: FAIL (" . $e->getMessage() . ")\n";
                                }
                            }
                        } else {
                            $output = '.env file not found';
                        }
                        break;
                    default:
                        $output = 'Unknown command';
                }

                chdir($currentDir);
                echo json_encode(['success' => true, 'message' => 'Command executed successfully', 'output' => $output]);
            } catch (Exception $e) {
                chdir($currentDir);
                echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            }
            exit;
    }
}

// Check installation progress
$wscrmPath = detectWscrmFolder();
$documentRoot = rtrim(str_replace('\\', '/', (string) ($_SERVER['DOCUMENT_ROOT'] ?? '')), '/');
$publicHtmlParent = str_replace('\\', '/', dirname($documentRoot));
$targetWscrmPath = $publicHtmlParent . '/wscrm';
$publicDir = rtrim(str_replace('\\', '/', dirname(__DIR__)), '/');

$sourceCandidates = array_unique(array_filter([
    ($documentRoot !== '' ? $documentRoot . '/wscrm' : null),
    ($documentRoot !== '' ? $documentRoot . '/public/wscrm' : null),
    ($publicDir !== '' ? $publicDir . '/wscrm' : null),
]));
$targetReal = realpath($targetWscrmPath);
$hasWscrmInPublicHtml = false;
foreach ($sourceCandidates as $candidate) {
    if (! is_dir($candidate)) {
        continue;
    }
    $candidateReal = realpath($candidate);
    if ($candidateReal && $targetReal && $candidateReal === $targetReal) {
        continue;
    }
    $hasWscrmInPublicHtml = true;
    break;
}

// Check if installation is completely finished
$isAlreadyInstalled = is_dir($targetWscrmPath) && file_exists($targetWscrmPath . '/.env') && file_exists($targetWscrmPath . '/storage/installer.lock') && ! $hasWscrmInPublicHtml;
$hasInstalledTarget = is_dir($targetWscrmPath) && file_exists($targetWscrmPath . '/.env');
$incomingWscrmPath = detectIncomingWscrmFolder($targetWscrmPath);
$hasIncomingUpdate = $incomingWscrmPath !== '' && is_dir($incomingWscrmPath);
$isUpdateMode = $hasInstalledTarget;

// Check progress markers
$step1Complete = (bool) $wscrmPath; // Folder detected
$step2Complete = is_dir($targetWscrmPath) && ! $hasWscrmInPublicHtml; // Folder moved to target location
$step3Complete = file_exists($targetWscrmPath . '/.env'); // Environment configured

$host = $_SERVER['HTTP_HOST'] ?? '';
$hostNoPort = explode(':', $host)[0];
$isLocalHost = in_array($hostNoPort, ['localhost', '127.0.0.1', '::1'], true);

$scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
$scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
$scriptDir = rtrim(str_replace('\\', '/', (string) dirname($scriptName)), '/');
$basePath = preg_replace('~/install$~', '', $scriptDir);
$basePath = $basePath === '/' ? '' : $basePath;
$defaultAppUrl = $scheme . '://' . $host . $basePath;

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WSCRM Installer v2</title>
    <style>
        :root {
            --bg: #f5f4ed;
            --surface: #faf9f5;
            --surface-2: #ffffff;
            --text: #141413;
            --text-2: #5e5d59;
            --text-3: #87867f;
            --border: #f0eee6;
            --border-2: #e8e6dc;
            --ring: #d1cfc5;
            --ring-deep: #c2c0b6;
            --brand: #c96442;
            --brand-2: #d97757;
            --error: #b53333;
            --focus: #3898ec;
            --shadow-whisper: rgba(0, 0, 0, 0.05) 0px 4px 24px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: system-ui, -apple-system, 'Segoe UI', Arial, sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 32px 20px;
            line-height: 1.6;
        }

        .container {
            width: 100%;
            max-width: 860px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 24px;
            box-shadow: var(--shadow-whisper);
            padding: 28px;
        }

        .header {
            margin-bottom: 22px;
            padding-bottom: 18px;
            border-bottom: 1px solid var(--border-2);
        }

        .header h1 {
            font-family: Georgia, 'Times New Roman', Times, serif;
            font-weight: 500;
            letter-spacing: -0.02em;
            line-height: 1.15;
            font-size: 2.25rem;
            color: var(--text);
        }

        .header p {
            margin-top: 8px;
            color: var(--text-2);
            font-size: 1rem;
        }

        .step {
            margin-top: 14px;
            padding: 18px;
            border: 1px solid var(--border-2);
            background: var(--surface-2);
            border-radius: 16px;
            box-shadow: 0px 0px 0px 1px rgba(0, 0, 0, 0);
            transition: box-shadow 160ms ease, border-color 160ms ease, transform 160ms ease;
        }

        .step.active {
            border-color: var(--brand);
            box-shadow: 0px 0px 0px 1px var(--brand);
        }

        .step.completed {
            border-color: var(--border-2);
            box-shadow: 0px 0px 0px 1px var(--ring);
        }

        .step-title {
            font-family: Georgia, 'Times New Roman', Times, serif;
            font-weight: 500;
            color: var(--text);
            letter-spacing: -0.01em;
            line-height: 1.25;
            font-size: 1.35rem;
            margin-bottom: 8px;
        }

        .step-description {
            color: var(--text-2);
            margin-bottom: 14px;
        }

        .btn {
            appearance: none;
            border: 0;
            border-radius: 12px;
            padding: 9px 14px;
            font-size: 0.98rem;
            cursor: pointer;
            transition: transform 140ms ease, box-shadow 140ms ease, background 140ms ease, color 140ms ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            background: var(--border-2);
            color: var(--text);
            box-shadow: 0px 0px 0px 1px var(--ring);
            text-decoration: none;
            user-select: none;
        }

        .btn:hover {
            background: var(--surface-2);
            box-shadow: 0px 0px 0px 1px var(--ring-deep);
            transform: translateY(-1px);
        }

        .btn:active {
            transform: translateY(0px);
        }

        .btn:disabled {
            opacity: 0.55;
            cursor: not-allowed;
            transform: none;
        }

        .btn-success {
            background: var(--text);
            color: var(--surface);
            box-shadow: 0px 0px 0px 1px var(--text);
        }

        .btn-success:hover {
            background: #30302e;
            box-shadow: 0px 0px 0px 1px #30302e;
        }

        .btn-primary {
            background: var(--brand);
            color: var(--surface);
            box-shadow: 0px 0px 0px 1px var(--brand);
        }

        .btn-primary:hover {
            background: var(--brand-2);
            box-shadow: 0px 0px 0px 1px var(--brand-2);
        }

        .btn-danger {
            background: var(--error);
            color: var(--surface);
            box-shadow: 0px 0px 0px 1px var(--error);
        }

        .btn-danger:hover {
            background: #a72d2d;
            box-shadow: 0px 0px 0px 1px #a72d2d;
        }

        .btn-outline {
            background: var(--surface-2);
            color: var(--text);
            box-shadow: 0px 0px 0px 1px var(--border-2);
        }

        .btn-outline:hover {
            box-shadow: 0px 0px 0px 1px var(--ring-deep);
        }

        .alert {
            margin-top: 14px;
            padding: 14px;
            border-radius: 16px;
            border: 1px solid var(--border-2);
            background: var(--surface);
            color: var(--text-2);
            box-shadow: 0px 0px 0px 1px rgba(0, 0, 0, 0);
        }

        .alert strong {
            color: var(--text);
            font-weight: 600;
        }

        .alert-success {
            border-color: var(--ring);
            box-shadow: 0px 0px 0px 1px var(--ring);
        }

        .alert-info {
            border-color: var(--border-2);
        }

        .alert-error {
            border-color: rgba(181, 51, 51, 0.35);
            box-shadow: 0px 0px 0px 1px rgba(181, 51, 51, 0.2);
        }

        code,
        .path-info,
        pre {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;
        }

        .path-info {
            margin-top: 10px;
            background: var(--surface-2);
            border: 1px solid var(--border-2);
            border-radius: 12px;
            padding: 12px;
            color: var(--text);
            word-break: break-word;
        }

        .radio-group {
            margin-top: 12px;
        }

        .radio-group label {
            display: block;
            margin-top: 10px;
            cursor: pointer;
            color: var(--text-2);
        }

        .radio-group input[type="radio"] {
            margin-right: 10px;
            accent-color: var(--brand);
        }

        .config-form {
            margin-top: 16px;
            padding: 18px;
            background: var(--surface);
            border-radius: 16px;
            border: 1px solid var(--border-2);
            box-shadow: 0px 0px 0px 1px var(--ring);
        }

        .config-header {
            margin-bottom: 18px;
        }

        .config-header h3 {
            font-family: Georgia, 'Times New Roman', Times, serif;
            font-weight: 500;
            line-height: 1.2;
            letter-spacing: -0.01em;
            color: var(--text);
            font-size: 1.6rem;
        }

        .config-subtitle {
            margin-top: 6px;
            color: var(--text-2);
        }

        .config-section {
            margin-top: 14px;
            padding: 18px;
            background: var(--surface-2);
            border-radius: 16px;
            border: 1px solid var(--border-2);
        }

        .config-section h4 {
            font-family: Georgia, 'Times New Roman', Times, serif;
            font-weight: 500;
            line-height: 1.25;
            color: var(--text);
            font-size: 1.25rem;
            margin-bottom: 12px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px;
                border-radius: 20px;
            }

            .form-row {
                grid-template-columns: 1fr;
            }
        }

        .form-group {
            margin-bottom: 14px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
        }

        .label-text {
            font-size: 0.82rem;
            letter-spacing: 0.12px;
            color: var(--text);
            font-weight: 600;
            display: block;
        }

        .label-desc {
            margin-top: 3px;
            font-size: 0.9rem;
            color: var(--text-2);
            font-weight: 400;
            display: block;
        }

        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border-2);
            border-radius: 12px;
            font-size: 1rem;
            background: var(--surface-2);
            color: var(--text);
            transition: border-color 140ms ease, box-shadow 140ms ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--focus);
            box-shadow: 0px 0px 0px 3px rgba(56, 152, 236, 0.18);
        }

        .form-help {
            color: var(--text-3);
            font-size: 0.9rem;
            margin-top: 6px;
            display: block;
        }

        .database-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 14px;
        }

        @media (max-width: 768px) {
            .database-options {
                grid-template-columns: 1fr;
            }
        }

        .radio-card {
            display: block;
            padding: 16px;
            border: 1px solid var(--border-2);
            border-radius: 16px;
            cursor: pointer;
            transition: box-shadow 140ms ease, border-color 140ms ease, background 140ms ease;
            position: relative;
            background: var(--surface-2);
        }

        .radio-card:hover {
            box-shadow: 0px 0px 0px 1px var(--ring);
        }

        .radio-card input[type="radio"] {
            position: absolute;
            top: 14px;
            right: 14px;
            scale: 1.1;
            accent-color: var(--brand);
        }

        .radio-card:has(input[type="radio"]:checked) {
            border-color: var(--brand);
            box-shadow: 0px 0px 0px 1px var(--brand);
        }

        .radio-content {
            padding-right: 34px;
            color: var(--text-2);
        }

        .radio-title {
            font-family: Georgia, 'Times New Roman', Times, serif;
            font-weight: 500;
            color: var(--text);
            font-size: 1.05rem;
            margin-bottom: 4px;
            line-height: 1.25;
        }

        .radio-desc {
            font-size: 0.95rem;
            color: var(--text-2);
            line-height: 1.5;
        }

        .mysql-config {
            margin-top: 14px;
            padding: 16px;
            background: var(--surface);
            border-radius: 16px;
            border: 1px solid var(--border-2);
        }

        .config-actions {
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid var(--border-2);
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            flex-wrap: wrap;
        }

        .tools {
            margin-top: 14px;
            padding: 16px;
            border-radius: 16px;
            border: 1px solid var(--border-2);
            background: var(--surface-2);
        }

        .tools-title {
            font-family: Georgia, 'Times New Roman', Times, serif;
            font-weight: 500;
            color: var(--text);
            font-size: 1.15rem;
            line-height: 1.2;
            margin-bottom: 12px;
        }

        .tools-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 10px;
        }

        .result {
            margin-top: 14px;
        }

        .code-block {
            margin-top: 10px;
            background: var(--surface-2);
            border: 1px solid var(--border-2);
            padding: 12px;
            border-radius: 12px;
            font-size: 0.9rem;
            max-height: 220px;
            overflow: auto;
            color: var(--text);
            white-space: pre-wrap;
        }

        .progress-wrap {
            margin-top: 12px;
            height: 10px;
            border-radius: 999px;
            border: 1px solid var(--border-2);
            background: var(--surface-2);
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, var(--brand), var(--brand-2));
            transition: width 180ms ease;
        }

        .actions {
            margin-top: 14px;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>WSCRM Installer v2</h1>
            <p>Installer untuk struktur package baru dengan folder wscrm terpisah</p>
        </div>

        <?php if ($isLocalHost) { ?>
            <div class="alert alert-info">
                <strong>Local mode:</strong> Anda bisa skip installer untuk development tanpa menghapus folder install.
            </div>
            <div class="actions">
                <button onclick="skipInstaller()" class="btn btn-outline">Skip installer (local)</button>
                <a href="../" class="btn btn-outline">Buka aplikasi</a>
            </div>
            <div id="skip-result" class="result"></div>
        <?php } ?>

        <?php if ($isUpdateMode) { ?>
            <div class="alert alert-info">
                <strong>Mode Update</strong><br>
                Instalasi WSCRM terdeteksi di: <code><?= htmlspecialchars($targetWscrmPath) ?></code><br>
                <?php if ($hasIncomingUpdate) { ?>
                    Package update terdeteksi di: <code><?= htmlspecialchars($incomingWscrmPath) ?></code><br>
                    Klik "Update aplikasi" untuk mengganti code, menjalankan migrasi, lalu refresh cache.<br><br>
                <?php } else { ?>
                    Package update belum terdeteksi.<br>
                    Upload dan extract package update sampai muncul folder <code>wscrm</code> di public_html, lalu refresh halaman ini.<br><br>
                <?php } ?>

                <div class="actions">
                    <button onclick="finalizeInstall()" class="btn btn-primary" data-mode="update" <?= $hasIncomingUpdate ? '' : 'disabled' ?>>Update aplikasi</button>
                    <button onclick="finalizeInstall()" class="btn btn-outline" data-mode="cleanup">Hapus folder install</button>
                    <a href="../" class="btn btn-outline">Buka aplikasi</a>
                </div>
                <div id="finalize-result" class="result"></div>
            </div>
        <?php } else { ?>
            <div class="step <?= $step1Complete ? 'completed' : '' ?>" id="step1">
                <div class="step-title">Step 1: Deteksi folder WSCRM</div>
                <div class="step-description">
                    Sistem akan mencari folder wscrm yang berisi file Laravel backend.
                </div>

                <?php if ($step1Complete) { ?>
                    <div class="alert alert-success">
                        <strong>Folder wscrm sudah ditemukan</strong><br>
                        Lokasi: <div class="path-info"><?= htmlspecialchars($wscrmPath) ?></div>
                    </div>
                <?php } else { ?>
                    <button class="btn" onclick="detectWscrm()">Deteksi Folder WSCRM</button>
                    <div id="detection-result"></div>
                <?php } ?>
            </div>

            <div class="step <?= $step3Complete ? 'completed' : '' ?><?= $step1Complete && ! $step3Complete ? ' active' : '' ?>" id="step3" style="display: <?= $step1Complete ? 'block' : 'none' ?>;">
                <div class="step-title">Step 2: <?= $step3Complete ? 'Konfigurasi selesai' : 'Install (Setup environment)' ?></div>
                <div class="step-description">
                    <?= $step3Complete ? 'Environment sudah dikonfigurasi. Aplikasi siap digunakan.' : 'Konfigurasikan environment untuk menyelesaikan instalasi. Folder wscrm akan dipindahkan otomatis saat proses install.' ?>
                </div>

                <?php if ($step3Complete) { ?>
                    <div class="alert alert-success">
                        <strong>Instalasi berhasil diselesaikan</strong><br>
                        Environment sudah dikonfigurasi dan aplikasi siap digunakan.
                    </div>
                    <div class="actions">
                        <button onclick="finalizeInstall()" class="btn btn-primary">Install</button>
                    </div>
                    <div id="finalize-result" class="result"></div>
                <?php } else { ?>
                    <button class="btn btn-success" onclick="showEnvConfiguration()">Install</button>
                    <div id="env-config-form" class="config-form" style="display: none;">
                        <div class="config-header">
                            <h3>Konfigurasi environment</h3>
                            <p class="config-subtitle">Konfigurasikan pengaturan dasar untuk aplikasi WSCRM Anda</p>
                        </div>

                        <div class="config-section">
                            <h4>Informasi aplikasi</h4>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="app_name">
                                        <span class="label-text">Nama Aplikasi</span>
                                        <span class="label-desc">Nama yang akan ditampilkan di aplikasi</span>
                                    </label>
                                    <input type="text" id="app_name" class="form-control" value="WSCRM" required>
                                </div>

                                <div class="form-group">
                                    <label for="app_url">
                                        <span class="label-text">URL Aplikasi</span>
                                        <span class="label-desc">URL lengkap dimana aplikasi dapat diakses</span>
                                    </label>
                                    <input type="url" id="app_url" class="form-control" value="<?= htmlspecialchars($defaultAppUrl) ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="config-section">
                            <h4>Konfigurasi database</h4>
                            <div class="database-options">
                                <label class="radio-card">
                                    <input type="radio" name="db_type" value="sqlite" checked onclick="toggleDatabaseConfig()">
                                    <div class="radio-content">
                                        <div class="radio-title">SQLite</div>
                                        <div class="radio-desc">Database file lokal, cocok untuk instalasi cepat dan development</div>
                                    </div>
                                </label>

                                <label class="radio-card">
                                    <input type="radio" name="db_type" value="mysql" onclick="toggleDatabaseConfig()">
                                    <div class="radio-content">
                                        <div class="radio-title">MySQL</div>
                                        <div class="radio-desc">Database server, cocok untuk production dengan performa tinggi</div>
                                    </div>
                                </label>
                            </div>

                            <div id="mysql-config" class="mysql-config" style="display: none;">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="db_host">
                                            <span class="label-text">Database Host</span>
                                        </label>
                                        <input type="text" id="db_host" class="form-control" value="localhost" placeholder="localhost">
                                    </div>

                                    <div class="form-group">
                                        <label for="db_port">
                                            <span class="label-text">Port</span>
                                        </label>
                                        <input type="number" id="db_port" class="form-control" value="3306" placeholder="3306">
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="db_name">
                                            <span class="label-text">Database Name</span>
                                        </label>
                                        <input type="text" id="db_name" class="form-control" placeholder="wscrm" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="db_username">
                                            <span class="label-text">Username</span>
                                        </label>
                                        <input type="text" id="db_username" class="form-control" placeholder="root" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="db_password">
                                        <span class="label-text">Password</span>
                                    </label>
                                    <input type="password" id="db_password" class="form-control" placeholder="Masukkan password database (kosongkan jika tidak ada password)">
                                    <small class="form-help">Kosongkan field ini jika database tidak menggunakan password (seperti setup local XAMPP/WAMP)</small>
                                </div>

                                <button type="button" class="btn btn-outline" onclick="testDatabaseConnection()">
                                    Test koneksi database
                                </button>
                                <div id="db-test-result"></div>
                            </div>
                        </div>

                        <div class="config-actions">
                            <button class="btn btn-primary" onclick="configureEnvironment()">
                                Simpan dan lanjutkan
                            </button>
                        </div>
                        <div id="env-config-result"></div>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
    </div>

    <script>
        let detectedWscrmPath = '';

        // Initialize on page load if wscrm is already detected
        document.addEventListener('DOMContentLoaded', function() {
            // Check if wscrm path is already shown in the UI
            const pathInfo = document.querySelector('.path-info');
            if (pathInfo && pathInfo.textContent.trim()) {
                detectedWscrmPath = pathInfo.textContent.trim();
                window.detectedWscrmPath = detectedWscrmPath;
                console.log('Initialized detectedWscrmPath from UI:', detectedWscrmPath);
            }
        });

        function detectWscrm() {
            const btn = event.target;
            btn.disabled = true;
            btn.textContent = 'Mendeteksi...';

            fetch('index.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=detect_wscrm'
                })
                .then(response => response.json())
                .then(data => {
                    const resultDiv = document.getElementById('detection-result');

                    // Log debug info to console for troubleshooting
                    if (data.debug) {
                        console.log('WSCRM Detection Debug:', data.debug);
                    }

                    if (data.success) {
                        // Normalize path - remove trailing slash
                        detectedWscrmPath = data.path.replace(/\/$/, '');

                        // Debug: Log detected path
                        console.log('WSCRM path detected:', detectedWscrmPath);
                        console.log('Full detection data:', data);
                        console.log('detectedWscrmPath after assignment:', detectedWscrmPath);
                        console.log('detectedWscrmPath type after assignment:', typeof detectedWscrmPath);

                        // Test if variable is accessible
                        window.detectedWscrmPath = detectedWscrmPath;
                        console.log('Global detectedWscrmPath set:', window.detectedWscrmPath);

                        // Check if wscrm is already in correct location (outside public_html)
                        const isAlreadyMoved = data.already_in_target_location || false;

                        if (isAlreadyMoved) {
                            // Skip Step 2 - already in correct location
                            resultDiv.innerHTML = `
                            <div class="alert alert-success">
                                <strong>Folder wscrm sudah di lokasi yang benar</strong><br>
                                Lokasi: <div class="path-info">${data.path}</div>
                                <br><small>Step 2 dilewati karena folder sudah berada di luar public_html.</small>
                            </div>
                        `;

                            document.getElementById('step1').classList.add('completed');
                            document.getElementById('step2').classList.add('completed');
                            document.getElementById('step2').style.display = 'none';
                            document.getElementById('step3').style.display = 'block';
                            document.getElementById('step3').classList.add('active');
                        } else {
                            // Show Step 2 - needs to be moved
                            resultDiv.innerHTML = `
                            <div class="alert alert-success">
                                <strong>Folder wscrm ditemukan</strong><br>
                                Lokasi: <div class="path-info">${data.path}</div>
                                <br><small>Perlu dipindahkan ke luar public_html untuk keamanan.</small>
                            </div>
                        `;

                            document.getElementById('step1').classList.add('completed');
                            document.getElementById('step2').style.display = 'block';
                            document.getElementById('step2').classList.add('active');
                        }
                    } else {
                        resultDiv.innerHTML = `
                        <div class="alert alert-error">
                            <strong>${data.message}</strong><br>
                            Pastikan file package sudah diekstrak dengan benar.
                        </div>
                    `;
                    }

                    btn.disabled = false;
                    btn.textContent = 'Deteksi Folder WSCRM';
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('detection-result').innerHTML = `
                    <div class="alert alert-error">
                        <strong>Terjadi kesalahan saat mendeteksi folder wscrm</strong>
                    </div>
                `;
                    btn.disabled = false;
                    btn.textContent = 'Deteksi Folder WSCRM';
                });
        }

        function moveWscrm() {
            const btn = event.target;
            console.log('Current detectedWscrmPath value:', detectedWscrmPath);
            console.log('detectedWscrmPath type:', typeof detectedWscrmPath);
            console.log('detectedWscrmPath length:', detectedWscrmPath ? detectedWscrmPath.length : 'undefined');
            console.log('window.detectedWscrmPath:', window.detectedWscrmPath);

            // Try to use global variable as fallback
            if (!detectedWscrmPath && window.detectedWscrmPath) {
                detectedWscrmPath = window.detectedWscrmPath;
                console.log('Using global detectedWscrmPath as fallback:', detectedWscrmPath);
            }

            // Validasi detectedWscrmPath sebelum mengirim
            if (!detectedWscrmPath || detectedWscrmPath.trim() === '') {
                document.getElementById('move-result').innerHTML = `
                    <div class="alert alert-error">
                        <strong>Path wscrm tidak terdeteksi. Silakan jalankan deteksi terlebih dahulu.</strong>
                        <br><small>Debug: detectedWscrmPath = '${detectedWscrmPath}' (${typeof detectedWscrmPath})</small>
                        <br><small>window.detectedWscrmPath = '${window.detectedWscrmPath}'</small>
                    </div>
                `;
                return;
            }

            console.log('Sending wscrm_path:', detectedWscrmPath);

            btn.disabled = true;
            btn.textContent = 'Memindahkan...';

            const payload = `action=move_wscrm&wscrm_path=${encodeURIComponent(detectedWscrmPath)}`;
            console.log('Request payload:', payload);

            fetch('index.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: payload
                })
                .then(response => response.json())
                .then(data => {
                    const resultDiv = document.getElementById('move-result');

                    if (data.success) {
                        resultDiv.innerHTML = `
                        <div class="alert alert-success">
                            <strong>${data.message}</strong>
                        </div>
                    `;

                        document.getElementById('step2').classList.add('completed');
                        document.getElementById('step2').classList.remove('active');
                        document.getElementById('step3').style.display = 'block';
                        document.getElementById('step3').classList.add('active');
                    } else {
                        resultDiv.innerHTML = `
                        <div class="alert alert-error">
                            <strong>${data.message}</strong>
                        </div>
                    `;
                    }

                    btn.disabled = false;
                    btn.textContent = 'Jalankan Operasi';
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('move-result').innerHTML = `
                    <div class="alert alert-error">
                        <strong>Terjadi kesalahan saat memindahkan folder wscrm</strong>
                    </div>
                `;
                    btn.disabled = false;
                    btn.textContent = 'Jalankan Operasi';
                });
        }

        function showEnvConfiguration() {
            document.getElementById('env-config-form').style.display = 'block';
            event.target.style.display = 'none';
        }

        function toggleDatabaseConfig() {
            const mysqlConfig = document.getElementById('mysql-config');
            const dbType = document.querySelector('input[name="db_type"]:checked').value;

            if (dbType === 'mysql') {
                mysqlConfig.style.display = 'block';
            } else {
                mysqlConfig.style.display = 'none';
            }
        }

        function testDatabaseConnection() {
            const btn = event.target;
            const originalText = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'Testing...';

            const dbHost = document.getElementById('db_host').value;
            const dbPort = document.getElementById('db_port').value;
            const dbName = document.getElementById('db_name').value;
            const dbUsername = document.getElementById('db_username').value;
            const dbPassword = document.getElementById('db_password').value;

            fetch('index.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=test_database&db_host=${encodeURIComponent(dbHost)}&db_port=${encodeURIComponent(dbPort)}&db_name=${encodeURIComponent(dbName)}&db_username=${encodeURIComponent(dbUsername)}&db_password=${encodeURIComponent(dbPassword)}`
                })
                .then(response => response.json())
                .then(data => {
                    const resultDiv = document.getElementById('db-test-result');

                    if (data.success) {
                        resultDiv.innerHTML = `
                        <div class="alert alert-success">
                            <strong>Koneksi database berhasil</strong><br>
                            ${data.message}
                        </div>
                    `;
                    } else {
                        resultDiv.innerHTML = `
                        <div class="alert alert-error">
                            <strong>Koneksi database gagal</strong><br>
                            ${data.message}
                        </div>
                    `;
                    }

                    btn.disabled = false;
                    btn.textContent = originalText;
                })
                .catch(error => {
                    document.getElementById('db-test-result').innerHTML = `
                    <div class="alert alert-error">
                        <strong>Error: ${error.message}</strong>
                    </div>
                `;
                    btn.disabled = false;
                    btn.textContent = originalText;
                });
        }

        function deleteInstallFolder() {
            if (!confirm('Apakah Anda yakin ingin menghapus folder install? Tindakan ini tidak dapat dibatalkan.')) {
                return;
            }

            const btn = event.target;
            const originalText = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'Menghapus...';

            fetch('index.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=delete_install_folder'
                })
                .then(response => response.json())
                .then(data => {
                    const resultDiv = document.getElementById('delete-result');

                    if (data.success) {
                        resultDiv.innerHTML = `
                        <div class="alert alert-success">
                            <strong>${data.message}</strong><br>
                            Halaman akan dialihkan ke aplikasi dalam 3 detik...
                        </div>
                    `;

                        setTimeout(() => {
                            window.location.href = '../';
                        }, 3000);
                    } else {
                        resultDiv.innerHTML = `
                        <div class="alert alert-error">
                            <strong>${data.message}</strong>
                        </div>
                    `;

                        btn.disabled = false;
                        btn.textContent = originalText;
                    }
                })
                .catch(error => {
                    document.getElementById('delete-result').innerHTML = `
                    <div class="alert alert-error">
                        <strong>Error: ${error.message}</strong>
                    </div>
                `;
                    btn.disabled = false;
                    btn.textContent = originalText;
                });
        }

        function finalizeInstall() {
            const btn = event.target;
            const originalText = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'Menjalankan...';
            const mode = btn && btn.dataset && btn.dataset.mode ? btn.dataset.mode : 'full';

            const resultDiv = document.getElementById('finalize-result');
            if (resultDiv) {
                resultDiv.innerHTML = `
                <div class="alert alert-info">
                    <strong>${mode === 'cleanup' ? 'Menghapus folder install...' : (mode === 'update' ? 'Menjalankan update aplikasi...' : 'Menjalankan proses install...')}</strong><br>
                    ${mode === 'cleanup'
                        ? 'Membersihkan folder install dan folder wscrm di public_html (jika masih ada).'
                        : (mode === 'update'
                            ? 'Mengganti folder wscrm dengan package update, menjalankan migrasi, lalu refresh cache.'
                            : 'Menjalankan cek lokasi folder, migrasi, storage link, clear cache, optimize, config cache, lalu cleanup.')}
                    <div class="result">
                        <div class="progress-wrap">
                            <div class="progress-bar" id="install-progress-bar" style="width: 0%"></div>
                        </div>
                        <pre class="code-block" id="install-log"></pre>
                    </div>
                </div>
            `;
            }

            const logEl = document.getElementById('install-log');
            const barEl = document.getElementById('install-progress-bar');

            const appendLog = (text) => {
                if (!logEl) {
                    return;
                }
                logEl.textContent += (logEl.textContent ? "\n" : "") + text;
                logEl.scrollTop = logEl.scrollHeight;
            };

            const setProgress = (percent) => {
                if (barEl) {
                    barEl.style.width = `${percent}%`;
                }
            };

            const post = (body) => {
                return fetch('index.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body,
                }).then((r) => r.json());
            };

            const fullSteps = [{
                label: 'Deteksi & cek lokasi wscrm',
                run: async () => {
                    const data = await post('action=detect_wscrm');
                    if (!data.success) {
                        throw new Error(data.message || 'Gagal mendeteksi folder wscrm');
                    }
                    appendLog(`== Deteksi WSCRM ==\nPath: ${data.path}\nTarget: ${data.target_path}\nSejajar public_html: ${data.already_in_target_location ? 'YA' : 'TIDAK'}`);
                },
            }, {
                label: 'Cek environment',
                run: async () => {
                    const data = await post('action=laravel_command&command=check_env');
                    if (!data.success) {
                        throw new Error(data.message || 'Gagal cek environment');
                    }
                    appendLog(`== Check Env ==\n${data.output || ''}`);
                },
            }, {
                label: 'Migrasi',
                run: async () => {
                    const data = await post('action=laravel_command&command=migrate');
                    if (!data.success) {
                        throw new Error(data.message || 'Gagal menjalankan migrasi');
                    }
                    appendLog(`== Migrate ==\n${data.output || ''}`);
                },
            }, {
                label: 'Storage Link',
                run: async () => {
                    const data = await post('action=laravel_command&command=storage_link');
                    if (!data.success) {
                        throw new Error(data.message || 'Gagal membuat storage link');
                    }
                    appendLog(`== Storage Link ==\n${data.output || ''}`);
                },
            }, {
                label: 'Clear Cache',
                run: async () => {
                    const data = await post('action=laravel_command&command=clear_cache');
                    if (!data.success) {
                        throw new Error(data.message || 'Gagal clear cache');
                    }
                    appendLog(`== Clear Cache ==\n${data.output || ''}`);
                },
            }, {
                label: 'Optimize',
                run: async () => {
                    const data = await post('action=laravel_command&command=optimize');
                    if (!data.success) {
                        throw new Error(data.message || 'Gagal optimize');
                    }
                    appendLog(`== Optimize ==\n${data.output || ''}`);
                },
            }, {
                label: 'Config Cache',
                run: async () => {
                    const data = await post('action=laravel_command&command=config_cache');
                    if (!data.success) {
                        throw new Error(data.message || 'Gagal config cache');
                    }
                    appendLog(`== Config Cache ==\n${data.output || ''}`);
                },
            }, {
                label: 'Artisan Up',
                run: async () => {
                    const data = await post('action=laravel_command&command=up');
                    if (!data.success) {
                        throw new Error(data.message || 'Gagal artisan up');
                    }
                    appendLog(`== Artisan Up ==\n${data.output || ''}`);
                },
            }, {
                label: 'Cleanup',
                run: async () => {
                    const data = await post('action=cleanup_after_install');
                    const out = data.output ? `\n\n${data.output}` : '';
                    if (!data.success) {
                        throw new Error((data.message || 'Cleanup gagal') + out);
                    }
                    appendLog(`== Cleanup ==\n${data.message}${out}`);
                },
            }];

            const updateSteps = [{
                label: 'Deteksi update',
                run: async () => {
                    const data = await post('action=detect_update');
                    if (!data.success) {
                        throw new Error(data.message || 'Gagal mendeteksi mode update');
                    }
                    appendLog(`== Detect Update ==\nInstalled: ${data.installed ? 'YA' : 'TIDAK'}\nTarget: ${data.target}\nIncoming: ${data.incoming ? 'YA' : 'TIDAK'}\nIncoming Path: ${data.incoming_path || '-'}`);
                    if (!data.installed) {
                        throw new Error('Instalasi belum terdeteksi. Mode update membutuhkan wscrm target yang sudah terinstall.');
                    }
                    if (!data.incoming) {
                        throw new Error('Package update tidak ditemukan di public_html. Upload + extract package update dulu.');
                    }
                },
            }, {
                label: 'Deploy & migrate',
                run: async () => {
                    const data = await post('action=update_app');
                    const out = data.output ? `\n\n${data.output}` : '';
                    if (!data.success) {
                        throw new Error((data.message || 'Update gagal') + out);
                    }
                    appendLog(`== Update ==\n${data.message}${out}`);
                },
            }, {
                label: 'Cleanup',
                run: async () => {
                    const data = await post('action=cleanup_after_install');
                    const out = data.output ? `\n\n${data.output}` : '';
                    if (!data.success) {
                        throw new Error((data.message || 'Cleanup gagal') + out);
                    }
                    appendLog(`== Cleanup ==\n${data.message}${out}`);
                },
            }];

            const steps = mode === 'cleanup' ? [{
                label: 'Cleanup',
                run: async () => {
                    const data = await post('action=cleanup_after_install');
                    const out = data.output ? `\n\n${data.output}` : '';
                    if (!data.success) {
                        throw new Error((data.message || 'Cleanup gagal') + out);
                    }
                    appendLog(`== Cleanup ==\n${data.message}${out}`);
                },
            }] : (mode === 'update' ? updateSteps : fullSteps);

            (async () => {
                try {
                    appendLog(mode === 'cleanup' ? 'Mulai cleanup...' : (mode === 'update' ? 'Mulai update...' : 'Mulai proses install...'));
                    for (let i = 0; i < steps.length; i++) {
                        const step = steps[i];
                        appendLog(`\n[${i + 1}/${steps.length}] ${step.label}`);
                        await step.run();
                        setProgress(Math.round(((i + 1) / steps.length) * 100));
                    }

                    appendLog(mode === 'cleanup' ? '\nCleanup selesai. Mengalihkan ke aplikasi...' : (mode === 'update' ? '\nUpdate selesai. Mengalihkan ke aplikasi...' : '\nInstall selesai. Mengalihkan ke aplikasi...'));
                    setTimeout(() => {
                        window.location.href = '../';
                    }, 1800);
                } catch (e) {
                    if (resultDiv) {
                        resultDiv.innerHTML = `
                        <div class="alert alert-error">
                            <strong>Gagal:</strong> ${e && e.message ? e.message : 'Unknown error'}
                        </div>
                    `;
                    }
                    btn.disabled = false;
                    btn.textContent = originalText;
                }
            })();
        }

        function skipInstaller() {
            const btn = event.target;
            const originalText = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'Memproses...';

            fetch('index.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=skip_installer'
                })
                .then(response => response.json())
                .then(data => {
                    const resultDiv = document.getElementById('skip-result');
                    if (data.success) {
                        resultDiv.innerHTML = `
                        <div class="alert alert-success">
                            <strong>${data.message}</strong><br>
                            Mengalihkan ke aplikasi...
                        </div>
                    `;
                        setTimeout(() => {
                            window.location.href = '../';
                        }, 800);
                    } else {
                        resultDiv.innerHTML = `
                        <div class="alert alert-error">
                            <strong>${data.message}</strong>
                        </div>
                    `;
                        btn.disabled = false;
                        btn.textContent = originalText;
                    }
                })
                .catch(error => {
                    document.getElementById('skip-result').innerHTML = `
                    <div class="alert alert-error">
                        <strong>Error: ${error.message}</strong>
                    </div>
                `;
                    btn.disabled = false;
                    btn.textContent = originalText;
                });
        }

        function runLaravelCommand(command) {
            const btn = event.target;
            const originalText = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'Running...';

            fetch('index.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=laravel_command&command=${encodeURIComponent(command)}`
                })
                .then(response => response.json())
                .then(data => {
                    const resultDiv = document.getElementById('laravel-tools-result');

                    if (data.success) {
                        resultDiv.innerHTML = `
                        <div class="alert alert-success">
                            <strong>${data.message}</strong><br>
                            <pre class="code-block">${data.output || 'No output'}</pre>
                        </div>
                    `;
                    } else {
                        resultDiv.innerHTML = `
                        <div class="alert alert-error">
                            <strong>${data.message}</strong>
                        </div>
                    `;
                    }

                    btn.disabled = false;
                    btn.textContent = originalText;
                })
                .catch(error => {
                    document.getElementById('laravel-tools-result').innerHTML = `
                    <div class="alert alert-error">
                        <strong>Error: ${error.message}</strong>
                    </div>
                `;
                    btn.disabled = false;
                    btn.textContent = originalText;
                });
        }

        function configureEnvironment() {
            const btn = event.target;
            btn.disabled = true;
            btn.textContent = 'Menyimpan...';

            const appUrl = document.getElementById('app_url').value;
            const appName = document.getElementById('app_name').value;
            const dbType = document.querySelector('input[name="db_type"]:checked').value;

            let postData = `action=configure_env&app_url=${encodeURIComponent(appUrl)}&app_name=${encodeURIComponent(appName)}&db_type=${encodeURIComponent(dbType)}`;

            // Add MySQL config if selected
            if (dbType === 'mysql') {
                const dbHost = document.getElementById('db_host').value;
                const dbPort = document.getElementById('db_port').value;
                const dbName = document.getElementById('db_name').value;
                const dbUsername = document.getElementById('db_username').value;
                const dbPassword = document.getElementById('db_password').value;

                postData += `&db_host=${encodeURIComponent(dbHost)}&db_port=${encodeURIComponent(dbPort)}&db_name=${encodeURIComponent(dbName)}&db_username=${encodeURIComponent(dbUsername)}&db_password=${encodeURIComponent(dbPassword)}`;
            }

            fetch('index.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: postData
                })
                .then(response => response.json())
                .then(data => {
                    const resultDiv = document.getElementById('env-config-result');

                    if (data.success) {
                        resultDiv.innerHTML = `
                        <div class="alert alert-success">
                            <strong>Environment berhasil dikonfigurasi</strong><br>
                            ${data.message}
                        </div>
                    `;

                        // Reload page to show completed state
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        resultDiv.innerHTML = `
                        <div class="alert alert-error">
                            <strong>${data.message}</strong>
                        </div>
                    `;

                        btn.disabled = false;
                        btn.textContent = 'Simpan Konfigurasi';
                    }
                })
                .catch(error => {
                    document.getElementById('env-config-result').innerHTML = `
                    <div class="alert alert-error">
                        <strong>Error: ${error.message}</strong>
                    </div>
                `;
                    btn.disabled = false;
                    btn.textContent = 'Simpan Konfigurasi';
                });
        }
    </script>
</body>

</html>