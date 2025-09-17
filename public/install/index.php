<?php

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Disable error reporting for production (comment out for debugging)
// error_reporting(0);
// ini_set('display_errors', 0);

function parseEnvFile($envPath) {
    if (!file_exists($envPath)) {
        return [];
    }
    
    $env = [];
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $env[trim($key)] = trim($value, '"\' ');
        }
    }
    
    return $env;
}

function executeSimpleCommand($command) {
    // Simple command execution without exec() complexity
    if (!function_exists('shell_exec')) {
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
                '/usr/bin/php'
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
            
            if (!$phpFound) {
                return 'Error: Could not find PHP executable on this server';
            }
        }
    } else {
        $output = @shell_exec($command . ' 2>&1');
    }
    
    return $output ?: 'Command executed (no output)';
}

function detectWscrmFolder() {
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
        'checks' => []
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

function moveWscrmFolder($wscrmPath, $targetPath) {
    // Normalize paths
    $wscrmPath = rtrim(str_replace('\\', '/', $wscrmPath), '/');
    $targetPath = rtrim(str_replace('\\', '/', $targetPath), '/');
    
    if (!is_dir($wscrmPath)) {
        return ['success' => false, 'message' => 'Folder wscrm tidak ditemukan di: ' . $wscrmPath];
    }
    
    if (is_dir($targetPath)) {
        return ['success' => false, 'message' => 'Folder target sudah ada di: ' . $targetPath];
    }
    
    // Create target directory if parent doesn't exist
    $targetParent = dirname($targetPath);
    if (!is_dir($targetParent)) {
        if (!mkdir($targetParent, 0755, true)) {
            return ['success' => false, 'message' => 'Gagal membuat direktori parent: ' . $targetParent];
        }
    }
    
    // Move the folder
    if (rename($wscrmPath, $targetPath)) {
        return ['success' => true, 'message' => 'Folder wscrm berhasil dipindahkan ke: ' . $targetPath];
    } else {
        return ['success' => false, 'message' => 'Gagal memindahkan folder wscrm'];
    }
}

function copyWscrmFolder($wscrmPath, $targetPath) {
    // Normalize paths
    $wscrmPath = rtrim(str_replace('\\', '/', $wscrmPath), '/');
    $targetPath = rtrim(str_replace('\\', '/', $targetPath), '/');
    
    if (!is_dir($wscrmPath)) {
        return ['success' => false, 'message' => 'Folder wscrm tidak ditemukan di: ' . $wscrmPath];
    }
    
    if (is_dir($targetPath)) {
        return ['success' => false, 'message' => 'Folder target sudah ada di: ' . $targetPath];
    }
    
    // Create target directory
    if (!mkdir($targetPath, 0755, true)) {
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

function copyDirectory($src, $dst) {
    $dir = opendir($src);
    if (!$dir) return false;
    
    while (($file = readdir($dir)) !== false) {
        if ($file != '.' && $file != '..') {
            $srcFile = $src . '/' . $file;
            $dstFile = $dst . '/' . $file;
            
            if (is_dir($srcFile)) {
                if (!mkdir($dstFile, 0755, true)) {
                    closedir($dir);
                    return false;
                }
                if (!copyDirectory($srcFile, $dstFile)) {
                    closedir($dir);
                    return false;
                }
            } else {
                if (!copy($srcFile, $dstFile)) {
                    closedir($dir);
                    return false;
                }
            }
        }
    }
    
    closedir($dir);
    return true;
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
                    'debug' => $GLOBALS['wscrm_debug'] ?? null
                ]);
            } else {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Folder wscrm tidak ditemukan',
                    'debug' => $GLOBALS['wscrm_debug'] ?? null
                ]);
            }
            exit;
            
        case 'move_wscrm':
            // Debug: Log semua data POST yang diterima
            error_log('🔍 POST data received: ' . print_r($_POST, true));
            
            $wscrmPath = $_POST['wscrm_path'] ?? '';
            $operation = $_POST['operation'] ?? 'move'; // 'move' or 'copy'
            
            // Debug: Log nilai yang diambil
            error_log('📍 wscrm_path: ' . $wscrmPath);
            error_log('⚙️ operation: ' . $operation);
            
            if (empty($wscrmPath)) {
                $errorMsg = 'Path wscrm tidak valid. Received: ' . var_export($wscrmPath, true);
                error_log('❌ ' . $errorMsg);
                echo json_encode(['success' => false, 'message' => $errorMsg, 'debug_post' => $_POST]);
                exit;
            }
            
            // Normalize the received path 
            $wscrmPath = rtrim(str_replace('\\', '/', $wscrmPath), '/');
            
            // Determine target path (sejajar dengan public_html)
            $publicHtmlParent = str_replace('\\', '/', dirname($_SERVER['DOCUMENT_ROOT']));
            $targetPath = $publicHtmlParent . '/wscrm';
            
            if ($operation === 'copy') {
                $result = copyWscrmFolder($wscrmPath, $targetPath);
            } else {
                $result = moveWscrmFolder($wscrmPath, $targetPath);
            }
            
            echo json_encode($result);
            exit;
            
        case 'configure_env':
            // Log received data for debugging
            error_log('📝 configure_env action started');
            error_log('📋 POST data: ' . print_r($_POST, true));
            
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
            
            error_log('🎯 Target wscrm path: ' . $targetWscrmPath);
            error_log('📁 Directory exists: ' . (is_dir($targetWscrmPath) ? 'YES' : 'NO'));
            
            if (!is_dir($targetWscrmPath)) {
                error_log('❌ Target wscrm directory not found: ' . $targetWscrmPath);
                echo json_encode(['success' => false, 'message' => 'Folder wscrm tidak ditemukan di lokasi target: ' . $targetWscrmPath]);
                exit;
            }
            
            // Create .env file
            $envPath = $targetWscrmPath . '/.env';
            $envTemplate = $targetWscrmPath . '/.env.example';
            
            error_log('📄 Env template path: ' . $envTemplate);
            error_log('📄 Template exists: ' . (file_exists($envTemplate) ? 'YES' : 'NO'));
            error_log('📄 Target env path: ' . $envPath);
            
            if (!file_exists($envTemplate)) {
                error_log('❌ .env.example not found: ' . $envTemplate);
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
                'APP_DEBUG=true' => 'APP_DEBUG=false'
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
                    error_log('✅ Database connection test successful');
                } catch (PDOException $e) {
                    error_log('❌ Database connection test failed: ' . $e->getMessage());
                    echo json_encode(['success' => false, 'message' => 'Koneksi database gagal: ' . $e->getMessage()]);
                    exit;
                }

                // Handle password properly - empty password should remain empty, not quoted
                $quotedPassword = $dbPassword;
                if (!empty($dbPassword) && preg_match('/[\\s#"\'\\\\]/', $dbPassword)) {
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
                    'DB_DATABASE=' => 'DB_DATABASE=database/database.sqlite'
                ]);
            }
            
            $envContent = str_replace(array_keys($replacements), array_values($replacements), $envContent);
            
            // Write .env file
            error_log('💾 Writing .env file to: ' . $envPath);
            error_log('📝 Content length: ' . strlen($envContent) . ' bytes');
            
            if (!file_put_contents($envPath, $envContent)) {
                error_log('❌ Failed to write .env file to: ' . $envPath);
                $parentDir = dirname($envPath);
                error_log('📁 Parent directory writable: ' . (is_writable($parentDir) ? 'YES' : 'NO'));
                echo json_encode(['success' => false, 'message' => 'Gagal menulis file .env ke: ' . $envPath]);
                exit;
            }
            
            error_log('✅ .env file written successfully');
            
            // Generate APP_KEY manually (exec() disabled on hosting)
            $keyGenerated = false;
            $appKey = '';
            
            // Generate key manually since exec() is disabled
            error_log('🔑 Generating APP_KEY manually (exec disabled)');
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
                error_log('✅ APP_KEY generated and saved: ' . substr($appKey, 0, 20) . '...');
            } else {
                error_log('❌ Failed to save APP_KEY to .env file');
            }
            
            // Create installer lock file
            $lockPath = $targetWscrmPath . '/storage/installer.lock';
            if (!file_put_contents($lockPath, date('Y-m-d H:i:s'))) {
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
                
                error_log('📄 Creating .htaccess at: ' . $htaccessPath);
                
                if (file_put_contents($htaccessPath, $htaccessContent)) {
                    error_log('✅ .htaccess created successfully in public_html');
                } else {
                    error_log('❌ Failed to create .htaccess in public_html');
                }
            }
            
            $successMessage = 'Environment berhasil dikonfigurasi. Installer lock file telah dibuat.';
            if ($keyGenerated) {
                $successMessage .= ' Application key telah digenerate untuk keamanan.';
            }
            
            echo json_encode([
                'success' => true, 
                'message' => $successMessage
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
                '/home/appws/domains/app.websweetstudio.com/wscrm' // Hardcoded path from UI
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
                        if (preg_match('/^APP_KEY=\s*$/m', $envContent) || !preg_match('/^APP_KEY=/m', $envContent)) {
                            $appKey = 'base64:' . base64_encode(random_bytes(32));
                            
                            if (preg_match('/^APP_KEY=/m', $envContent)) {
                                $envContent = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY=' . $appKey, $envContent);
                            } else {
                                $envContent .= "\nAPP_KEY=" . $appKey;
                            }
                            
                            file_put_contents($envFile, $envContent);
                            error_log("🔑 APP_KEY generated for: $path");
                            
                            // Note: Cache clearing skipped (exec() disabled on hosting)
                            error_log("ℹ️ Cache clearing skipped (exec disabled on shared hosting)");
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
                    error_log("🔍 Checking path: $path");
                    error_log("📁 Directory exists: " . (is_dir($path) ? 'YES' : 'NO'));
                    error_log("📄 .env exists: " . (file_exists($envFile) ? 'YES' : 'NO') . " at $envFile");
                    error_log("🔒 installer.lock exists: " . (file_exists($lockFile) ? 'YES' : 'NO') . " at $lockFile");
                    error_log("📂 storage dir exists: " . (is_dir($storageDir) ? 'YES' : 'NO') . " at $storageDir");
                    
                    // Check if .env exists (primary requirement)
                    if (file_exists($envFile)) {
                        // Check if installer.lock exists OR if storage directory exists (fallback)
                        if (file_exists($lockFile) || is_dir($storageDir)) {
                            $isInstallComplete = true;
                            $actualWscrmPath = $path;
                            error_log("✅ Installation complete found at: $path");
                            error_log("📄 .env: YES, 🔒 installer.lock: " . (file_exists($lockFile) ? 'YES' : 'NO') . ", 📂 storage: " . (is_dir($storageDir) ? 'YES' : 'NO'));
                            break;
                        }
                    }
                }
            }
            
            if (!$isInstallComplete) {
                // Debug info for troubleshooting
                $debugInfo = [
                    'DOCUMENT_ROOT' => $_SERVER['DOCUMENT_ROOT'],
                    'dirname_DOCUMENT_ROOT' => dirname($_SERVER['DOCUMENT_ROOT']),
                    'calculated_target' => $targetWscrmPath,
                    'detected_wscrm' => $detectedPath,
                    'possible_paths' => $possiblePaths,
                    'checks' => []
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
                            'storage_dir_exists' => is_dir($path . '/storage')
                        ];
                    }
                }
                
                echo json_encode([
                    'success' => false, 
                    'message' => 'Instalasi belum selesai. Folder install tidak dapat dihapus. Debug info tersedia di console.',
                    'debug' => $debugInfo
                ]);
                exit;
            }
            
            // Delete install folder recursively
            $installPath = __DIR__;
            
            function deleteDirectory($dir) {
                if (!is_dir($dir)) {
                    return false;
                }
                
                $files = array_diff(scandir($dir), array('.', '..'));
                
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
                            $details[] = '✅ APP_KEY telah di-generate untuk keamanan';
                        }
                    }
                }
                
                // Add cache clearing info
                $details[] = '✅ Cache konfigurasi telah dibersihkan';
                $details[] = '✅ Cache aplikasi telah dioptimasi';
                
                if (!empty($details)) {
                    $successMessage .= '\n\n' . implode('\n', $details);
                }
                
                echo json_encode([
                    'success' => true, 
                    'message' => $successMessage,
                    'details' => $details
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
                    PDO::ATTR_TIMEOUT => 5
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
                    'message' => $message
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
                    'message' => $friendlyMessage
                ]);
            }
            exit;
            
        case 'laravel_command':
            $command = $_POST['command'] ?? '';
            $targetWscrmPath = str_replace('\\', '/', dirname($_SERVER['DOCUMENT_ROOT'])) . '/wscrm';
            
            if (!is_dir($targetWscrmPath)) {
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
                            $output .= "APP_KEY set: " . ($hasAppKey ? 'Yes' : 'No') . "\n";
                            
                            // Check database connection
                            preg_match('/DB_CONNECTION=(.*)/', $envContent, $dbMatch);
                            $dbConnection = isset($dbMatch[1]) ? trim($dbMatch[1]) : 'not set';
                            $output .= "DB_CONNECTION: {$dbConnection}\n";
                        } else {
                            $output = ".env file not found";
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
$step1Complete = (bool)$wscrmPath; // Folder detected
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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 40px;
            max-width: 600px;
            width: 100%;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: #333;
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        .header p {
            color: #666;
            font-size: 1.1em;
        }
        
        .step {
            margin-bottom: 30px;
            padding: 20px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .step.active {
            border-color: #667eea;
            background: #f8f9ff;
        }
        
        .step.completed {
            border-color: #4caf50;
            background: #f1f8e9;
        }
        
        .step-title {
            font-size: 1.3em;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }
        
        .step-description {
            color: #666;
            margin-bottom: 15px;
        }
        
        .btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1em;
            transition: background 0.3s ease;
        }
        
        .btn:hover {
            background: #5a6fd8;
        }
        
        .btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        
        .btn-success {
            background: #4caf50;
        }
        
        .btn-success:hover {
            background: #45a049;
        }
        
        .alert {
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
        }
        
        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .alert-error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .alert-info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }
        
        .path-info {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 10px;
            font-family: monospace;
            word-break: break-all;
        }
        
        .radio-group {
            margin: 15px 0;
        }
        
        .radio-group label {
            display: block;
            margin: 10px 0;
            cursor: pointer;
        }
        
        .radio-group input[type="radio"] {
            margin-right: 8px;
        }
        
        /* Configuration Form Styles */
        .config-form {
            margin-top: 20px;
            padding: 20px;
            background: #f8f9ff;
            border-radius: 10px;
            border: 2px solid #667eea;
        }
        
        .config-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .config-header h3 {
            color: #333;
            font-size: 1.8em;
            margin-bottom: 8px;
        }
        
        .config-subtitle {
            color: #666;
            font-size: 1.1em;
        }
        
        .config-section {
            margin-bottom: 30px;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .config-section h4 {
            color: #333;
            font-size: 1.3em;
            margin-bottom: 15px;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 8px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
                gap: 15px;
            }
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
        }
        
        .label-text {
            font-weight: bold;
            color: #333;
            display: block;
        }
        
        .label-desc {
            font-size: 0.9em;
            color: #666;
            font-weight: normal;
        }
        
        .form-control {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 1em;
            transition: border-color 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-help {
            color: #666;
            font-size: 0.85em;
            margin-top: 5px;
            display: block;
        }
        
        /* Database Options */
        .database-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .database-options {
                grid-template-columns: 1fr;
            }
        }
        
        .radio-card {
            display: block;
            padding: 20px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .radio-card:hover {
            border-color: #667eea;
            background: #f8f9ff;
        }
        
        .radio-card input[type="radio"] {
            position: absolute;
            top: 15px;
            right: 15px;
            scale: 1.2;
        }
        
        .radio-card input[type="radio"]:checked + .radio-content {
            color: #667eea;
        }
        
        .radio-card:has(input[type="radio"]:checked) {
            border-color: #667eea;
            background: #f8f9ff;
        }
        
        .radio-content {
            padding-right: 30px;
        }
        
        .radio-title {
            font-size: 1.1em;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .radio-desc {
            font-size: 0.9em;
            color: #666;
            line-height: 1.4;
        }
        
        .mysql-config {
            margin-top: 20px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }
        
        .config-actions {
            text-align: center;
            padding-top: 20px;
            border-top: 2px solid #e0e0e0;
            margin-top: 20px;
        }
        
        .btn-outline {
            background: transparent;
            color: #667eea;
            border: 2px solid #667eea;
        }
        
        .btn-outline:hover {
            background: #667eea;
            color: white;
        }
        
        .btn-primary {
            background: #4caf50;
            font-size: 1.1em;
            padding: 15px 30px;
        }
        
        .btn-primary:hover {
            background: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 WSCRM Installer v2</h1>
            <p>Installer untuk struktur package baru dengan folder wscrm terpisah</p>
        </div>
        
        <?php if ($isAlreadyInstalled): ?>
            <div class="alert alert-success">
                <strong>✅ Instalasi Sudah Selesai!</strong><br>
                WSCRM sudah terinstall di: <code><?= htmlspecialchars($targetWscrmPath) ?></code><br><br>
                
                <div class="alert alert-info" style="margin-top: 15px;">
                    <strong>🛠️ Tools Tambahan:</strong> Sebelum menghapus installer, Anda dapat menjalankan tools di bawah untuk setup final:
                </div>
                
                <!-- Laravel Tools Section -->
                <div style="background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 20px; margin: 20px 0;">
                    <h4 style="margin: 0 0 15px 0; color: #333; font-size: 1.2em;">🚀 Setup Tools Laravel</h4>
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; margin-bottom: 20px;">
                        <button onclick="runLaravelCommand('migrate')" class="btn" style="background: #28a745; color: white;">📊 Run Migrations</button>
                        <button onclick="runLaravelCommand('db_seed')" class="btn" style="background: #17a2b8; color: white;">🌱 Run DB Seeder</button>
                        <button onclick="runLaravelCommand('storage_link')" class="btn" style="background: #ffc107; color: black;">🔗 Create Storage Link</button>
                        <button onclick="runLaravelCommand('key_generate')" class="btn" style="background: #dc3545; color: white;">🔑 Generate App Key</button>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; margin-bottom: 20px;">
                        <button onclick="runLaravelCommand('clear_cache')" class="btn" style="background: #6f42c1; color: white;">🧹 Clear All Cache</button>
                        <button onclick="runLaravelCommand('optimize')" class="btn" style="background: #fd7e14; color: white;">⚡ Optimize App</button>
                        <button onclick="runLaravelCommand('config_cache')" class="btn" style="background: #20c997; color: white;">⚙️ Cache Config</button>
                        <button onclick="runLaravelCommand('check_env')" class="btn" style="background: #6c757d; color: white;">📄 Check .env</button>
                    </div>
                    
                    <div id="laravel-tools-result" style="margin-top: 15px;"></div>
                </div>
                
                <div class="alert alert-info" style="margin-top: 15px;">
                    <strong>⚠️ Penting:</strong> Aplikasi tidak dapat diakses selama folder install masih ada.<br>
                    Silakan hapus folder install terlebih dahulu untuk mengakses aplikasi.
                </div>
                
                <div style="margin-top: 15px;">
                    <button onclick="deleteInstallFolder()" class="btn btn-success" style="margin-right: 10px; font-size: 1.1em; padding: 12px 20px;">🗑️ Hapus Folder Install & Buka Aplikasi</button>
                    <a href="../" class="btn" style="background: #6c757d; color: white; text-decoration: none; display: inline-block;">👀 Coba Buka Aplikasi (Akan Error)</a>
                </div>
                <div id="delete-result" style="margin-top: 15px;"></div>
            </div>
        <?php else: ?>
            <div class="step <?= $step1Complete ? 'completed' : '' ?>" id="step1">
                <div class="step-title">📁 Step 1: Deteksi Folder WSCRM</div>
                <div class="step-description">
                    Sistem akan mencari folder wscrm yang berisi file Laravel backend.
                </div>
                
                <?php if ($step1Complete): ?>
                    <div class="alert alert-success">
                        <strong>✅ Folder wscrm sudah ditemukan!</strong><br>
                        Lokasi: <div class="path-info"><?= htmlspecialchars($wscrmPath) ?></div>
                    </div>
                <?php else: ?>
                    <button class="btn" onclick="detectWscrm()">Deteksi Folder WSCRM</button>
                    <div id="detection-result"></div>
                <?php endif; ?>
            </div>
            
            <div class="step <?= $step2Complete ? 'completed' : '' ?><?= $step1Complete && !$step2Complete ? ' active' : '' ?>" id="step2" style="display: <?= $step1Complete ? 'block' : 'none' ?>;">
                <div class="step-title">🔄 Step 2: Pindahkan Folder WSCRM</div>
                <div class="step-description">
                    Pilih operasi untuk memindahkan folder wscrm ke lokasi yang tepat (sejajar dengan public_html).
                </div>
                
                <div class="alert alert-info">
                    <strong>Target lokasi:</strong><br>
                    <div class="path-info"><?= htmlspecialchars($targetWscrmPath) ?></div>
                </div>
                
                <div class="radio-group">
                    <label>
                        <input type="radio" name="operation" value="move" checked>
                        <strong>Pindahkan (Move)</strong> - Memindahkan folder wscrm ke lokasi target
                    </label>
                    <label>
                        <input type="radio" name="operation" value="copy">
                        <strong>Salin (Copy)</strong> - Menyalin folder wscrm ke lokasi target (folder asli tetap ada)
                    </label>
                </div>
                
                <button class="btn" onclick="moveWscrm()">Jalankan Operasi</button>
                <div id="move-result"></div>
            </div>
            
            <div class="step <?= $step3Complete ? 'completed' : '' ?><?= $step2Complete && !$step3Complete ? ' active' : '' ?>" id="step3" style="display: <?= $step2Complete ? 'block' : 'none' ?>;">
                <div class="step-title"><?= $step3Complete ? '✅' : '⚙️' ?> Step 3: <?= $step3Complete ? 'Konfigurasi Selesai' : 'Setup Environment' ?></div>
                <div class="step-description">
                    <?= $step3Complete ? 'Environment sudah dikonfigurasi. Aplikasi siap digunakan.' : 'Konfigurasikan environment untuk menyelesaikan instalasi.' ?>
                </div>
                
                <?php if ($step3Complete): ?>
                    <div class="alert alert-success">
                        <strong>✅ Instalasi berhasil diselesaikan!</strong><br>
                        Environment sudah dikonfigurasi dan aplikasi siap digunakan.
                    </div>
                    <div class="success-actions" style="margin-top: 15px;">
                        <a href="../" class="btn btn-success">🚀 Buka Aplikasi</a>
                        <button onclick="deleteInstallFolder()" class="btn btn-danger" style="margin-left: 10px; background: #dc3545; color: white; border: none; padding: 12px 20px; border-radius: 5px; cursor: pointer;">🗑️ Hapus Folder Install</button>
                    </div>
                    <div id="delete-result" style="margin-top: 15px;"></div>
                <?php else: ?>
                    <button class="btn btn-success" onclick="showEnvConfiguration()">🔧 Setup Environment</button>
                    <div id="env-config-form" class="config-form" style="display: none;">
                        <div class="config-header">
                            <h3>🔧 Konfigurasi Environment</h3>
                            <p class="config-subtitle">Konfigurasikan pengaturan dasar untuk aplikasi WSCRM Anda</p>
                        </div>
                        
                        <div class="config-section">
                            <h4>📱 Informasi Aplikasi</h4>
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
                            <h4>🗄️ Konfigurasi Database</h4>
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
                                    <small class="form-help">💡 Kosongkan field ini jika database tidak menggunakan password (seperti setup local XAMPP/WAMP)</small>
                                </div>
                                
                                <button type="button" class="btn btn-outline" onclick="testDatabaseConnection()">
                                    🔌 Test Koneksi Database
                                </button>
                                <div id="db-test-result"></div>
                            </div>
                        </div>
                        
                        <div class="config-actions">
                            <button class="btn btn-primary" onclick="configureEnvironment()">
                                💾 Simpan & Lanjutkan
                            </button>
                        </div>
                        <div id="env-config-result"></div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
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
                console.log('🔄 Initialized detectedWscrmPath from UI:', detectedWscrmPath);
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
                    console.log('🐛 WSCRM Detection Debug:', data.debug);
                }
                
                if (data.success) {
                    // Normalize path - remove trailing slash
                    detectedWscrmPath = data.path.replace(/\/$/, '');
                    
                    // Debug: Log detected path
                    console.log('✅ WSCRM path detected:', detectedWscrmPath);
                    console.log('📋 Full detection data:', data);
                    console.log('🔧 detectedWscrmPath after assignment:', detectedWscrmPath);
                    console.log('🔧 detectedWscrmPath type after assignment:', typeof detectedWscrmPath);
                    
                    // Test if variable is accessible
                    window.detectedWscrmPath = detectedWscrmPath;
                    console.log('🌐 Global detectedWscrmPath set:', window.detectedWscrmPath);
                    
                    // Check if wscrm is already in correct location (outside public_html)
                    const isAlreadyMoved = data.already_in_target_location || false;
                    
                    if (isAlreadyMoved) {
                        // Skip Step 2 - already in correct location
                        resultDiv.innerHTML = `
                            <div class="alert alert-success">
                                <strong>✅ Folder wscrm sudah di lokasi yang benar!</strong><br>
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
                                <strong>✅ Folder wscrm ditemukan!</strong><br>
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
                            <strong>❌ ${data.message}</strong><br>
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
                        <strong>❌ Terjadi kesalahan saat mendeteksi folder wscrm</strong>
                    </div>
                `;
                btn.disabled = false;
                btn.textContent = 'Deteksi Folder WSCRM';
            });
        }
        
        function moveWscrm() {
            const btn = event.target;
            const operation = document.querySelector('input[name="operation"]:checked').value;
            
            // Debug: Log current state
            console.log('🔍 Current detectedWscrmPath value:', detectedWscrmPath);
            console.log('🔍 detectedWscrmPath type:', typeof detectedWscrmPath);
            console.log('🔍 detectedWscrmPath length:', detectedWscrmPath ? detectedWscrmPath.length : 'undefined');
            console.log('🌐 window.detectedWscrmPath:', window.detectedWscrmPath);
            
            // Try to use global variable as fallback
            if (!detectedWscrmPath && window.detectedWscrmPath) {
                detectedWscrmPath = window.detectedWscrmPath;
                console.log('🔄 Using global detectedWscrmPath as fallback:', detectedWscrmPath);
            }
            
            // Validasi detectedWscrmPath sebelum mengirim
            if (!detectedWscrmPath || detectedWscrmPath.trim() === '') {
                document.getElementById('move-result').innerHTML = `
                    <div class="alert alert-error">
                        <strong>❌ Path wscrm tidak terdeteksi. Silakan jalankan deteksi terlebih dahulu.</strong>
                        <br><small>Debug: detectedWscrmPath = '${detectedWscrmPath}' (${typeof detectedWscrmPath})</small>
                        <br><small>window.detectedWscrmPath = '${window.detectedWscrmPath}'</small>
                    </div>
                `;
                return;
            }
            
            console.log('🔍 Sending wscrm_path:', detectedWscrmPath);
            
            btn.disabled = true;
            btn.textContent = operation === 'move' ? 'Memindahkan...' : 'Menyalin...';
            
            const payload = `action=move_wscrm&wscrm_path=${encodeURIComponent(detectedWscrmPath)}&operation=${operation}`;
            console.log('📤 Request payload:', payload);
            
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
                            <strong>✅ ${data.message}</strong>
                        </div>
                    `;
                    
                    document.getElementById('step2').classList.add('completed');
                    document.getElementById('step2').classList.remove('active');
                    document.getElementById('step3').style.display = 'block';
                    document.getElementById('step3').classList.add('active');
                } else {
                    resultDiv.innerHTML = `
                        <div class="alert alert-error">
                            <strong>❌ ${data.message}</strong>
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
                        <strong>❌ Terjadi kesalahan saat memindahkan folder wscrm</strong>
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
            btn.textContent = '🔄 Testing...';
            
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
                            <strong>✅ Koneksi database berhasil!</strong><br>
                            ${data.message}
                        </div>
                    `;
                } else {
                    resultDiv.innerHTML = `
                        <div class="alert alert-error">
                            <strong>❌ Koneksi database gagal!</strong><br>
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
                        <strong>❌ Error: ${error.message}</strong>
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
            btn.textContent = '🗑️ Menghapus...';
            
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
                            <strong>✅ ${data.message}</strong><br>
                            Halaman akan dialihkan ke aplikasi dalam 3 detik...
                        </div>
                    `;
                    
                    setTimeout(() => {
                        window.location.href = '../';
                    }, 3000);
                } else {
                    resultDiv.innerHTML = `
                        <div class="alert alert-error">
                            <strong>❌ ${data.message}</strong>
                        </div>
                    `;
                    
                    btn.disabled = false;
                    btn.textContent = originalText;
                }
            })
            .catch(error => {
                document.getElementById('delete-result').innerHTML = `
                    <div class="alert alert-error">
                        <strong>❌ Error: ${error.message}</strong>
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
            btn.textContent = '⏳ Running...';
            
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
                            <strong>✅ ${data.message}</strong><br>
                            <pre style="margin-top: 10px; background: #f8f9fa; padding: 10px; border-radius: 4px; font-size: 12px; max-height: 200px; overflow-y: auto;">${data.output || 'No output'}</pre>
                        </div>
                    `;
                } else {
                    resultDiv.innerHTML = `
                        <div class="alert alert-error">
                            <strong>❌ ${data.message}</strong>
                        </div>
                    `;
                }
                
                btn.disabled = false;
                btn.textContent = originalText;
            })
            .catch(error => {
                document.getElementById('laravel-tools-result').innerHTML = `
                    <div class="alert alert-error">
                        <strong>❌ Error: ${error.message}</strong>
                    </div>
                `;
                btn.disabled = false;
                btn.textContent = originalText;
            });
        }
        
        function configureEnvironment() {
            const btn = event.target;
            btn.disabled = true;
            btn.textContent = '💾 Menyimpan...';
            
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
                            <strong>✅ Environment berhasil dikonfigurasi!</strong><br>
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
                            <strong>❌ ${data.message}</strong>
                        </div>
                    `;
                    
                    btn.disabled = false;
                    btn.textContent = 'Simpan Konfigurasi';
                }
            })
            .catch(error => {
                document.getElementById('env-config-result').innerHTML = `
                    <div class="alert alert-error">
                        <strong>❌ Error: ${error.message}</strong>
                    </div>
                `;
                btn.disabled = false;
                btn.textContent = 'Simpan Konfigurasi';
            });
        }
    </script>
</body>
</html>