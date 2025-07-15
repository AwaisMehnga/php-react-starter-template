<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Core\Auth;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->redirect('/dashboard');
        }
        
        return $this->view('auth/login');
    }

    /**
     * Handle login request
     */
    public function login()
    {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);

        // Validate input
        if (empty($email) || empty($password)) {
            return $this->redirectBack(['error' => 'Email and password are required']);
        }

        // Attempt login
        if (Auth::attempt(['email' => $email, 'password' => $password], $remember)) {
            return $this->redirect('/dashboard');
        }

        return $this->redirectBack(['error' => 'Invalid credentials']);
    }

    /**
     * Show registration form
     */
    public function showRegisterForm()
    {
        if (Auth::check()) {
            return $this->redirect('/dashboard');
        }
        
        return $this->view('auth/register');
    }

    /**
     * Handle registration request
     */
    public function register()
    {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $passwordConfirmation = $_POST['password_confirmation'] ?? '';

        // Validate input
        $errors = [];
        
        if (empty($name)) {
            $errors[] = 'Name is required';
        }
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid email is required';
        }
        
        if (empty($password) || strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters';
        }
        
        if ($password !== $passwordConfirmation) {
            $errors[] = 'Password confirmation does not match';
        }

        // Check if email already exists
        if (User::findByEmail($email)) {
            $errors[] = 'Email already exists';
        }

        if (!empty($errors)) {
            return $this->redirectBack(['errors' => $errors]);
        }

        // Create user
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password
        ]);

        // Login user
        Auth::login($user);

        return $this->redirect('/dashboard');
    }

    /**
     * Handle logout request
     */
    public function logout()
    {
        Auth::logout();
        return $this->redirect('/');
    }

    /**
     * Show forgot password form
     */
    public function showForgotPasswordForm()
    {
        return $this->view('auth/forgot-password');
    }

    /**
     * Handle forgot password request
     */
    public function forgotPassword()
    {
        $email = $_POST['email'] ?? '';

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->redirectBack(['error' => 'Valid email is required']);
        }

        $user = User::findByEmail($email);
        if (!$user) {
            // Don't reveal if email exists or not for security
            return $this->redirectBack(['success' => 'If your email exists, you will receive a reset link']);
        }

        // Generate reset token (implement email sending logic here)
        $token = bin2hex(random_bytes(32));
        
        // Store token in database (you'll need to implement this)
        // PasswordReset::create(['email' => $email, 'token' => $token]);

        return $this->redirectBack(['success' => 'Password reset link sent to your email']);
    }

    /**
     * Dashboard (protected route)
     */
    public function dashboard()
    {
        if (!Auth::check()) {
            return $this->redirect('/login');
        }

        return $this->view('dashboard', ['user' => Auth::user()]);
    }
}
