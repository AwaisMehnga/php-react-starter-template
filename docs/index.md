---
layout: default
title: PHP React MVC Template
nav_order: 1
description: "A modern PHP template with Laravel-style MVC architecture and React SPA integration"
permalink: /
---

# PHP React MVC Template
{: .fs-9 }

A modern, production-ready PHP template combining Laravel-style MVC architecture with React SPAs powered by Vite.
{: .fs-6 .fw-300 }

[Get started now](#quick-start){: .btn .btn-primary .fs-5 .mb-4 .mb-md-0 .mr-2 }
[View on GitHub](https://github.com/AwaisMehnga/php-react-starter-template){: .btn .fs-5 .mb-4 .mb-md-0 }

---

## Features

✨ **Laravel-style MVC Architecture**
- Clean separation of concerns
- Eloquent-like models with database integration
- Middleware pipeline for request handling
- Flexible routing with FastRoute

⚛️ **React SPA Integration**  
- Multiple React applications in one project
- Vite for lightning-fast development
- Code splitting and optimization
- Hot module replacement

🗄️ **Database Integration**
- PDO with prepared statements
- Migration-like SQL setup
- Model relationships and queries
- Transaction support

🛡️ **Security & Best Practices**
- CSRF protection middleware
- Password hashing
- Role-based access control
- Input validation helpers

📦 **Developer Experience**
- PSR-4 autoloading
- Composer dependency management
- One-command project setup
- Comprehensive documentation

---

## Quick Start

### 1. Clone the Template

```bash
git clone https://github.com/AwaisMehnga/php-react-starter-template.git my-project
cd my-project
```

### 2. Run Setup

```bash
php setup.php
```

This interactive setup will:
- Configure your project details
- Set up database connection
- Customize SPAs and routes
- Install dependencies

### 3. Start Development

```bash
# Start your web server (XAMPP, etc.)
# Visit http://localhost/my-project
```

---

## Architecture Overview

```
📁 Project Structure
├── app/
│   ├── Controllers/     # MVC Controllers
│   ├── Models/         # Data Models  
│   ├── Middleware/     # Request Middleware
│   └── Core/          # Framework Core
├── views/             # PHP Views
├── modules/           # React SPAs
├── routes/            # Route Definitions
├── config/            # Configuration
├── database/          # Database Schema
└── docs/             # Documentation
```

### MVC Flow

```mermaid
graph LR
    A[Request] --> B[Router]
    B --> C[Middleware]
    C --> D[Controller]
    D --> E[Model]
    E --> F[Database]
    D --> G[View/JSON]
    G --> H[Response]
```

---

## Quick Examples

### Controller
```php
<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        $this->view('users/index', ['users' => $users]);
    }
    
    public function show($id)
    {
        $user = User::find($id);
        $this->view('users/show', ['user' => $user]);
    }
}
```

### Routing
```php
// Simple routes
Route::get('/users', [UserController::class, 'index']);
Route::get('/users/{id}', [UserController::class, 'show']);

// Route groups with middleware
Route::group(['prefix' => 'admin', 'middleware' => ['auth']], function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard']);
});
```

### Model
```php
<?php
namespace App\Models;

use App\Core\Model;

class User extends Model
{
    protected $fillable = ['name', 'email', 'password'];
    
    public static function findByEmail($email)
    {
        return static::where('email', $email)->first();
    }
}
```

### React SPA
```jsx
// modules/Dashboard/App.jsx
import React from 'react';

function Dashboard() {
    return (
        <div>
            <h1>Dashboard</h1>
            <p>Your React SPA content here</p>
        </div>
    );
}

export default Dashboard;
```

---

## Documentation

<div class="grid">
  <div class="grid-item">
    <h3><a href="guide/getting-started">🚀 Getting Started</a></h3>
    <p>Installation, setup, and first steps</p>
  </div>
  
  <div class="grid-item">
    <h3><a href="guide/controllers">🎮 Controllers</a></h3>
    <p>Creating and organizing controllers</p>
  </div>
  
  <div class="grid-item">
    <h3><a href="guide/models">🗃️ Models</a></h3>
    <p>Database models and relationships</p>
  </div>
  
  <div class="grid-item">
    <h3><a href="guide/routing">🛣️ Routing</a></h3>
    <p>Route definitions and middleware</p>
  </div>
  
  <div class="grid-item">
    <h3><a href="guide/spa-development">⚛️ React SPAs</a></h3>
    <p>Building React applications with Vite</p>
  </div>
  
  <div class="grid-item">
    <h3><a href="guide/database">🗄️ Database</a></h3>
    <p>Migrations, queries, and relationships</p>
  </div>
  
  <div class="grid-item">
    <h3><a href="guide/middleware">🔒 Middleware</a></h3>
    <p>Authentication, CORS, and request processing</p>
  </div>
  
  <div class="grid-item">
    <h3><a href="guide/deployment">🚀 Deployment</a></h3>
    <p>Production deployment and optimization</p>
  </div>
</div>

---

## Contributing

We welcome contributions! Please see our [Contributing Guide](contributing) for details.

---

## License

This template is open source and available under the [MIT License](https://github.com/AwaisMehnga/php-react-starter-template/blob/main/LICENSE).

<style>
.grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
  margin: 20px 0;
}

.grid-item {
  padding: 20px;
  border: 1px solid #e1e4e8;
  border-radius: 6px;
  background: #f6f8fa;
}

.grid-item h3 {
  margin-top: 0;
}

.grid-item h3 a {
  text-decoration: none;
  color: #0366d6;
}

.grid-item p {
  margin-bottom: 0;
  color: #586069;
}
</style>
