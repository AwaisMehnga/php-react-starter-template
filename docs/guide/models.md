---
layout: default
title: Models
nav_order: 4
---

# Models
{: .no_toc }

Models represent your application's data and business logic, providing an elegant interface to interact with your database.
{: .fs-6 .fw-300 }

## Table of contents
{: .no_toc .text-delta }

1. TOC
{:toc}

---

## Overview

Models in this template provide:
- Eloquent-style database interactions
- Automatic relationship mapping
- Built-in validation
- Query builder methods
- Data serialization

---

## Creating Models

### Basic Model

Create a new model in `app/Models/`:

```php
<?php

namespace App\Models;

use App\Core\Model;

class Post extends Model
{
    /**
     * The table associated with the model
     */
    protected static $table = 'posts';
    
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
