<?php

// Disable error reporting for production
error_reporting(0);
ini_set('display_errors', 0);

// Enable error reporting for debugging (uncomment if needed)
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

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
    $currentDir = __DIR__;
    $parentDir = dirname($currentDir, 2); // Go up 2 levels from /public/install/
    
    // Check if wscrm folder exists in current directory
    if (is_dir($currentDir . '/../../wscrm')) {
        return $currentDir . '/../../wscrm';
    }
    
    // Check if wscrm folder exists in parent directory
    if (is_dir($parentDir . '/wscrm')) {
        return $parentDir . '/wscrm';
    }
    
    // Check if wscrm folder exists in same level as public_html
    $publicHtmlParent = dirname($_SERVER['DOCUMENT_ROOT']);
    if (is_dir($publicHtmlParent . '/wscrm')) {
        return $publicHtmlParent . '/wscrm';
    }
    
    return false;
}

function moveWscrmFolder($wscrmPath, $targetPath) {
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
                echo json_encode([
                    'success' => true, 
                    'path' => $wscrmPath,
                    'message' => 'Folder wscrm ditemukan di: ' . $wscrmPath
                ]);
            } else {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Folder wscrm tidak ditemukan'
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
            
            // Determine target path (sejajar dengan public_html)
            $publicHtmlParent = dirname($_SERVER['DOCUMENT_ROOT']);
            $targetPath = $publicHtmlParent . '/wscrm';
            
            if ($operation === 'copy') {
                $result = copyWscrmFolder($wscrmPath, $targetPath);
            } else {
                $result = moveWscrmFolder($wscrmPath, $targetPath);
            }
            
            echo json_encode($result);
            exit;
    }
}

// Check if already installed
$wscrmPath = detectWscrmFolder();
$publicHtmlParent = dirname($_SERVER['DOCUMENT_ROOT']);
$targetWscrmPath = $publicHtmlParent . '/wscrm';
$isAlreadyInstalled = is_dir($targetWscrmPath) && file_exists($targetWscrmPath . '/.env');

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
            <div class="step" id="step1">
                <div class="step-title">📁 Step 1: Deteksi Folder WSCRM</div>
                <div class="step-description">
                    Sistem akan mencari folder wscrm yang berisi file Laravel backend.
                </div>
                <button class="btn" onclick="detectWscrm()">Deteksi Folder WSCRM</button>
                <div id="detection-result"></div>
            </div>
            
            <div class="step" id="step2" style="display: none;">
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
            
            <div class="step" id="step3" style="display: none;">
                <div class="step-title">✅ Step 3: Instalasi Selesai</div>
                <div class="step-description">
                    Folder wscrm berhasil dipindahkan. Anda dapat melanjutkan ke konfigurasi aplikasi.
                </div>
                <a href="../" class="btn btn-success">Lanjut ke Konfigurasi</a>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        let detectedWscrmPath = '';
        
        function detectWscrm() {
            const btn = event.target;
            btn.disabled = true;
            btn.textContent = 'Mendeteksi...';
            
            fetch('v2.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=detect_wscrm'
            })
            .then(response => response.json())
            .then(data => {
                const resultDiv = document.getElementById('detection-result');
                
                if (data.success) {
                    detectedWscrmPath = data.path;
                    resultDiv.innerHTML = `
                        <div class="alert alert-success">
                            <strong>✅ Folder wscrm ditemukan!</strong><br>
                            Lokasi: <div class="path-info">${data.path}</div>
                        </div>
                    `;
                    
                    document.getElementById('step1').classList.add('completed');
                    document.getElementById('step2').style.display = 'block';
                    document.getElementById('step2').classList.add('active');
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
            
            fetch('v2.php', {
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
    </script>
</body>
</html>