<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Check if installer exists and installation is not completed (skip in development)
$skipInstaller = false;
if (file_exists(__DIR__ . '/../.env')) {
    $envContent = file_get_contents(__DIR__ . '/../.env');
    $skipInstaller = strpos($envContent, 'APP_ENV=local') !== false;
}

if (is_dir(__DIR__ . '/install') && !$skipInstaller) {
    // For flat deployment, check wscrm folder for installer.lock
    $installerLockPaths = [
        __DIR__ . '/wscrm/storage/installer.lock',      // wscrm in same directory
        __DIR__ . '/../wscrm/storage/installer.lock',   // wscrm moved outside web root
        __DIR__ . '/../storage/installer.lock'          // standard Laravel structure
    ];
    
    $installCompleted = false;
    foreach ($installerLockPaths as $lockPath) {
        if (file_exists($lockPath)) {
            $installCompleted = true;
            break;
        }
    }
    
    if (!$installCompleted) {
        // Redirect to installer for non-install URLs
        $requestUri = $_SERVER['REQUEST_URI'];
        if (strpos($requestUri, '/install') !== 0) {
            header('Location: /install/');
            exit;
        }
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
if (file_exists($maintenance = $laravel_root.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $laravel_root.'/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once $laravel_root.'/bootstrap/app.php';

$app->handleRequest(Request::capture());
