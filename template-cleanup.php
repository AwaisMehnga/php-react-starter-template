<?php
/**
 * Template Cleanup Script
 * 
 * This script prepares the project to be used as a template by:
 * - Removing demo/example content
 * - Creating placeholder files
 * - Resetting configuration to defaults
 * - Setting up GitHub Pages for documentation
 */

class TemplateCleanup
{
    private $projectRoot;
    
    public function __construct()
    {
        $this->projectRoot = dirname(__FILE__);
    }

    public function run()
    {
        echo "🧹 Starting template cleanup...\n";
        
        $this->removeExampleContent();
        $this->createPlaceholderFiles();
        $this->resetConfiguration();
        $this->setupGitHubPages();
        $this->createGitIgnore();
        $this->updateComposerJson();
        
        echo "✅ Template cleanup completed!\n";
        echo "\n";
        echo "📋 Next steps:\n";
        echo "1. Push to GitHub repository\n";
        echo "2. Enable GitHub Pages in repository settings\n";
        echo "3. Users can clone and run 'php setup.php' to start\n";
    }

    private function removeExampleContent()
    {
        echo "🗑️  Removing example content...\n";
        
        // Remove demo views
        $demoFiles = [
            'views/awais.php',
            'views/index.php'
        ];
        
        foreach ($demoFiles as $file) {
            if (file_exists($file)) {
                unlink($file);
                echo "   Removed: $file\n";
            }
        }
        
        // Clear build directory but keep structure
        if (is_dir('build')) {
            $this->deleteDirectoryContents('build');
            echo "   Cleared: build/ directory\n";
        }
        
        // Clear cache directories
        $cacheDirectories = ['cache/views', 'cache/routes', 'cache/config'];
        foreach ($cacheDirectories as $dir) {
            if (is_dir($dir)) {
                $this->deleteDirectoryContents($dir);
                echo "   Cleared: $dir/\n";
            }
        }
    }

    private function createPlaceholderFiles()
    {
        echo "📄 Creating placeholder files...\n";
        
        // Create placeholder views
        $this->createFile('views/home.php', $this->getHomeViewTemplate());
        $this->createFile('views/errors/404.php', $this->get404Template());
        $this->createFile('views/errors/500.php', $this->get500Template());
        
        // Create placeholder React modules
        $this->createFile('modules/App/app.jsx', $this->getReactAppTemplate());
        $this->createFile('modules/App/App.jsx', $this->getReactComponentTemplate());
        $this->createFile('modules/App/App.css', $this->getReactStylesTemplate());
        
        // Create example controller
        $this->createFile('app/Controllers/HomeController.php', $this->getHomeControllerTemplate());
        
        // Create placeholder routes
        $this->updateFile('routes/web.php', $this->getRoutesTemplate());
        
        echo "   Created placeholder files\n";
    }

    private function resetConfiguration()
    {
        echo "⚙️  Resetting configuration...\n";
        
        // Create template .env file
        $this->createFile('.env.example', $this->getEnvTemplate());
        
        // Remove existing .env if it exists
        if (file_exists('.env')) {
            unlink('.env');
            echo "   Removed existing .env file\n";
        }
        
        // Update vite.config.js for template
        $this->updateFile('vite.config.js', $this->getViteConfigTemplate());
        
        echo "   Configuration reset to defaults\n";
    }

    private function setupGitHubPages()
    {
        echo "📚 Setting up GitHub Pages...\n";
        
        // GitHub Pages workflow
        if (!is_dir('.github/workflows')) {
            mkdir('.github/workflows', 0755, true);
        }
        
        $this->createFile('.github/workflows/docs.yml', $this->getGitHubPagesWorkflow());
        
        echo "   Created GitHub Pages workflow\n";
    }

    private function createGitIgnore()
    {
        echo "🚫 Creating .gitignore...\n";
        
        $this->createFile('.gitignore', $this->getGitIgnoreTemplate());
        
        echo "   Created .gitignore file\n";
    }

    private function updateComposerJson()
    {
        echo "📦 Updating composer.json...\n";
        
        $composer = json_decode(file_get_contents('composer.json'), true);
        $composer['name'] = 'your-username/php-react-starter-template';
        $composer['description'] = 'Laravel-style MVC framework with React SPA integration';
        $composer['keywords'] = ['php', 'mvc', 'react', 'vite', 'template', 'laravel-style'];
        $composer['homepage'] = 'https://github.com/your-username/php-react-starter-template';
        
        file_put_contents('composer.json', json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        
        echo "   Updated composer.json\n";
    }

    // Template content methods
    private function getHomeViewTemplate()
    {
        return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? "My App" ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; text-align: center; }
        p { color: #666; line-height: 1.6; }
        .cta { text-align: center; margin-top: 30px; }
        .btn { display: inline-block; padding: 12px 24px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome to Your PHP React App!</h1>
        <p>Your Laravel-style MVC application with React integration is ready to go.</p>
        <p>This is a placeholder homepage. You can customize it by editing <code>views/home.php</code></p>
        <div class="cta">
            <a href="/docs" class="btn">View Documentation</a>
        </div>
    </div>
</body>
</html>';
    }

    private function get404Template()
    {
        return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; text-align: center; }
        .container { max-width: 600px; margin: 100px auto; background: white; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #e74c3c; font-size: 72px; margin: 0; }
        h2 { color: #333; margin: 20px 0; }
        p { color: #666; }
        a { color: #007bff; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <h1>404</h1>
        <h2>Page Not Found</h2>
        <p>The page you\'re looking for doesn\'t exist.</p>
        <a href="/">← Back to Home</a>
    </div>
</body>
</html>';
    }

    private function get500Template()
    {
        return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Server Error</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; text-align: center; }
        .container { max-width: 600px; margin: 100px auto; background: white; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #e74c3c; font-size: 72px; margin: 0; }
        h2 { color: #333; margin: 20px 0; }
        p { color: #666; }
        a { color: #007bff; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <h1>500</h1>
        <h2>Server Error</h2>
        <p>Something went wrong on our end.</p>
        <a href="/">← Back to Home</a>
    </div>
</body>
</html>';
    }

    private function getReactAppTemplate()
    {
        return "import React from 'react';
import ReactDOM from 'react-dom/client';
import App from './App.jsx';
import './App.css';

const root = ReactDOM.createRoot(document.getElementById('root'));
root.render(<App />);
";
    }

    private function getReactComponentTemplate()
    {
        return "import React, { useState } from 'react';

function App() {
    const [count, setCount] = useState(0);

    return (
        <div className=\"app\">
            <header className=\"app-header\">
                <h1>React SPA</h1>
                <p>Your React single-page application is ready!</p>
                <div className=\"counter\">
                    <button onClick={() => setCount(count - 1)}>-</button>
                    <span>Count: {count}</span>
                    <button onClick={() => setCount(count + 1)}>+</button>
                </div>
                <p>Edit <code>modules/App/App.jsx</code> to customize this page.</p>
            </header>
        </div>
    );
}

export default App;
";
    }

    private function getReactStylesTemplate()
    {
        return ".app {
    text-align: center;
    padding: 20px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.app-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 40px 20px;
    border-radius: 8px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.app-header h1 {
    margin: 0 0 20px 0;
    font-size: 2.5rem;
}

.counter {
    margin: 20px 0;
}

.counter button {
    background: #007bff;
    color: white;
    border: none;
    padding: 10px 20px;
    margin: 0 10px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
}

.counter button:hover {
    background: #0056b3;
}

.counter span {
    font-size: 18px;
    font-weight: bold;
}

code {
    background: rgba(255,255,255,0.1);
    padding: 2px 6px;
    border-radius: 3px;
    font-family: 'Courier New', monospace;
}
";
    }

    private function getHomeControllerTemplate()
    {
        return '<?php

namespace App\Controllers;

use App\Core\Controller;

class HomeController extends Controller
{
    public function index()
    {
        return $this->view(\'home\', [
            \'title\' => \'Welcome to My App\'
        ]);
    }
    
    public function spa()
    {
        return $this->view(\'template/react_shell\', [
            \'title\' => \'React SPA\',
            \'scripts\' => [\'/build/App/main.js\'],
            \'styles\' => [\'/build/App/App.css\']
        ]);
    }
}
';
    }

    private function getRoutesTemplate()
    {
        return '<?php

use App\Core\Route;
use App\Controllers\HomeController;

// Homepage
Route::get(\'/\', [HomeController::class, \'index\']);

// React SPA example
Route::get(\'/app\', [HomeController::class, \'spa\']);

// Add your routes here...
';
    }

    private function getEnvTemplate()
    {
        return '# Application Configuration
APP_NAME="My Awesome App"
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=my_database
DB_USERNAME=root
DB_PASSWORD=

# Frontend Development
VITE_API_URL=http://localhost:8000/api
VITE_APP_NAME="My Awesome App"
';
    }

    private function getViteConfigTemplate()
    {
        return "import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import { resolve } from 'path';

export default defineConfig({
    plugins: [react()],
    
    build: {
        rollupOptions: {
            input: {
                app: resolve(__dirname, 'modules/App/app.jsx'),
                // Add more SPAs here:
                // dashboard: resolve(__dirname, 'modules/Dashboard/app.jsx'),
            },
            output: {
                entryFileNames: '[name]/main.js',
                chunkFileNames: '[name]/chunks/[name].js',
                assetFileNames: '[name]/[name].[ext]'
            }
        },
        outDir: 'build',
    },
    
    server: {
        port: 3000,
        proxy: {
            '/api': 'http://localhost:8000'
        }
    }
});
";
    }

    private function getGitHubPagesWorkflow()
    {
        return 'name: Deploy Documentation to GitHub Pages

on:
  push:
    branches: [ main ]
  pull_request:
    branches: [ main ]

permissions:
  contents: read
  pages: write
  id-token: write

concurrency:
  group: "pages"
  cancel-in-progress: false

jobs:
  deploy:
    environment:
      name: github-pages
      url: ${{ steps.deployment.outputs.page_url }}
    runs-on: ubuntu-latest
    steps:
      - name: Checkout
        uses: actions/checkout@v3
        
      - name: Setup Pages
        uses: actions/configure-pages@v3
        
      - name: Build with Jekyll
        uses: actions/jekyll-build-pages@v1
        with:
          source: ./docs
          destination: ./_site
          
      - name: Upload artifact
        uses: actions/upload-pages-artifact@v2
        
      - name: Deploy to GitHub Pages
        id: deployment
        uses: actions/deploy-pages@v2
';
    }

    private function getGitIgnoreTemplate()
    {
        return '# Dependencies
node_modules/
vendor/

# Environment files
.env
.env.local
.env.production

# Build outputs
build/
dist/

# Cache directories
cache/
logs/

# IDE files
.vscode/
.idea/
*.swp
*.swo

# OS files
.DS_Store
Thumbs.db

# Temporary files
*.tmp
*.temp

# Database
*.sqlite
*.db

# Composer
composer.phar

# NPM
npm-debug.log*
yarn-debug.log*
yarn-error.log*
';
    }

    // Utility methods
    private function createFile($path, $content)
    {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        file_put_contents($path, $content);
    }

    private function updateFile($path, $content)
    {
        file_put_contents($path, $content);
    }

    private function deleteDirectoryContents($dir)
    {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->deleteDirectoryContents($path);
                rmdir($path);
            } else {
                unlink($path);
            }
        }
    }
}

// Run the cleanup if called directly
if (php_sapi_name() === 'cli') {
    $cleanup = new TemplateCleanup();
    $cleanup->run();
}
