<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class User extends Model
{
    protected static $table = 'users';
    protected static $primaryKey = 'id';
    
    protected $fillable = [
        'name', 'email', 'password', 'role', 'phone', 'bio', 'avatar', 'is_active'
    ];

    /**
     * Get all users from database
     *
     * @return array
     */
    public static function all()
    {
        $db = Database::getInstance();
        $results = $db->fetchAll("SELECT * FROM " . static::$table . " WHERE is_active = 1 ORDER BY created_at DESC");
        
        return array_map(function($row) {
            return new static($row);
        }, $results);
    }

    /**
     * Find user by ID from database
     *
     * @param int $id
     * @return static|null
     */
    public static function find($id)
    {
        $db = Database::getInstance();
        $result = $db->fetch("SELECT * FROM " . static::$table . " WHERE id = ?", [$id]);
        return $result ? new static($result) : null;
    }

    /**
     * Find user by email
     *
     * @param string $email
     * @return static|null
     */
    public static function findByEmail($email)
    {
        $db = Database::getInstance();
        $result = $db->fetch("SELECT * FROM " . static::$table . " WHERE email = ?", [$email]);
        return $result ? new static($result) : null;
    }

    /**
     * Create new user in database
     *
     * @param array $attributes
     * @return static
     */
    public static function create($attributes = [])
    {
        $db = Database::getInstance();
        
        // Hash password if provided
        if (isset($attributes['password'])) {
            $attributes['password'] = password_hash($attributes['password'], PASSWORD_DEFAULT);
        }
        
        $fields = implode(', ', array_keys($attributes));
        $placeholders = ':' . implode(', :', array_keys($attributes));
        
        $sql = "INSERT INTO " . static::$table . " ({$fields}) VALUES ({$placeholders})";
        $db->query($sql, $attributes);
        
        $id = $db->lastInsertId();
        return static::find($id);
    }

    /**
     * Save model to database
     *
     * @return $this
     */
    public function save()
    {
        $db = Database::getInstance();
        
        if (isset($this->attributes['id'])) {
            // Update existing record
            $sets = [];
            $values = [];
            foreach ($this->fillable as $field) {
                if (isset($this->attributes[$field])) {
                    $sets[] = "{$field} = ?";
                    $values[] = $this->attributes[$field];
                }
            }
            $values[] = $this->attributes['id'];
            
            $sql = "UPDATE " . static::$table . " SET " . implode(', ', $sets) . ", updated_at = NOW() WHERE id = ?";
            $db->query($sql, $values);
        } else {
            // Create new record
            $data = [];
            foreach ($this->fillable as $field) {
                if (isset($this->attributes[$field])) {
                    $data[$field] = $this->attributes[$field];
                }
            }
            $created = static::create($data);
            $this->attributes = $created->attributes;
        }
        
        return $this;
    }

    /**
     * Delete user from database
     *
     * @return bool
     */
    public function delete()
    {
        if (isset($this->attributes['id'])) {
            $db = Database::getInstance();
            $db->query("DELETE FROM " . static::$table . " WHERE id = ?", [$this->attributes['id']]);
            return true;
        }
        return false;
    }

    /**
     * Get user's full name
     *
     * @return string
     */
    public function getFullName()
    {
        return $this->getAttribute('name');
    }

    /**
     * Check if user is admin
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->getAttribute('role') === 'admin';
    }

    /**
     * Check if user is moderator or admin
     *
     * @return bool
     */
    public function isModerator()
    {
        return in_array($this->getAttribute('role'), ['admin', 'moderator']);
    }

    /**
     * Get avatar URL
     *
     * @return string
     */
    public function getAvatarUrl()
    {
        $avatar = $this->getAttribute('avatar');
        return $avatar ? "/uploads/avatars/{$avatar}" : "/assets/images/default-avatar.png";
    }

    /**
     * Verify password
     *
     * @param string $password
     * @return bool
     */
    public function verifyPassword($password)
    {
        return password_verify($password, $this->getAttribute('password'));
    }

    /**
     * Update last login timestamp
     *
     * @return void
     */
    public function updateLastLogin()
    {
        $db = Database::getInstance();
        $db->query("UPDATE " . static::$table . " SET last_login_at = NOW() WHERE id = ?", [$this->getAttribute('id')]);
    }

    /**
     * Get attribute value with fallback
     *
     * @param string $key
     * @return mixed
     */
    private function getAttribute($key)
    {
        return isset($this->attributes[$key]) ? $this->attributes[$key] : null;
    }
}
