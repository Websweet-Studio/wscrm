<?php
/**
 * Debug installer - untuk mendiagnosis masalah
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Laravel Installer Debug</h1>";
echo "<div style='font-family: monospace; background: #f0f0f0; padding: 20px;'>";

// Basic PHP info
echo "<h2>PHP Information</h2>";
echo "PHP Version: " . PHP_VERSION . "<br>";
echo "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Script Path: " . __FILE__ . "<br>";
echo "Install Directory: " . __DIR__ . "<br>";
echo "Parent Directory: " . dirname(__DIR__) . "<br>";

// Check extensions
echo "<h2>Required Extensions</h2>";
$extensions = ['pdo', 'pdo_mysql', 'openssl', 'mbstring', 'tokenizer', 'xml', 'ctype', 'json'];
foreach ($extensions as $ext) {
    $status = extension_loaded($ext) ? "✅" : "❌";
    echo "$status $ext<br>";
}

// Check file permissions
echo "<h2>File System</h2>";
$paths = [
    '.' => __DIR__,
    '..' => dirname(__DIR__),
    '../index.php' => dirname(__DIR__) . '/index.php',
    '../.env.example' => dirname(__DIR__) . '/.env.example',
    '../storage' => dirname(__DIR__) . '/storage',
    '../bootstrap/cache' => dirname(__DIR__) . '/bootstrap/cache',
];

foreach ($paths as $label => $path) {
    $exists = file_exists($path) ? "EXISTS" : "MISSING";
    $readable = is_readable($path) ? "READABLE" : "NOT READABLE";
    $writable = is_writable($path) ? "WRITABLE" : "NOT WRITABLE";
    
    echo "$label: $exists, $readable, $writable<br>";
    if (file_exists($path)) {
        echo "&nbsp;&nbsp;Path: $path<br>";
        if (is_file($path)) {
            echo "&nbsp;&nbsp;Size: " . filesize($path) . " bytes<br>";
        }
    }
    echo "<br>";
}

// Test simple file operations
echo "<h2>File Operation Test</h2>";
try {
    $testFile = __DIR__ . '/test.txt';
    $result = file_put_contents($testFile, 'test');
    if ($result) {
        echo "✅ Can write files<br>";
        unlink($testFile);
        echo "✅ Can delete files<br>";
    } else {
        echo "❌ Cannot write files<br>";
    }
} catch (Exception $e) {
    echo "❌ File operation error: " . $e->getMessage() . "<br>";
}

// Test parent directory access
echo "<h2>Parent Directory Test</h2>";
$parentIndex = dirname(__DIR__) . '/index.php';
if (file_exists($parentIndex)) {
    $size = filesize($parentIndex);
    echo "✅ Can access parent index.php ($size bytes)<br>";
    
    // Try to read first few lines
    try {
        $content = file_get_contents($parentIndex, false, null, 0, 200);
        if ($content) {
            echo "✅ Can read file content<br>";
            echo "<pre>" . htmlspecialchars(substr($content, 0, 100)) . "...</pre>";
        }
    } catch (Exception $e) {
        echo "❌ Cannot read file: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ Cannot access parent index.php<br>";
}

// Check if we can include files
echo "<h2>Include Test</h2>";
try {
    // Test if we can access PHP functions properly
    $reflection = new ReflectionClass('PDO');
    echo "✅ Can use reflection and PDO class<br>";
} catch (Exception $e) {
    echo "❌ Reflection error: " . $e->getMessage() . "<br>";
}

echo "</div>";

echo "<hr>";
echo "<p><a href='index.php'>Try Main Installer</a> | <a href='debug.php?phpinfo=1'>PHP Info</a></p>";

if (isset($_GET['phpinfo'])) {
    phpinfo();
}
?>