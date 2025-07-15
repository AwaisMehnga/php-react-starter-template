<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Database;
use App\Models\User;

function setupAuthentication() {
    echo "🚀 Setting up Billo Craft Authentication System\n";
    echo "=============================================\n\n";
    
    try {
        // Step 1: Test database connection
        echo "1. Testing database connection...\n";
        $db = Database::getInstance();
        $connection = $db->getConnection();
        echo "   ✅ Database connected successfully!\n\n";
        
        // Step 2: Run migrations
        echo "2. Running database migrations...\n";
        $migrationPath = __DIR__ . '/database/migrations/001_create_users_table.sql';
        
        if (!file_exists($migrationPath)) {
            throw new Exception("Migration file not found: $migrationPath");
        }
        
        $sql = file_get_contents($migrationPath);
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        
        foreach ($statements as $statement) {
            if (!empty($statement)) {
                $connection->exec($statement);
            }
        }
        echo "   ✅ Database tables created successfully!\n\n";
        
        // Step 3: Create admin user (if doesn't exist)
        echo "3. Setting up admin user...\n";
        $existingUser = User::findByEmail('admin@billoacraft.com');
        
        if ($existingUser) {
            echo "   ⚠️  Admin user already exists!\n";
        } else {
            $user = User::create([
                'name' => 'Admin',
                'email' => 'admin@billoacraft.com',
                'password' => 'password'
            ]);
            echo "   ✅ Admin user created successfully!\n";
            echo "   📧 Email: admin@billoacraft.com\n";
            echo "   🔑 Password: password\n";
        }
        echo "\n";
        
        // Step 4: Display setup completion
        echo "🎉 Authentication Setup Complete!\n";
        echo "===============================\n\n";
        echo "Your authentication system is now ready with the following features:\n\n";
        echo "📋 Features Installed:\n";
        echo "   • User Registration\n";
        echo "   • User Login/Logout\n";
        echo "   • Password Hashing\n";
        echo "   • Remember Me Functionality\n";
        echo "   • Session Management\n";
        echo "   • Middleware Protection\n";
        echo "   • Forgot Password (UI ready)\n\n";
        
        echo "🔗 Available Routes:\n";
        echo "   • /login - Login page\n";
        echo "   • /register - Registration page\n";
        echo "   • /dashboard - Protected dashboard\n";
        echo "   • /logout - Logout action\n";
        echo "   • /forgot-password - Password recovery\n\n";
        
        echo "🛡️  Middleware:\n";
        echo "   • 'auth' - Protects routes for authenticated users only\n";
        echo "   • 'guest' - Protects routes for non-authenticated users only\n\n";
        
        echo "📁 Files Created/Modified:\n";
        echo "   • Models: User.php\n";
        echo "   • Controllers: AuthController.php\n";
        echo "   • Middleware: AuthMiddleware.php, GuestMiddleware.php\n";
        echo "   • Core: Auth.php, Session.php\n";
        echo "   • Views: auth/login.php, auth/register.php, dashboard.php\n";
        echo "   • Routes: Updated web.php with auth routes\n\n";
        
        echo "⚠️  Important Notes:\n";
        echo "   • Change the default admin password after first login\n";
        echo "   • Configure email settings for password reset functionality\n";
        echo "   • Review and customize the authentication views as needed\n";
        echo "   • Consider implementing email verification if required\n\n";
        
        echo "🚀 Next Steps:\n";
        echo "   1. Start your development server\n";
        echo "   2. Visit /login to test the authentication\n";
        echo "   3. Customize the dashboard and add your business logic\n";
        echo "   4. Implement additional features like user roles if needed\n\n";
        
        echo "Happy coding! 🎯\n";
        
    } catch (Exception $e) {
        echo "❌ Setup failed: " . $e->getMessage() . "\n";
        exit(1);
    }
}

// Run setup
setupAuthentication();
