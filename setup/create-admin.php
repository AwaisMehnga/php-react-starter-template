<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\User;

function createAdminUser() {
    try {
        echo "Creating admin user...\n";
        
        // Check if admin user already exists
        $existingUser = User::findByEmail('admin@billoacraft.com');
        
        if ($existingUser) {
            echo "❌ Admin user already exists!\n";
            echo "Email: admin@billoacraft.com\n";
            return;
        }
        
        // Create admin user
        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin@billoacraft.com',
            'password' => 'Awais5+2=7' // You should change this!
        ]);
        
        if ($user) {
            echo "✅ Admin user created successfully!\n";
            echo "Email: admin@billoacraft.com\n";
            echo "Password: Awais5+2=7\n";
            echo "\n⚠️  IMPORTANT: Please change the default password after logging in!\n";
        } else {
            echo "❌ Failed to create admin user\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Error creating admin user: " . $e->getMessage() . "\n";
        exit(1);
    }
}

// Create admin user
createAdminUser();
