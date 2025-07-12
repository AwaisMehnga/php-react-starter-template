---
layout: default
title: Routing
nav_order: 5
---

# Routing
{: .no_toc }

The routing system implements a sophisticated request dispatching mechanism using FastRoute library with custom middleware pipeline integration.
{: .fs-6 .fw-300 }

## Table of contents
{: .no_toc .text-delta }

1. TOC
{:toc}

---

## How Routing Works Internally

### The Route Registration Process

The routing system uses a **Fluent Interface** pattern combined with the **Builder Pattern**:

```php
<?php
namespace App\Core;

use FastRoute\RouteCollector;

class Route
{
    private static $routeCollector;    // FastRoute's collector instance
    private static $middleware = [];   // Route-specific middleware
    private static $groupOptions = []; // Current group context
    
    // Fluent interface methods
    public static function get($uri, $action)
    {
        self::addRoute('GET', $uri, $action);
    }
    
    public static function middleware($middleware)
    {
        if (is_string($middleware)) {
            $middleware = [$middleware];
        }
        
        self::$middleware = array_merge(self::$middleware, $middleware);
        return new static(); // Returns instance for method chaining
    }
}
```

### Route Compilation Process

Here's how routes are processed from definition to execution:

```php
// 1. Route Definition (in routes/web.php)
Route::get('/users/{id}', [UserController::class, 'show']);

// 2. Internal Processing in addRoute()
private static function addRoute($method, $uri, $action)
{
    // Apply group prefix if within route group
    if (isset(self::$groupOptions['prefix'])) {
        $uri = '/' . trim(self::$groupOptions['prefix'], '/') . '/' . ltrim($uri, '/');
        $uri = rtrim($uri, '/') ?: '/';
    }
    
    // Collect all applicable middleware
    $middleware = [];
    
    // Group middleware (inherited from Route::group())
    if (isset(self::$groupOptions['middleware'])) {
        $groupMiddleware = is_array(self::$groupOptions['middleware']) 
            ? self::$groupOptions['middleware'] 
            : [self::$groupOptions['middleware']];
        $middleware = array_merge($middleware, $groupMiddleware);
    }
    
    // Route-specific middleware (from Route::middleware())
    $middleware = array_merge($middleware, self::$middleware);
    
    // Create route data structure
    $routeData = [
        'action' => $action,      // Controller and method
        'middleware' => $middleware // Middleware stack
    ];
    
    // Register with FastRoute
    self::$routeCollector->addRoute($method, $uri, $routeData);
    
    // Reset route-specific middleware for next route
    self::$middleware = [];
}
```

### Route Dispatching Mechanism

The `router.php` file handles the request dispatching:

```php
// 1. Create FastRoute dispatcher
$dispatcher = FastRoute\simpleDispatcher(function(RouteCollector $r) use ($routeFiles) {
    Route::setRouteCollector($r); // Inject collector into Route class
    
    // Load all route files
    foreach ($routeFiles as $file) {
        require $file; // Executes route definitions
    }
});

// 2. Parse incoming request
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// 3. Dispatch route
$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

// 4. Handle dispatch result
switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::FOUND:
        $vars = $routeInfo[2]; // Route parameters
        $app->handleRoute($routeInfo, $vars); // Execute middleware pipeline + controller
        break;
        
    case FastRoute\Dispatcher::NOT_FOUND:
        // 404 handling
        break;
        
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        // 405 handling
        break;
}
```

---

## Advanced Routing Concepts

### 1. Route Parameter Resolution

FastRoute uses **regular expressions** to match route patterns:

```php
// Route pattern: /users/{id}/posts/{slug}
// Compiles to regex: #^/users/([^/]+)/posts/([^/]+)$#

// When request "/users/123/posts/my-first-post" comes in:
// $vars = ['id' => '123', 'slug' => 'my-first-post']
```

The parameter injection uses **Named Capture Groups**:

```php
public function show($id, $slug)
{
    // PHP Reflection automatically maps:
    // 'id' parameter → $id argument
    // 'slug' parameter → $slug argument
}
```

### 2. Route Group Context Management

Route groups use a **Stack-based Context** system:

```php
public static function group($options, $callback)
{
    $prevGroupOptions = self::$groupOptions; // Save current context
    
    // Merge new options with current context
    self::$groupOptions = array_merge(self::$groupOptions, $options);
    
    // Execute callback with new context
    $callback(new static());
    
    // Restore previous context (stack pop)
    self::$groupOptions = $prevGroupOptions;
}
```

This allows nested groups:

```php
Route::group(['prefix' => 'api'], function () {
    Route::group(['prefix' => 'v1'], function () {
        Route::group(['middleware' => 'auth'], function () {
            Route::get('/users', [UserController::class, 'index']);
            // Final route: /api/v1/users with 'auth' middleware
        });
    });
});
```

### 3. Middleware Pipeline Integration

The routing system integrates with the middleware pipeline through the Application class:

```php
public function handleRoute($routeInfo, $vars = [])
{
    $routeData = $routeInfo[1];
    $action = $routeData['action'];
    $middleware = $routeData['middleware'] ?? [];
    
    // Create middleware pipeline using array_reduce
    $pipeline = $this->createMiddlewarePipeline($middleware, function() use ($action, $vars) {
        return $this->callAction($action, $vars);
    });
    
    return $pipeline(); // Execute the pipeline
}

private function createMiddlewarePipeline($middleware, $destination)
{
    // Use array_reduce to build nested closures (Russian Doll pattern)
    return array_reduce(
        array_reverse($middleware), // Reverse to maintain correct order
        function ($next, $middlewareName) {
            return function () use ($middlewareName, $next) {
                $middlewareClass = $this->middlewareRegistry[$middlewareName];
                $middlewareInstance = new $middlewareClass();
                return $middlewareInstance->handle($next);
            };
        },
        $destination // Final destination (controller action)
    );
}
```

---

## Route Patterns and Examples

### 1. Parameter Constraints

You can add parameter validation using FastRoute's regex patterns:

```php
// Only numeric IDs
Route::get('/users/{id:[0-9]+}', [UserController::class, 'show']);

// Alphanumeric slugs
Route::get('/posts/{slug:[a-zA-Z0-9-]+}', [PostController::class, 'show']);

// Optional parameters with constraints
Route::get('/search/{query?}', [SearchController::class, 'index']);
```

### 2. HTTP Method Handling

The system supports all HTTP methods through method delegation:

```php
class Route
{
    public static function get($uri, $action) { self::addRoute('GET', $uri, $action); }
    public static function post($uri, $action) { self::addRoute('POST', $uri, $action); }
    public static function put($uri, $action) { self::addRoute('PUT', $uri, $action); }
    public static function patch($uri, $action) { self::addRoute('PATCH', $uri, $action); }
    public static function delete($uri, $action) { self::addRoute('DELETE', $uri, $action); }
    public static function options($uri, $action) { self::addRoute('OPTIONS', $uri, $action); }
}
```

### 3. Resource Route Pattern

You can implement RESTful resource routes:

```php
class Route
{
    public static function resource($name, $controller)
    {
        $routes = [
            ['GET', "/{$name}", 'index'],
            ['GET', "/{$name}/create", 'create'],
            ['POST', "/{$name}", 'store'],
            ['GET', "/{$name}/{id}", 'show'],
            ['GET', "/{$name}/{id}/edit", 'edit'],
            ['PUT', "/{$name}/{id}", 'update'],
            ['PATCH', "/{$name}/{id}", 'update'],
            ['DELETE', "/{$name}/{id}", 'destroy'],
        ];
        
        foreach ($routes as [$method, $uri, $action]) {
            self::addRoute($method, $uri, [$controller, $action]);
        }
    }
}

// Usage
Route::resource('posts', PostController::class);
// Creates all 8 RESTful routes automatically
```

---

## Real-World Routing Examples

### 1. API Versioning

```php
// routes/api.php
Route::group(['prefix' => 'api'], function () {
    
    // Version 1
    Route::group(['prefix' => 'v1'], function () {
        Route::group(['middleware' => ['cors', 'auth:api']], function () {
            Route::resource('users', Api\V1\UserController::class);
            Route::resource('posts', Api\V1\PostController::class);
        });
    });
    
    // Version 2 with breaking changes
    Route::group(['prefix' => 'v2'], function () {
        Route::group(['middleware' => ['cors', 'auth:jwt']], function () {
            Route::resource('users', Api\V2\UserController::class);
            Route::resource('posts', Api\V2\PostController::class);
        });
    });
});
```

### 2. Multi-tenant Routing

```php
Route::group(['domain' => '{tenant}.example.com'], function () {
    Route::get('/', function ($tenant) {
        // Handle tenant-specific homepage
        $tenant = Tenant::findByDomain($tenant);
        return view('tenant.home', compact('tenant'));
    });
    
    Route::group(['middleware' => 'tenant'], function () {
        Route::resource('posts', TenantPostController::class);
    });
});
```

### 3. Complex Route Groups

```php
// Admin panel with nested organization
Route::group([
    'prefix' => 'admin',
    'middleware' => ['auth', 'admin'],
    'namespace' => 'Admin'
], function () {
    
    Route::get('/dashboard', [DashboardController::class, 'index']);
    
    // User management
    Route::group(['prefix' => 'users'], function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('/banned', [UserController::class, 'banned']);
        Route::post('/{id}/ban', [UserController::class, 'ban']);
        Route::post('/{id}/unban', [UserController::class, 'unban']);
    });
    
    // Content management
    Route::group(['prefix' => 'content'], function () {
        Route::resource('posts', PostController::class);
        Route::resource('categories', CategoryController::class);
        
        // Bulk operations
        Route::post('/posts/bulk-delete', [PostController::class, 'bulkDelete']);
        Route::post('/posts/bulk-publish', [PostController::class, 'bulkPublish']);
    });
});
```

### 4. Rate-Limited Routes

```php
// Different rate limits for different endpoints
Route::group(['middleware' => 'throttle:60,1'], function () {
    Route::get('/api/search', [SearchController::class, 'index']);
});

Route::group(['middleware' => 'throttle:10,1'], function () {
    Route::post('/api/upload', [FileController::class, 'upload']);
});

Route::group(['middleware' => 'throttle:5,1'], function () {
    Route::post('/api/send-email', [EmailController::class, 'send']);
});
```

---

## Route Caching and Performance

### 1. Route Compilation Optimization

FastRoute compiles routes into optimized arrays:

```php
// Development: Routes compiled on every request
$dispatcher = FastRoute\simpleDispatcher($routeDefinitionCallback);

// Production: Routes cached as PHP arrays
$dispatcher = FastRoute\cachedDispatcher($routeDefinitionCallback, [
    'cacheFile' => __DIR__ . '/cache/routes.cache',
    'cacheDisabled' => false,
]);
```

### 2. Route Matching Performance

FastRoute uses a **two-stage matching process**:

1. **Static routes** are stored in a hash map for O(1) lookup
2. **Dynamic routes** are compiled into a single regex for efficient matching

```php
// Static routes (fast hash lookup)
$staticRoutes = [
    '/about' => ['handler' => AboutController::class],
    '/contact' => ['handler' => ContactController::class],
];

// Dynamic routes (compiled regex)
$dynamicRoutes = [
    '#^/users/([^/]+)$#' => ['handler' => UserController::class, 'vars' => ['id']],
    '#^/posts/([^/]+)/comments/([^/]+)$#' => ['handler' => CommentController::class, 'vars' => ['postId', 'commentId']],
];
```

This routing system provides excellent performance while maintaining flexibility and clean syntax for route definitions.

## Basic Routing

### Defining Routes

Routes are defined in `routes/web.php`:

```php
<?php
use App\Core\Route;
use App\Controllers\HomeController;
use App\Controllers\UserController;

// Basic GET route
Route::get('/', [HomeController::class, 'index']);

// Multiple HTTP methods
Route::get('/users', [UserController::class, 'index']);
Route::post('/users', [UserController::class, 'store']);
Route::put('/users/{id}', [UserController::class, 'update']);
Route::delete('/users/{id}', [UserController::class, 'destroy']);

// Route with closure
Route::get('/hello', function() {
    return json_response(['message' => 'Hello World!']);
});
```

### Available HTTP Methods

```php
Route::get($uri, $callback);
Route::post($uri, $callback);
Route::put($uri, $callback);
Route::patch($uri, $callback);
Route::delete($uri, $callback);
Route::options($uri, $callback);
```

### File-based Routes (Backward Compatibility)

You can still use file-based routes for simple pages:

```php
// Serve static view files
Route::get('/about', 'views/about.php');
Route::get('/contact', 'views/contact.php');
```

---

## Route Parameters

### Required Parameters

```php
// Single parameter
Route::get('/users/{id}', [UserController::class, 'show']);

// Multiple parameters
Route::get('/users/{userId}/posts/{postId}', [PostController::class, 'show']);

// Controller method automatically receives parameters
class UserController extends Controller
{
    public function show($id)
    {
        $user = User::find($id);
        return $this->view('users/show', compact('user'));
    }
}
```

### Optional Parameters

```php
// Optional parameter with default value
Route::get('/posts/{slug?}', [PostController::class, 'show']);

class PostController extends Controller
{
    public function show($slug = 'latest')
    {
        if ($slug === 'latest') {
            $post = Post::latest()->first();
        } else {
            $post = Post::findBySlug($slug);
        }
        
        return $this->view('posts/show', compact('post'));
    }
}
```

### Parameter Constraints

```php
// Numeric constraint
Route::get('/users/{id}', [UserController::class, 'show'])->where('id', '[0-9]+');

// Alpha constraint
Route::get('/categories/{slug}', [CategoryController::class, 'show'])->where('slug', '[a-zA-Z-]+');

// Multiple constraints
Route::get('/archive/{year}/{month}', [PostController::class, 'archive'])
     ->where(['year' => '[0-9]{4}', 'month' => '[0-9]{2}']);
```

---

## Route Groups

### Basic Groups

```php
Route::group(['prefix' => 'admin'], function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard']);
    Route::get('/users', [AdminController::class, 'users']);
    Route::get('/settings', [AdminController::class, 'settings']);
});

// Results in routes:
// /admin/dashboard
// /admin/users  
// /admin/settings
```

### Groups with Middleware

```php
Route::group(['middleware' => ['auth', 'admin']], function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
    Route::get('/admin/users', [AdminController::class, 'users']);
});

// Single middleware
Route::group(['middleware' => 'auth'], function () {
    Route::get('/profile', [UserController::class, 'profile']);
});
```

### Groups with Prefix and Middleware

```php
Route::group(['prefix' => 'api', 'middleware' => 'cors'], function () {
    Route::get('/users', [Api\UserController::class, 'index']);
    Route::get('/users/{id}', [Api\UserController::class, 'show']);
    Route::post('/users', [Api\UserController::class, 'store']);
});
```

### Nested Groups

```php
Route::group(['prefix' => 'api'], function () {
    Route::group(['prefix' => 'v1'], function () {
        Route::group(['middleware' => 'auth'], function () {
            Route::get('/users', [Api\V1\UserController::class, 'index']);
        });
    });
});

// Results in: /api/v1/users
```

---

## Middleware

### Route-Specific Middleware

```php
// Single middleware
Route::middleware('auth')->get('/dashboard', [DashboardController::class, 'index']);

// Multiple middleware
Route::middleware(['auth', 'verified'])->get('/dashboard', [DashboardController::class, 'index']);

// Chaining
Route::get('/admin/users', [AdminController::class, 'users'])
     ->middleware('auth')
     ->middleware('admin');
```

### Middleware with Parameters

```php
// Throttle middleware with parameters
Route::middleware('throttle:60,1')->get('/api/data', [ApiController::class, 'data']);

// Custom middleware with parameters
Route::middleware('role:admin,moderator')->get('/admin', [AdminController::class, 'index']);
```

### Global Middleware

Register global middleware in `router.php`:

```php
$app = Application::getInstance();

// Register middleware
$app->registerMiddleware('auth', \App\Middleware\AuthMiddleware::class);
$app->registerMiddleware('admin', \App\Middleware\AdminMiddleware::class);
$app->registerMiddleware('cors', \App\Middleware\CorsMiddleware::class);
```

---

## RESTful Routes

### Resource Routes

```php
// Creates all RESTful routes for a resource
Route::resource('posts', PostController::class);

// Equivalent to:
Route::get('/posts', [PostController::class, 'index']);           // GET /posts
Route::get('/posts/create', [PostController::class, 'create']);   // GET /posts/create
Route::post('/posts', [PostController::class, 'store']);          // POST /posts
Route::get('/posts/{id}', [PostController::class, 'show']);       // GET /posts/{id}
Route::get('/posts/{id}/edit', [PostController::class, 'edit']);  // GET /posts/{id}/edit
Route::put('/posts/{id}', [PostController::class, 'update']);     // PUT /posts/{id}
Route::delete('/posts/{id}', [PostController::class, 'destroy']); // DELETE /posts/{id}
```

### Partial Resource Routes

```php
// Only specific actions
Route::resource('posts', PostController::class)->only(['index', 'show']);

// Exclude specific actions
Route::resource('posts', PostController::class)->except(['create', 'edit']);
```

### API Resources

```php
// API-only routes (excludes create/edit forms)
Route::apiResource('posts', Api\PostController::class);

// Equivalent to:
Route::get('/posts', [Api\PostController::class, 'index']);
Route::post('/posts', [Api\PostController::class, 'store']);
Route::get('/posts/{id}', [Api\PostController::class, 'show']);
Route::put('/posts/{id}', [Api\PostController::class, 'update']);
Route::delete('/posts/{id}', [Api\PostController::class, 'destroy']);
```

---

## API Routes

### API Route Groups

```php
Route::group(['prefix' => 'api/v1', 'middleware' => ['cors', 'auth:api']], function () {
    
    // User endpoints
    Route::apiResource('users', Api\UserController::class);
    
    // Custom API endpoints
    Route::get('/users/{id}/posts', [Api\UserController::class, 'posts']);
    Route::post('/users/{id}/avatar', [Api\UserController::class, 'uploadAvatar']);
    
    // Nested resources
    Route::group(['prefix' => 'users/{userId}'], function () {
        Route::apiResource('posts', Api\PostController::class);
        Route::apiResource('comments', Api\CommentController::class);
    });
});
```

### Versioned APIs

```php
// API v1
Route::group(['prefix' => 'api/v1'], function () {
    Route::apiResource('users', Api\V1\UserController::class);
});

// API v2  
Route::group(['prefix' => 'api/v2'], function () {
    Route::apiResource('users', Api\V2\UserController::class);
});
```

---

## Route Model Binding

### Implicit Binding

```php
// Route parameter matches model
Route::get('/users/{user}', [UserController::class, 'show']);

class UserController extends Controller
{
    public function show(User $user)
    {
        // $user is automatically resolved from the {user} parameter
        return $this->view('users/show', compact('user'));
    }
}
```

### Explicit Binding

```php
// Custom resolution logic
Route::bind('user', function ($value) {
    return User::where('slug', $value)->firstOrFail();
});

Route::get('/users/{user}', [UserController::class, 'show']);
```

---

## Route Caching

### Generating Route Cache

```php
// Generate route cache for production
php artisan route:cache

// Clear route cache
php artisan route:clear
```

### Cache Implementation

```php
class RouteCache
{
    public static function cache()
    {
        $routes = require 'routes/web.php';
        $cached = serialize($routes);
        file_put_contents('cache/routes.php', $cached);
    }
    
    public static function load()
    {
        if (file_exists('cache/routes.php')) {
            return unserialize(file_get_contents('cache/routes.php'));
        }
        
        return require 'routes/web.php';
    }
}
```

---

## Advanced Routing

### Route Fallbacks

```php
// Catch-all route for SPAs
Route::fallback(function () {
    return view('spa');
});

// Custom 404 handling
Route::fallback([ErrorController::class, 'notFound']);
```

### Subdomain Routing

```php
Route::group(['domain' => 'admin.{domain}'], function () {
    Route::get('/', [AdminController::class, 'dashboard']);
});

Route::group(['domain' => 'api.{domain}'], function () {
    Route::apiResource('users', Api\UserController::class);
});
```

### Route Conditions

```php
Route::get('/posts/{id}', [PostController::class, 'show'])
     ->where('id', '[0-9]+')
     ->middleware('auth')
     ->name('posts.show');
```

---

## Route Helpers

### URL Generation

```php
// Generate URLs to routes
function route($name, $parameters = [])
{
    $routes = [
        'users.index' => '/users',
        'users.show' => '/users/{id}',
        'posts.show' => '/posts/{id}'
    ];
    
    $url = $routes[$name] ?? '/';
    
    foreach ($parameters as $key => $value) {
        $url = str_replace('{' . $key . '}', $value, $url);
    }
    
    return $url;
}

// Usage
$userUrl = route('users.show', ['id' => 1]); // /users/1
```

### Named Routes

```php
Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');

// In views
echo route('users.show', ['id' => $user->id]);
```

---

## Testing Routes

### Route Testing

```php
use PHPUnit\Framework\TestCase;

class RouteTest extends TestCase
{
    public function testUserRoutes()
    {
        // Test GET /users
        $response = $this->get('/users');
        $this->assertEquals(200, $response->getStatusCode());
        
        // Test GET /users/1
        $response = $this->get('/users/1');
        $this->assertEquals(200, $response->getStatusCode());
        
        // Test 404
        $response = $this->get('/users/999');
        $this->assertEquals(404, $response->getStatusCode());
    }
    
    public function testMiddleware()
    {
        // Test protected route
        $response = $this->get('/admin/dashboard');
        $this->assertEquals(401, $response->getStatusCode());
        
        // Test with authentication
        $this->actingAs($user = User::factory()->admin());
        $response = $this->get('/admin/dashboard');
        $this->assertEquals(200, $response->getStatusCode());
    }
}
```

---

## Best Practices

### 1. Use Resource Routes

```php
// ❌ Manual route definitions
Route::get('/posts', [PostController::class, 'index']);
Route::get('/posts/{id}', [PostController::class, 'show']);
Route::post('/posts', [PostController::class, 'store']);

// ✅ Resource route
Route::resource('posts', PostController::class);
```

### 2. Group Related Routes

```php
// ❌ Scattered routes
Route::middleware('auth')->get('/profile', [UserController::class, 'profile']);
Route::middleware('auth')->get('/settings', [UserController::class, 'settings']);

// ✅ Grouped routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [UserController::class, 'profile']);
    Route::get('/settings', [UserController::class, 'settings']);
});
```

### 3. Use Meaningful Names

```php
// ❌ Generic names
Route::get('/u/{id}', [UserController::class, 'show']);

// ✅ Clear names
Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');
```

### 4. Validate Parameters

```php
// ❌ No validation
Route::get('/users/{id}', [UserController::class, 'show']);

// ✅ Parameter validation
Route::get('/users/{id}', [UserController::class, 'show'])->where('id', '[0-9]+');
```

---

## Example Route File

```php
<?php

use App\Core\Route;
use App\Controllers\HomeController;
use App\Controllers\UserController;
use App\Controllers\PostController;
use App\Controllers\AdminController;
use App\Controllers\Api\ApiUserController;

// Home routes
Route::get('/', [HomeController::class, 'index']);
Route::get('/about', [HomeController::class, 'about']);

// User authentication routes
Route::get('/login', [AuthController::class, 'showLogin']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

// Protected user routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::resource('posts', PostController::class);
});

// Admin routes
Route::group(['prefix' => 'admin', 'middleware' => ['auth', 'admin']], function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard']);
    Route::resource('users', AdminController::class);
});

// API routes
Route::group(['prefix' => 'api/v1', 'middleware' => 'cors'], function () {
    Route::apiResource('users', ApiUserController::class);
    Route::get('/health', function () {
        return json_response(['status' => 'OK']);
    });
});
```

This routing system provides the flexibility and power needed for modern web applications while maintaining simplicity and Laravel-style conventions.
