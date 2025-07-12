---
layout: default
title: Getting Started
nav_order: 2
---

# Getting Started
{: .no_toc }

This comprehensive guide explains how the PHP React MVC Template works internally and how to develop with it effectively.
{: .fs-6 .fw-300 }

## Table of contents
{: .no_toc .text-delta }

1. TOC
{:toc}

---

## System Architecture Overview

This template implements a sophisticated MVC (Model-View-Controller) architecture with several design patterns:

### Core Design Patterns

1. **Singleton Pattern**: The `Application` class uses the singleton pattern to ensure only one instance manages the entire request lifecycle.

2. **Pipeline Pattern**: Middleware is implemented using a pipeline pattern where each middleware is a layer that can process, modify, or terminate the request flow.

3. **Registry Pattern**: The Application class maintains a middleware registry that maps string identifiers to middleware classes.

4. **Dependency Injection**: Controllers automatically receive route parameters through PHP's Reflection API.

5. **Active Record Pattern**: Models provide an object-oriented interface to database records.

### Request Flow Architecture

```
HTTP Request → Router → Application → Middleware Pipeline → Controller → Model → Database
                                                         ↓
HTTP Response ← View/JSON ← Controller ← Model ← Database
```

---

## Prerequisites

Before you begin, ensure you have the following installed:

- **PHP 8.0+** with extensions:
  - PDO
  - PDO_MySQL  
  - JSON
  - OpenSSL
- **Composer** for dependency management
- **Node.js 16+** and **npm** for React development
- **Web Server** (Apache/Nginx) or **XAMPP** for local development
- **MySQL 5.7+** or **MariaDB** for database

---

## Installation

### 1. Clone the Repository

```bash
# Clone the template
git clone https://github.com/AwaisMehnga/php-react-starter-template.git my-project
cd my-project

# Remove git history to start fresh
rm -rf .git
git init
```

### 2. Run Interactive Setup

The template includes an interactive setup script that configures everything for you:

```bash
php setup.php
```

#### Setup Process

The setup script will ask you for:

**Project Information:**
- Project name (e.g., "My Awesome App")
- Description
- Author details

**Database Configuration:**
- Database name
- Host (default: localhost)
- Username (default: root)  
- Password

**React SPA Configuration:**
- Default SPA name
- Application URL

#### What the Setup Does

1. **Updates project files** with your configuration
2. **Configures database** connection
3. **Sets up database** schema with sample data
4. **Updates Composer** autoloader
5. **Customizes views** with your project name

### 3. Manual Setup (Alternative)

If you prefer manual setup:

```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies  
npm install

# Copy and edit configuration
cp config/database.php.example config/database.php
# Edit database credentials

# Setup database
php setup_database.php

# Build assets
npm run build
```

---

## Project Structure

After setup, your project will have this structure:

```
my-project/
├── app/                    # MVC Application
│   ├── Controllers/        # HTTP Controllers
│   ├── Models/            # Data Models
│   ├── Middleware/        # Request Middleware
│   └── Core/             # Framework Core Classes
├── config/               # Configuration Files
│   └── database.php      # Database Config
├── database/             # Database Schema
│   └── schema.sql        # Database Structure
├── modules/              # React SPA Modules
│   ├── Home/            # Example React App
│   └── Dashboard/       # Another React App
├── views/               # PHP Views/Templates
│   ├── template/        # Shared Templates
│   └── pages/          # Page Views
├── routes/              # Route Definitions
│   └── web.php         # Web Routes
├── public/             # Public Assets
├── build/              # Built Assets (Vite)
├── vendor/             # Composer Dependencies
├── node_modules/       # Node Dependencies
├── docs/              # Documentation
├── setup.php          # Setup Script
├── router.php         # Front Controller
├── composer.json      # PHP Dependencies
├── package.json       # Node Dependencies
├── vite.config.js     # Vite Configuration
└── README.md          # Project README
```

---

## Development Workflow

### 1. Start Web Server

**Using XAMPP:**
```bash
# Start Apache and MySQL in XAMPP Control Panel
# Access your project at: http://localhost/my-project
```

**Using PHP Built-in Server:**
```bash
php -S localhost:8000 router.php
# Access at: http://localhost:8000
```

### 2. Develop React SPAs

```bash
# Start Vite development server for hot reloading
npm run dev

# Build for production
npm run build
```

### 3. Test Your Application

Visit these URLs to verify everything works:

- **Home Page:** `http://localhost/my-project/`
- **MVC Demo:** `http://localhost/my-project/users`
- **API Endpoint:** `http://localhost/my-project/api/users/1`
- **Admin Area:** `http://localhost/my-project/admin/dashboard`

### 4. Sample Login Credentials

The setup creates sample users you can use:

- **Admin:** `admin@yourproject.com` / `password`
- **User:** `john@example.com` / `password`

---

## Configuration

### Database Configuration

Edit `config/database.php` to customize your database connection:

```php
<?php
return [
    'default' => 'mysql',
    'connections' => [
        'mysql' => [
            'host' => 'localhost',
            'database' => 'your_database',
            'username' => 'your_username', 
            'password' => 'your_password',
            'charset' => 'utf8mb4',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        ]
    ]
];
```

### Vite Configuration

Customize `vite.config.js` for your React SPAs:

```javascript
import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

export default defineConfig({
  plugins: [react()],
  build: {
    rollupOptions: {
      input: {
        // Add your React SPAs here
        'Home': './modules/Home/app.jsx',
        'Dashboard': './modules/Dashboard/app.jsx',
      }
    }
  }
})
```

---

## Next Steps

Now that your project is set up, explore these guides:

1. **[Controllers](controllers)** - Learn to create MVC controllers
2. **[Models](models)** - Work with database models  
3. **[Routing](routing)** - Define routes and middleware
4. **[React SPAs](spa-development)** - Build React applications
5. **[Database](database)** - Manage your database

---

## Troubleshooting

### Common Issues

**Database Connection Failed:**
```bash
# Check if MySQL is running
# Verify credentials in config/database.php
# Ensure database exists
```

**Class Not Found:**
```bash
# Regenerate autoloader
composer dump-autoload
```

**Vite Build Issues:**
```bash
# Clear npm cache
npm cache clean --force
npm install
```

**Permission Denied:**
```bash
# Set proper file permissions (Linux/Mac)
chmod -R 755 ./
```

### Getting Help

- **Documentation:** Browse the complete [guide](../guide/)
- **Issues:** Report bugs on [GitHub](https://github.com/AwaisMehnga/php-react-starter-template/issues)
- **Discussions:** Join [GitHub Discussions](https://github.com/AwaisMehnga/php-react-starter-template/discussions)
