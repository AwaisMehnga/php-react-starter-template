<?php

/**
 * Database Setup Script
 * Run this file once to set up your database
 */

echo "🚀 Setting up Tool Site Database...\n\n";

// Database configuration
$config = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => '', // Default XAMPP password
    'database' => 'tool_site_db'
];

try {
    // Connect to MySQL server (without selecting database)
    $pdo = new PDO("mysql:host={$config['host']}", $config['username'], $config['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connected to MySQL server\n";
    
    // Read and execute SQL file
    $sqlFile = __DIR__ . '/database/tool_site_db.sql';
    
    if (!file_exists($sqlFile)) {
        throw new Exception("SQL file not found: {$sqlFile}");
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Split SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (!empty($statement) && !preg_match('/^\s*--/', $statement)) {
            try {
                $pdo->exec($statement);
            } catch (PDOException $e) {
                // Skip errors for statements that might already exist
                if (strpos($e->getMessage(), 'already exists') === false) {
                    echo "Warning: " . $e->getMessage() . "\n";
                }
            }
        }
    }
    
    echo "✅ Database and tables created successfully\n";
    echo "✅ Sample data inserted\n\n";
    
    echo "🎉 Database setup complete!\n\n";
    echo "📊 Database Details:\n";
    echo "   - Database: {$config['database']}\n";
    echo "   - Host: {$config['host']}\n";
    echo "   - Tables: users, sessions, posts, categories, tools, settings\n\n";
    
    echo "👤 Sample Users Created:\n";
    echo "   - admin@toolsite.com (Admin) - Password: password\n";
    echo "   - john@example.com (User) - Password: password\n";
    echo "   - jane@example.com (Moderator) - Password: password\n";
    echo "   - bob@example.com (User) - Password: password\n";
    echo "   - alice@example.com (User) - Password: password\n\n";
    
    echo "🔧 Next Steps:\n";
    echo "   1. Update composer autoloader: composer dump-autoload\n";
    echo "   2. Start your XAMPP server\n";
    echo "   3. Visit your site to test the MVC system\n\n";
    
    echo "🌐 Test URLs:\n";
    echo "   - http://localhost/Tool-site/users (All users)\n";
    echo "   - http://localhost/Tool-site/users/1 (User profile)\n";
    echo "   - http://localhost/Tool-site/admin/dashboard (Admin area)\n";
    echo "   - http://localhost/Tool-site/api/users/1 (JSON API)\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\n💡 Make sure:\n";
    echo "   - XAMPP is running\n";
    echo "   - MySQL service is started\n";
    echo "   - Database credentials are correct\n";
}
