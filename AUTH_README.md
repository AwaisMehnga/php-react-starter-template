# Billo Craft - Authentication System

A complete Laravel-style authentication system for your PHP application.

## 🚀 Features

### ✅ Completed Features
- **User Registration** - Complete signup process with validation
- **User Login/Logout** - Secure authentication with session management
- **Password Hashing** - Bcrypt password encryption
- **Remember Me** - Persistent login functionality
- **Session Management** - Secure session handling
- **Middleware Protection** - Route protection for authenticated/guest users
- **Flash Messages** - User feedback for forms
- **Responsive UI** - Modern, mobile-friendly authentication pages

### 🔗 Available Routes

| Route | Method | Description | Middleware |
|-------|--------|-------------|------------|
| `/` | GET | Homepage | - |
| `/login` | GET/POST | Login form and authentication | guest |
| `/register` | GET/POST | Registration form | guest |
| `/dashboard` | GET | User dashboard | auth |
| `/logout` | GET/POST | Logout action | auth |
| `/forgot-password` | GET/POST | Password recovery | guest |

### 🛡️ Middleware

- **`auth`** - Protects routes for authenticated users only
- **`guest`** - Protects routes for non-authenticated users only (redirects logged-in users)

## 🏗️ Setup Instructions

### 1. Database Setup
```bash
# Create the database
php create-database.php

# Run migrations and create admin user
php setup-auth.php
```

### 2. Default Admin Credentials
- **Email:** admin@billoacraft.com
- **Password:** password

> ⚠️ **Important:** Change the default password after first login!

### 3. Test the System
1. Start your development server
2. Visit `/login` to test authentication
3. Use the admin credentials to login
4. Visit `/register` to test user registration

## 📁 File Structure

```
app/
├── Controllers/
│   └── AuthController.php      # Authentication logic
├── Core/
│   ├── Auth.php               # Authentication helper
│   └── Session.php            # Session management
├── Middleware/
│   ├── AuthMiddleware.php     # Authentication middleware
│   └── GuestMiddleware.php    # Guest-only middleware
└── Models/
    └── User.php               # User model

views/
├── auth/
│   ├── login.php             # Login form
│   ├── register.php          # Registration form
│   └── forgot-password.php   # Password recovery form
└── dashboard.php             # User dashboard

database/
└── migrations/
    └── 001_create_users_table.sql
```

## 🔧 Usage Examples

### Using Auth Helper
```php
// Check if user is authenticated
if (Auth::check()) {
    // User is logged in
}

// Get current user
$user = Auth::user();

// Get user ID
$userId = Auth::id();

// Check if user is guest
if (Auth::guest()) {
    // User is not logged in
}
```

### Protecting Routes
```php
// In routes/web.php
Route::get('/dashboard', [Controller::class, 'method'], ['auth']);
Route::get('/login', [AuthController::class, 'showLoginForm'], ['guest']);
```

### Flash Messages in Controllers
```php
// Redirect with error message
return $this->redirectBack(['error' => 'Invalid credentials']);

// Redirect with success message
return $this->redirectBack(['success' => 'Registration successful']);
```

### Session Management
```php
use App\Core\Session;

// Set session data
Session::put('key', 'value');

// Get session data
$value = Session::get('key', 'default');

// Flash data (available for next request only)
Session::flash('success', 'Operation completed');
```

## 🎨 Customization

### Styling
The authentication pages use inline CSS for simplicity. You can:
1. Extract CSS to separate files
2. Integrate with your existing CSS framework
3. Customize the color scheme and layout

### Views
All authentication views are located in `views/auth/`. Customize them to match your brand:
- `login.php` - Login form
- `register.php` - Registration form  
- `forgot-password.php` - Password recovery form

### Validation
Add custom validation rules in `AuthController.php`:
```php
// Example: Add password strength validation
if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password)) {
    $errors[] = 'Password must be at least 8 characters with uppercase letter';
}
```

## 🔐 Security Features

- **Password Hashing** - Uses PHP's `password_hash()` with bcrypt
- **Session Regeneration** - Session ID regenerated on login
- **CSRF Protection** - Ready for CSRF token implementation
- **Remember Token** - Secure persistent login
- **SQL Injection Protection** - Prepared statements used throughout

## 🚧 Future Enhancements

- [ ] Email verification
- [ ] Password reset via email
- [ ] Two-factor authentication
- [ ] User roles and permissions
- [ ] Account lockout after failed attempts
- [ ] OAuth social login
- [ ] API authentication (JWT)

## 🐛 Troubleshooting

### Database Connection Issues
1. Ensure XAMPP MySQL is running
2. Check database credentials in `config/database.php`
3. Verify database exists: `php create-database.php`

### Session Issues
1. Ensure PHP sessions are enabled
2. Check session permissions in php.ini
3. Verify session storage path is writable

### Route Issues
1. Check that middleware is properly registered in `router.php`
2. Verify route definitions in `routes/web.php`
3. Clear any route caches if implemented

## 📞 Support

For issues or questions:
1. Check the troubleshooting section above
2. Review the error logs
3. Verify all setup steps were completed

---

**Happy coding! 🎯**
