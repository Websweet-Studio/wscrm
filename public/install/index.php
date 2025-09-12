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
            $wscrmPath = $_POST['wscrm_path'] ?? '';
            $operation = $_POST['operation'] ?? 'move'; // 'move' or 'copy'
            
            if (empty($wscrmPath)) {
                echo json_encode(['success' => false, 'message' => 'Path wscrm tidak valid']);
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
            
            if (!is_dir($targetWscrmPath)) {
                echo json_encode(['success' => false, 'message' => 'Folder wscrm tidak ditemukan di lokasi target']);
                exit;
            }
            
            // Create .env file
            $envPath = $targetWscrmPath . '/.env';
            $envTemplate = $targetWscrmPath . '/.env.example';
            
            if (!file_exists($envTemplate)) {
                echo json_encode(['success' => false, 'message' => 'File .env.example tidak ditemukan']);
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
                
                $replacements = array_merge($replacements, [
                    'DB_CONNECTION=sqlite' => 'DB_CONNECTION=mysql',
                    'DB_HOST=127.0.0.1' => 'DB_HOST=' . $dbHost,
                    'DB_PORT=3306' => 'DB_PORT=' . $dbPort,
                    'DB_DATABASE=database/database.sqlite' => 'DB_DATABASE=' . $dbName,
                    'DB_USERNAME=null' => 'DB_USERNAME=' . $dbUsername,
                    'DB_PASSWORD=null' => 'DB_PASSWORD=' . $dbPassword,
                    '# DB_HOST=127.0.0.1' => 'DB_HOST=' . $dbHost,
                    '# DB_PORT=3306' => 'DB_PORT=' . $dbPort,
                    '# DB_DATABASE=wscrm' => 'DB_DATABASE=' . $dbName,
                    '# DB_USERNAME=root' => 'DB_USERNAME=' . $dbUsername,
                    '# DB_PASSWORD=' => 'DB_PASSWORD=' . $dbPassword,
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
            if (!file_put_contents($envPath, $envContent)) {
                echo json_encode(['success' => false, 'message' => 'Gagal menulis file .env']);
                exit;
            }
            
            // Generate APP_KEY using Laravel's key:generate command
            $keyGenerated = false;
            $appKey = '';
            
            // Try to generate key using PHP artisan
            if (file_exists($targetWscrmPath . '/artisan')) {
                $currentDir = getcwd();
                chdir($targetWscrmPath);
                
                // Try to run key:generate command
                $output = [];
                $returnVar = 0;
                exec('php artisan key:generate --show --no-interaction 2>&1', $output, $returnVar);
                
                chdir($currentDir);
                
                if ($returnVar === 0 && !empty($output)) {
                    $appKey = trim(end($output));
                    if (strpos($appKey, 'base64:') === 0) {
                        $keyGenerated = true;
                        
                        // Update .env file with generated key
                        $envContent = file_get_contents($envPath);
                        if (strpos($envContent, 'APP_KEY=') !== false) {
                            $envContent = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY=' . $appKey, $envContent);
                        } else {
                            $envContent .= "\nAPP_KEY=" . $appKey;
                        }
                        file_put_contents($envPath, $envContent);
                    }
                }
            }
            
            // Fallback: Generate key manually if artisan failed
            if (!$keyGenerated) {
                $appKey = 'base64:' . base64_encode(random_bytes(32));
                
                // Update .env file with generated key
                $envContent = file_get_contents($envPath);
                if (strpos($envContent, 'APP_KEY=') !== false) {
                    $envContent = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY=' . $appKey, $envContent);
                } else {
                    $envContent .= "\nAPP_KEY=" . $appKey;
                }
                file_put_contents($envPath, $envContent);
                $keyGenerated = true;
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
                
                $publicHtmlDir = dirname(__DIR__);
                $htaccessPath = $publicHtmlDir . '/.htaccess';
                file_put_contents($htaccessPath, $htaccessContent);
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
                WSCRM sudah terinstall di: <code><?= htmlspecialchars($targetWscrmPath) ?></code><br>
                <a href="../" class="btn btn-success" style="margin-top: 10px; text-decoration: none; display: inline-block;">Buka Aplikasi</a>
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
                    <a href="../" class="btn btn-success">Buka Aplikasi</a>
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
                                    <input type="password" id="db_password" class="form-control" placeholder="Masukkan password database">
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
            
            btn.disabled = true;
            btn.textContent = operation === 'move' ? 'Memindahkan...' : 'Menyalin...';
            
            fetch('index.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=move_wscrm&wscrm_path=${encodeURIComponent(detectedWscrmPath)}&operation=${operation}`
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