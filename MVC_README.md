# MVC Architecture Implementation

This project now implements a clean MVC (Model-View-Controller) architecture pattern for better code organization and maintainability.

## Directory Structure

```
app/
├── Controllers/        # Application controllers
│   ├── HomeController.php
│   └── UserController.php
├── Models/            # Data models
│   └── User.php
└── Core/              # Core framework classes
    ├── Controller.php  # Base controller class
    ├── Model.php      # Base model class
    └── Database.php   # Database connection singleton

config/                # Configuration files
├── app.php           # Application configuration
└── database.php      # Database configuration

views/                # View templates
├── template/         # Template partials
├── user/            # User-related views
├── index.php        # Home view
├── awais.php        # Legacy view
└── 404.php          # Error view

routes/               # Route definitions
└── web.php          # Web routes
```

## Key Features

### 1. **Controllers**
- Handle business logic and coordinate between models and views
- Base controller provides common functionality
- Support for both HTML and JSON responses
- Built-in request handling methods

### 2. **Models**
- Handle data operations and database interactions
- Base model provides CRUD operations
- Support for custom queries and relationships
- Built-in data validation and filtering

### 3. **Views**
- Clean separation of presentation logic
- Template system with data passing
- Support for both PHP views and React frontend

### 4. **Routing**
- Controller-based routing with `Controller@method` syntax
- Support for route parameters
- Legacy view-based routing for backward compatibility

## Usage Examples

### Creating a Controller

```php
<?php
namespace App\Controllers;

use App\Core\Controller;

class ProductController extends Controller
{
    public function index()
    {
        $this->setData('title', 'Products');
        echo $this->render('products/index');
    }
    
    public function show(string $id)
    {
        $product = $this->productModel->find($id);
        $this->setData('product', $product);
        echo $this->render('products/show');
    }
}
```

### Creating a Model

```php
<?php
namespace App\Models;

use App\Core\Model;

class Product extends Model
{
    protected $table = 'products';
    protected $fillable = ['name', 'price', 'description'];
    
    public function findByCategory(string $category): array
    {
        return $this->findAll(['category' => $category]);
    }
}
```

### Adding Routes

```php
// routes/web.php
$Route->addRoute('GET', '/products', 'ProductController@index');
$Route->addRoute('GET', '/products/{id}', 'ProductController@show');
```

## Configuration

### Environment Variables
Copy `.env.example` to `.env` and configure your environment:

```env
APP_ENV=dev
DB_HOST=localhost
DB_NAME=tool_site
DB_USER=root
DB_PASSWORD=
```

### Database Setup
Configure your database connection in `config/database.php` or use environment variables.

## API Endpoints

The application supports both web and API routes:

- `GET /api` - API status
- `GET /api/user/{name}` - Get user data
- `POST /api/user` - Create user

## Frontend Integration

The MVC structure works seamlessly with your existing React frontend:

- Development mode uses Vite HMR
- Production mode serves built assets
- Configuration-based environment detection

## Backward Compatibility

All existing routes and views continue to work. The MVC implementation provides:

- Legacy route support
- Existing view compatibility
- Gradual migration path

## Best Practices

1. **Controllers**: Keep controllers thin, delegate to models and services
2. **Models**: Handle data logic, validation, and database operations
3. **Views**: Focus on presentation, avoid business logic
4. **Configuration**: Use environment variables for sensitive data
5. **Routing**: Use descriptive route names and controller methods

## Testing Your Implementation

1. Visit `/` - Home page (HomeController)
2. Visit `/user/john` - User profile page
3. Visit `/api/user/john` - JSON API response
4. Visit `/awais/test` - Legacy compatibility

The MVC architecture provides a solid foundation for scaling your application while maintaining clean, organized code.
