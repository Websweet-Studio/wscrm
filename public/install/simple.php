<!DOCTYPE html>
<html>
<head>
    <title>Laravel Simple Installer</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .container { max-width: 800px; margin: 0 auto; }
        .step { background: #f8f9fa; padding: 20px; margin: 20px 0; border-radius: 5px; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .warning { color: #ffc107; }
        input[type="text"], input[type="password"] { width: 300px; padding: 8px; margin: 5px 0; }
        button { padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 3px; }
        button:hover { background: #0056b3; }
        pre { background: #f1f1f1; padding: 10px; overflow: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Laravel Simple Installer</h1>
        
        <?php
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
        // Basic environment check
        echo "<div class='step'>";
        echo "<h2>Environment Check</h2>";
        echo "PHP Version: " . PHP_VERSION . "<br>";
        echo "Current Directory: " . __DIR__ . "<br>";
        echo "Parent Directory: " . dirname(__DIR__) . "<br>";
        
        // Check if Laravel files exist
        $laravelFiles = [
            '../index.php' => 'Laravel Entry Point',
            '../.env.example' => 'Environment Example', 
            '../artisan' => 'Artisan Console',
            '../storage' => 'Storage Directory',
            '../bootstrap/cache' => 'Bootstrap Cache'
        ];
        
        echo "<h3>Laravel Files:</h3>";
        foreach ($laravelFiles as $file => $desc) {
            $path = __DIR__ . '/' . $file;
            $exists = file_exists($path);
            $status = $exists ? "<span class='success'>✅ Found</span>" : "<span class='error'>❌ Missing</span>";
            echo "$desc: $status<br>";
        }
        echo "</div>";
        
        // Simple database form
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['setup_db'])) {
            echo "<div class='step'>";
            echo "<h2>Database Setup Result</h2>";
            
            $host = $_POST['db_host'] ?? 'localhost';
            $port = $_POST['db_port'] ?? '3306';
            $database = $_POST['db_database'] ?? '';
            $username = $_POST['db_username'] ?? '';
            $password = $_POST['db_password'] ?? '';
            
            try {
                $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";
                $pdo = new PDO($dsn, $username, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
                
                echo "<div class='success'>✅ Database connection successful!</div>";
                
                // Try to create .env file
                $envTemplate = __DIR__ . '/../.env.example';
                if (file_exists($envTemplate)) {
                    $envContent = file_get_contents($envTemplate);
                    
                    // Simple replacement
                    $envContent = str_replace('DB_HOST=127.0.0.1', "DB_HOST=$host", $envContent);
                    $envContent = str_replace('DB_PORT=3306', "DB_PORT=$port", $envContent);
                    $envContent = str_replace('DB_DATABASE=laravel', "DB_DATABASE=$database", $envContent);
                    $envContent = str_replace('DB_USERNAME=root', "DB_USERNAME=$username", $envContent);
                    $envContent = str_replace('DB_PASSWORD=', "DB_PASSWORD=$password", $envContent);
                    
                    // Also handle commented versions
                    $envContent = str_replace('# DB_HOST=127.0.0.1', "DB_HOST=$host", $envContent);
                    $envContent = str_replace('# DB_PORT=3306', "DB_PORT=$port", $envContent);
                    $envContent = str_replace('# DB_DATABASE=laravel', "DB_DATABASE=$database", $envContent);
                    $envContent = str_replace('# DB_USERNAME=root', "DB_USERNAME=$username", $envContent);
                    $envContent = str_replace('# DB_PASSWORD=', "DB_PASSWORD=$password", $envContent);
                    
                    $envPath = __DIR__ . '/../.env';
                    if (file_put_contents($envPath, $envContent)) {
                        echo "<div class='success'>✅ .env file created successfully!</div>";
                        echo "<p><strong>Installation Complete!</strong></p>";
                        echo "<p><a href='../'>Visit Your Site</a></p>";
                    } else {
                        echo "<div class='error'>❌ Could not create .env file. Check permissions.</div>";
                    }
                } else {
                    echo "<div class='error'>❌ .env.example not found</div>";
                }
                
            } catch (PDOException $e) {
                echo "<div class='error'>❌ Database connection failed: " . $e->getMessage() . "</div>";
            }
            echo "</div>";
        } else {
            // Show form
            echo "<div class='step'>";
            echo "<h2>Database Configuration</h2>";
            echo "<form method='POST'>";
            echo "<table>";
            echo "<tr><td>Host:</td><td><input type='text' name='db_host' value='localhost' required></td></tr>";
            echo "<tr><td>Port:</td><td><input type='text' name='db_port' value='3306' required></td></tr>";
            echo "<tr><td>Database:</td><td><input type='text' name='db_database' placeholder='database_name' required></td></tr>";
            echo "<tr><td>Username:</td><td><input type='text' name='db_username' placeholder='db_user' required></td></tr>";
            echo "<tr><td>Password:</td><td><input type='password' name='db_password' placeholder='db_password'></td></tr>";
            echo "</table>";
            echo "<br><button type='submit' name='setup_db'>Setup Database</button>";
            echo "</form>";
            echo "</div>";
        }
        ?>
        
        <div class="step">
            <h3>Troubleshooting</h3>
            <p><a href="test.php">Basic PHP Test</a></p>
            <p><a href="debug.php">Advanced Debug</a></p>
            <p><a href="index.php">Original Installer</a></p>
        </div>
    </div>
</body>
</html>