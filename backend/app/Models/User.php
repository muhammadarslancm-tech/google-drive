<?php

namespace App\Models;

use App\Core\Model;

/**
 * User Model
 */
class User extends Model
{
    protected string $collectionName = 'users';

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?array
    {
        return $this->findOne(['email' => $email]);
    }

    /**
     * Create a new user
     */
    public function createUser(string $firstName, string $lastName, string $email, string $password): ?string
    {
        // Check if user exists
        if ($this->findByEmail($email)) {
            return null;
        }

        $data = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'created_at' => new \MongoDB\BSON\UTCDateTime(),
            'updated_at' => new \MongoDB\BSON\UTCDateTime()
        ];

        return $this->create($data);
    }

    /**
     * Verify password
     */
    public function verifyPassword(string $email, string $password): ?array
    {
        $user = $this->findByEmail($email);
        
        if (!$user || !password_verify($password, $user['password'])) {
            return null;
        }

        // Remove password from response
        unset($user['password']);
        
        return $user;
    }

    /**
     * Get user without password
     */
    public function findByIdSafe(string $id): ?array
    {
        $user = $this->findById($id);
        
        if ($user) {
            unset($user['password']);
        }
        
        return $user;
    }
}
