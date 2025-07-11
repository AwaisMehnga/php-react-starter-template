---
layout: default
title: Controllers
nav_order: 3
---

# Controllers
{: .no_toc }

Controllers handle HTTP requests, process business logic, and return responses in the MVC architecture.
{: .fs-6 .fw-300 }

## Table of contents
{: .no_toc .text-delta }

1. TOC
{:toc}

---

## Overview

Controllers in this template follow Laravel-style conventions with:
- Clean separation of concerns
- Automatic dependency injection
- Built-in helper methods
- Middleware support

---

## Creating Controllers

### Basic Controller

Create a new controller in `app/Controllers/`:

```php
<?php

namespace App\Controllers;

use App\Core\Controller;

class PostController extends Controller
{
    /**
     * Display all posts
     */
    public function index()
    {
        $posts = Post::all();
        $this->view('posts/index', ['posts' => $posts]);
    }
    
    /**
     * Show a specific post
     */
    public function show($id)
    {
        $post = Post::find($id);
        
        if (!$post) {
            http_response_code(404);
            $this->view('404');
            return;
        }
        
        $this->view('posts/show', ['post' => $post]);
    }
    
    /**
     * Create a new post
     */
    public function create()
    {
        $this->view('posts/create');
    }
    
    /**
     * Store a new post
     */
    public function store()
    {
        $data = $this->request();
        
        // Validate data
        if (empty($data['title']) || empty($data['content'])) {
            $this->json(['error' => 'Title and content are required'], 422);
            return;
        }
        
        $post = Post::create($data);
        $this->redirect('/posts/' . $post->id);
    }
}
```

### Resource Controller

For full CRUD operations, create a resource controller:

```php
<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index()
    {
        $users = User::all();
        $this->view('users/index', compact('users'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        $this->view('users/create');
    }

    /**
     * Store a newly created user
     */
    public function store()
    {
        $data = $this->request();
        
        // Validation
        $errors = $this->validateUser($data);
        if (!empty($errors)) {
            $this->view('users/create', [
                'errors' => $errors,
                'old' => $data
            ]);
            return;
        }
        
        $user = User::create($data);
        $this->redirect('/users/' . $user->id);
    }

    /**
     * Display the specified user
     */
    public function show($id)
    {
        $user = User::find($id);
        
        if (!$user) {
            $this->abort(404);
        }
        
        $this->view('users/show', compact('user'));
    }

    /**
     * Show the form for editing the user
     */
    public function edit($id)
    {
        $user = User::find($id);
        
        if (!$user) {
            $this->abort(404);
        }
        
        $this->view('users/edit', compact('user'));
    }

    /**
     * Update the specified user
     */
    public function update($id)
    {
        $user = User::find($id);
        
        if (!$user) {
            $this->abort(404);
        }
        
        $data = $this->request();
        $user->fill($data)->save();
        
        $this->redirect('/users/' . $user->id);
    }

    /**
     * Remove the specified user
     */
    public function destroy($id)
    {
        $user = User::find($id);
        
        if (!$user) {
            $this->abort(404);
        }
        
        $user->delete();
        $this->redirect('/users');
    }
    
    /**
     * Validate user data
     */
    private function validateUser($data)
    {
        $errors = [];
        
        if (empty($data['name'])) {
            $errors['name'] = 'Name is required';
        }
        
        if (empty($data['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }
        
        return $errors;
    }
}
```

---

## Controller Methods

### Response Methods

The base `Controller` class provides several response methods:

```php
class MyController extends Controller
{
    public function apiExample()
    {
        // Return JSON response
        $this->json(['message' => 'Success'], 200);
    }
    
    public function viewExample()
    {
        // Render view with data
        $this->view('example', [
            'title' => 'Page Title',
            'data' => $someData
        ]);
    }
    
    public function redirectExample()
    {
        // Redirect to another URL
        $this->redirect('/success');
    }
    
    public function errorExample()
    {
        // Return error response
        $this->abort(404, 'Not Found');
    }
}
```

### Request Methods

Access request data using built-in methods:

```php
class FormController extends Controller
{
    public function handle()
    {
        // Get all request data
        $allData = $this->request();
        
        // Get specific field
        $name = $this->request('name');
        
        // Get with default value
        $page = $this->request('page', 1);
        
        // Check if field exists
        if ($this->has('email')) {
            // Process email
        }
        
        // Get file uploads
        $file = $this->file('avatar');
    }
}
```

---

## API Controllers

### JSON API Controller

Create dedicated API controllers for JSON responses:

```php
<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Models\Product;

class ProductController extends Controller
{
    /**
     * Get all products
     */
    public function index()
    {
        $products = Product::all();
        
        $this->json([
            'data' => $products,
            'total' => count($products)
        ]);
    }
    
    /**
     * Get single product
     */
    public function show($id)
    {
        $product = Product::find($id);
        
        if (!$product) {
            $this->json(['error' => 'Product not found'], 404);
            return;
        }
        
        $this->json(['data' => $product]);
    }
    
    /**
     * Create product
     */
    public function store()
    {
        $data = $this->request();
        
        // Validate
        $validator = $this->validate($data, [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id'
        ]);
        
        if ($validator->fails()) {
            $this->json(['errors' => $validator->errors()], 422);
            return;
        }
        
        $product = Product::create($data);
        
        $this->json([
            'message' => 'Product created successfully',
            'data' => $product
        ], 201);
    }
}
```

### RESTful API Structure

Organize API controllers with consistent structure:

```
app/Controllers/Api/
├── AuthController.php      # Authentication
├── UserController.php      # User management
├── ProductController.php   # Product CRUD
├── CategoryController.php  # Category CRUD
└── OrderController.php     # Order management
```

---

## Advanced Features

### Dependency Injection

Controllers support automatic dependency injection:

```php
<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\EmailService;
use App\Services\PaymentService;

class OrderController extends Controller
{
    private $emailService;
    private $paymentService;
    
    public function __construct(EmailService $emailService, PaymentService $paymentService)
    {
        $this->emailService = $emailService;
        $this->paymentService = $paymentService;
    }
    
    public function processOrder($id)
    {
        $order = Order::find($id);
        
        // Process payment
        $result = $this->paymentService->process($order);
        
        if ($result->success) {
            // Send confirmation email
            $this->emailService->sendOrderConfirmation($order);
            
            $this->json(['message' => 'Order processed successfully']);
        } else {
            $this->json(['error' => 'Payment failed'], 400);
        }
    }
}
```

### Controller Middleware

Apply middleware directly in controllers:

```php
<?php

namespace App\Controllers;

use App\Core\Controller;

class AdminController extends Controller
{
    public function __construct()
    {
        // Apply middleware to all methods
        $this->middleware('auth');
        $this->middleware('admin');
        
        // Apply to specific methods only
        $this->middleware('verified')->only(['sensitive']);
        $this->middleware('throttle:60,1')->except(['public']);
    }
    
    public function dashboard()
    {
        // Only accessible to authenticated admins
        $this->view('admin/dashboard');
    }
}
```

---

## Testing Controllers

### Unit Testing

Test controllers with PHPUnit:

```php
<?php

use PHPUnit\Framework\TestCase;
use App\Controllers\UserController;

class UserControllerTest extends TestCase
{
    public function testIndex()
    {
        $controller = new UserController();
        
        // Mock dependencies
        $userMock = $this->createMock(User::class);
        $userMock->method('all')->willReturn([]);
        
        // Test method
        $response = $controller->index();
        
        $this->assertInstanceOf('Response', $response);
    }
    
    public function testShowWithValidId()
    {
        $controller = new UserController();
        
        // Test with valid user ID
        $response = $controller->show(1);
        
        $this->assertEquals(200, $response->getStatusCode());
    }
    
    public function testShowWithInvalidId()
    {
        $controller = new UserController();
        
        // Test with invalid user ID
        $response = $controller->show(999);
        
        $this->assertEquals(404, $response->getStatusCode());
    }
}
```

---

## Best Practices

### 1. Keep Controllers Thin

Move business logic to services or models:

```php
// ❌ Fat controller
class OrderController extends Controller
{
    public function process($id)
    {
        // 50+ lines of business logic
    }
}

// ✅ Thin controller
class OrderController extends Controller
{
    public function process($id)
    {
        $order = Order::find($id);
        $result = app(OrderService::class)->process($order);
        
        return $this->json($result);
    }
}
```

### 2. Use Resource Controllers

Group related actions in resource controllers:

```php
Route::resource('posts', PostController::class);
// Generates: index, create, store, show, edit, update, destroy
```

### 3. Consistent Response Format

Standardize API responses:

```php
// Success response
$this->json([
    'success' => true,
    'data' => $data,
    'message' => 'Operation successful'
]);

// Error response  
$this->json([
    'success' => false,
    'error' => 'Error message',
    'code' => 'ERROR_CODE'
], 400);
```

### 4. Validate Input

Always validate user input:

```php
public function store()
{
    $data = $this->validate($this->request(), [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:8|confirmed'
    ]);
    
    // Data is now validated and safe to use
}
```

---

## Example Patterns

### CRUD Controller Template

```php
<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\YourModel;

class YourModelController extends Controller
{
    public function index()
    {
        $items = YourModel::all();
        return $this->view('your-model/index', compact('items'));
    }

    public function show($id)
    {
        $item = YourModel::find($id);
        if (!$item) $this->abort(404);
        return $this->view('your-model/show', compact('item'));
    }

    public function create()
    {
        return $this->view('your-model/create');
    }

    public function store()
    {
        $data = $this->request();
        $item = YourModel::create($data);
        return $this->redirect('/your-model/' . $item->id);
    }

    public function edit($id)
    {
        $item = YourModel::find($id);
        if (!$item) $this->abort(404);
        return $this->view('your-model/edit', compact('item'));
    }

    public function update($id)
    {
        $item = YourModel::find($id);
        if (!$item) $this->abort(404);
        
        $data = $this->request();
        $item->update($data);
        return $this->redirect('/your-model/' . $item->id);
    }

    public function destroy($id)
    {
        $item = YourModel::find($id);
        if (!$item) $this->abort(404);
        
        $item->delete();
        return $this->redirect('/your-model');
    }
}
```

This template provides a solid foundation for building robust, maintainable controllers following Laravel-style conventions.
