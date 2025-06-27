<?php

namespace App\Models;

use App\Core\Model;

/**
 * User Model
 * Handles user data operations
 */
class User extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $fillable = ['name', 'email', 'password', 'created_at', 'updated_at'];

    /**
     * Find user by email
     * 
     * @param string $email
     * @return array|null
     */
    public function findByEmail(string $email): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email LIMIT 1";
        $result = $this->query($sql, ['email' => $email]);

        return $result[0] ?? null;
    }

    /**
     * Find user by name
     * 
     * @param string $name
     * @return array|null
     */
    public function findByName(string $name): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE name = :name LIMIT 1";
        $result = $this->query($sql, ['name' => $name]);

        return $result[0] ?? null;
    }

    /**
     * Get all users with pagination
     * 
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getAllUsers(int $limit = 10, int $offset = 0): array
    {
        return $this->findAll([], 'created_at DESC', $limit, $offset);
    }

    /**
     * Create a new user with hashed password
     * 
     * @param array $data
     * @return int|false
     */
    public function createUser(array $data)
    {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        return $this->create($data);
    }

    /**
     * Update user data
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateUser(int $id, array $data): bool
    {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $data['updated_at'] = date('Y-m-d H:i:s');

        return $this->update($id, $data);
    }

    /**
     * Verify user password
     * 
     * @param string $email
     * @param string $password
     * @return array|false
     */
    public function verifyUser(string $email, string $password)
    {
        $user = $this->findByEmail($email);

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }

        return false;
    }
}
