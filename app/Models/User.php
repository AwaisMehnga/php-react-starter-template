<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class User extends Model
{
    protected static $table = 'users';
    protected $fillable = ['name', 'email', 'password'];
    protected $hidden = ['password', 'remember_token'];

    /**
     * Create a new user
     */
    public static function create($data = [])
    {
        $db = Database::getInstance();
        
        // Hash password if provided
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        $sql = "INSERT INTO users (name, email, password, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())";
        $db->query($sql, [$data['name'], $data['email'], $data['password']]);
        
        return self::find($db->lastInsertId());
    }

    /**
     * Find user by email
     */
    public static function findByEmail($email)
    {
        $db = Database::getInstance();
        $sql = "SELECT * FROM users WHERE email = ?";
        return $db->fetch($sql, [$email]);
    }

    /**
     * Find user by ID
     */
    public static function find($id)
    {
        $db = Database::getInstance();
        $sql = "SELECT * FROM users WHERE id = ?";
        return $db->fetch($sql, [$id]);
    }

    /**
     * Verify password
     */
    public static function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }

    /**
     * Update user's remember token
     */
    public static function updateRememberToken($userId, $token)
    {
        $db = Database::getInstance();
        $sql = "UPDATE users SET remember_token = ?, updated_at = NOW() WHERE id = ?";
        return $db->query($sql, [$token, $userId]);
    }

    /**
     * Find user by remember token
     */
    public static function findByRememberToken($token)
    {
        $db = Database::getInstance();
        $sql = "SELECT * FROM users WHERE remember_token = ?";
        return $db->fetch($sql, [$token]);
    }

    /**
     * Update email verification
     */
    public static function markEmailAsVerified($userId)
    {
        $db = Database::getInstance();
        $sql = "UPDATE users SET email_verified_at = NOW(), updated_at = NOW() WHERE id = ?";
        return $db->query($sql, [$userId]);
    }

    /**
     * Check if email is verified
     */
    public function isEmailVerified()
    {
        return !is_null($this->email_verified_at);
    }
}
