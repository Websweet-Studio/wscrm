<?php

define('LARAVEL_START', microtime(true));

$skipInstaller = false;
$envPaths = [
    __DIR__.'/wscrm/.env',
    __DIR__.'/../wscrm/.env',
    __DIR__.'/../.env',
];
foreach ($envPaths as $envPath) {
    if (file_exists($envPath)) {
        $envContent = file_get_contents($envPath);
        $skipInstaller = strpos($envContent, 'APP_ENV=local') !== false;
        break;
    }
}

$skipMarkerPaths = [
    __DIR__.'/../storage/installer.skip',
    __DIR__.'/../wscrm/storage/installer.skip',
    __DIR__.'/wscrm/storage/installer.skip',
];
$hasSkipMarker = false;
foreach ($skipMarkerPaths as $path) {
    if (file_exists($path)) {
        $hasSkipMarker = true;
        break;
    }
}

if (is_dir(__DIR__.'/install') && ! $skipInstaller && ! $hasSkipMarker) {
    $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
    if (strpos($requestUri, '/install') !== 0) {
        header('Location: /install/', true, 302);
        exit;
    }
}

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

// Auto-detect Laravel path for flexible deployment
$laravel_root = __DIR__;

// Check priority: wscrm folder (flat deployment after installer)
if (file_exists(__DIR__.'/../wscrm/vendor/autoload.php')) {
    $laravel_root = __DIR__.'/../wscrm';  // wscrm moved outside web root
}
// Check if we're in flat deployment (wscrm in same directory)
elseif (file_exists(__DIR__.'/wscrm/vendor/autoload.php')) {
    $laravel_root = __DIR__.'/wscrm';
}
// Check if we're in standard Laravel structure (public folder)
elseif (file_exists(__DIR__.'/../vendor/autoload.php')) {
    $laravel_root = __DIR__.'/..';
}
// Check if Laravel is in parent directory
elseif (file_exists(dirname(__DIR__).'/vendor/autoload.php')) {
    $laravel_root = dirname(__DIR__);
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $laravel_root.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $laravel_root.'/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once $laravel_root.'/bootstrap/app.php';

$app->handleRequest(Request::capture());
