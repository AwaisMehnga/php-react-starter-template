---
layout: default
title: Middleware
nav_order: 8
---

# Middleware
{: .no_toc }

Middleware implements the **Chain of Responsibility** pattern to process HTTP requests through a pipeline of handlers before reaching the controller.
{: .fs-6 .fw-300 }

## Table of contents
{: .no_toc .text-delta }

1. TOC
{:toc}

---

## How Middleware Works Internally

### The Pipeline Pattern Implementation

The middleware system uses the **Pipeline Pattern** (also known as the **Russian Doll Pattern**) where each middleware wraps the next one:

```php
// Conceptual middleware stack
Request → Auth → CORS → RateLimit → Controller → RateLimit → CORS → Auth → Response
```

### Middleware Base Class

All middleware extends the abstract `Middleware` class:

```php
<?php
namespace App\Core;

abstract class Middleware
{
    /**
     * Handle the request
     * @param callable $next - The next middleware in the pipeline
     * @return mixed
     */
    abstract public function handle($next);
}
```

### Pipeline Construction Algorithm

The Application class builds the middleware pipeline using `array_reduce` and **higher-order functions**:

```php
private function createMiddlewarePipeline($middleware, $destination)
{
    // Build pipeline using array_reduce (functional programming approach)
    $pipeline = array_reduce(
        array_reverse($middleware), // Reverse to maintain correct execution order
        function ($next, $middlewareName) {
            // Return a closure that wraps the next middleware
            return function () use ($middlewareName, $next) {
                // Resolve middleware class from registry
                $middlewareClass = $this->middlewareRegistry[$middlewareName] ?? null;
                
                if (!$middlewareClass) {
                    throw new \Exception("Middleware not found: {$middlewareName}");
                }
                
                // Instantiate and execute middleware
                $middlewareInstance = new $middlewareClass();
                return $middlewareInstance->handle($next);
            };
        },
        $destination // Final destination (controller action)
    );
    
    return $pipeline;
}
```

### Execution Flow Analysis

Here's how a request flows through the middleware pipeline:

```php
// Given middleware stack: ['auth', 'cors', 'throttle']
// And controller action: UserController@index

// 1. array_reverse(['auth', 'cors', 'throttle']) = ['throttle', 'cors', 'auth']

// 2. array_reduce builds nested closures:
$pipeline = function() { // auth wrapper
    return $authMiddleware->handle(function() { // cors wrapper
        return $corsMiddleware->handle(function() { // throttle wrapper
            return $throttleMiddleware->handle(function() { // final destination
                return $controller->index(); // Controller action
            });
        });
    });
};

// 3. Execution order:
// auth->handle() → cors->handle() → throttle->handle() → controller → throttle response → cors response → auth response
```
<?php

namespace App\Core;

class MiddlewarePipeline
{
    private $middleware = [];

    public function add($middleware)
    {
        $this->middleware[] = $middleware;
        return $this;
    }

    public function handle($request, callable $destination)
    {
        $pipeline = array_reduce(
            array_reverse($this->middleware),
            function ($next, $middleware) {
                return function ($request) use ($middleware, $next) {
                    if (is_string($middleware)) {
                        $middleware = new $middleware();
                    }
                    return $middleware->handle($request, $next);
                };
            },
            $destination
        );

        return $pipeline($request);
    }
}
```

### Request Object

```php
<?php

namespace App\Core;

class Request
{
    private $get;
    private $post;
    private $server;
    private $headers;
    private $body;

    public function __construct()
    {
        $this->get = $_GET;
        $this->post = $_POST;
        $this->server = $_SERVER;
        $this->headers = $this->getAllHeaders();
        $this->body = file_get_contents('php://input');
    }

    public function method()
    {
        return $this->server['REQUEST_METHOD'] ?? 'GET';
    }

    public function uri()
    {
        return $this->server['REQUEST_URI'] ?? '/';
    }

    public function path()
    {
        return parse_url($this->uri(), PHP_URL_PATH);
    }

    public function get($key = null, $default = null)
    {
        return $key ? ($this->get[$key] ?? $default) : $this->get;
    }

    public function post($key = null, $default = null)
    {
        return $key ? ($this->post[$key] ?? $default) : $this->post;
    }

    public function input($key = null, $default = null)
    {
        $input = array_merge($this->get, $this->post);
        return $key ? ($input[$key] ?? $default) : $input;
    }

    public function header($key, $default = null)
    {
        return $this->headers[strtolower($key)] ?? $default;
    }

    public function bearerToken()
    {
        $header = $this->header('authorization', '');
        return str_starts_with($header, 'Bearer ') ? substr($header, 7) : null;
    }

    public function json()
    {
        return json_decode($this->body, true);
    }

    public function isJson()
    {
        return str_contains($this->header('content-type', ''), 'application/json');
    }

    public function isAjax()
    {
        return $this->header('x-requested-with') === 'XMLHttpRequest';
    }

    private function getAllHeaders()
    {
        $headers = [];
        foreach ($this->server as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $header = str_replace('_', '-', strtolower(substr($key, 5)));
                $headers[$header] = $value;
            }
        }
        return $headers;
    }
}
```

---

## Built-in Middleware

### Authentication Middleware

```php
<?php

namespace App\Middleware;

use App\Core\MiddlewareInterface;
use App\Models\User;

class AuthMiddleware implements MiddlewareInterface
{
    public function handle($request, callable $next)
    {
        // Check session-based authentication
        if (isset($_SESSION['user_id'])) {
            $user = User::find($_SESSION['user_id']);
            if ($user) {
                $request->user = $user;
                return $next($request);
            }
        }

        // Check token-based authentication
        $token = $request->bearerToken();
        if ($token) {
            $user = $this->validateToken($token);
            if ($user) {
                $request->user = $user;
                return $next($request);
            }
        }

        // Unauthorized
        if ($request->isAjax() || $request->isJson()) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        // Redirect to login
        header('Location: /login');
        exit;
    }

    private function validateToken($token)
    {
        // Implement JWT validation or database token lookup
        $user = User::where('api_token', $token)->first();
        return $user && $user->token_expires_at > date('Y-m-d H:i:s') ? $user : null;
    }
}
```

### Admin Middleware

```php
<?php

namespace App\Middleware;

use App\Core\MiddlewareInterface;

class AdminMiddleware implements MiddlewareInterface
{
    public function handle($request, callable $next)
    {
        if (!isset($request->user) || !$request->user->is_admin) {
            if ($request->isAjax() || $request->isJson()) {
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Forbidden']);
                exit;
            }

            http_response_code(403);
            echo "Access Denied";
            exit;
        }

        return $next($request);
    }
}
```

### CORS Middleware

```php
<?php

namespace App\Middleware;

use App\Core\MiddlewareInterface;

class CorsMiddleware implements MiddlewareInterface
{
    private $config = [
        'allowed_origins' => ['*'],
        'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
        'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With'],
        'allow_credentials' => true,
        'max_age' => 86400, // 24 hours
    ];

    public function __construct($config = [])
    {
        $this->config = array_merge($this->config, $config);
    }

    public function handle($request, callable $next)
    {
        $origin = $request->header('origin');
        
        // Set CORS headers
        if ($this->isOriginAllowed($origin)) {
            header("Access-Control-Allow-Origin: $origin");
        } elseif (in_array('*', $this->config['allowed_origins'])) {
            header('Access-Control-Allow-Origin: *');
        }

        header('Access-Control-Allow-Methods: ' . implode(', ', $this->config['allowed_methods']));
        header('Access-Control-Allow-Headers: ' . implode(', ', $this->config['allowed_headers']));
        
        if ($this->config['allow_credentials']) {
            header('Access-Control-Allow-Credentials: true');
        }
        
        header('Access-Control-Max-Age: ' . $this->config['max_age']);

        // Handle preflight requests
        if ($request->method() === 'OPTIONS') {
            http_response_code(200);
            exit;
        }

        return $next($request);
    }

    private function isOriginAllowed($origin)
    {
        return in_array($origin, $this->config['allowed_origins']);
    }
}
```

### Rate Limiting Middleware

```php
<?php

namespace App\Middleware;

use App\Core\MiddlewareInterface;

class RateLimitMiddleware implements MiddlewareInterface
{
    private $maxAttempts;
    private $decayMinutes;
    private $storage;

    public function __construct($maxAttempts = 60, $decayMinutes = 1)
    {
        $this->maxAttempts = $maxAttempts;
        $this->decayMinutes = $decayMinutes;
        $this->storage = $this->getStorage();
    }

    public function handle($request, callable $next)
    {
        $key = $this->getRateLimitKey($request);
        $attempts = $this->getAttempts($key);
        
        if ($attempts >= $this->maxAttempts) {
            $retryAfter = $this->getRetryAfter($key);
            
            http_response_code(429);
            header("Retry-After: $retryAfter");
            header('Content-Type: application/json');
            
            echo json_encode([
                'error' => 'Too Many Requests',
                'retry_after' => $retryAfter
            ]);
            exit;
        }

        $this->incrementAttempts($key);
        
        $response = $next($request);
        
        // Add rate limit headers
        $remaining = max(0, $this->maxAttempts - $attempts - 1);
        header("X-RateLimit-Limit: {$this->maxAttempts}");
        header("X-RateLimit-Remaining: $remaining");
        header("X-RateLimit-Reset: " . $this->getResetTime($key));

        return $response;
    }

    private function getRateLimitKey($request)
    {
        $ip = $request->server['REMOTE_ADDR'] ?? 'unknown';
        $path = $request->path();
        return "rate_limit:{$ip}:{$path}";
    }

    private function getAttempts($key)
    {
        $data = $this->storage->get($key);
        return $data ? $data['attempts'] : 0;
    }

    private function incrementAttempts($key)
    {
        $expiry = time() + ($this->decayMinutes * 60);
        $data = $this->storage->get($key) ?: ['attempts' => 0, 'reset_time' => $expiry];
        
        if (time() > $data['reset_time']) {
            $data = ['attempts' => 1, 'reset_time' => $expiry];
        } else {
            $data['attempts']++;
        }
        
        $this->storage->set($key, $data, $this->decayMinutes * 60);
    }

    private function getRetryAfter($key)
    {
        $data = $this->storage->get($key);
        return $data ? max(0, $data['reset_time'] - time()) : 0;
    }

    private function getResetTime($key)
    {
        $data = $this->storage->get($key);
        return $data ? $data['reset_time'] : time() + ($this->decayMinutes * 60);
    }

    private function getStorage()
    {
        // Simple file-based storage for demo
        return new class {
            private $cacheDir = 'cache/rate_limits/';

            public function __construct()
            {
                if (!is_dir($this->cacheDir)) {
                    mkdir($this->cacheDir, 0755, true);
                }
            }

            public function get($key)
            {
                $file = $this->cacheDir . md5($key) . '.json';
                if (file_exists($file)) {
                    $data = json_decode(file_get_contents($file), true);
                    if ($data && $data['expires'] > time()) {
                        return $data['value'];
                    }
                    unlink($file);
                }
                return null;
            }

            public function set($key, $value, $ttl)
            {
                $file = $this->cacheDir . md5($key) . '.json';
                $data = [
                    'value' => $value,
                    'expires' => time() + $ttl
                ];
                file_put_contents($file, json_encode($data));
            }
        };
    }
}
```

### Logging Middleware

```php
<?php

namespace App\Middleware;

use App\Core\MiddlewareInterface;

class LoggingMiddleware implements MiddlewareInterface
{
    private $logFile;

    public function __construct($logFile = 'logs/requests.log')
    {
        $this->logFile = $logFile;
        $this->ensureLogDirectory();
    }

    public function handle($request, callable $next)
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        // Log request
        $this->logRequest($request);

        // Process request
        $response = $next($request);

        // Log response
        $endTime = microtime(true);
        $endMemory = memory_get_usage();
        
        $this->logResponse([
            'duration' => round(($endTime - $startTime) * 1000, 2), // milliseconds
            'memory_usage' => $endMemory - $startMemory,
            'response_code' => http_response_code()
        ]);

        return $response;
    }

    private function logRequest($request)
    {
        $data = [
            'timestamp' => date('Y-m-d H:i:s'),
            'method' => $request->method(),
            'uri' => $request->uri(),
            'ip' => $request->server['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $request->header('user-agent', 'unknown'),
            'referer' => $request->header('referer', ''),
        ];

        $this->writeLog('REQUEST', $data);
    }

    private function logResponse($data)
    {
        $this->writeLog('RESPONSE', $data);
    }

    private function writeLog($type, $data)
    {
        $logEntry = [
            'type' => $type,
            'data' => $data
        ];

        $logLine = json_encode($logEntry) . "\n";
        file_put_contents($this->logFile, $logLine, FILE_APPEND | LOCK_EX);
    }

    private function ensureLogDirectory()
    {
        $dir = dirname($this->logFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
}
```

---

## Custom Middleware

### Validation Middleware

```php
<?php

namespace App\Middleware;

use App\Core\MiddlewareInterface;

class ValidationMiddleware implements MiddlewareInterface
{
    private $rules;

    public function __construct($rules = [])
    {
        $this->rules = $rules;
    }

    public function handle($request, callable $next)
    {
        $errors = $this->validate($request->input(), $this->rules);

        if (!empty($errors)) {
            if ($request->isAjax() || $request->isJson()) {
                http_response_code(422);
                header('Content-Type: application/json');
                echo json_encode(['errors' => $errors]);
                exit;
            }

            // Store errors in session for form redirection
            $_SESSION['validation_errors'] = $errors;
            $_SESSION['old_input'] = $request->input();
            
            $referer = $request->header('referer', '/');
            header("Location: $referer");
            exit;
        }

        return $next($request);
    }

    private function validate($data, $rules)
    {
        $errors = [];

        foreach ($rules as $field => $ruleSet) {
            $value = $data[$field] ?? null;
            $fieldRules = explode('|', $ruleSet);

            foreach ($fieldRules as $rule) {
                $error = $this->validateRule($field, $value, $rule, $data);
                if ($error) {
                    $errors[$field] = $error;
                    break; // Stop at first error for this field
                }
            }
        }

        return $errors;
    }

    private function validateRule($field, $value, $rule, $data)
    {
        $parts = explode(':', $rule);
        $ruleName = $parts[0];
        $parameter = $parts[1] ?? null;

        switch ($ruleName) {
            case 'required':
                return empty($value) ? "$field is required" : null;

            case 'email':
                return $value && !filter_var($value, FILTER_VALIDATE_EMAIL) 
                    ? "$field must be a valid email" : null;

            case 'min':
                return $value && strlen($value) < $parameter 
                    ? "$field must be at least $parameter characters" : null;

            case 'max':
                return $value && strlen($value) > $parameter 
                    ? "$field must not exceed $parameter characters" : null;

            case 'numeric':
                return $value && !is_numeric($value) 
                    ? "$field must be a number" : null;

            case 'confirmed':
                $confirmField = $field . '_confirmation';
                return $value !== ($data[$confirmField] ?? null) 
                    ? "$field confirmation does not match" : null;

            case 'unique':
                // Would need database check
                return $this->checkUnique($field, $value, $parameter) 
                    ? "$field already exists" : null;

            default:
                return null;
        }
    }

    private function checkUnique($field, $value, $table)
    {
        // Implementation would check database
        // return Database::table($table)->where($field, $value)->exists();
        return false;
    }
}
```

### Cache Middleware

```php
<?php

namespace App\Middleware;

use App\Core\MiddlewareInterface;

class CacheMiddleware implements MiddlewareInterface
{
    private $duration;
    private $cacheDir = 'cache/pages/';

    public function __construct($duration = 3600) // 1 hour default
    {
        $this->duration = $duration;
        $this->ensureCacheDirectory();
    }

    public function handle($request, callable $next)
    {
        // Only cache GET requests
        if ($request->method() !== 'GET') {
            return $next($request);
        }

        $cacheKey = $this->getCacheKey($request);
        $cachedResponse = $this->getFromCache($cacheKey);

        if ($cachedResponse) {
            // Serve from cache
            header('X-Cache: HIT');
            echo $cachedResponse;
            return;
        }

        // Capture response
        ob_start();
        $response = $next($request);
        $content = ob_get_contents();
        ob_end_clean();

        // Cache the response
        $this->putInCache($cacheKey, $content);
        
        header('X-Cache: MISS');
        echo $content;
        
        return $response;
    }

    private function getCacheKey($request)
    {
        $uri = $request->uri();
        $query = $request->get();
        ksort($query); // Normalize query parameter order
        
        return md5($uri . serialize($query));
    }

    private function getFromCache($key)
    {
        $file = $this->cacheDir . $key . '.cache';
        
        if (file_exists($file) && (time() - filemtime($file)) < $this->duration) {
            return file_get_contents($file);
        }
        
        return null;
    }

    private function putInCache($key, $content)
    {
        $file = $this->cacheDir . $key . '.cache';
        file_put_contents($file, $content, LOCK_EX);
    }

    private function ensureCacheDirectory()
    {
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }
}
```

---

## Middleware Registration

### Global Middleware

```php
<?php
// In router.php or Application class

use App\Core\Application;
use App\Middleware\LoggingMiddleware;
use App\Middleware\CorsMiddleware;

$app = Application::getInstance();

// Register global middleware (runs on every request)
$app->addGlobalMiddleware(new LoggingMiddleware());
$app->addGlobalMiddleware(new CorsMiddleware());
```

### Route-Specific Middleware

```php
<?php
// In routes/web.php

use App\Core\Route;
use App\Middleware\AuthMiddleware;
use App\Middleware\AdminMiddleware;
use App\Middleware\ValidationMiddleware;

// Single middleware
Route::middleware(AuthMiddleware::class)
     ->get('/dashboard', [DashboardController::class, 'index']);

// Multiple middleware
Route::middleware([AuthMiddleware::class, AdminMiddleware::class])
     ->get('/admin', [AdminController::class, 'index']);

// Middleware with parameters
Route::middleware(new ValidationMiddleware([
    'name' => 'required|min:3',
    'email' => 'required|email|unique:users',
    'password' => 'required|min:8|confirmed'
]))->post('/register', [AuthController::class, 'register']);
```

### Middleware Groups

```php
<?php

namespace App\Core;

class Application
{
    private $middlewareGroups = [
        'web' => [
            \App\Middleware\SessionMiddleware::class,
            \App\Middleware\CsrfMiddleware::class,
        ],
        'api' => [
            \App\Middleware\CorsMiddleware::class,
            \App\Middleware\RateLimitMiddleware::class,
        ],
        'auth' => [
            \App\Middleware\AuthMiddleware::class,
        ],
        'admin' => [
            \App\Middleware\AuthMiddleware::class,
            \App\Middleware\AdminMiddleware::class,
        ]
    ];

    public function getMiddlewareGroup($group)
    {
        return $this->middlewareGroups[$group] ?? [];
    }
}

// Usage in routes
Route::middleware('auth')->get('/dashboard', [DashboardController::class, 'index']);
Route::middleware('admin')->get('/admin', [AdminController::class, 'index']);
```

---

## Testing Middleware

### Middleware Testing

```php
<?php

namespace Tests\Middleware;

use App\Middleware\AuthMiddleware;
use App\Core\Request;
use App\Models\User;
use PHPUnit\Framework\TestCase;

class AuthMiddlewareTest extends TestCase
{
    public function testUnauthenticatedRequestIsRedirected()
    {
        $middleware = new AuthMiddleware();
        $request = new Request();
        
        $this->expectException(\Exception::class); // Or however you handle redirects
        
        $middleware->handle($request, function() {
            return 'next';
        });
    }

    public function testAuthenticatedRequestContinues()
    {
        // Mock authenticated user
        $_SESSION['user_id'] = 1;
        
        $middleware = new AuthMiddleware();
        $request = new Request();
        
        $result = $middleware->handle($request, function($req) {
            return 'success';
        });
        
        $this->assertEquals('success', $result);
        $this->assertInstanceOf(User::class, $request->user);
    }

    public function testApiTokenAuthentication()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'api_token' => 'test-token',
            'token_expires_at' => date('Y-m-d H:i:s', strtotime('+1 hour'))
        ]);

        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer test-token';
        
        $middleware = new AuthMiddleware();
        $request = new Request();
        
        $result = $middleware->handle($request, function($req) {
            return 'success';
        });
        
        $this->assertEquals('success', $result);
        $this->assertEquals($user->id, $request->user->id);
    }
}
```

---

## Best Practices

### 1. Keep Middleware Focused

```php
// ❌ Don't combine multiple concerns
class AuthAndLoggingMiddleware implements MiddlewareInterface
{
    public function handle($request, callable $next)
    {
        // Auth logic
        // Logging logic
        // Too much responsibility
    }
}

// ✅ Separate concerns
class AuthMiddleware implements MiddlewareInterface { /* ... */ }
class LoggingMiddleware implements MiddlewareInterface { /* ... */ }
```

### 2. Use Dependency Injection

```php
// ✅ Inject dependencies
class AuthMiddleware implements MiddlewareInterface
{
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
}
```

### 3. Handle Errors Gracefully

```php
public function handle($request, callable $next)
{
    try {
        // Middleware logic
        return $next($request);
    } catch (\Exception $e) {
        // Log error
        error_log($e->getMessage());
        
        // Return appropriate response
        if ($request->isJson()) {
            return json_response(['error' => 'Server Error'], 500);
        }
        
        return error_response('Something went wrong', 500);
    }
}
```

### 4. Make Middleware Configurable

```php
class RateLimitMiddleware implements MiddlewareInterface
{
    public function __construct($maxAttempts = 60, $decayMinutes = 1)
    {
        $this->maxAttempts = $maxAttempts;
        $this->decayMinutes = $decayMinutes;
    }
}

// Usage
Route::middleware(new RateLimitMiddleware(100, 5)) // 100 requests per 5 minutes
     ->get('/api/data', [ApiController::class, 'data']);
```

This middleware system provides a clean, testable way to handle cross-cutting concerns in your application while maintaining separation of responsibilities.
