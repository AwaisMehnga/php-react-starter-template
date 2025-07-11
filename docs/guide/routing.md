---
layout: default
title: Routing
nav_order: 5
---

# Routing
{: .no_toc }

The routing system provides Laravel-style route definitions with support for middleware, groups, and parameters.
{: .fs-6 .fw-300 }

## Table of contents
{: .no_toc .text-delta }

1. TOC
{:toc}

---

## Overview

The routing system features:
- Laravel-style syntax
- Route parameters and constraints
- Middleware pipeline
- Route groups with shared attributes
- RESTful resource routes

---

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
