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
    $publicHtmlParent = dirname($_SERVER['DOCUMENT_ROOT']);

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

    // Priority 2: Check if wscrm exists in public_html (fresh extract)
    $path2 = $publicHtmlDir . '/wscrm';
    $exists2 = is_dir($path2);
    $debugInfo['checks'][] = ['path' => $path2, 'exists' => $exists2, 'priority' => 2];
    if ($exists2) {
        return rtrim($path2, '/'); // Remove trailing slash
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

            // Get target wscrm path
            $publicHtmlParent = str_replace('\\', '/', dirname($_SERVER['DOCUMENT_ROOT']));
            $targetWscrmPath = $publicHtmlParent . '/wscrm';

            error_log('Target wscrm path: ' . $targetWscrmPath);
            error_log('Directory exists: ' . (is_dir($targetWscrmPath) ? 'YES' : 'NO'));

            if (! is_dir($targetWscrmPath)) {
                error_log('ERROR Target wscrm directory not found: ' . $targetWscrmPath);
                echo json_encode(['success' => false, 'message' => 'Folder wscrm tidak ditemukan di lokasi target: ' . $targetWscrmPath]);
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

            // Basic app configuration
            $replacements = [
                'APP_NAME=Laravel' => 'APP_NAME="' . $appName . '"',
                'APP_URL=http://localhost' => 'APP_URL=' . $appUrl,
                'APP_ENV=local' => 'APP_ENV=production',
                'APP_DEBUG=true' => 'APP_DEBUG=false',
            ];

            // Database configuration
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

                // Test database connection before proceeding
                try {
                    $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName}";
                    $pdo = new PDO($dsn, $dbUsername, $dbPassword);
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    error_log('Database connection test successful');
                } catch (PDOException $e) {
                    error_log('ERROR Database connection test failed: ' . $e->getMessage());
                    echo json_encode(['success' => false, 'message' => 'Koneksi database gagal: ' . $e->getMessage()]);
                    exit;
                }

                // Handle password properly - empty password should remain empty, not quoted
                $quotedPassword = $dbPassword;
                if (! empty($dbPassword) && preg_match('/[\\s#"\'\\\\]/', $dbPassword)) {
                    $quotedPassword = '"' . str_replace('"', '\\"', $dbPassword) . '"';
                }

                $replacements = array_merge($replacements, [
                    'DB_CONNECTION=sqlite' => 'DB_CONNECTION=mysql',
                    'DB_HOST=127.0.0.1' => 'DB_HOST=' . $dbHost,
                    'DB_PORT=3306' => 'DB_PORT=' . $dbPort,
                    'DB_DATABASE=database/database.sqlite' => 'DB_DATABASE=' . $dbName,
                    'DB_USERNAME=null' => 'DB_USERNAME=' . $dbUsername,
                    'DB_PASSWORD=null' => 'DB_PASSWORD=' . $quotedPassword,
                    '# DB_HOST=127.0.0.1' => 'DB_HOST=' . $dbHost,
                    '# DB_PORT=3306' => 'DB_PORT=' . $dbPort,
                    '# DB_DATABASE=wscrm' => 'DB_DATABASE=' . $dbName,
                    '# DB_USERNAME=root' => 'DB_USERNAME=' . $dbUsername,
                    '# DB_PASSWORD=' => 'DB_PASSWORD=' . $quotedPassword,
                ]);
            } else {
                // Ensure SQLite configuration
                $replacements = array_merge($replacements, [
                    'DB_CONNECTION=mysql' => 'DB_CONNECTION=sqlite',
                    'DB_DATABASE=' => 'DB_DATABASE=database/database.sqlite',
                ]);
            }

            $envContent = str_replace(array_keys($replacements), array_values($replacements), $envContent);

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

                // Generate .htaccess in public_html directory (web root)
                $publicHtmlDir = $_SERVER['DOCUMENT_ROOT'];
                $htaccessPath = $publicHtmlDir . '/.htaccess';

                error_log('Creating .htaccess at: ' . $htaccessPath);

                if (file_put_contents($htaccessPath, $htaccessContent)) {
                    error_log('.htaccess created successfully in public_html');
                } else {
                    error_log('ERROR Failed to create .htaccess in public_html');
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
            $targetWscrmPath = str_replace('\\', '/', dirname($_SERVER['DOCUMENT_ROOT'])) . '/wscrm';

            if (! is_dir($targetWscrmPath)) {
                echo json_encode(['success' => false, 'message' => 'Laravel directory not found']);
                exit;
            }

            $currentDir = getcwd();
            chdir($targetWscrmPath);

            try {
                switch ($command) {
                    case 'migrate':
                        $output = executeSimpleCommand('php artisan migrate --force');
                        break;
                    case 'db_seed':
                        $output = executeSimpleCommand('php artisan db:seed --force');
                        break;
                    case 'storage_link':
                        $output = executeSimpleCommand('php artisan storage:link');
                        break;
                    case 'key_generate':
                        $output = executeSimpleCommand('php artisan key:generate --force');
                        break;
                    case 'clear_cache':
                        $output = executeSimpleCommand('php artisan cache:clear');
                        $output .= "\n" . executeSimpleCommand('php artisan config:clear');
                        $output .= "\n" . executeSimpleCommand('php artisan route:clear');
                        $output .= "\n" . executeSimpleCommand('php artisan view:clear');
                        break;
                    case 'optimize':
                        $output = executeSimpleCommand('php artisan optimize');
                        break;
                    case 'config_cache':
                        $output = executeSimpleCommand('php artisan config:cache');
                        break;
                    case 'check_env':
                        $envPath = '.env';
                        if (file_exists($envPath)) {
                            $envSize = filesize($envPath);
                            $envModified = date('Y-m-d H:i:s', filemtime($envPath));
                            $envContent = file_get_contents($envPath);
                            $hasAppKey = strpos($envContent, 'APP_KEY=') !== false && strpos($envContent, 'APP_KEY=base64:') !== false;

                            $output = "Environment File Check:\n";
                            $output .= ".env exists: Yes\n";
                            $output .= ".env size: {$envSize} bytes\n";
                            $output .= ".env modified: {$envModified}\n";
                            $output .= 'APP_KEY set: ' . ($hasAppKey ? 'Yes' : 'No') . "\n";

                            // Check database connection
                            preg_match('/DB_CONNECTION=(.*)/', $envContent, $dbMatch);
                            $dbConnection = isset($dbMatch[1]) ? trim($dbMatch[1]) : 'not set';
                            $output .= "DB_CONNECTION: {$dbConnection}\n";
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
$publicHtmlParent = str_replace('\\', '/', dirname($_SERVER['DOCUMENT_ROOT']));
$targetWscrmPath = $publicHtmlParent . '/wscrm';

// Check if installation is completely finished
$isAlreadyInstalled = is_dir($targetWscrmPath) && file_exists($targetWscrmPath . '/.env') && file_exists($targetWscrmPath . '/storage/installer.lock');

// Check progress markers
$step1Complete = (bool) $wscrmPath; // Folder detected
$step2Complete = is_dir($targetWscrmPath); // Folder moved to target location
$step3Complete = file_exists($targetWscrmPath . '/.env'); // Environment configured

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

        <?php if ($isAlreadyInstalled) { ?>
            <div class="alert alert-success">
                <strong>Instalasi sudah selesai</strong><br>
                WSCRM sudah terinstall di: <code><?= htmlspecialchars($targetWscrmPath) ?></code><br><br>

                <div class="alert alert-info">
                    <strong>Tools tambahan:</strong> Sebelum menghapus installer, Anda dapat menjalankan tools di bawah untuk setup final:
                </div>

                <!-- Laravel Tools Section -->
                <div class="tools">
                    <h4 class="tools-title">Setup tools Laravel</h4>

                    <div class="tools-grid">
                        <button onclick="runLaravelCommand('migrate')" class="btn">Run migrations</button>
                        <button onclick="runLaravelCommand('db_seed')" class="btn">Run DB seeder</button>
                        <button onclick="runLaravelCommand('storage_link')" class="btn">Create storage link</button>
                        <button onclick="runLaravelCommand('key_generate')" class="btn">Generate app key</button>
                        <button onclick="runLaravelCommand('clear_cache')" class="btn">Clear all cache</button>
                        <button onclick="runLaravelCommand('optimize')" class="btn">Optimize app</button>
                        <button onclick="runLaravelCommand('config_cache')" class="btn">Cache config</button>
                        <button onclick="runLaravelCommand('check_env')" class="btn">Check .env</button>
                    </div>

                    <div id="laravel-tools-result" class="result"></div>
                </div>

                <div class="alert alert-info">
                    <strong>Penting:</strong> Aplikasi tidak dapat diakses selama folder install masih ada.<br>
                    Silakan hapus folder install terlebih dahulu untuk mengakses aplikasi.
                </div>

                <div class="actions">
                    <button onclick="deleteInstallFolder()" class="btn btn-danger">Hapus folder install dan buka aplikasi</button>
                    <a href="../" class="btn btn-outline">Coba buka aplikasi (akan error)</a>
                </div>
                <div id="delete-result" class="result"></div>
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

            <div class="step <?= $step2Complete ? 'completed' : '' ?><?= $step1Complete && ! $step2Complete ? ' active' : '' ?>" id="step2" style="display: <?= $step1Complete ? 'block' : 'none' ?>;">
                <div class="step-title">Step 2: Pindahkan folder WSCRM</div>
                <div class="step-description">
                    Folder wscrm akan dipindahkan ke lokasi yang tepat (sejajar dengan public_html).
                </div>

                <div class="alert alert-info">
                    <strong>Target lokasi:</strong><br>
                    <div class="path-info"><?= htmlspecialchars($targetWscrmPath) ?></div>
                </div>

                <button class="btn" onclick="moveWscrm()">Jalankan Operasi</button>
                <div id="move-result"></div>
            </div>

            <div class="step <?= $step3Complete ? 'completed' : '' ?><?= $step2Complete && ! $step3Complete ? ' active' : '' ?>" id="step3" style="display: <?= $step2Complete ? 'block' : 'none' ?>;">
                <div class="step-title">Step 3: <?= $step3Complete ? 'Konfigurasi selesai' : 'Setup environment' ?></div>
                <div class="step-description">
                    <?= $step3Complete ? 'Environment sudah dikonfigurasi. Aplikasi siap digunakan.' : 'Konfigurasikan environment untuk menyelesaikan instalasi.' ?>
                </div>

                <?php if ($step3Complete) { ?>
                    <div class="alert alert-success">
                        <strong>Instalasi berhasil diselesaikan</strong><br>
                        Environment sudah dikonfigurasi dan aplikasi siap digunakan.
                    </div>
                    <div class="actions">
                        <a href="../" class="btn btn-primary">Buka aplikasi</a>
                        <button onclick="deleteInstallFolder()" class="btn btn-danger">Hapus folder install</button>
                    </div>
                    <div id="delete-result" class="result"></div>
                <?php } else { ?>
                    <button class="btn btn-success" onclick="showEnvConfiguration()">Setup environment</button>
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
                                    <input type="url" id="app_url" class="form-control" value="<?= htmlspecialchars((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']) ?>" required>
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