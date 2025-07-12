# PHP React MVC Template

A sophisticated web application framework implementing advanced design patterns and programming concepts. This template combines robust PHP MVC architecture with modern React SPAs, demonstrating enterprise-level software engineering principles.

## 🏗️ Architecture Overview

### Design Patterns Implemented

- **MVC (Model-View-Controller)**: Clean separation of presentation, business logic, and data layers
- **Singleton Pattern**: Database connection management and application instance control
- **Pipeline Pattern**: Middleware system using the Chain of Responsibility pattern
- **Active Record Pattern**: Object-oriented database interactions with automatic mapping
- **Registry Pattern**: Service location for middleware and route management
- **Factory Pattern**: Dynamic instantiation of controllers and models
- **Observer Pattern**: Model events and lifecycle hooks

### Request Processing Pipeline

```
HTTP Request → Router → Application → Middleware Pipeline → Controller → Model → Database
                                    ↓
HTTP Response ← View/JSON ← Controller ← Business Logic ← Data Layer
```

## 🔧 Core Components Deep Dive

### 1. Routing System

**Technology**: FastRoute library with custom middleware integration

**How it works**:
- Routes are compiled into optimized hash maps and regular expressions
- Static routes use O(1) hash lookups for maximum performance
- Dynamic routes use compiled regex patterns for parameter extraction
- Middleware is applied using functional programming concepts

```php
// Route definition with fluent interface
Route::get('/users/{id}', [UserController::class, 'show'])
     ->middleware(['auth', 'throttle:60,1']);

// Group routes with shared middleware and prefixes
Route::group(['prefix' => 'api', 'middleware' => 'cors'], function () {
    Route::resource('users', UserController::class);
});
```

### 2. Controller System

**Programming Concepts**: 
- Reflection API for automatic parameter injection
- Template Method Pattern for consistent structure
- Dependency Injection for testability

```php
class UserController extends Controller
{
    // Automatic parameter injection from route
    public function show($id)
    {
        $user = User::find($id);
        
        // Automatic content negotiation
        if ($this->expectsJson()) {
            return $this->json(['data' => $user]);
        }
        
        return $this->view('users.show', compact('user'));
    }
}
```

The setup script will guide you through:
- Project name and configuration
- Database connection setup
- SPA module configuration
- Dependency installation

### 3. Start Development

#### Using XAMPP
1. Place project in `xampp/htdocs/`
2. Start Apache and MySQL
3. Visit `http://localhost/my-project`

#### Using Built-in PHP Server
```bash
php -S localhost:8000 -t public
```

#### Development with Hot Reload
```bash
# Terminal 1: Start PHP server
php -S localhost:8000

# Terminal 2: Start Vite dev server
npm run dev
```

## 🏗️ Project Structure

```
my-project/
├── app/
│   ├── Controllers/     # MVC Controllers
│   ├── Models/         # Eloquent-style Models
│   ├── Middleware/     # Request/Response middleware
│   └── Core/          # Framework core classes
├── config/
│   ├── app.php        # Application configuration
│   └── database.php   # Database configuration
├── database/
│   ├── migrations/    # Database migration files
│   └── tool_site_db.sql # Complete database schema
├── modules/
│   ├── Home/          # React SPA for homepage
│   ├── Dashboard/     # React SPA for dashboard
│   └── shared/        # Shared React components
├── views/
│   ├── template/      # PHP view templates
│   └── errors/        # Error pages
├── routes/
│   └── web.php        # Route definitions
├── public/
│   └── index.php      # Application entry point
├── build/             # Built React assets (generated)
├── docs/              # Documentation (GitHub Pages)
└── setup.php          # Interactive setup script
```

## 📖 Documentation

Complete documentation is available in the `docs/` folder and can be deployed to GitHub Pages.

### Quick Links
- [Getting Started Guide](docs/getting-started.md)
- [Controllers](docs/guide/controllers.md)
- [Models & Database](docs/guide/models.md)
- [Routing](docs/guide/routing.md)
- [React SPA Development](docs/guide/spa-development.md)
- [Middleware](docs/guide/middleware.md)
- [Deployment Guide](docs/guide/deployment.md)

## 🔧 Configuration

### Environment Variables

Create a `.env` file:

```bash
# Application
APP_NAME="My Awesome App"
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=my_database
DB_USERNAME=root
DB_PASSWORD=

# Development
VITE_API_URL=http://localhost:8000/api
```

### Database Setup

1. **Automatic Setup**: Run `php setup.php` for guided configuration
2. **Manual Setup**: Import `database/tool_site_db.sql` to your MySQL server

## 🎯 Usage Examples

### Creating a Controller

```php
<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Post;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::with('user')->orderBy('created_at', 'desc')->get();
        return $this->view('posts/index', compact('posts'));
    }

    public function store()
    {
        $post = Post::create([
            'title' => $this->request->input('title'),
            'content' => $this->request->input('content'),
            'user_id' => auth()->id()
        ]);

        return $this->json(['success' => true, 'post' => $post]);
    }
}
```

### Defining Routes

```php
<?php
// routes/web.php

use App\Controllers\PostController;
use App\Controllers\DashboardController;

// Basic routes
Route::get('/', [HomeController::class, 'index']);
Route::get('/posts', [PostController::class, 'index']);

// Protected routes with middleware
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::resource('posts', PostController::class);
});

// API routes
Route::group(['prefix' => 'api'], function () {
    Route::get('/posts', [PostController::class, 'apiIndex']);
    Route::post('/posts', [PostController::class, 'store']);
});
```

### Creating Models

```php
<?php
namespace App\Models;

use App\Core\Model;

class Post extends Model
{
    protected $table = 'posts';
    protected $fillable = ['title', 'content', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'post_tags');
    }
}
```

### React SPA Development

```jsx
// modules/Dashboard/Dashboard.jsx
import React, { useState, useEffect } from 'react';
import api from '../shared/services/api';

function Dashboard() {
    const [data, setData] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        fetchData();
    }, []);

    const fetchData = async () => {
        try {
            const response = await api.get('/posts');
            setData(response.data);
        } catch (error) {
            console.error('Error:', error);
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="dashboard">
            <h1>Dashboard</h1>
            {loading ? (
                <div>Loading...</div>
            ) : (
                <div className="data-grid">
                    {data.map(item => (
                        <div key={item.id} className="data-card">
                            <h3>{item.title}</h3>
                            <p>{item.content}</p>
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
}

export default Dashboard;
```

## 🚀 Deployment

### Production Build

```bash
# Install production dependencies
composer install --no-dev --optimize-autoloader

# Build React assets
npm run build

# Run database migrations
php scripts/migrate.php
```

### Server Configuration

#### Apache (.htaccess)
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ /index.php [QSA,L]
```

#### Nginx
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ \.php$ {
    fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    include fastcgi_params;
}
```

## 🧪 Testing

### Backend Testing (PHPUnit)

```bash
# Run all tests
vendor/bin/phpunit

# Run specific test
vendor/bin/phpunit tests/Controllers/UserControllerTest.php
```

### Frontend Testing

```bash
# Run React tests
npm test

# Run with coverage
npm run test:coverage
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📝 License

This project is open source and available under the [MIT License](LICENSE).

## 🆘 Support

- **Documentation**: Complete guides in the `docs/` folder
- **Issues**: [GitHub Issues](https://github.com/your-username/php-react-starter-template/issues)
- **Discussions**: [GitHub Discussions](https://github.com/your-username/php-react-starter-template/discussions)

## 🙏 Acknowledgments

- Inspired by Laravel's elegant syntax and structure
- FastRoute for powerful routing capabilities
- Vite for blazing-fast development experience
- React community for excellent tools and practices

---

**Happy Coding!** 🎉

Made with ❤️ for developers who want to build modern full-stack applications with PHP and React.
