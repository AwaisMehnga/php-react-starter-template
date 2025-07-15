<?php

// Create database setup script
$host = 'localhost';
$username = 'root';
$password = ''; // Default XAMPP password is empty
$dbname = 'billo-craft';

try {
    // Connect to MySQL server (without selecting a database)
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if it doesn't exist
    $sql = "CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $pdo->exec($sql);
    
    echo "✅ Database '$dbname' created successfully (or already exists)!\n";
    echo "You can now run: php setup-auth.php\n";
    
} catch (PDOException $e) {
    echo "❌ Error creating database: " . $e->getMessage() . "\n";
    echo "Please make sure:\n";
    echo "1. XAMPP MySQL service is running\n";
    echo "2. MySQL credentials in config/database.php are correct\n";
    exit(1);
}
