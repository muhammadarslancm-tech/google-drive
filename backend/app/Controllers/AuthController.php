<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\Token;

/**
 * Authentication Controller
 */
class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(): void
    {
        $data = $this->request->all();
        
        $errors = $this->validateRequired($data, ['first_name', 'last_name', 'email', 'password']);
        
        if (!empty($errors)) {
            $this->error('Validation failed', 400, $errors);
        }

        // Validate email format
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid email format', 400);
        }

        // Validate password strength
        if (strlen($data['password']) < 8) {
            $this->error('Password must be at least 8 characters long', 400);
        }

        $userModel = new User();
        $userId = $userModel->createUser(
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            $data['password']
        );

        if (!$userId) {
            $this->error('Email already registered', 409);
        }

        $user = $userModel->findByIdSafe($userId);
        
        $this->success([
            'user' => $user,
            'message' => 'Registration successful'
        ], 'User registered successfully', 201);
    }

    /**
     * Login user
     */
    public function login(): void
    {
        $data = $this->request->all();
        
        $errors = $this->validateRequired($data, ['email', 'password']);
        
        if (!empty($errors)) {
            $this->error('Validation failed', 400, $errors);
        }

        $userModel = new User();
        $user = $userModel->verifyPassword($data['email'], $data['password']);

        if (!$user) {
            $this->error('Invalid email or password', 401);
        }

        // Create token
        $tokenModel = new Token();
        $token = $tokenModel->createToken(
            $user['_id'],
            $this->request->ip(),
            $this->request->userAgent()
        );

        $this->success([
            'user' => $user,
            'token' => $token
        ], 'Login successful');
    }

    /**
     * Logout user
     */
    public function logout(): void
    {
        $token = $this->request->header('Authorization');
        
        if (empty($token)) {
            $this->error('Token required', 400);
        }

        // Remove 'Bearer ' prefix if present
        if (strpos($token, 'Bearer ') === 0) {
            $token = substr($token, 7);
        }

        $tokenModel = new Token();
        $deleted = $tokenModel->deleteToken($token);

        if (!$deleted) {
            $this->error('Token not found', 404);
        }

        $this->success([], 'Logout successful');
    }

    /**
     * Get current user
     */
    public function me(): void
    {
        $user = $this->request->user();
        
        if (!$user) {
            $this->error('User not authenticated', 401);
        }

        $this->success(['user' => $user]);
    }
}
