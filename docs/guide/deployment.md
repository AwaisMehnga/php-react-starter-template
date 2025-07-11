---
layout: default
title: Deployment
nav_order: 9
---

# Deployment
{: .no_toc }

Deploy your MVC application to production with proper configuration and optimization.
{: .fs-6 .fw-300 }

## Table of contents
{: .no_toc .text-delta }

1. TOC
{:toc}

---

## Overview

This guide covers deploying your Laravel-style MVC application to various hosting environments with proper security, performance optimization, and monitoring.

---

## Environment Configuration

### Environment Files

Create environment-specific configuration files:

```bash
# .env.production
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourapp.com

DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_PORT=3306
DB_DATABASE=your_production_db
DB_USERNAME=your_db_user
DB_PASSWORD=your_secure_password

CACHE_DRIVER=redis
SESSION_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

MAIL_DRIVER=smtp
MAIL_HOST=smtp.yourprovider.com
MAIL_PORT=587
MAIL_USERNAME=your_email
MAIL_PASSWORD=your_email_password

LOG_LEVEL=error
```

### Configuration Loader

```php
<?php
// config/app.php

function loadEnvironment($envFile = '.env')
{
    if (!file_exists($envFile)) {
        return;
    }
    
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) {
            continue; // Skip comments
        }
        
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, '"\'');
            
            if (!array_key_exists($key, $_ENV)) {
                $_ENV[$key] = $value;
            }
        }
    }
}

// Load environment
$envFile = file_exists('.env.production') ? '.env.production' : '.env';
loadEnvironment($envFile);

return [
    'name' => $_ENV['APP_NAME'] ?? 'MVC App',
    'env' => $_ENV['APP_ENV'] ?? 'production',
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
    'url' => $_ENV['APP_URL'] ?? 'http://localhost',
    'timezone' => $_ENV['APP_TIMEZONE'] ?? 'UTC',
    'locale' => $_ENV['APP_LOCALE'] ?? 'en',
];
```

---

## Server Configuration

### Apache Configuration

Create `.htaccess` file in your web root:

```apache
# .htaccess
RewriteEngine On

# Handle Frontend Routes (React SPAs)
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_URI} !^/api/
RewriteRule ^(.*)$ /index.php [QSA,L]

# Security Headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
Header always set Referrer-Policy "strict-origin-when-cross-origin"

# Compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript application/json
</IfModule>

# Browser Caching
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType image/jpg "access plus 1 month"
    ExpiresByType image/jpeg "access plus 1 month"
    ExpiresByType image/gif "access plus 1 month"
    ExpiresByType image/webp "access plus 1 month"
</IfModule>

# Deny access to sensitive files
<Files "composer.json">
    Require all denied
</Files>
<Files "composer.lock">
    Require all denied
</Files>
<Files ".env*">
    Require all denied
</Files>
<FilesMatch "\.log$">
    Require all denied
</FilesMatch>
```

### Nginx Configuration

```nginx
# /etc/nginx/sites-available/yourapp
server {
    listen 80;
    server_name yourapp.com www.yourapp.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name yourapp.com www.yourapp.com;
    
    root /var/www/yourapp/public;
    index index.php;

    # SSL Configuration
    ssl_certificate /path/to/your/certificate.crt;
    ssl_certificate_key /path/to/your/private.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    # Gzip Compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/javascript application/xml+rss application/json;

    # Handle SPA routes and API routes
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # API routes
    location /api {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP processing
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        
        # Security
        fastcgi_param HTTP_PROXY "";
        fastcgi_hide_header X-Powered-By;
    }

    # Static assets caching
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|webp|woff|woff2|ttf|eot)$ {
        expires 1M;
        add_header Cache-Control "public, immutable";
        access_log off;
    }

    # Deny access to sensitive files
    location ~ /\. {
        deny all;
    }
    
    location ~ /(composer\.(json|lock)|\.env.*|.*\.log)$ {
        deny all;
    }
}
```

---

## Database Migration for Production

### Migration Script

```php
<?php
// scripts/deploy.php

class ProductionDeployer
{
    private $db;
    private $config;

    public function __construct()
    {
        $this->config = require 'config/database.php';
        $this->connectDatabase();
    }

    private function connectDatabase()
    {
        $config = $this->config['connections'][$this->config['default']];
        
        $dsn = "mysql:host={$config['host']};port={$config['port']};charset={$config['charset']}";
        $this->db = new PDO($dsn, $config['username'], $config['password'], $config['options']);
    }

    public function deploy()
    {
        echo "Starting deployment...\n";
        
        $this->createDatabaseIfNotExists();
        $this->runMigrations();
        $this->seedDatabase();
        $this->optimizeApplication();
        $this->clearCache();
        
        echo "Deployment completed successfully!\n";
    }

    private function createDatabaseIfNotExists()
    {
        $dbName = $this->config['connections'][$this->config['default']]['database'];
        
        echo "Creating database if not exists: $dbName\n";
        
        $this->db->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $this->db->exec("USE `$dbName`");
    }

    private function runMigrations()
    {
        echo "Running migrations...\n";
        
        $migrationFiles = glob('database/migrations/*.sql');
        sort($migrationFiles);
        
        foreach ($migrationFiles as $file) {
            echo "Running migration: " . basename($file) . "\n";
            
            $sql = file_get_contents($file);
            $this->db->exec($sql);
        }
    }

    private function seedDatabase()
    {
        echo "Seeding database...\n";
        
        $seedFiles = glob('database/seeds/*.sql');
        sort($seedFiles);
        
        foreach ($seedFiles as $file) {
            echo "Running seed: " . basename($file) . "\n";
            
            $sql = file_get_contents($file);
            $this->db->exec($sql);
        }
    }

    private function optimizeApplication()
    {
        echo "Optimizing application...\n";
        
        // Build production assets
        $this->runCommand('npm run build');
        
        // Optimize composer autoloader
        $this->runCommand('composer install --no-dev --optimize-autoloader');
        
        // Generate optimized configuration cache
        $this->generateConfigCache();
    }

    private function clearCache()
    {
        echo "Clearing caches...\n";
        
        $cacheDirectories = ['cache/views/', 'cache/routes/', 'cache/config/'];
        
        foreach ($cacheDirectories as $dir) {
            if (is_dir($dir)) {
                $this->deleteDirectory($dir);
                mkdir($dir, 0755, true);
            }
        }
    }

    private function runCommand($command)
    {
        echo "Running: $command\n";
        $output = shell_exec($command . ' 2>&1');
        echo $output . "\n";
    }

    private function generateConfigCache()
    {
        $config = [
            'app' => require 'config/app.php',
            'database' => require 'config/database.php',
        ];
        
        if (!is_dir('cache/config')) {
            mkdir('cache/config', 0755, true);
        }
        
        file_put_contents('cache/config/config.php', '<?php return ' . var_export($config, true) . ';');
    }

    private function deleteDirectory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }
        
        rmdir($dir);
    }
}

// Run deployment
if (php_sapi_name() === 'cli') {
    $deployer = new ProductionDeployer();
    $deployer->deploy();
}
```

---

## Performance Optimization

### Application Optimization

```php
<?php
// config/optimization.php

class ApplicationOptimizer
{
    public static function optimize()
    {
        // Enable OPcache
        if (function_exists('opcache_compile_file')) {
            self::compileFiles();
        }
        
        // Optimize session handling
        self::optimizeSession();
        
        // Setup caching
        self::setupCaching();
    }

    private static function compileFiles()
    {
        $files = array_merge(
            glob('app/**/*.php'),
            glob('config/*.php'),
            ['vendor/autoload.php']
        );
        
        foreach ($files as $file) {
            if (is_file($file)) {
                opcache_compile_file($file);
            }
        }
    }

    private static function optimizeSession()
    {
        ini_set('session.save_handler', 'redis');
        ini_set('session.save_path', 'tcp://127.0.0.1:6379');
        ini_set('session.gc_maxlifetime', 3600);
        ini_set('session.cookie_lifetime', 0);
        ini_set('session.cookie_secure', 1);
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_strict_mode', 1);
    }

    private static function setupCaching()
    {
        // Setup Redis for caching
        if (class_exists('Redis')) {
            $redis = new Redis();
            $redis->connect('127.0.0.1', 6379);
            
            // Store Redis instance globally
            $GLOBALS['redis'] = $redis;
        }
    }
}

// Run optimization in production
if ($_ENV['APP_ENV'] === 'production') {
    ApplicationOptimizer::optimize();
}
```

### Database Optimization

```sql
-- database/optimization.sql

-- Add indexes for common queries
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_posts_user_id ON posts(user_id);
CREATE INDEX idx_posts_created_at ON posts(created_at);
CREATE INDEX idx_sessions_user_id ON sessions(user_id);

-- Optimize table structures
ALTER TABLE users ENGINE=InnoDB;
ALTER TABLE posts ENGINE=InnoDB;

-- Setup database connection pooling
SET GLOBAL max_connections = 200;
SET GLOBAL innodb_buffer_pool_size = 1073741824; -- 1GB
SET GLOBAL query_cache_size = 268435456; -- 256MB
SET GLOBAL query_cache_type = 1;
```

---

## Monitoring and Logging

### Error Monitoring

```php
<?php
// app/Core/ErrorHandler.php

namespace App\Core;

class ErrorHandler
{
    private static $logFile = 'logs/errors.log';

    public static function register()
    {
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    public static function handleError($severity, $message, $file, $line)
    {
        if (!(error_reporting() & $severity)) {
            return false;
        }

        $error = [
            'type' => 'error',
            'severity' => $severity,
            'message' => $message,
            'file' => $file,
            'line' => $line,
            'timestamp' => date('Y-m-d H:i:s'),
            'url' => $_SERVER['REQUEST_URI'] ?? '',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ];

        self::logError($error);

        if ($_ENV['APP_ENV'] === 'production') {
            self::notifyAdmins($error);
        }

        return true;
    }

    public static function handleException($exception)
    {
        $error = [
            'type' => 'exception',
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'timestamp' => date('Y-m-d H:i:s'),
            'url' => $_SERVER['REQUEST_URI'] ?? '',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? ''
        ];

        self::logError($error);

        if ($_ENV['APP_ENV'] === 'production') {
            self::notifyAdmins($error);
            
            // Show user-friendly error page
            http_response_code(500);
            include 'views/errors/500.php';
        } else {
            // Show detailed error in development
            echo "<pre>" . print_r($error, true) . "</pre>";
        }

        exit;
    }

    public static function handleShutdown()
    {
        $error = error_get_last();
        
        if ($error && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
            self::handleError($error['type'], $error['message'], $error['file'], $error['line']);
        }
    }

    private static function logError($error)
    {
        $logEntry = json_encode($error) . "\n";
        
        if (!is_dir(dirname(self::$logFile))) {
            mkdir(dirname(self::$logFile), 0755, true);
        }
        
        file_put_contents(self::$logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }

    private static function notifyAdmins($error)
    {
        // Send email notification for critical errors
        if (in_array($error['type'], ['exception', E_ERROR, E_CORE_ERROR])) {
            $subject = "Critical Error on " . ($_ENV['APP_NAME'] ?? 'Application');
            $message = "Error Details:\n" . print_r($error, true);
            
            mail($_ENV['ADMIN_EMAIL'] ?? 'admin@example.com', $subject, $message);
        }
    }
}
```

### Performance Monitoring

```php
<?php
// app/Core/PerformanceMonitor.php

namespace App\Core;

class PerformanceMonitor
{
    private static $startTime;
    private static $startMemory;
    private static $queryCount = 0;
    private static $queryTime = 0;

    public static function start()
    {
        self::$startTime = microtime(true);
        self::$startMemory = memory_get_usage();
    }

    public static function end()
    {
        $endTime = microtime(true);
        $endMemory = memory_get_usage();
        
        $stats = [
            'execution_time' => round(($endTime - self::$startTime) * 1000, 2), // ms
            'memory_usage' => round(($endMemory - self::$startMemory) / 1024 / 1024, 2), // MB
            'peak_memory' => round(memory_get_peak_usage() / 1024 / 1024, 2), // MB
            'query_count' => self::$queryCount,
            'query_time' => round(self::$queryTime * 1000, 2), // ms
            'timestamp' => date('Y-m-d H:i:s'),
            'url' => $_SERVER['REQUEST_URI'] ?? '',
            'method' => $_SERVER['REQUEST_METHOD'] ?? ''
        ];

        self::logPerformance($stats);
        
        // Add performance headers in development
        if ($_ENV['APP_DEBUG'] ?? false) {
            header("X-Execution-Time: {$stats['execution_time']}ms");
            header("X-Memory-Usage: {$stats['memory_usage']}MB");
            header("X-Query-Count: {$stats['query_count']}");
        }
    }

    public static function logQuery($queryTime)
    {
        self::$queryCount++;
        self::$queryTime += $queryTime;
    }

    private static function logPerformance($stats)
    {
        $logFile = 'logs/performance.log';
        $logEntry = json_encode($stats) . "\n";
        
        if (!is_dir(dirname($logFile))) {
            mkdir(dirname($logFile), 0755, true);
        }
        
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
}
```

---

## Security Configuration

### Security Hardening

```php
<?php
// config/security.php

class SecurityConfig
{
    public static function apply()
    {
        // Secure session configuration
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', 1);
        ini_set('session.use_strict_mode', 1);
        ini_set('session.cookie_samesite', 'Strict');

        // Hide PHP version
        header_remove('X-Powered-By');

        // Security headers
        self::setSecurityHeaders();

        // Disable dangerous functions in production
        if ($_ENV['APP_ENV'] === 'production') {
            self::disableDangerousFunctions();
        }
    }

    private static function setSecurityHeaders()
    {
        $headers = [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'X-XSS-Protection' => '1; mode=block',
            'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Content-Security-Policy' => "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'"
        ];

        foreach ($headers as $header => $value) {
            header("$header: $value");
        }
    }

    private static function disableDangerousFunctions()
    {
        $dangerousFunctions = [
            'exec', 'shell_exec', 'system', 'passthru',
            'eval', 'file_get_contents', 'file_put_contents',
            'fopen', 'fwrite'
        ];

        foreach ($dangerousFunctions as $function) {
            if (function_exists($function)) {
                // In a real scenario, you'd configure php.ini to disable these
                // ini_set('disable_functions', implode(',', $dangerousFunctions));
            }
        }
    }
}

// Apply security configuration
SecurityConfig::apply();
```

---

## Deployment Scripts

### Automated Deployment

```bash
#!/bin/bash
# scripts/deploy.sh

set -e

echo "Starting deployment..."

# Configuration
APP_DIR="/var/www/yourapp"
BACKUP_DIR="/var/backups/yourapp"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")

# Create backup
echo "Creating backup..."
mkdir -p $BACKUP_DIR
tar -czf $BACKUP_DIR/backup_$TIMESTAMP.tar.gz -C $APP_DIR .

# Update code
echo "Updating code..."
cd $APP_DIR
git pull origin main

# Update dependencies
echo "Updating dependencies..."
composer install --no-dev --optimize-autoloader

# Build assets
echo "Building assets..."
npm ci
npm run build

# Run database migrations
echo "Running database migrations..."
php scripts/deploy.php

# Clear caches
echo "Clearing caches..."
rm -rf cache/views/*
rm -rf cache/routes/*

# Set permissions
echo "Setting permissions..."
chmod -R 755 $APP_DIR
chmod -R 777 $APP_DIR/logs
chmod -R 777 $APP_DIR/cache

# Restart services
echo "Restarting services..."
sudo service php8.1-fpm restart
sudo service nginx restart

# Health check
echo "Performing health check..."
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" https://yourapp.com/health)

if [ $HTTP_CODE -eq 200 ]; then
    echo "Deployment successful!"
    
    # Clean old backups (keep last 5)
    cd $BACKUP_DIR
    ls -t backup_*.tar.gz | tail -n +6 | xargs -r rm
else
    echo "Deployment failed! HTTP Code: $HTTP_CODE"
    echo "Rolling back..."
    
    # Rollback to previous backup
    LATEST_BACKUP=$(ls -t $BACKUP_DIR/backup_*.tar.gz | head -n 1)
    tar -xzf $LATEST_BACKUP -C $APP_DIR
    
    exit 1
fi

echo "Deployment completed successfully!"
```

### Health Check Endpoint

```php
<?php
// Add to routes/web.php

Route::get('/health', function() {
    $checks = [
        'database' => checkDatabase(),
        'cache' => checkCache(),
        'storage' => checkStorage(),
        'external_apis' => checkExternalAPIs()
    ];
    
    $allHealthy = array_reduce($checks, function($carry, $check) {
        return $carry && $check['status'] === 'ok';
    }, true);
    
    http_response_code($allHealthy ? 200 : 503);
    
    return json_encode([
        'status' => $allHealthy ? 'healthy' : 'unhealthy',
        'timestamp' => date('c'),
        'checks' => $checks
    ]);
});

function checkDatabase() {
    try {
        $db = Database::getInstance();
        $db->query('SELECT 1');
        return ['status' => 'ok', 'message' => 'Database connection successful'];
    } catch (Exception $e) {
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}

function checkCache() {
    try {
        if (class_exists('Redis')) {
            $redis = new Redis();
            $redis->connect('127.0.0.1', 6379);
            $redis->ping();
            return ['status' => 'ok', 'message' => 'Cache connection successful'];
        }
        return ['status' => 'ok', 'message' => 'No cache configured'];
    } catch (Exception $e) {
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}

function checkStorage() {
    $testFile = 'storage/health-check.txt';
    try {
        file_put_contents($testFile, 'health check');
        $content = file_get_contents($testFile);
        unlink($testFile);
        
        return $content === 'health check' 
            ? ['status' => 'ok', 'message' => 'Storage is writable']
            : ['status' => 'error', 'message' => 'Storage read/write failed'];
    } catch (Exception $e) {
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}

function checkExternalAPIs() {
    // Add checks for external services your app depends on
    return ['status' => 'ok', 'message' => 'All external APIs are responsive'];
}
```

This deployment guide provides a comprehensive approach to deploying your MVC application with proper security, monitoring, and automated deployment processes.
