#!/usr/bin/env php
<?php

/**
 * PHP React MVC Template Setup
 * 
 * This script sets up a new project from the template with custom configuration
 */

class TemplateSetup
{
    private $config = [];
    private $projectPath;

    public function __construct()
    {
        $this->projectPath = getcwd();
    }

    public function run()
    {
        $this->showWelcome();
        $this->collectProjectInfo();
        $this->setupProject();
        $this->setupDatabase();
        $this->updateComposer();
        $this->showCompletion();
    }

    private function showWelcome()
    {
        echo "\n";
        echo "🚀 PHP React MVC Template Setup\n";
        echo "================================\n\n";
        echo "This setup will create a new project with:\n";
        echo "✅ Laravel-style MVC architecture\n";
        echo "✅ React SPA integration with Vite\n";
        echo "✅ Database configuration\n";
        echo "✅ Custom project configuration\n\n";
    }

    private function collectProjectInfo()
    {
        echo "📝 Project Configuration\n";
        echo "------------------------\n";

        $this->config['project_name'] = $this->prompt('Project Name', 'My Awesome Project');
        $this->config['project_slug'] = $this->slugify($this->config['project_name']);
        $this->config['description'] = $this->prompt('Project Description', 'A modern PHP project with React SPAs');
        $this->config['author_name'] = $this->prompt('Author Name', 'Your Name');
        $this->config['author_email'] = $this->prompt('Author Email', 'your.email@example.com');
        
        echo "\n🗄️ Database Configuration\n";
        echo "-------------------------\n";
        
        $this->config['db_name'] = $this->prompt('Database Name', $this->config['project_slug'] . '_db');
        $this->config['db_host'] = $this->prompt('Database Host', 'localhost');
        $this->config['db_username'] = $this->prompt('Database Username', 'root');
        $this->config['db_password'] = $this->prompt('Database Password', '');
        
        echo "\n⚛️ React SPA Configuration\n";
        echo "--------------------------\n";
        
        $this->config['default_spa'] = $this->prompt('Default SPA Name', 'Home');
        $this->config['app_url'] = $this->prompt('Application URL', 'http://localhost/' . $this->config['project_slug']);

        echo "\n✅ Configuration Complete!\n\n";
    }

    private function prompt($question, $default = '')
    {
        $defaultText = $default ? " [{$default}]" : '';
        echo "{$question}{$defaultText}: ";
        $handle = fopen("php://stdin", "r");
        $line = trim(fgets($handle));
        fclose($handle);
        return $line ?: $default;
    }

    private function slugify($text)
    {
        $text = preg_replace('/[^a-zA-Z0-9\s]/', '', $text);
        $text = preg_replace('/\s+/', '-', trim($text));
        return strtolower($text);
    }

    private function setupProject()
    {
        echo "🔧 Setting up project files...\n";

        // Update package.json
        $this->updatePackageJson();
        
        // Update composer.json
        $this->updateComposerJson();
        
        // Update database config
        $this->updateDatabaseConfig();
        
        // Update default views
        $this->updateDefaultViews();
        
        // Update vite config
        $this->updateViteConfig();

        echo "✅ Project files updated\n";
    }

    private function updatePackageJson()
    {
        $packageFile = $this->projectPath . '/package.json';
        if (file_exists($packageFile)) {
            $package = json_decode(file_get_contents($packageFile), true);
            $package['name'] = $this->config['project_slug'];
            $package['description'] = $this->config['description'];
            $package['author'] = $this->config['author_name'] . ' <' . $this->config['author_email'] . '>';
            file_put_contents($packageFile, json_encode($package, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }
    }

    private function updateComposerJson()
    {
        $composerFile = $this->projectPath . '/composer.json';
        if (file_exists($composerFile)) {
            $composer = json_decode(file_get_contents($composerFile), true);
            $composer['name'] = strtolower($this->config['author_name']) . '/' . $this->config['project_slug'];
            $composer['description'] = $this->config['description'];
            $composer['authors'] = [[
                'name' => $this->config['author_name'],
                'email' => $this->config['author_email']
            ]];
            file_put_contents($composerFile, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }
    }

    private function updateDatabaseConfig()
    {
        $configFile = $this->projectPath . '/config/database.php';
        if (file_exists($configFile)) {
            $content = file_get_contents($configFile);
            $content = str_replace('tool_site_db', $this->config['db_name'], $content);
            $content = str_replace("'host' => 'localhost'", "'host' => '{$this->config['db_host']}'", $content);
            $content = str_replace("'username' => 'root'", "'username' => '{$this->config['db_username']}'", $content);
            $content = str_replace("'password' => ''", "'password' => '{$this->config['db_password']}'", $content);
            file_put_contents($configFile, $content);
        }
    }

    private function updateDefaultViews()
    {
        // Update index.php with project name
        $indexFile = $this->projectPath . '/views/index.php';
        if (file_exists($indexFile)) {
            $content = file_get_contents($indexFile);
            $content = str_replace('React Code Split Example', $this->config['project_name'], $content);
            $content = str_replace('Laravel-style MVC Routes Available:', $this->config['project_name'] . ' - Available Routes:', $content);
            file_put_contents($indexFile, $content);
        }

        // Update other view files
        $aboutFile = $this->projectPath . '/views/about.php';
        if (file_exists($aboutFile)) {
            $content = file_get_contents($aboutFile);
            $content = str_replace('Tool Site', $this->config['project_name'], $content);
            file_put_contents($aboutFile, $content);
        }
    }

    private function updateViteConfig()
    {
        $viteFile = $this->projectPath . '/vite.config.js';
        if (file_exists($viteFile)) {
            $content = file_get_contents($viteFile);
            // Update default SPA name if needed
            $content = str_replace('"Home"', '"' . $this->config['default_spa'] . '"', $content);
            file_put_contents($viteFile, $content);
        }
    }

    private function setupDatabase()
    {
        echo "\n🗄️ Setting up database...\n";

        $setupChoice = $this->prompt('Setup database now? (y/n)', 'y');
        if (strtolower($setupChoice) === 'y') {
            $this->updateDatabaseSQL();
            $this->runDatabaseSetup();
        } else {
            echo "⏭️  Database setup skipped. Run 'php setup_database.php' later.\n";
        }
    }

    private function updateDatabaseSQL()
    {
        $sqlFile = $this->projectPath . '/database/tool_site_db.sql';
        if (file_exists($sqlFile)) {
            $content = file_get_contents($sqlFile);
            $content = str_replace('tool_site_db', $this->config['db_name'], $content);
            $content = str_replace('Tool Site', $this->config['project_name'], $content);
            file_put_contents($sqlFile, $content);
        }
    }

    private function runDatabaseSetup()
    {
        $setupFile = $this->projectPath . '/setup_database.php';
        if (file_exists($setupFile)) {
            echo "Running database setup...\n";
            $output = shell_exec("php {$setupFile} 2>&1");
            echo $output;
        }
    }

    private function updateComposer()
    {
        echo "\n📦 Updating Composer dependencies...\n";
        $output = shell_exec("composer dump-autoload 2>&1");
        echo $output;
        echo "✅ Composer autoloader updated\n";
    }

    private function showCompletion()
    {
        echo "\n🎉 Setup Complete!\n";
        echo "==================\n\n";
        echo "Your project '{$this->config['project_name']}' is ready!\n\n";
        
        echo "📂 Project Structure:\n";
        echo "   app/Controllers/     - MVC Controllers\n";
        echo "   app/Models/          - Data Models\n";
        echo "   app/Middleware/      - Request Middleware\n";
        echo "   views/               - PHP Views\n";
        echo "   modules/             - React SPAs\n";
        echo "   routes/              - Route Definitions\n\n";
        
        echo "🚀 Next Steps:\n";
        echo "   1. Start your web server (XAMPP, etc.)\n";
        echo "   2. Visit: {$this->config['app_url']}\n";
        echo "   3. Check the documentation: {$this->config['app_url']}/docs\n\n";
        
        echo "👤 Sample Login:\n";
        echo "   Email: admin@" . strtolower($this->config['project_slug']) . ".com\n";
        echo "   Password: password\n\n";
        
        echo "📚 Documentation:\n";
        echo "   - Controllers: docs/guide/controllers.md\n";
        echo "   - Models: docs/guide/models.md\n";
        echo "   - Routing: docs/guide/routing.md\n";
        echo "   - React SPAs: docs/guide/spa-development.md\n\n";
        
        echo "Happy coding! 🎯\n\n";
    }
}

// Run the setup
if (php_sapi_name() === 'cli') {
    $setup = new TemplateSetup();
    $setup->run();
} else {
    echo "This script must be run from the command line.";
}
