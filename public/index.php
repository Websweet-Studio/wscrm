<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
$basePath = rtrim(str_replace('\\', '/', (string) dirname($scriptName)), '/');
if ($basePath === '/') {
    $basePath = '';
}
$installPrefix = $basePath . '/install';
$installUrl = $installPrefix . '/';
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestPath = parse_url($requestUri, PHP_URL_PATH) ?: '/';

$envCandidates = [
    __DIR__ . '/../.env',
    __DIR__ . '/../wscrm/.env',
    __DIR__ . '/wscrm/.env',
];
$appEnv = getenv('APP_ENV') ?: ($_SERVER['APP_ENV'] ?? '');
foreach ($envCandidates as $envPath) {
    if ($appEnv !== '' || ! is_file($envPath)) {
        continue;
    }
    $envContent = @file_get_contents($envPath);
    if ($envContent === false) {
        continue;
    }
    if (preg_match('/^APP_ENV\s*=\s*(.*)$/m', $envContent, $m)) {
        $appEnv = trim($m[1], " \t\n\r\0\x0B\"'");
        break;
    }
}
$isLocal = in_array(strtolower((string) $appEnv), ['local', 'development', 'dev'], true);
$serverHost = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? '')));
$isLocalHttp = in_array($serverHost, ['localhost', '127.0.0.1', '::1'], true);
$skipByEnv = $isLocal && $isLocalHttp;

$skipMarkerPaths = [
    __DIR__ . '/../storage/installer.skip',
    __DIR__ . '/../wscrm/storage/installer.skip',
    __DIR__ . '/wscrm/storage/installer.skip',
];
$hasSkipMarker = false;
foreach ($skipMarkerPaths as $path) {
    if (is_file($path)) {
        $hasSkipMarker = true;
        break;
    }
}

$installerLockPaths = [
    __DIR__ . '/../storage/installer.lock',
    __DIR__ . '/../wscrm/storage/installer.lock',
    __DIR__ . '/wscrm/storage/installer.lock',
];
$installCompleted = false;
foreach ($installerLockPaths as $lockPath) {
    if (is_file($lockPath)) {
        $installCompleted = true;
        break;
    }
}

$installerDirs = [
    __DIR__ . '/install',
    dirname(__DIR__) . '/install',
    (string) (($_SERVER['DOCUMENT_ROOT'] ?? '') ? rtrim((string) $_SERVER['DOCUMENT_ROOT'], '/\\') . '/install' : ''),
    (string) (($_SERVER['DOCUMENT_ROOT'] ?? '') ? rtrim((string) $_SERVER['DOCUMENT_ROOT'], '/\\') . '/public/install' : ''),
];
$installerExists = false;
foreach ($installerDirs as $dir) {
    if ($dir !== '' && is_dir($dir)) {
        $installerExists = true;
        break;
    }
}

if ($installerExists && ! $skipByEnv && ! $hasSkipMarker && ! $installCompleted) {
    if (strpos($requestPath, $installPrefix) !== 0) {
        header('Location: ' . $installUrl, true, 302);
        exit;
    }
}

// Auto-detect Laravel path for flexible deployment
$laravel_root = __DIR__;

// Check priority: wscrm folder (flat deployment after installer)
if (file_exists(__DIR__ . '/../wscrm/vendor/autoload.php')) {
    $laravel_root = __DIR__ . '/../wscrm';  // wscrm moved outside web root
}
// Check if we're in flat deployment (wscrm in same directory)
elseif (file_exists(__DIR__ . '/wscrm/vendor/autoload.php')) {
    $laravel_root = __DIR__ . '/wscrm';
}
// Check if we're in standard Laravel structure (public folder)
elseif (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    $laravel_root = __DIR__ . '/..';
}
// Check if Laravel is in parent directory
elseif (file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
    $laravel_root = dirname(__DIR__);
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $laravel_root . '/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $laravel_root . '/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once $laravel_root . '/bootstrap/app.php';

$app->handleRequest(Request::capture());
