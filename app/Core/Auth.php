<?php

namespace App\Core;

use App\Models\User;

class Auth
{
    /**
     * Check if user is authenticated
     */
    public static function check()
    {
        return self::user() !== null;
    }

    /**
     * Get the authenticated user
     */
    public static function user()
    {
        Session::start();

        if (Session::has('user_id')) {
            return User::find(Session::get('user_id'));
        }

        // Check remember token
        if (isset($_COOKIE['remember_token'])) {
            $user = User::findByRememberToken($_COOKIE['remember_token']);
            if ($user) {
                self::login($user, false);
                return $user;
            }
        }

        return null;
    }

    /**
     * Get user ID
     */
    public static function id()
    {
        $user = self::user();
        return $user ? $user['id'] : null;
    }

    /**
     * Attempt to authenticate user
     */
    public static function attempt($credentials, $remember = false)
    {
        $user = User::findByEmail($credentials['email']);
        
        if ($user && User::verifyPassword($credentials['password'], $user['password'])) {
            self::login($user, $remember);
            return true;
        }

        return false;
    }

    /**
     * Login user
     */
    public static function login($user, $remember = false)
    {
        Session::start();
        Session::put('user_id', $user['id']);

        if ($remember) {
            $token = bin2hex(random_bytes(32));
            User::updateRememberToken($user['id'], $token);
            setcookie('remember_token', $token, time() + (86400 * 7), '/'); // 30 days
        }

        // Regenerate session ID for security
        Session::regenerate(true);
    }

    /**
     * Logout user
     */
    public static function logout()
    {
        Session::start();

        // Clear remember token from database
        if (Session::has('user_id')) {
            User::updateRememberToken(Session::get('user_id'), null);
        }

        // Clear session
        Session::destroy();

        // Clear remember cookie
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
        }
    }

    /**
     * Check if user is guest (not authenticated)
     */
    public static function guest()
    {
        return !self::check();
    }

    /**
     * Require authentication (redirect if not authenticated)
     */
    public static function requireAuth($redirectTo = '/login')
    {
        if (self::guest()) {
            header("Location: $redirectTo");
            exit;
        }
    }
}
