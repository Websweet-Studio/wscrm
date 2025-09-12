<?php
/**
 * Laravel Installer - WordPress Style
 * Simple installation system untuk kemudahan deployment
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Helper function to parse Laravel .env file safely
function parseEnvFile($path) {
    $env = [];
    if (!file_exists($path)) {
        return $env;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        
        // Skip comments
        if (strpos($line, '#') === 0) {
            continue;
        }
        
        // Parse KEY=VALUE
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remove quotes if present
            if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
                (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
                $value = substr($value, 1, -1);
            }
            
            $env[$key] = $value;
        }
    }
    
    return $env;
}

// Check if already installed
if (file_exists(__DIR__ . '/../.env') && !file_exists(__DIR__ . '/../storage/installer.lock')) {
    // Check if database connection works
    try {
        $env = parseEnvFile(__DIR__ . '/../.env');
        if (isset($env['DB_CONNECTION']) && $env['DB_CONNECTION'] === 'sqlite') {
            $dbPath = __DIR__ . '/../database/database.sqlite';
            if (file_exists($dbPath)) {
                $pdo = new PDO('sqlite:' . $dbPath);
                $result = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users' LIMIT 1");
                if ($result && $result->fetch()) {
                    header('Location: /');
                    exit('Installation sudah selesai. <a href="/">Akses aplikasi</a>');
                }
            }
        }
    } catch (Exception $e) {
        // Continue with installation if database check fails
    }
}

// Handle installation process
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle test connection request
    if (isset($_POST['test_connection'])) {
        handleTestConnection();
        exit;
    }
    
    $step = $_POST['step'] ?? 1;
    
    switch ($step) {
        case 2:
            handleEnvironmentSetup();
            break;
        case 3:
            handleDatabaseSetup();
            break;
        case 4:
            handleFinalSetup();
            break;
    }
} else {
    // GET request - show current step
    $step = $_GET['step'] ?? 1;
    showStep($step);
}

function handleTestConnection() {
    $dbHost = trim($_POST['db_host'] ?? 'localhost');
    $dbPort = trim($_POST['db_port'] ?? '3306');
    $dbDatabase = trim($_POST['db_database'] ?? '');
    $dbUsername = trim($_POST['db_username'] ?? '');
    $dbPassword = $_POST['db_password'] ?? '';
    
    // Debug output
    echo "Koneksi test dimulai...<br>";
    echo "Host: " . htmlspecialchars($dbHost) . "<br>";
    echo "Port: " . htmlspecialchars($dbPort) . "<br>";
    echo "Database: " . htmlspecialchars($dbDatabase) . "<br>";
    echo "Username: " . htmlspecialchars($dbUsername) . "<br>";
    echo "Password: " . (empty($dbPassword) ? '(kosong)' : '***ada***') . "<br><br>";
    
    if (empty($dbUsername)) {
        echo "❌ Error: Database Username tidak boleh kosong!";
        return;
    }
    
    if (empty($dbDatabase)) {
        echo "❌ Error: Database Name tidak boleh kosong!";
        return;
    }
    
    try {
        $dsn = "mysql:host=$dbHost;port=$dbPort;dbname=$dbDatabase;charset=utf8mb4";
        $pdo = new PDO($dsn, $dbUsername, $dbPassword, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 5, // 5 second timeout
        ]);
        
        // Test with a simple query
        $stmt = $pdo->query("SELECT 1 as test");
        $result = $stmt->fetch();
        
        if ($result && $result['test'] == 1) {
            echo "✅ Koneksi berhasil! Database dapat diakses dengan baik.";
        } else {
            echo "❌ Koneksi berhasil tapi ada masalah dengan query test.";
        }
    } catch (PDOException $e) {
        echo "❌ Koneksi database gagal: " . htmlspecialchars($e->getMessage());
        echo "<br><br>Debug Info:<br>";
        echo "DSN: " . htmlspecialchars($dsn) . "<br>";
        echo "Username: " . htmlspecialchars($dbUsername) . "<br>";
        
        // Common error solutions
        if (strpos($e->getMessage(), 'Access denied') !== false) {
            echo "<br>💡 Kemungkinan penyebab:<br>";
            echo "1. Username atau password salah<br>";
            echo "2. User tidak memiliki akses ke database tersebut<br>";
            echo "3. Host tidak diizinkan untuk user tersebut<br>";
        } elseif (strpos($e->getMessage(), 'Unknown database') !== false) {
            echo "<br>💡 Database tidak ditemukan. Pastikan database sudah dibuat.";
        } elseif (strpos($e->getMessage(), 'Connection refused') !== false) {
            echo "<br>💡 Server MySQL tidak dapat diakses. Periksa host dan port.";
        }
    }
}

function handleEnvironmentSetup() {
    // Debug: Log received POST data
    error_log("POST data received: " . print_r($_POST, true));
    
    $appName = trim($_POST['app_name'] ?? 'WSCRM');
    $appUrl = trim($_POST['app_url'] ?? 'http://localhost');
    $dbHost = trim($_POST['db_host'] ?? 'localhost');
    $dbPort = trim($_POST['db_port'] ?? '3306');
    $dbDatabase = trim($_POST['db_database'] ?? 'wscrm_db');
    $dbUsername = trim($_POST['db_username'] ?? '');
    $dbPassword = $_POST['db_password'] ?? ''; // Don't trim password, might have spaces
    $skipPermissions = isset($_POST['skip_permissions']) && $_POST['skip_permissions'] === '1';
    
    // Only validate if user actually submitted the form (not first visit to step 2)
    if (isset($_POST['step']) && $_POST['step'] == 2) {
        // Validate required fields
        if (empty($dbUsername)) {
            showStep(2, 'Error: Database Username tidak boleh kosong!', true);
            return;
        }
        
        if (empty($dbDatabase)) {
            showStep(2, 'Error: Database Name tidak boleh kosong!', true);
            return;
        }
        
        if (empty($dbHost)) {
            showStep(2, 'Error: Database Host tidak boleh kosong!', true);
            return;
        }
    }
    
    $envTemplate = file_get_contents(__DIR__ . '/../.env.example');
    
    $envContent = str_replace([
        'APP_NAME=Laravel',
        'APP_URL=http://localhost',
        'DB_CONNECTION=sqlite',
        'DB_HOST=127.0.0.1',
        'DB_PORT=3306',
        'DB_DATABASE=laravel',
        'DB_USERNAME=root',
        'DB_PASSWORD=',
    ], [
        "APP_NAME=\"$appName\"",
        "APP_URL=$appUrl",
        "DB_CONNECTION=mysql",
        "DB_HOST=$dbHost",
        "DB_PORT=$dbPort",
        "DB_DATABASE=$dbDatabase",
        "DB_USERNAME=$dbUsername",
        "DB_PASSWORD=$dbPassword",
    ], $envTemplate);
    
    // Generate app key
    $appKey = 'base64:' . base64_encode(random_bytes(32));
    $envContent = str_replace('APP_KEY=', "APP_KEY=$appKey", $envContent);
    
    file_put_contents(__DIR__ . '/../.env', $envContent);
    
    // Try to create essential directories if they don't exist
    $essentialDirs = [
        __DIR__ . '/../storage/app/public',
        __DIR__ . '/../storage/framework/cache',
        __DIR__ . '/../storage/framework/sessions',
        __DIR__ . '/../storage/framework/views',
        __DIR__ . '/../storage/logs',
        __DIR__ . '/../bootstrap/cache'
    ];
    
    foreach ($essentialDirs as $dir) {
        if (!file_exists($dir)) {
            @mkdir($dir, 0755, true);
        }
    }
    
    // Only test database connection if form was submitted
    if (isset($_POST['step']) && $_POST['step'] == 2) {
        // Test database connection only after validation passes
        try {
            $dsn = "mysql:host=$dbHost;port=$dbPort;dbname=$dbDatabase;charset=utf8mb4";
            $pdo = new PDO($dsn, $dbUsername, $dbPassword, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            // Connection successful
        } catch (PDOException $e) {
        $errorMsg = 'Error koneksi database: ' . $e->getMessage();
        $errorMsg .= "<br><br><strong>Debug Info:</strong>";
        $errorMsg .= "<br>- Host: " . htmlspecialchars($dbHost);
        $errorMsg .= "<br>- Port: " . htmlspecialchars($dbPort);
        $errorMsg .= "<br>- Database: " . htmlspecialchars($dbDatabase);
        $errorMsg .= "<br>- Username: " . htmlspecialchars($dbUsername);
        $errorMsg .= "<br>- Password: " . (empty($dbPassword) ? '(kosong)' : '***ada***');
        $errorMsg .= "<br>- DSN: " . htmlspecialchars("mysql:host=$dbHost;port=$dbPort;dbname=$dbDatabase;charset=utf8mb4");
        
        // Common error solutions
        if (strpos($e->getMessage(), 'Access denied') !== false) {
            $errorMsg .= "<br><br>💡 <strong>Solusi untuk 'Access denied':</strong>";
            $errorMsg .= "<br>1. Periksa username dan password database";
            $errorMsg .= "<br>2. Pastikan user memiliki akses ke database '$dbDatabase'";
            $errorMsg .= "<br>3. Gunakan tombol 'Test Koneksi' untuk debug lebih lanjut";
            $errorMsg .= "<br>4. Hubungi hosting provider untuk memastikan kredensial benar";
        }
        
        if (strpos($e->getMessage(), 'Unknown database') !== false) {
            $errorMsg .= "<br><br>💡 <strong>Solusi untuk 'Unknown database':</strong>";
            $errorMsg .= "<br>1. Buat database '$dbDatabase' terlebih dahulu di cPanel/hosting panel";
            $errorMsg .= "<br>2. Atau gunakan nama database yang sudah ada";
        }
        
        if (strpos($e->getMessage(), 'Connection refused') !== false || strpos($e->getMessage(), 'Can\'t connect') !== false) {
            $errorMsg .= "<br><br>💡 <strong>Server MySQL tidak dapat diakses:</strong>";
            $errorMsg .= "<br>1. Periksa host dan port (biasanya localhost:3306)";
            $errorMsg .= "<br>2. Pastikan MySQL server berjalan";
        }
        
            showStep(2, $errorMsg, true);
            return;
        }
        
        // If we reach here, connection was successful
        $message = 'Environment berhasil dikonfigurasi';
        if ($skipPermissions) {
            $message .= ' (⚠️ Permissions check dilewati - pastikan untuk memperbaiki permissions manual)';
        }
        showStep(3, $message);
    } else {
        // First visit to step 2, just show the form
        showStep(2);
    }
}

function handleDatabaseSetup() {
    try {
        // Get database configuration from .env
        $env = parseEnvFile(__DIR__ . '/../.env');
        $dbHost = $env['DB_HOST'] ?? 'localhost';
        $dbPort = $env['DB_PORT'] ?? '3306';
        $dbDatabase = $env['DB_DATABASE'] ?? 'wscrm_db';
        $dbUsername = $env['DB_USERNAME'] ?? '';
        $dbPassword = $env['DB_PASSWORD'] ?? '';
        
        // Connect to MySQL database
        $dsn = "mysql:host=$dbHost;port=$dbPort;dbname=$dbDatabase;charset=utf8mb4";
        $pdo = new PDO($dsn, $dbUsername, $dbPassword, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        
        // Run basic migrations manually for essential tables
        runBasicMigrations($pdo);
        
        // Create basic admin data if needed
        createBasicData($pdo);
        
        showStep(4, 'Database berhasil disetup');
    } catch (Exception $e) {
        showStep(3, 'Error saat setup database: ' . $e->getMessage(), true);
    }
}

function runBasicMigrations($pdo) {
    // Create users table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            email_verified_at TIMESTAMP NULL,
            password VARCHAR(255) NOT NULL,
            remember_token VARCHAR(100) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Create password_reset_tokens table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS password_reset_tokens (
            email VARCHAR(255) PRIMARY KEY,
            token VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Create sessions table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS sessions (
            id VARCHAR(255) PRIMARY KEY,
            user_id BIGINT UNSIGNED NULL,
            ip_address VARCHAR(45) NULL,
            user_agent TEXT NULL,
            payload LONGTEXT NOT NULL,
            last_activity INT NOT NULL,
            INDEX sessions_user_id_index (user_id),
            INDEX sessions_last_activity_index (last_activity)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Create cache table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS cache (
            `key` VARCHAR(255) PRIMARY KEY,
            value LONGTEXT NOT NULL,
            expiration INT NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Create cache_locks table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS cache_locks (
            `key` VARCHAR(255) PRIMARY KEY,
            owner VARCHAR(255) NOT NULL,
            expiration INT NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Create jobs table for queue
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS jobs (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            queue VARCHAR(255) NOT NULL,
            payload LONGTEXT NOT NULL,
            attempts TINYINT UNSIGNED NOT NULL,
            reserved_at INT UNSIGNED NULL,
            available_at INT UNSIGNED NOT NULL,
            created_at INT UNSIGNED NOT NULL,
            INDEX jobs_queue_index (queue)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Create failed_jobs table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS failed_jobs (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            uuid VARCHAR(255) NOT NULL UNIQUE,
            connection TEXT NOT NULL,
            queue TEXT NOT NULL,
            payload LONGTEXT NOT NULL,
            exception LONGTEXT NOT NULL,
            failed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
}

function createBasicData($pdo) {
    // Check if users table has any data
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['count'] == 0) {
        // Create a default admin user
        $defaultPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            INSERT INTO users (name, email, email_verified_at, password, created_at, updated_at) 
            VALUES (?, ?, ?, ?, NOW(), NOW())
        ");
        $stmt->execute(['Administrator', 'admin@wscrm.local', date('Y-m-d H:i:s'), $defaultPassword]);
    }
}

function handleFinalSetup() {
    try {
        // Clear cache directories manually
        clearCacheDirectories();
        
        // Create lock file
        file_put_contents(__DIR__ . '/../storage/installer.lock', date('Y-m-d H:i:s'));
        
        // Create admin user if requested or needed
        $adminEmail = $_POST['admin_email'] ?? '';
        $adminPassword = $_POST['admin_password'] ?? '';
        
        // Check if any users exist first
        $env = parseEnvFile(__DIR__ . '/../.env');
        $dbHost = $env['DB_HOST'] ?? 'localhost';
        $dbPort = $env['DB_PORT'] ?? '3306';
        $dbDatabase = $env['DB_DATABASE'] ?? 'wscrm_db';
        $dbUsername = $env['DB_USERNAME'] ?? '';
        $dbPassword = $env['DB_PASSWORD'] ?? '';
        
        $dsn = "mysql:host=$dbHost;port=$dbPort;dbname=$dbDatabase;charset=utf8mb4";
        $pdo = new PDO($dsn, $dbUsername, $dbPassword, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $userCount = $result['count'] ?? 0;
        
        if ($adminEmail && $adminPassword) {
            // Create custom admin user
            createAdminUser($adminEmail, $adminPassword);
        } elseif ($userCount == 0) {
            // Create default admin only if no users exist
            createAdminUser('admin@wscrm.local', 'admin123');
        }
        
        showSuccess();
    } catch (Exception $e) {
        showStep(4, 'Error saat finalisasi: ' . $e->getMessage(), true);
    }
}

function clearCacheDirectories() {
    $cacheDirs = [
        __DIR__ . '/../bootstrap/cache',
        __DIR__ . '/../storage/framework/cache',
        __DIR__ . '/../storage/framework/views',
        __DIR__ . '/../storage/framework/sessions'
    ];
    
    foreach ($cacheDirs as $dir) {
        if (file_exists($dir)) {
            $files = glob($dir . '/*');
            foreach ($files as $file) {
                if (is_file($file) && basename($file) !== '.gitignore') {
                    @unlink($file);
                }
            }
        }
    }
}

function createAdminUser($email, $password) {
    $env = parseEnvFile(__DIR__ . '/../.env');
    $dbHost = $env['DB_HOST'] ?? 'localhost';
    $dbPort = $env['DB_PORT'] ?? '3306';
    $dbDatabase = $env['DB_DATABASE'] ?? 'wscrm_db';
    $dbUsername = $env['DB_USERNAME'] ?? '';
    $dbPassword = $env['DB_PASSWORD'] ?? '';
    
    $dsn = "mysql:host=$dbHost;port=$dbPort;dbname=$dbDatabase;charset=utf8mb4";
    $pdo = new PDO($dsn, $dbUsername, $dbPassword, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    // Check if user already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['count'] == 0) {
        // Create the admin user
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            INSERT INTO users (name, email, email_verified_at, password, created_at, updated_at) 
            VALUES (?, ?, ?, ?, NOW(), NOW())
        ");
        $stmt->execute(['Administrator', $email, date('Y-m-d H:i:s'), $hashedPassword]);
    }
}

function showStep($step, $message = '', $error = false) {
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Instalasi WSCRM</title>
        <style>
            :root { 
                --background: 0 0% 100%; --foreground: 240 10% 3.9%; --card: 0 0% 100%; --card-foreground: 240 10% 3.9%; 
                --popover: 0 0% 100%; --popover-foreground: 240 10% 3.9%; --primary: 240 5.9% 10%; --primary-foreground: 0 0% 98%;
                --secondary: 240 4.8% 95.9%; --secondary-foreground: 240 5.9% 10%; --muted: 240 4.8% 95.9%; --muted-foreground: 240 3.8% 46.1%;
                --accent: 240 4.8% 95.9%; --accent-foreground: 240 5.9% 10%; --destructive: 0 84.2% 60.2%; --destructive-foreground: 0 0% 98%;
                --border: 240 5.9% 90%; --input: 240 5.9% 90%; --ring: 240 5% 64.9%; --radius: 0.5rem; --success: 142.1 76.2% 36.3%;
            }
            * { box-sizing: border-box; }
            body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; margin: 0; padding: 20px; background: hsl(var(--muted)); line-height: 1.5; color: hsl(var(--foreground)); }
            .container { max-width: 640px; margin: 0 auto; background: hsl(var(--card)); padding: 2rem; border-radius: var(--radius); border: 1px solid hsl(var(--border)); box-shadow: 0 1px 3px 0 rgba(0,0,0,0.1), 0 1px 2px 0 rgba(0,0,0,0.06); }
            .logo { text-align: center; margin-bottom: 2rem; }
            .logo h1 { color: hsl(var(--primary)); margin: 0; font-size: 2rem; font-weight: 600; letter-spacing: -0.025em; }
            .logo p { color: hsl(var(--muted-foreground)); margin-top: 0.5rem; font-size: 0.875rem; }
            .step-indicator { display: flex; justify-content: center; margin-bottom: 2rem; gap: 0.5rem; }
            .step { width: 2rem; height: 2rem; border-radius: 50%; background: hsl(var(--muted)); display: flex; align-items: center; justify-content: center; color: hsl(var(--muted-foreground)); font-size: 0.875rem; font-weight: 500; position: relative; }
            .step.active { background: hsl(var(--primary)); color: hsl(var(--primary-foreground)); }
            .step.completed { background: hsl(var(--success)); color: white; }
            .step:not(:last-child)::after { content: ''; position: absolute; left: 100%; top: 50%; width: 0.5rem; height: 1px; background: hsl(var(--border)); transform: translateY(-50%); }
            h2 { font-size: 1.5rem; font-weight: 600; color: hsl(var(--foreground)); margin-bottom: 1rem; }
            .form-group { margin-bottom: 1.5rem; }
            .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 500; color: hsl(var(--foreground)); font-size: 0.875rem; }
            .form-group input, .form-group select { width: 100%; padding: 0.75rem 1rem; border: 1px solid hsl(var(--input)); border-radius: var(--radius); font-size: 0.875rem; background: hsl(var(--background)); color: hsl(var(--foreground)); transition: border-color 0.2s, box-shadow 0.2s; }
            .form-group input:focus, .form-group select:focus { outline: none; border-color: hsl(var(--ring)); box-shadow: 0 0 0 2px hsl(var(--ring) / 0.2); }
            .btn { background: hsl(var(--primary)); color: hsl(var(--primary-foreground)); padding: 0.75rem 1.5rem; border: none; border-radius: var(--radius); cursor: pointer; font-size: 0.875rem; font-weight: 500; width: 100%; transition: background-color 0.2s; }
            .btn:hover { background: hsl(var(--primary) / 0.9); }
            .btn:disabled { opacity: 0.6; cursor: not-allowed; }
            .btn-secondary { background: hsl(var(--secondary)); color: hsl(var(--secondary-foreground)); }
            .btn-secondary:hover { background: hsl(var(--secondary) / 0.8); }
            .btn-destructive { background: hsl(var(--destructive)); color: hsl(var(--destructive-foreground)); }
            .btn-destructive:hover { background: hsl(var(--destructive) / 0.9); }
            .message { padding: 1rem; margin-bottom: 1.5rem; border-radius: var(--radius); border: 1px solid; font-size: 0.875rem; }
            .message.success { background: hsl(142.1 76.2% 36.3% / 0.1); color: hsl(142.1 100% 17%); border-color: hsl(142.1 76.2% 36.3% / 0.3); }
            .message.error { background: hsl(var(--destructive) / 0.1); color: hsl(var(--destructive)); border-color: hsl(var(--destructive) / 0.3); }
            .requirements { background: hsl(var(--muted)); padding: 1.5rem; border-radius: var(--radius); margin-bottom: 1.5rem; border: 1px solid hsl(var(--border)); }
            .req-item { display: flex; align-items: center; margin-bottom: 0.75rem; padding: 0.5rem; border-radius: calc(var(--radius) - 2px); transition: background-color 0.2s; }
            .req-item:last-child { margin-bottom: 0; }
            .req-item:hover { background: hsl(var(--muted) / 0.5); }
            .req-status { margin-right: 0.75rem; width: 1.25rem; height: 1.25rem; display: flex; align-items: center; justify-content: center; border-radius: 50%; font-size: 0.75rem; font-weight: 600; }
            .req-ok { background: hsl(var(--success)); color: white; }
            .req-fail { background: hsl(var(--destructive)); color: white; }
            .req-text { font-size: 0.875rem; }
            .permissions-notice { background: hsl(46 93% 50% / 0.1); border: 1px solid hsl(46 93% 50% / 0.3); color: hsl(46 100% 20%); padding: 1rem; border-radius: var(--radius); margin-bottom: 1rem; font-size: 0.875rem; }
            code { background: hsl(var(--muted)); padding: 0.25rem 0.375rem; border-radius: calc(var(--radius) - 4px); font-size: 0.8rem; font-family: ui-monospace, 'Cascadia Code', 'Source Code Pro', Menlo, Monaco, Consolas, monospace; }
            ol, ul { margin: 0.5rem 0; padding-left: 1.5rem; }
            li { margin-bottom: 0.25rem; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="logo">
                <h1>🚀 WSCRM</h1>
                <p>Web Services Customer Relationship Management</p>
            </div>
            
            <div class="step-indicator">
                <div class="step <?php echo $step >= 1 ? ($step > 1 ? 'completed' : 'active') : ''; ?>">1</div>
                <div class="step <?php echo $step >= 2 ? ($step > 2 ? 'completed' : 'active') : ''; ?>">2</div>
                <div class="step <?php echo $step >= 3 ? ($step > 3 ? 'completed' : 'active') : ''; ?>">3</div>
                <div class="step <?php echo $step >= 4 ? 'active' : ''; ?>">4</div>
            </div>

            <?php if ($message): ?>
                <div class="message <?php echo $error ? 'error' : 'success'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <?php
            switch ($step) {
                case 1:
                    showRequirements();
                    break;
                case 2:
                    showEnvironmentForm();
                    break;
                case 3:
                    showDatabaseForm();
                    break;
                case 4:
                    showFinalForm();
                    break;
            }
            ?>
        </div>
    </body>
    </html>
    <?php
}

function showRequirements() {
    $storageDir = __DIR__ . '/../storage';
    $bootstrapDir = __DIR__ . '/../bootstrap/cache';
    
    // Get detailed directory info
    $storageInfo = [];
    $bootstrapInfo = [];
    
    if (file_exists($storageDir)) {
        $storageInfo = [
            'exists' => true,
            'permissions' => substr(sprintf('%o', fileperms($storageDir)), -4),
            'owner' => function_exists('posix_getpwuid') && function_exists('fileowner') ? 
                      (posix_getpwuid(fileowner($storageDir))['name'] ?? 'unknown') : 'unknown',
            'writable' => is_writable($storageDir),
            'readable' => is_readable($storageDir),
        ];
    }
    
    if (file_exists($bootstrapDir)) {
        $bootstrapInfo = [
            'exists' => true,
            'permissions' => substr(sprintf('%o', fileperms($bootstrapDir)), -4),
            'owner' => function_exists('posix_getpwuid') && function_exists('fileowner') ? 
                      (posix_getpwuid(fileowner($bootstrapDir))['name'] ?? 'unknown') : 'unknown',
            'writable' => is_writable($bootstrapDir),
            'readable' => is_readable($bootstrapDir),
        ];
    }
    
    $requirements = [
        'PHP >= 8.2' => version_compare(PHP_VERSION, '8.2.0', '>='),
        'Extension: PDO' => extension_loaded('pdo'),
        'Extension: SQLite' => extension_loaded('pdo_sqlite'),
        'Extension: OpenSSL' => extension_loaded('openssl'),
        'Extension: Mbstring' => extension_loaded('mbstring'),
        'Extension: Tokenizer' => extension_loaded('tokenizer'),
        'Extension: XML' => extension_loaded('xml'),
        'Extension: Ctype' => extension_loaded('ctype'),
        'Extension: JSON' => extension_loaded('json'),
        'Directory Writable: storage' => is_writable($storageDir),
        'Directory Writable: bootstrap/cache' => is_writable($bootstrapDir),
    ];
    
    // Try to fix writable permissions automatically
    $permissionsFixed = false;
    $manualFixNeeded = [];
    
    if (!is_writable($storageDir)) {
        // Try multiple permission levels
        $success = @chmod($storageDir, 0755) ||
                  @chmod($storageDir, 0775) ||
                  @chmod($storageDir, 0777);
        
        if ($success) {
            // Also fix subdirectories
            @chmod($storageDir . '/app', 0755);
            @chmod($storageDir . '/framework', 0755);
            @chmod($storageDir . '/framework/cache', 0755);
            @chmod($storageDir . '/framework/sessions', 0755);
            @chmod($storageDir . '/framework/views', 0755);
            @chmod($storageDir . '/logs', 0755);
        }
        
        // Test actual write capability
        $testFile = $storageDir . '/write-test-' . time() . '.tmp';
        $canWrite = @file_put_contents($testFile, 'test') !== false;
        if ($canWrite) {
            @unlink($testFile);
            $requirements['Directory Writable: storage'] = true;
            $permissionsFixed = true;
        } else {
            $requirements['Directory Writable: storage'] = false;
            $manualFixNeeded[] = 'storage/';
        }
    }
    
    if (!is_writable($bootstrapDir)) {
        $success = @chmod($bootstrapDir, 0755) ||
                  @chmod($bootstrapDir, 0775) ||
                  @chmod($bootstrapDir, 0777);
        
        // Test actual write capability
        $testFile = $bootstrapDir . '/write-test-' . time() . '.tmp';
        $canWrite = @file_put_contents($testFile, 'test') !== false;
        if ($canWrite) {
            @unlink($testFile);
            $requirements['Directory Writable: bootstrap/cache'] = true;
            $permissionsFixed = true;
        } else {
            $requirements['Directory Writable: bootstrap/cache'] = false;
            $manualFixNeeded[] = 'bootstrap/cache/';
        }
    }
    
    $allOk = true;
    foreach ($requirements as $req => $status) {
        if (!$status) $allOk = false;
    }
    ?>
    
    <h2>Pemeriksaan Sistem</h2>
    
    <?php if ($permissionsFixed): ?>
        <div class="permissions-notice">
            <strong>✨ Permissions telah diperbaiki otomatis!</strong> Beberapa direktori telah disesuaikan permission-nya.
        </div>
    <?php endif; ?>
    
    <?php if (!empty($manualFixNeeded)): ?>
        <div class="message error">
            <strong>🔧 Perbaikan Manual Diperlukan</strong>
            <p>Sistem tidak dapat memperbaiki permissions secara otomatis. Detail diagnostik:</p>
            
            <div style="background: hsl(var(--muted)); padding: 1rem; border-radius: calc(var(--radius) - 2px); margin: 1rem 0; font-family: monospace; font-size: 0.75rem; overflow-x: auto;">
                <?php if (in_array('storage/', $manualFixNeeded) && !empty($storageInfo)): ?>
                    <strong>📁 storage/ directory:</strong><br>
                    - Permissions: <?php echo $storageInfo['permissions']; ?><br>
                    - Owner: <?php echo $storageInfo['owner']; ?><br>
                    - PHP User: <?php echo function_exists('posix_getpwuid') ? (posix_getpwuid(posix_geteuid())['name'] ?? 'unknown') : 'unknown'; ?><br>
                    - Writable: <?php echo $storageInfo['writable'] ? 'Yes' : 'No'; ?><br><br>
                <?php endif; ?>
                
                <?php if (in_array('bootstrap/cache/', $manualFixNeeded) && !empty($bootstrapInfo)): ?>
                    <strong>📁 bootstrap/cache/ directory:</strong><br>
                    - Permissions: <?php echo $bootstrapInfo['permissions']; ?><br>
                    - Owner: <?php echo $bootstrapInfo['owner']; ?><br>
                    - PHP User: <?php echo function_exists('posix_getpwuid') ? (posix_getpwuid(posix_geteuid())['name'] ?? 'unknown') : 'unknown'; ?><br>
                    - Writable: <?php echo $bootstrapInfo['writable'] ? 'Yes' : 'No'; ?><br><br>
                <?php endif; ?>
            </div>
            
            <p><strong>📝 Solusi Manual:</strong></p>
            <div style="background: hsl(var(--muted)); padding: 1rem; border-radius: calc(var(--radius) - 2px); margin: 1rem 0; font-family: monospace; font-size: 0.8rem; overflow-x: auto;">
                <?php foreach ($manualFixNeeded as $dir): ?>
                    <div style="margin-bottom: 0.5rem;">
                        <strong>Via SSH/Terminal:</strong><br>
                        <code>chmod -R 775 <?php echo $dir; ?></code><br>
                        <code>chown -R www-data:www-data <?php echo $dir; ?></code><br>
                        <small style="color: hsl(var(--muted-foreground));">atau gunakan user yang sesuai dengan server Anda</small>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <p><strong>Via cPanel File Manager:</strong></p>
            <ol style="text-align: left; font-size: 0.875rem;">
                <li>Buka File Manager di cPanel</li>
                <li>Klik kanan pada folder <code><?php echo implode('</code> dan <code>', $manualFixNeeded); ?></code></li>
                <li>Pilih "Change Permissions"</li>
                <li>Set permissions ke <strong>775</strong> (atau 777 jika masih error)</li>
                <li>Centang "Recurse into subdirectories"</li>
                <li>Klik "Change Permissions"</li>
            </ol>
            
            <p><strong>⚠️ Kemungkinan Penyebab:</strong></p>
            <ul style="text-align: left; font-size: 0.875rem;">
                <li>Owner file berbeda dengan user PHP</li>
                <li>SELinux atau security module aktif</li>
                <li>Hosting memblokir operasi chmod via PHP</li>
                <li>Directory tidak ada atau path salah</li>
            </ul>
        </div>
    <?php endif; ?>
    
    <div class="requirements">
        <?php foreach ($requirements as $req => $status): ?>
            <div class="req-item">
                <span class="req-status <?php echo $status ? 'req-ok' : 'req-fail'; ?>">
                    <?php echo $status ? '✓' : '✗'; ?>
                </span>
                <span class="req-text"><?php echo $req; ?></span>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ($allOk): ?>
        <form method="POST">
            <input type="hidden" name="step" value="2">
            <button type="submit" class="btn">Lanjutkan Instalasi</button>
        </form>
    <?php else: ?>
        <div class="message error">
            <strong>Persyaratan belum terpenuhi!</strong> Harap perbaiki masalah di atas sebelum melanjutkan instalasi.
            <br><br>
            <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                <button type="button" onclick="window.location.reload()" class="btn btn-secondary" style="flex: 1;">🔄 Periksa Ulang</button>
                
                <?php 
                // Check if only permission issues remain
                $onlyPermissionIssues = true;
                foreach ($requirements as $req => $status) {
                    if (!$status && !str_contains($req, 'Directory Writable')) {
                        $onlyPermissionIssues = false;
                        break;
                    }
                }
                ?>
                
                <?php if ($onlyPermissionIssues): ?>
                    <form method="POST" style="flex: 1;">
                        <input type="hidden" name="step" value="2">
                        <input type="hidden" name="skip_permissions" value="1">
                        <button type="submit" class="btn btn-destructive" onclick="return confirm('⚠️ PERINGATAN: Anda akan melanjutkan tanpa permissions yang tepat.\n\nIni mungkin menyebabkan:\n- Error saat menyimpan file\n- Gagal generate cache\n- Masalah log aplikasi\n\nYakin tetap lanjutkan?')" style="width: 100%;">⚠️ Lewati & Lanjutkan</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php endif;
}

function showEnvironmentForm() {
    // Preserve values from previous POST if available
    $appName = $_POST['app_name'] ?? 'WSCRM';
    $appUrl = $_POST['app_url'] ?? ('http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['REQUEST_URI'])));
    $dbHost = $_POST['db_host'] ?? 'localhost';
    $dbPort = $_POST['db_port'] ?? '3306';
    $dbDatabase = $_POST['db_database'] ?? '';
    $dbUsername = $_POST['db_username'] ?? '';
    $dbPassword = $_POST['db_password'] ?? '';
    ?>
    <h2>Konfigurasi Aplikasi</h2>
    
    <form method="POST">
        <input type="hidden" name="step" value="2">
        
        <div class="form-group">
            <label for="app_name">Nama Aplikasi</label>
            <input type="text" id="app_name" name="app_name" value="<?php echo htmlspecialchars($appName); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="app_url">URL Aplikasi</label>
            <input type="url" id="app_url" name="app_url" value="<?php echo htmlspecialchars($appUrl); ?>" required>
        </div>
        
        <h3 style="margin-top: 2rem; margin-bottom: 1rem;">Database Configuration</h3>
        
        <div class="form-group">
            <label for="db_host">Database Host</label>
            <input type="text" id="db_host" name="db_host" value="<?php echo htmlspecialchars($dbHost); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="db_port">Database Port</label>
            <input type="number" id="db_port" name="db_port" value="<?php echo htmlspecialchars($dbPort); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="db_database">Database Name</label>
            <input type="text" id="db_database" name="db_database" value="<?php echo htmlspecialchars($dbDatabase); ?>" placeholder="wscrm_db" required>
        </div>
        
        <div class="form-group">
            <label for="db_username">Database Username</label>
            <input type="text" id="db_username" name="db_username" value="<?php echo htmlspecialchars($dbUsername); ?>" placeholder="Database username" required>
        </div>
        
        <div class="form-group">
            <label for="db_password">Database Password</label>
            <input type="password" id="db_password" name="db_password" value="<?php echo htmlspecialchars($dbPassword); ?>" placeholder="Database password">
        </div>
        
        <div style="display: flex; gap: 0.5rem; margin-bottom: 1rem;">
            <button type="button" onclick="testConnection()" class="btn btn-secondary" style="flex: 1;">🔍 Test Koneksi</button>
            <button type="submit" class="btn" style="flex: 1;">Lanjutkan</button>
        </div>
    </form>

    <script>
    function testConnection() {
        const formData = new FormData();
        formData.append('test_connection', '1');
        formData.append('db_host', document.getElementById('db_host').value);
        formData.append('db_port', document.getElementById('db_port').value);
        formData.append('db_database', document.getElementById('db_database').value);
        formData.append('db_username', document.getElementById('db_username').value);
        formData.append('db_password', document.getElementById('db_password').value);
        
        const button = event.target;
        const originalText = button.innerHTML;
        button.innerHTML = '⏳ Testing...';
        button.disabled = true;
        
        fetch('', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            if (data.includes('Koneksi berhasil')) {
                alert('✅ Koneksi database berhasil!');
            } else {
                alert('❌ Koneksi gagal. Lihat pesan error di halaman.');
                location.reload();
            }
        })
        .catch(error => {
            alert('❌ Error testing connection: ' + error);
        })
        .finally(() => {
            button.innerHTML = originalText;
            button.disabled = false;
        });
    }
    </script>
    <?php
}

function showDatabaseForm() {
    ?>
    <h2>Setup Database</h2>
    
    <p>Aplikasi akan melakukan migrasi database dan setup data awal.</p>
    
    <form method="POST">
        <input type="hidden" name="step" value="3">
        <button type="submit" class="btn">Setup Database</button>
    </form>
    <?php
}

function showFinalForm() {
    // Check if admin users already exist
    $hasAdmin = false;
    $adminCount = 0;
    
    try {
        $env = parseEnvFile(__DIR__ . '/../.env');
        $dbHost = $env['DB_HOST'] ?? 'localhost';
        $dbPort = $env['DB_PORT'] ?? '3306';
        $dbDatabase = $env['DB_DATABASE'] ?? 'wscrm_db';
        $dbUsername = $env['DB_USERNAME'] ?? '';
        $dbPassword = $env['DB_PASSWORD'] ?? '';
        
        $dsn = "mysql:host=$dbHost;port=$dbPort;dbname=$dbDatabase;charset=utf8mb4";
        $pdo = new PDO($dsn, $dbUsername, $dbPassword, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $adminCount = $result['count'] ?? 0;
        $hasAdmin = $adminCount > 0;
    } catch (Exception $e) {
        // If we can't check, assume no admin exists
    }
    
    ?>
    <h2>Finalisasi</h2>
    
    <?php if ($hasAdmin): ?>
        <div class="message success">
            <strong>✅ Admin Account Detected</strong><br>
            Ditemukan <?php echo $adminCount; ?> user admin yang sudah ada di database.<br>
            Anda dapat langsung menyelesaikan instalasi tanpa membuat admin baru.
        </div>
        
        <p>Atau buat akun administrator tambahan (opsional):</p>
    <?php else: ?>
        <div class="message" style="background: hsl(46 93% 50% / 0.1); border: 1px solid hsl(46 93% 50% / 0.3); color: hsl(46 100% 20%);">
            <strong>ℹ️ Admin Account Required</strong><br>
            Tidak ditemukan user admin di database. Sistem akan membuat akun admin default atau Anda dapat membuat custom admin.
        </div>
        
        <div class="message" style="background: hsl(142.1 76.2% 36.3% / 0.1); color: hsl(142.1 100% 17%); border-color: hsl(142.1 76.2% 36.3% / 0.3);">
            <strong>🔐 Default Admin Credentials</strong><br>
            Jika tidak mengisi form di bawah, akan dibuat akun default:<br>
            <strong>Email:</strong> admin@wscrm.local<br>
            <strong>Password:</strong> admin123<br>
            <small>Silakan ganti password setelah login pertama kali.</small>
        </div>
        
        <p>Atau buat akun administrator custom:</p>
    <?php endif; ?>
    
    <form method="POST">
        <input type="hidden" name="step" value="4">
        
        <div class="form-group">
            <label for="admin_email">Email Admin <?php echo $hasAdmin ? '(Tambahan - Opsional)' : '(Opsional)'; ?></label>
            <input type="email" id="admin_email" name="admin_email" placeholder="admin@example.com">
        </div>
        
        <div class="form-group">
            <label for="admin_password">Password Admin <?php echo $hasAdmin ? '(Tambahan - Opsional)' : '(Opsional)'; ?></label>
            <input type="password" id="admin_password" name="admin_password" placeholder="Minimal 8 karakter">
        </div>
        
        <button type="submit" class="btn">
            <?php echo $hasAdmin ? 'Selesaikan Instalasi' : 'Selesaikan Instalasi'; ?>
        </button>
    </form>
    
    <?php if ($hasAdmin): ?>
        <div style="text-align: center; margin-top: 1rem;">
            <small style="color: hsl(var(--muted-foreground));">
                💡 Tip: Anda dapat langsung klik "Selesaikan Instalasi" tanpa mengisi form admin tambahan
            </small>
        </div>
    <?php endif; ?>
    
    <?php
}

function showSuccess() {
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Instalasi Berhasil - WSCRM</title>
        <style>
            :root { 
                --background: 0 0% 100%; --foreground: 240 10% 3.9%; --card: 0 0% 100%; --card-foreground: 240 10% 3.9%; 
                --popover: 0 0% 100%; --popover-foreground: 240 10% 3.9%; --primary: 240 5.9% 10%; --primary-foreground: 0 0% 98%;
                --secondary: 240 4.8% 95.9%; --secondary-foreground: 240 5.9% 10%; --muted: 240 4.8% 95.9%; --muted-foreground: 240 3.8% 46.1%;
                --accent: 240 4.8% 95.9%; --accent-foreground: 240 5.9% 10%; --destructive: 0 84.2% 60.2%; --destructive-foreground: 0 0% 98%;
                --border: 240 5.9% 90%; --input: 240 5.9% 90%; --ring: 240 5% 64.9%; --radius: 0.5rem; --success: 142.1 76.2% 36.3%;
            }
            * { box-sizing: border-box; }
            body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; margin: 0; padding: 20px; background: hsl(var(--muted)); line-height: 1.5; color: hsl(var(--foreground)); }
            .container { max-width: 640px; margin: 0 auto; background: hsl(var(--card)); padding: 3rem; border-radius: var(--radius); border: 1px solid hsl(var(--border)); box-shadow: 0 1px 3px 0 rgba(0,0,0,0.1), 0 1px 2px 0 rgba(0,0,0,0.06); text-align: center; }
            .success-icon { font-size: 4rem; margin-bottom: 1.5rem; }
            h1 { color: hsl(var(--success)); margin-bottom: 1rem; font-size: 2rem; font-weight: 600; }
            p { color: hsl(var(--muted-foreground)); margin-bottom: 1.5rem; }
            .btn { background: hsl(var(--primary)); color: hsl(var(--primary-foreground)); padding: 0.75rem 1.5rem; border: none; border-radius: var(--radius); cursor: pointer; font-size: 0.875rem; font-weight: 500; text-decoration: none; display: inline-block; margin: 0.5rem; transition: background-color 0.2s; }
            .btn:hover { background: hsl(var(--primary) / 0.9); }
            .btn-secondary { background: hsl(var(--secondary)); color: hsl(var(--secondary-foreground)); }
            .btn-secondary:hover { background: hsl(var(--secondary) / 0.8); }
            .btn-destructive { background: hsl(var(--destructive)); color: hsl(var(--destructive-foreground)); }
            .btn-destructive:hover { background: hsl(var(--destructive) / 0.9); }
            small { color: hsl(var(--muted-foreground)); font-size: 0.75rem; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="success-icon">🎉</div>
            <h1>Instalasi Berhasil!</h1>
            <p>WSCRM telah berhasil diinstall dan siap digunakan.</p>
            
            <p>
                <a href="/" class="btn">🚀 Akses Aplikasi</a>
                <a href="#" onclick="deleteInstaller()" class="btn btn-destructive">🗑️ Hapus Installer</a>
            </p>
            
            <p><small>Untuk keamanan, disarankan untuk menghapus folder installer setelah instalasi selesai.</small></p>
        </div>
        
        <script>
        function deleteInstaller() {
            if (confirm('Hapus folder installer? Tindakan ini tidak dapat dibatalkan.')) {
                fetch('/install/cleanup.php', {method: 'POST'})
                .then(() => {
                    alert('Installer berhasil dihapus.');
                    window.location.href = '/';
                });
            }
        }
        </script>
    </body>
    </html>
    <?php
}

?>