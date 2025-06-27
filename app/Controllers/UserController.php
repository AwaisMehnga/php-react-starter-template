<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

/**
 * User Controller
 * Handles user-related operations
 */
class UserController extends Controller
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /**
     * Display user profile
     * 
     * @param string $name
     */
    public function profile(string $name = '')
    {
        $this->setData('title', 'User Profile - Tool Site');
        $this->setData('name', $name);
        $this->setData('user_data', $this->getUserData($name));

        echo $this->render('user/profile');
    }

    /**
     * Handle the awais route (legacy support)
     * 
     * @param string $name
     */
    public function awais(string $name = '')
    {
        $this->setData('name', $name);
        echo $this->render('awais');
    }

    /**
     * Get user data (placeholder for actual user data)
     * 
     * @param string $name
     * @return array
     */
    private function getUserData(string $name): array
    {
        // This would typically fetch from database
        // For now, return sample data
        return [
            'name' => $name,
            'email' => strtolower($name) . '@example.com',
            'joined' => '2024-01-01',
            'bio' => 'This is a sample user bio for ' . $name
        ];
    }

    /**
     * API endpoint to get user data
     * 
     * @param string $name
     */
    public function apiGetUser(string $name = '')
    {
        if (empty($name)) {
            $this->json(['error' => 'Name parameter is required'], 400);
            return;
        }

        $userData = $this->getUserData($name);
        $this->json([
            'status' => 'success',
            'user' => $userData
        ]);
    }

    /**
     * API endpoint to create user
     */
    public function apiCreateUser()
    {
        if (!$this->isPost()) {
            $this->json(['error' => 'POST method required'], 405);
            return;
        }

        $name = $this->post('name');
        $email = $this->post('email');

        if (empty($name) || empty($email)) {
            $this->json(['error' => 'Name and email are required'], 400);
            return;
        }

        // Here you would typically save to database
        // $userId = $this->userModel->create(['name' => $name, 'email' => $email]);

        $this->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'user' => ['name' => $name, 'email' => $email]
        ]);
    }
}
