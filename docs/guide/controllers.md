---
layout: default
title: Controllers
nav_order: 3
---

# Controllers
{: .no_toc }

Controllers handle HTTP requests and coordinate between models and views. This guide explains how the controller system works internally and demonstrates the underlying PHP concepts.
{: .fs-6 .fw-300 }

## Table of contents
{: .no_toc .text-delta }

1. TOC
{:toc}

---

## How Controllers Work Internally

### The Controller Base Class

The base `Controller` class provides foundational methods that every controller inherits. Here's how it works:

```php
<?php
namespace App\Core;

class Controller
{
    // View rendering uses PHP's extract() function to convert array keys to variables
    protected function view($view, $data = [])
    {
        extract($data); // Converts ['user' => $userObj] to $user variable
        $viewPath = __DIR__ . '/../../views/' . $view . '.php';
        
        if (file_exists($viewPath)) {
            require $viewPath; // PHP includes and executes the view file
        } else {
            throw new \Exception("View not found: {$view}");
        }
    }
    
    // JSON responses use PHP's http_response_code() and header() functions
    protected function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data); // Converts PHP data to JSON string
        exit; // Terminates script execution
    }
    
    // Request data merges superglobals $_GET and $_POST
    protected function request($key = null, $default = null)
    {
        $data = array_merge($_GET, $_POST); // Combines query params and form data
        return $key === null ? $data : ($data[$key] ?? $default);
    }
}
```

### Parameter Injection System

The Application class uses PHP's **Reflection API** to automatically inject route parameters into controller methods:

```php
// In Application::callAction()
$reflection = new \ReflectionMethod($controller, $method);
$parameters = $reflection->getParameters();
$args = [];

foreach ($parameters as $param) {
    $paramName = $param->getName();
    if (isset($vars[$paramName])) {
        $args[] = $vars[$paramName]; // Route parameter matched
    } elseif ($param->isDefaultValueAvailable()) {
        $args[] = $param->getDefaultValue(); // Use default value
    } else {
        throw new \Exception("Required parameter {$paramName} not found");
    }
}

call_user_func_array([$controller, $method], $args);
```

This allows you to write controller methods like:

```php
public function show($id, $slug = 'default')
{
    // $id comes from route parameter {id}
    // $slug comes from route parameter {slug} or uses default
}
```

---

## Programming Concepts in Action

### 1. Method Overloading Through Magic Methods

Controllers use PHP's `__call()` magic method pattern (though not explicitly implemented here, the concept applies):

```php
class ProductController extends Controller
{
    // Standard CRUD methods
    public function index() { /* List all products */ }
    public function show($id) { /* Show single product */ }
    public function store() { /* Create new product */ }
    public function update($id) { /* Update existing product */ }
    public function destroy($id) { /* Delete product */ }
}
```

### 2. Response Factory Pattern

Different response types are handled through method chaining:

```php
class ApiController extends Controller
{
    public function success($data, $message = 'Success')
    {
        return $this->json([
            'success' => true,
            'data' => $data,
            'message' => $message
        ]);
    }
    
    public function error($message, $code = 400)
    {
        return $this->json([
            'success' => false,
            'error' => $message
        ], $code);
    }
}
```

### 3. Template Method Pattern

Resource controllers follow a template pattern:

```php
abstract class ResourceController extends Controller
{
    // Template method that defines the structure
    final public function handleRequest($action, $params = [])
    {
        $this->beforeAction();
        $result = $this->$action(...$params);
        $this->afterAction();
        return $result;
    }
    
    // Hooks that subclasses can override
    protected function beforeAction() { /* Override in subclass */ }
    protected function afterAction() { /* Override in subclass */ }
}
```

---

## Advanced Controller Concepts

### 1. Controller Resolution Process

When a route is matched, here's how the controller is instantiated and called:

```php
// 1. Route definition
Route::get('/users/{id}', [UserController::class, 'show']);

// 2. Application resolves the controller
[$controllerClass, $method] = $action; // ['App\Controllers\UserController', 'show']

// 3. Class existence check
if (!class_exists($controllerClass)) {
    throw new \Exception("Controller not found: {$controllerClass}");
}

// 4. Controller instantiation
$controller = new $controllerClass(); // PHP instantiates the class

// 5. Method existence check
if (!method_exists($controller, $method)) {
    throw new \Exception("Method {$method} not found");
}

// 6. Method invocation with parameters
call_user_func_array([$controller, $method], $args);
```

### 2. Dependency Injection Container

While not fully implemented in the base template, you can extend the controller resolution to support dependency injection:

```php
class Container
{
    private $bindings = [];
    
    public function bind($abstract, $concrete)
    {
        $this->bindings[$abstract] = $concrete;
    }
    
    public function resolve($class)
    {
        $reflector = new ReflectionClass($class);
        $constructor = $reflector->getConstructor();
        
        if (!$constructor) {
            return new $class;
        }
        
        $parameters = $constructor->getParameters();
        $dependencies = [];
        
        foreach ($parameters as $parameter) {
            $type = $parameter->getType();
            if ($type && !$type->isBuiltin()) {
                $dependencies[] = $this->resolve($type->getName());
            }
        }
        
        return $reflector->newInstanceArgs($dependencies);
    }
}

// Usage
class UserController extends Controller
{
    private $userService;
    
    public function __construct(UserService $userService)
    {
        $this->userService = $userService; // Automatically injected
    }
}
```

### 3. Controller Middleware System

Controllers can apply middleware using method chaining:

```php
class SecureController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); // Apply to all methods
        $this->middleware('admin')->only(['destroy', 'edit']);
        $this->middleware('throttle:60,1')->except(['index']);
    }
    
    private function middleware($name)
    {
        // Store middleware for later application
        return new MiddlewareBuilder($this, $name);
    }
}

class MiddlewareBuilder
{
    public function only(array $methods)
    {
        // Apply middleware only to specified methods
        return $this;
    }
    
    public function except(array $methods)
    {
        // Apply middleware to all methods except specified
        return $this;
    }
}
```

---

## Real-World Implementation Examples

### 1. RESTful API Controller

```php
class TaskController extends Controller
{
    public function index()
    {
        // Using array_map with closures for data transformation
        $tasks = array_map(function($task) {
            return [
                'id' => $task->id,
                'title' => $task->title,
                'completed' => (bool) $task->completed,
                'created_at' => $task->created_at->format('Y-m-d H:i:s')
            ];
        }, Task::all());
        
        return $this->json(['data' => $tasks]);
    }
    
    public function store()
    {
        $data = $this->request();
        
        // Input validation using array operations
        $required = ['title', 'description'];
        $missing = array_diff($required, array_keys($data));
        
        if (!empty($missing)) {
            return $this->json([
                'error' => 'Missing required fields: ' . implode(', ', $missing)
            ], 422);
        }
        
        // Data sanitization using array_map
        $sanitized = array_map('trim', $data);
        $sanitized = array_map('htmlspecialchars', $sanitized);
        
        $task = Task::create($sanitized);
        return $this->json(['data' => $task], 201);
    }
    
    public function update($id)
    {
        $task = Task::find($id);
        if (!$task) {
            return $this->json(['error' => 'Task not found'], 404);
        }
        
        // Partial update using array_intersect_key
        $data = $this->request();
        $fillable = ['title', 'description', 'completed'];
        $updates = array_intersect_key($data, array_flip($fillable));
        
        $task->fill($updates)->save();
        return $this->json(['data' => $task]);
    }
}
```

### 2. File Upload Controller

```php
class FileController extends Controller
{
    public function upload()
    {
        // File validation using $_FILES superglobal
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            return $this->json(['error' => 'No file uploaded'], 400);
        }
        
        $file = $_FILES['file'];
        
        // MIME type validation using finfo
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        
        if (!in_array($mimeType, $allowedTypes)) {
            return $this->json(['error' => 'Invalid file type'], 400);
        }
        
        // Generate unique filename using hash
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = hash('sha256', $file['name'] . time()) . '.' . $extension;
        $destination = __DIR__ . '/../../public/uploads/' . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return $this->json(['filename' => $filename]);
        }
        
        return $this->json(['error' => 'Upload failed'], 500);
    }
}
```

### 3. Authentication Controller

```php
class AuthController extends Controller
{
    public function login()
    {
        $credentials = $this->request();
        
        // Input validation using filter_var
        if (!filter_var($credentials['email'], FILTER_VALIDATE_EMAIL)) {
            return $this->json(['error' => 'Invalid email format'], 422);
        }
        
        $user = User::findByEmail($credentials['email']);
        
        // Password verification using password_verify
        if (!$user || !password_verify($credentials['password'], $user->password)) {
            return $this->json(['error' => 'Invalid credentials'], 401);
        }
        
        // Session management
        session_start();
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_role'] = $user->role;
        
        // Generate CSRF token
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        
        return $this->json([
            'user' => $user->toArray(),
            'csrf_token' => $_SESSION['csrf_token']
        ]);
    }
    
    public function logout()
    {
        session_start();
        session_destroy(); // Clear all session data
        
        return $this->json(['message' => 'Logged out successfully']);
    }
}
```

---

## Error Handling Patterns

### 1. Exception Handling in Controllers

```php
class OrderController extends Controller
{
    public function process($id)
    {
        try {
            $order = Order::findOrFail($id); // Throws exception if not found
            
            // Business logic that might throw exceptions
            $this->validateOrder($order);
            $this->processPayment($order);
            $this->sendConfirmation($order);
            
            return $this->json(['message' => 'Order processed successfully']);
            
        } catch (ValidationException $e) {
            return $this->json(['error' => $e->getMessage()], 422);
        } catch (PaymentException $e) {
            return $this->json(['error' => 'Payment failed'], 402);
        } catch (Exception $e) {
            // Log the error (in a real app)
            error_log($e->getMessage());
            return $this->json(['error' => 'Internal server error'], 500);
        }
    }
    
    private function validateOrder($order)
    {
        if (!$order->isValid()) {
            throw new ValidationException('Order validation failed');
        }
    }
}
```

### 2. Response Status Patterns

```php
trait ResponseHelpers
{
    protected function success($data = [], $message = 'Success')
    {
        return $this->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
    }
    
    protected function created($data, $message = 'Created successfully')
    {
        return $this->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], 201);
    }
    
    protected function notFound($message = 'Resource not found')
    {
        return $this->json([
            'success' => false,
            'message' => $message
        ], 404);
    }
    
    protected function validationError($errors)
    {
        return $this->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $errors
        ], 422);
    }
}

class ProductController extends Controller
{
    use ResponseHelpers;
    
    public function show($id)
    {
        $product = Product::find($id);
        return $product ? $this->success($product) : $this->notFound();
    }
}
```

This controller system provides a clean, maintainable way to handle HTTP requests while leveraging PHP's built-in features and object-oriented programming concepts.

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
