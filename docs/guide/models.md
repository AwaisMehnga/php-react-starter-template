---
layout: default
title: Models
nav_order: 4
---

# Models
{: .no_toc }

Models implement the **Active Record** pattern to provide an object-oriented interface for database operations, encapsulating both data and behavior in a single class.
{: .fs-6 .fw-300 }

## Table of contents
{: .no_toc .text-delta }

1. TOC
{:toc}

---

## How Models Work Internally

### The Active Record Pattern

The Model system implements the **Active Record** design pattern where each model instance represents a database row and provides methods to manipulate that data:

```php
<?php
namespace App\Core;

class Model
{
    protected static $table;        // Database table name
    protected $attributes = [];     // Object properties/data
    protected $fillable = [];       // Mass-assignable attributes
    
    public function __construct($attributes = [])
    {
        $this->fill($attributes); // Populate model with data
    }
}
```

### Database Connection Singleton

The Database class uses the **Singleton** pattern to ensure a single database connection:

```php
class Database
{
    private static $instance = null;
    private $connection; // PDO instance
    
    private function __construct()
    {
        $this->config = require_once __DIR__ . '/../../config/database.php';
        $this->connect();
    }
    
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
```

### Magic Methods for Property Access

Models use PHP's **magic methods** to provide elegant property access:

```php
class Model
{
    /**
     * Get an attribute value using __get magic method
     */
    public function __get($key)
    {
        return isset($this->attributes[$key]) ? $this->attributes[$key] : null;
    }
    
    /**
     * Set an attribute value using __set magic method
     */
    public function __set($key, $value)
    {
        $this->attributes[$key] = $value;
    }
    
    /**
     * Check if an attribute exists using __isset magic method
     */
    public function __isset($key)
    {
        return isset($this->attributes[$key]);
    }
}

// Usage examples:
$user = new User(['name' => 'John']);
echo $user->name;        // Calls __get('name')
$user->email = 'j@ex.com'; // Calls __set('email', 'j@ex.com')
if (isset($user->phone)) { /* Calls __isset('phone') */ }
```

---

## Programming Concepts in Models

### 1. Static Factory Methods

Models use static methods to create instances from database queries:

```php
class User extends Model
{
    /**
     * Factory method for finding by ID
     */
    public static function find($id)
    {
        $db = Database::getInstance();
        $result = $db->fetch("SELECT * FROM " . static::$table . " WHERE id = ?", [$id]);
        
        // Return new instance or null
        return $result ? new static($result) : null;
    }
    
    /**
     * Factory method for creating from array
     */
    public static function create($attributes = [])
    {
        $instance = new static($attributes);
        $instance->save(); // Persist to database
        return $instance;
    }
}
```

### 2. Mass Assignment Protection

The `fillable` property implements **mass assignment protection**:

```php
public function fill($attributes)
{
    foreach ($attributes as $key => $value) {
        // Only allow attributes listed in $fillable array
        if (empty($this->fillable) || in_array($key, $this->fillable)) {
            $this->attributes[$key] = $value;
        }
    }
    return $this;
}

// Protected against mass assignment attacks:
class User extends Model
{
    protected $fillable = ['name', 'email']; // 'role' not included
}

// This won't set the 'role' attribute:
$user = User::create([
    'name' => 'John',
    'email' => 'john@example.com',
    'role' => 'admin' // Ignored due to mass assignment protection
]);
```

### 3. Query Builder Pattern

Models implement a simple query builder using method chaining:

```php
class User extends Model
{
    public static function where($column, $operator, $value)
    {
        return new QueryBuilder(static::class, [
            'where' => [$column, $operator, $value]
        ]);
    }
    
    public static function orderBy($column, $direction = 'ASC')
    {
        return new QueryBuilder(static::class, [
            'orderBy' => [$column, $direction]
        ]);
    }
}

class QueryBuilder
{
    private $model;
    private $conditions = [];
    
    public function __construct($model, $conditions = [])
    {
        $this->model = $model;
        $this->conditions = $conditions;
    }
    
    public function where($column, $operator, $value)
    {
        $this->conditions['where'][] = [$column, $operator, $value];
        return $this; // Method chaining
    }
    
    public function get()
    {
        // Build SQL from conditions
        $sql = $this->buildSQL();
        $results = Database::getInstance()->fetchAll($sql);
        
        // Convert results to model instances
        return array_map(function($row) {
            return new $this->model($row);
        }, $results);
    }
}

// Usage:
$users = User::where('role', '=', 'admin')
             ->where('is_active', '=', 1)
             ->orderBy('created_at', 'DESC')
             ->get();
```

---

## Advanced Model Concepts

### 1. Model Relationships

Implement relationships using **lazy loading**:

```php
class User extends Model
{
    /**
     * One-to-Many relationship: User has many posts
     */
    public function posts()
    {
        if (!isset($this->relations['posts'])) {
            $this->relations['posts'] = Post::where('user_id', '=', $this->id)->get();
        }
        return $this->relations['posts'];
    }
    
    /**
     * Belongs-to relationship: User belongs to a role
     */
    public function role()
    {
        if (!isset($this->relations['role'])) {
            $this->relations['role'] = Role::find($this->role_id);
        }
        return $this->relations['role'];
    }
    
    /**
     * Many-to-Many relationship: User has many permissions
     */
    public function permissions()
    {
        if (!isset($this->relations['permissions'])) {
            $db = Database::getInstance();
            $results = $db->fetchAll("
                SELECT p.* FROM permissions p
                JOIN user_permissions up ON p.id = up.permission_id
                WHERE up.user_id = ?
            ", [$this->id]);
            
            $this->relations['permissions'] = array_map(function($row) {
                return new Permission($row);
            }, $results);
        }
        return $this->relations['permissions'];
    }
}
```

### 2. Model Events and Observers

Implement the **Observer Pattern** for model events:

```php
class Model
{
    protected static $observers = [];
    
    public static function observe($observer)
    {
        static::$observers[] = $observer;
    }
    
    public function save()
    {
        // Fire 'saving' event
        $this->fireEvent('saving');
        
        if (isset($this->attributes['id'])) {
            $this->update();
        } else {
            $this->insert();
        }
        
        // Fire 'saved' event
        $this->fireEvent('saved');
        
        return $this;
    }
    
    private function fireEvent($event)
    {
        foreach (static::$observers as $observer) {
            if (method_exists($observer, $event)) {
                $observer->$event($this);
            }
        }
    }
}

class UserObserver
{
    public function saving($user)
    {
        // Hash password before saving
        if (isset($user->password)) {
            $user->password = password_hash($user->password, PASSWORD_DEFAULT);
        }
    }
    
    public function saved($user)
    {
        // Send welcome email after user is saved
        if ($user->wasRecentlyCreated()) {
            Mail::send('welcome', $user);
        }
    }
}

// Register observer
User::observe(new UserObserver());
```

### 3. Attribute Casting

Implement automatic type casting for attributes:

```php
class Model
{
    protected $casts = [];
    
    public function __get($key)
    {
        $value = $this->attributes[$key] ?? null;
        
        // Apply casting if defined
        if (isset($this->casts[$key])) {
            return $this->castAttribute($key, $value);
        }
        
        return $value;
    }
    
    private function castAttribute($key, $value)
    {
        $type = $this->casts[$key];
        
        switch ($type) {
            case 'int':
            case 'integer':
                return (int) $value;
                
            case 'float':
            case 'double':
                return (float) $value;
                
            case 'bool':
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
                
            case 'array':
            case 'json':
                return json_decode($value, true);
                
            case 'date':
                return new DateTime($value);
                
            default:
                return $value;
        }
    }
}

class User extends Model
{
    protected $casts = [
        'is_active' => 'boolean',
        'metadata' => 'json',
        'created_at' => 'date',
        'login_count' => 'integer'
    ];
}

// Usage:
$user = User::find(1);
$isActive = $user->is_active;    // Returns boolean, not string
$metadata = $user->metadata;     // Returns array, not JSON string
$createdAt = $user->created_at;  // Returns DateTime object
```

---

## Real-World Model Examples

### 1. User Model with Authentication

```php
class User extends Model
{
    protected static $table = 'users';
    protected $fillable = ['name', 'email', 'password', 'role'];
    protected $hidden = ['password']; // Hidden from JSON serialization
    
    protected $casts = [
        'is_active' => 'boolean',
        'last_login_at' => 'date',
        'settings' => 'json'
    ];
    
    /**
     * Hash password when setting
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = password_hash($value, PASSWORD_DEFAULT);
    }
    
    /**
     * Verify password
     */
    public function verifyPassword($password)
    {
        return password_verify($password, $this->attributes['password']);
    }
    
    /**
     * Scope for active users
     */
    public static function active()
    {
        return static::where('is_active', '=', true);
    }
    
    /**
     * Check if user has specific role
     */
    public function hasRole($role)
    {
        return $this->role === $role;
    }
    
    /**
     * Get full name accessor
     */
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
    
    /**
     * Override toArray to exclude hidden attributes
     */
    public function toArray()
    {
        $array = parent::toArray();
        
        foreach ($this->hidden as $hidden) {
            unset($array[$hidden]);
        }
        
        return $array;
    }
}
```

### 2. Post Model with Relationships

```php
class Post extends Model
{
    protected static $table = 'posts';
    protected $fillable = ['title', 'content', 'user_id', 'category_id'];
    
    protected $casts = [
        'published_at' => 'date',
        'is_published' => 'boolean',
        'view_count' => 'integer'
    ];
    
    /**
     * Belongs to User
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    /**
     * Belongs to Category
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    
    /**
     * Has many Comments
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
    
    /**
     * Many to Many Tags
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'post_tags');
    }
    
    /**
     * Scope for published posts
     */
    public static function published()
    {
        return static::where('is_published', '=', true)
                    ->where('published_at', '<=', date('Y-m-d H:i:s'));
    }
    
    /**
     * Get excerpt from content
     */
    public function getExcerptAttribute($length = 150)
    {
        return strlen($this->content) > $length 
            ? substr($this->content, 0, $length) . '...'
            : $this->content;
    }
    
    /**
     * Increment view count
     */
    public function incrementViews()
    {
        $this->view_count = ($this->view_count ?? 0) + 1;
        return $this->save();
    }
    
    // Helper method for relationships
    private function belongsTo($model, $foreignKey = null)
    {
        $foreignKey = $foreignKey ?? strtolower(class_basename($model)) . '_id';
        return $model::find($this->$foreignKey);
    }
    
    private function hasMany($model)
    {
        $foreignKey = strtolower(class_basename(static::class)) . '_id';
        return $model::where($foreignKey, '=', $this->id);
    }
}
```

### 3. Shopping Cart Model

```php
class Cart extends Model
{
    protected static $table = 'carts';
    protected $fillable = ['user_id', 'session_id'];
    
    /**
     * Cart items relationship
     */
    public function items()
    {
        if (!isset($this->relations['items'])) {
            $this->relations['items'] = CartItem::where('cart_id', '=', $this->id)->get();
        }
        return $this->relations['items'];
    }
    
    /**
     * Add item to cart
     */
    public function addItem($productId, $quantity = 1, $options = [])
    {
        $existingItem = $this->findItem($productId, $options);
        
        if ($existingItem) {
            $existingItem->quantity += $quantity;
            $existingItem->save();
            return $existingItem;
        }
        
        return CartItem::create([
            'cart_id' => $this->id,
            'product_id' => $productId,
            'quantity' => $quantity,
            'options' => json_encode($options)
        ]);
    }
    
    /**
     * Remove item from cart
     */
    public function removeItem($productId, $options = [])
    {
        $item = $this->findItem($productId, $options);
        return $item ? $item->delete() : false;
    }
    
    /**
     * Calculate total price
     */
    public function getTotalAttribute()
    {
        return array_reduce($this->items(), function($total, $item) {
            return $total + ($item->price * $item->quantity);
        }, 0);
    }
    
    /**
     * Get item count
     */
    public function getItemCountAttribute()
    {
        return array_sum(array_column($this->items(), 'quantity'));
    }
    
    /**
     * Clear all items
     */
    public function clear()
    {
        foreach ($this->items() as $item) {
            $item->delete();
        }
        unset($this->relations['items']); // Clear cached items
    }
    
    /**
     * Find specific item in cart
     */
    private function findItem($productId, $options = [])
    {
        $optionsJson = json_encode($options);
        
        foreach ($this->items() as $item) {
            if ($item->product_id == $productId && $item->options === $optionsJson) {
                return $item;
            }
        }
        
        return null;
    }
}
```

### 4. Model with File Uploads

```php
class Product extends Model
{
    protected static $table = 'products';
    protected $fillable = ['name', 'description', 'price', 'category_id'];
    
    protected $casts = [
        'price' => 'float',
        'is_active' => 'boolean',
        'images' => 'json'
    ];
    
    /**
     * Upload and attach image
     */
    public function addImage($uploadedFile)
    {
        if (!$this->isValidImage($uploadedFile)) {
            throw new InvalidArgumentException('Invalid image file');
        }
        
        $filename = $this->generateImageFilename($uploadedFile);
        $path = $this->getImagePath() . $filename;
        
        if (move_uploaded_file($uploadedFile['tmp_name'], $path)) {
            $images = $this->images ?? [];
            $images[] = $filename;
            $this->images = $images;
            $this->save();
            
            return $filename;
        }
        
        throw new RuntimeException('Failed to upload image');
    }
    
    /**
     * Get all image URLs
     */
    public function getImageUrlsAttribute()
    {
        return array_map(function($filename) {
            return '/uploads/products/' . $filename;
        }, $this->images ?? []);
    }
    
    /**
     * Get primary image URL
     */
    public function getPrimaryImageAttribute()
    {
        $images = $this->images ?? [];
        return !empty($images) ? '/uploads/products/' . $images[0] : '/images/no-image.png';
    }
    
    /**
     * Remove image
     */
    public function removeImage($filename)
    {
        $images = $this->images ?? [];
        $images = array_filter($images, function($img) use ($filename) {
            return $img !== $filename;
        });
        
        $this->images = array_values($images);
        $this->save();
        
        // Delete physical file
        $path = $this->getImagePath() . $filename;
        if (file_exists($path)) {
            unlink($path);
        }
    }
    
    private function isValidImage($file)
    {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        
        return in_array($mimeType, $allowedTypes) && $file['error'] === UPLOAD_ERR_OK;
    }
    
    private function generateImageFilename($file)
    {
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        return hash('sha256', $file['name'] . time()) . '.' . $extension;
    }
    
    private function getImagePath()
    {
        $path = __DIR__ . '/../../public/uploads/products/';
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
        return $path;
    }
}
```

This model system provides a clean, intuitive way to work with database records while leveraging PHP's object-oriented features and design patterns.
    
    /**
     * The primary key for the model
     */
    protected static $primaryKey = 'id';
    
    /**
     * The attributes that are mass assignable
     */
    protected $fillable = [
        'title', 'content', 'excerpt', 'status', 'user_id'
    ];
    
    /**
     * The attributes that should be hidden for serialization
     */
    protected $hidden = [
        'created_at', 'updated_at'
    ];
}
```

### Model with Relationships

```php
<?php

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    protected static $table = 'users';
    
    protected $fillable = [
        'name', 'email', 'password', 'role'
    ];
    
    protected $hidden = [
        'password', 'remember_token'
    ];
    
    /**
     * Get posts belonging to this user
     */
    public function posts()
    {
        return $this->hasMany(Post::class, 'user_id');
    }
    
    /**
     * Get user profile
     */
    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }
    
    /**
     * Get user roles
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }
}
```

---

## Database Operations

### Basic CRUD Operations

```php
// Create
$user = User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => 'secret123'
]);

// Read
$users = User::all();
$user = User::find(1);
$user = User::findByEmail('john@example.com');

// Update
$user = User::find(1);
$user->name = 'Jane Doe';
$user->save();

// Delete
$user = User::find(1);
$user->delete();
```

### Query Builder Methods

```php
// Where clauses
$activeUsers = User::where('status', 'active')->get();
$recentUsers = User::where('created_at', '>', '2024-01-01')->get();

// Multiple conditions
$users = User::where('role', 'admin')
              ->where('status', 'active')
              ->get();

// OR conditions
$users = User::where('role', 'admin')
              ->orWhere('role', 'moderator')
              ->get();

// Ordering
$users = User::orderBy('created_at', 'desc')->get();
$users = User::orderBy('name', 'asc')->get();

// Limiting
$users = User::limit(10)->get();
$users = User::take(5)->get();

// Pagination
$users = User::paginate(20);
```

### Advanced Queries

```php
// Join operations
$posts = Post::join('users', 'posts.user_id', '=', 'users.id')
             ->select('posts.*', 'users.name as author_name')
             ->get();

// Aggregates
$userCount = User::count();
$averageAge = User::average('age');
$maxSalary = User::max('salary');

// Grouping
$usersByRole = User::groupBy('role')->count();

// Raw queries
$users = User::raw("SELECT * FROM users WHERE email LIKE '%@gmail.com'");
```

---

## Relationships

### One-to-One

```php
class User extends Model
{
    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }
}

class UserProfile extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

// Usage
$user = User::find(1);
$profile = $user->profile;
$userName = $profile->user->name;
```

### One-to-Many

```php
class User extends Model
{
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}

class Post extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

// Usage
$user = User::find(1);
$posts = $user->posts;

$post = Post::find(1);
$author = $post->user;
```

### Many-to-Many

```php
class User extends Model
{
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }
}

class Role extends Model
{
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles');
    }
}

// Usage
$user = User::find(1);
$roles = $user->roles;

$role = Role::find(1);
$users = $role->users;

// Attach/Detach
$user->roles()->attach($roleId);
$user->roles()->detach($roleId);
```

---

## Model Features

### Accessors and Mutators

```php
class User extends Model
{
    /**
     * Get the user's full name
     */
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
    
    /**
     * Set the user's password (auto-hash)
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = password_hash($value, PASSWORD_DEFAULT);
    }
    
    /**
     * Get formatted created date
     */
    public function getCreatedDateAttribute()
    {
        return date('M j, Y', strtotime($this->created_at));
    }
}

// Usage
$user = User::find(1);
echo $user->full_name; // "John Doe"
echo $user->created_date; // "Jan 15, 2024"

$user->password = 'newpassword'; // Automatically hashed
```

### Scopes

```php
class Post extends Model
{
    /**
     * Scope for published posts
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
    
    /**
     * Scope for recent posts
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', date('Y-m-d', strtotime("-{$days} days")));
    }
    
    /**
     * Scope for posts by category
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }
}

// Usage
$publishedPosts = Post::published()->get();
$recentPosts = Post::recent(30)->get();
$categoryPosts = Post::byCategory(1)->published()->get();
```

### Model Events

```php
class User extends Model
{
    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();
        
        // Before creating
        static::creating(function ($user) {
            $user->uuid = generateUuid();
        });
        
        // After creating
        static::created(function ($user) {
            // Send welcome email
            Mail::send('welcome', $user);
        });
        
        // Before updating
        static::updating(function ($user) {
            $user->updated_at = now();
        });
        
        // Before deleting
        static::deleting(function ($user) {
            // Delete related records
            $user->posts()->delete();
        });
    }
}
```

---

## Validation

### Model Validation

```php
class User extends Model
{
    /**
     * Validation rules
     */
    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:8'
    ];
    
    /**
     * Validate model data
     */
    public function validate($data = null)
    {
        $data = $data ?: $this->attributes;
        $errors = [];
        
        foreach ($this->rules as $field => $rule) {
            $value = $data[$field] ?? null;
            
            if (strpos($rule, 'required') !== false && empty($value)) {
                $errors[$field] = ucfirst($field) . ' is required';
            }
            
            if (strpos($rule, 'email') !== false && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors[$field] = 'Invalid email format';
            }
            
            // Add more validation logic
        }
        
        return $errors;
    }
    
    /**
     * Save with validation
     */
    public function save()
    {
        $errors = $this->validate();
        
        if (!empty($errors)) {
            throw new ValidationException($errors);
        }
        
        return parent::save();
    }
}
```

---

## Data Serialization

### JSON Serialization

```php
class User extends Model
{
    /**
     * Convert model to array
     */
    public function toArray()
    {
        $array = parent::toArray();
        
        // Add computed attributes
        $array['full_name'] = $this->getFullNameAttribute();
        $array['avatar_url'] = $this->getAvatarUrlAttribute();
        
        // Remove sensitive data
        unset($array['password']);
        
        return $array;
    }
    
    /**
     * Convert model to JSON
     */
    public function toJson()
    {
        return json_encode($this->toArray());
    }
}

// Usage
$user = User::find(1);
$array = $user->toArray();
$json = $user->toJson();

// Multiple models
$users = User::all();
$json = json_encode($users);
```

---

## Advanced Features

### Custom Query Builder

```php
class User extends Model
{
    /**
     * Custom query builder
     */
    public static function query()
    {
        return new UserQueryBuilder(static::class);
    }
}

class UserQueryBuilder extends QueryBuilder
{
    public function active()
    {
        return $this->where('status', 'active');
    }
    
    public function byRole($role)
    {
        return $this->where('role', $role);
    }
    
    public function withPosts()
    {
        return $this->with('posts');
    }
}

// Usage
$activeAdmins = User::query()
                   ->active()
                   ->byRole('admin')
                   ->withPosts()
                   ->get();
```

### Model Factories

```php
class UserFactory
{
    public static function create($attributes = [])
    {
        $defaults = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'role' => 'user',
            'status' => 'active'
        ];
        
        $attributes = array_merge($defaults, $attributes);
        
        return User::create($attributes);
    }
    
    public static function admin($attributes = [])
    {
        return self::create(array_merge(['role' => 'admin'], $attributes));
    }
}

// Usage
$user = UserFactory::create();
$admin = UserFactory::admin(['name' => 'Admin User']);
```

---

## Testing Models

### Unit Testing

```php
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testUserCreation()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password'
        ]);
        
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('Test User', $user->name);
        $this->assertTrue(password_verify('password', $user->password));
    }
    
    public function testUserValidation()
    {
        $user = new User();
        $errors = $user->validate([
            'name' => '',
            'email' => 'invalid-email'
        ]);
        
        $this->assertArrayHasKey('name', $errors);
        $this->assertArrayHasKey('email', $errors);
    }
    
    public function testUserRelationships()
    {
        $user = UserFactory::create();
        $post = PostFactory::create(['user_id' => $user->id]);
        
        $this->assertEquals(1, $user->posts()->count());
        $this->assertEquals($user->id, $post->user->id);
    }
}
```

---

## Best Practices

### 1. Use Meaningful Names

```php
// ❌ Poor naming
class U extends Model {}

// ✅ Clear naming  
class User extends Model {}
```

### 2. Define Relationships Clearly

```php
class Post extends Model
{
    // ❌ Unclear relationship
    public function u() { return $this->belongsTo(User::class); }
    
    // ✅ Clear relationship
    public function author() { return $this->belongsTo(User::class, 'user_id'); }
}
```

### 3. Use Mass Assignment Protection

```php
class User extends Model
{
    // Define fillable attributes
    protected $fillable = ['name', 'email'];
    
    // OR define guarded attributes
    protected $guarded = ['id', 'password_reset_token'];
}
```

### 4. Implement Soft Deletes

```php
class Post extends Model
{
    use SoftDeletes;
    
    protected $dates = ['deleted_at'];
}

// Usage
$post->delete(); // Soft delete
$post->forceDelete(); // Permanent delete
$posts = Post::withTrashed()->get(); // Include deleted
```

---

## Example Model Template

```php
<?php

namespace App\Models;

use App\Core\Model;

class YourModel extends Model
{
    /**
     * The table associated with the model
     */
    protected static $table = 'your_table';
    
    /**
     * The primary key for the model
     */
    protected static $primaryKey = 'id';
    
    /**
     * The attributes that are mass assignable
     */
    protected $fillable = [
        'field1', 'field2', 'field3'
    ];
    
    /**
     * The attributes that should be hidden for serialization
     */
    protected $hidden = [
        'sensitive_field'
    ];
    
    /**
     * Validation rules
     */
    protected $rules = [
        'field1' => 'required|string|max:255',
        'field2' => 'required|email'
    ];
    
    /**
     * Relationships
     */
    public function relatedModel()
    {
        return $this->belongsTo(RelatedModel::class);
    }
    
    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
    
    /**
     * Accessors
     */
    public function getFormattedNameAttribute()
    {
        return ucwords($this->name);
    }
    
    /**
     * Mutators
     */
    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower($value);
    }
}
```

This model foundation provides robust database interactions while maintaining clean, readable code.
