<?php
// Debug script untuk deployment troubleshooting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔍 Debug Deployment - " . date('Y-m-d H:i:s') . "</h2>";

echo "<h3>📁 Directory Information</h3>";
echo "Current Directory: " . __DIR__ . "<br>";
echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Not set') . "<br>";
echo "Script Name: " . ($_SERVER['SCRIPT_NAME'] ?? 'Not set') . "<br>";

echo "<h3>🔍 Path Detection Analysis</h3>";

$paths_to_check = [
    'install_dir' => __DIR__ . '/install',
    'wscrm_same_dir' => __DIR__ . '/wscrm',
    'wscrm_parent' => __DIR__ . '/../wscrm', 
    'vendor_wscrm_same' => __DIR__ . '/wscrm/vendor/autoload.php',
    'vendor_wscrm_parent' => __DIR__ . '/../wscrm/vendor/autoload.php',
    'vendor_laravel_standard' => __DIR__ . '/../vendor/autoload.php',
    'vendor_parent' => dirname(__DIR__) . '/vendor/autoload.php',
];

foreach ($paths_to_check as $name => $path) {
    $exists = file_exists($path) ? '✅ EXISTS' : '❌ NOT FOUND';
    $is_readable = is_readable($path) ? '📖 READABLE' : '🔒 NOT READABLE';
    echo "{$name}: {$path} - {$exists} {$is_readable}<br>";
}

echo "<h3>🎯 Laravel Root Detection Logic</h3>";

$laravel_root = __DIR__;
echo "Initial laravel_root: {$laravel_root}<br>";

if (file_exists(__DIR__ . '/../wscrm/vendor/autoload.php')) {
    $laravel_root = __DIR__ . '/../wscrm';
    echo "✅ Found: wscrm moved outside web root<br>";
}
elseif (file_exists(__DIR__ . '/wscrm/vendor/autoload.php')) {
    $laravel_root = __DIR__ . '/wscrm';
    echo "✅ Found: wscrm in same directory<br>";
}
elseif (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    $laravel_root = __DIR__ . '/..';
    echo "✅ Found: standard Laravel structure<br>";
} 
elseif (file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
    $laravel_root = dirname(__DIR__);
    echo "✅ Found: Laravel in parent directory<br>";
} else {
    echo "❌ NO VALID LARAVEL ROOT FOUND!<br>";
}

echo "Final laravel_root: {$laravel_root}<br>";

echo "<h3>📦 Laravel Files Check</h3>";

$laravel_files = [
    'autoload' => $laravel_root . '/vendor/autoload.php',
    'bootstrap' => $laravel_root . '/bootstrap/app.php',
    'storage' => $laravel_root . '/storage',
    'env_example' => $laravel_root . '/.env.example',
    'env' => $laravel_root . '/.env',
];

foreach ($laravel_files as $name => $file) {
    $exists = file_exists($file) ? '✅ EXISTS' : '❌ NOT FOUND';
    $is_readable = is_readable($file) ? '📖 READABLE' : '🔒 NOT READABLE';
    echo "{$name}: {$file} - {$exists} {$is_readable}<br>";
}

echo "<h3>🔧 PHP Environment</h3>";
echo "PHP Version: " . phpversion() . "<br>";
echo "PHP Extensions:<br>";

$required_extensions = ['pdo', 'mbstring', 'tokenizer', 'openssl', 'xml', 'ctype', 'json'];
foreach ($required_extensions as $ext) {
    $loaded = extension_loaded($ext) ? '✅ LOADED' : '❌ MISSING';
    echo "- {$ext}: {$loaded}<br>";
}

echo "<h3>⚠️  Installer Lock Check</h3>";
$installerLockPaths = [
    __DIR__ . '/wscrm/storage/installer.lock',
    __DIR__ . '/../wscrm/storage/installer.lock',
    __DIR__ . '/../storage/installer.lock'
];

$installCompleted = false;
foreach ($installerLockPaths as $lockPath) {
    $exists = file_exists($lockPath) ? '✅ EXISTS' : '❌ NOT FOUND';
    echo "Lock file: {$lockPath} - {$exists}<br>";
    if (file_exists($lockPath)) {
        $installCompleted = true;
    }
}

echo "Installation completed: " . ($installCompleted ? '✅ YES' : '❌ NO') . "<br>";

echo "<h3>🚀 Test Autoload</h3>";
if (file_exists($laravel_root . '/vendor/autoload.php')) {
    try {
        require $laravel_root . '/vendor/autoload.php';
        echo "✅ Autoload successful<br>";
        
        if (file_exists($laravel_root . '/bootstrap/app.php')) {
            echo "✅ Bootstrap file exists<br>";
            // Don't actually bootstrap Laravel in debug mode
        } else {
            echo "❌ Bootstrap file missing<br>";
        }
    } catch (Exception $e) {
        echo "❌ Autoload failed: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ Autoload file not found<br>";
}

echo "<h3>📊 Directory Listing</h3>";
echo "Root directory contents:<br>";
$files = scandir(__DIR__);
foreach ($files as $file) {
    if ($file != '.' && $file != '..') {
        $type = is_dir(__DIR__ . '/' . $file) ? '📁 DIR' : '📄 FILE';
        echo "- {$file} {$type}<br>";
    }
}

if (is_dir(__DIR__ . '/wscrm')) {
    echo "<br>WSCRM directory contents:<br>";
    $wscrm_files = scandir(__DIR__ . '/wscrm');
    foreach ($wscrm_files as $file) {
        if ($file != '.' && $file != '..') {
            $type = is_dir(__DIR__ . '/wscrm/' . $file) ? '📁 DIR' : '📄 FILE';
            echo "- wscrm/{$file} {$type}<br>";
        }
    }
}
?>