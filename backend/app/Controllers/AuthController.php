<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\Token;

/**
 * Authentication Controller
 * All methods return JSON responses following API pattern
 */
class AuthController extends Controller
{
    /**
     * Register a new user
     * POST /api/auth/register
     * 
     * @return void (JSON response)
     */
    public function register(): void
    {
        // Only accept POST requests
        if ($this->request->method() !== 'POST') {
            $this->error('Method not allowed', 405);
        }

        $data = $this->request->all();

        // Validate required fields
        $errors = $this->validateRequired($data, ['first_name', 'last_name', 'email', 'password']);
        
        if (!empty($errors)) {
            $this->error('Validation failed', 400, $errors);
        }

        // Validate email format
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid email format', 400, ['email' => 'Please provide a valid email address']);
        }

        // Validate password strength
        if (strlen($data['password']) < 8) {
            $this->error('Password must be at least 8 characters long', 400, ['password' => 'Password must be at least 8 characters']);
        }

        // Sanitize input
        $firstName = trim($data['first_name']);
        $lastName = trim($data['last_name']);
        $email = strtolower(trim($data['email']));

        $userModel = new User();
        $userId = $userModel->createUser(
            $firstName,
            $lastName,
            $email,
            $data['password']
        );

        if (!$userId) {
            $this->error('Email already registered', 409, ['email' => 'This email is already registered']);
        }

        $user = $userModel->findByIdSafe($userId);
        
        if (!$user) {
            $this->error('Failed to retrieve user after registration', 500);
        }
        
        // Create token for newly registered user
        $tokenModel = new Token();
        try {
            $token = $tokenModel->createToken(
                $userId,
                $this->request->ip(),
                $this->request->userAgent()
            );
        } catch (\Exception $e) {
            // Log error but don't fail registration - user can login later
            error_log('Token creation failed during registration: ' . $e->getMessage());
            $this->error('Registration successful but failed to create access token. Please login.', 500);
        }
        
        // Return consistent JSON response with token (same format as login)
        $this->success([
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer'
        ], 'User registered successfully', 201);
    }

    /**
     * Login user
     * POST /api/auth/login
     * 
     * @return void (JSON response)
     */
    public function login(): void
    {
        // Only accept POST requests
        if ($this->request->method() !== 'POST') {
            $this->error('Method not allowed', 405);
        }

        $data = $this->request->all();
        
        // Validate required fields
        $errors = $this->validateRequired($data, ['email', 'password']);
        
        if (!empty($errors)) {
            $this->error('Validation failed', 400, $errors);
        }

        // Validate email format
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid email format', 400, ['email' => 'Please provide a valid email address']);
        }

        $email = strtolower(trim($data['email']));
        $userModel = new User();
        $user = $userModel->verifyPassword($email, $data['password']);

        if (!$user) {
            $this->error('Invalid email or password', 401, ['credentials' => 'Invalid email or password']);
        }

        // Create token
        $tokenModel = new Token();
        try {
            $token = $tokenModel->createToken(
                $user['_id'],
                $this->request->ip(),
                $this->request->userAgent()
            );
        } catch (\Exception $e) {
            $this->error('Failed to create authentication token', 500);
        }

        // Return consistent JSON response with token
        $this->success([
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer'
        ], 'Login successful');
    }

    /**
     * Logout user
     * POST /api/auth/logout
     * Requires: Authorization header with Bearer token
     * 
     * @return void (JSON response)
     */
    public function logout(): void
    {
        // Only accept POST requests
        if ($this->request->method() !== 'POST') {
            $this->error('Method not allowed', 405);
        }

        $token = $this->request->header('Authorization');
        
        if (empty($token)) {
            $this->error('Authentication token required', 401, ['token' => 'Authorization header is required']);
        }

        // Remove 'Bearer ' prefix if present
        if (strpos($token, 'Bearer ') === 0) {
            $token = substr($token, 7);
        }

        if (empty($token)) {
            $this->error('Invalid token format', 400, ['token' => 'Token format is invalid']);
        }

        $tokenModel = new Token();
        $deleted = $tokenModel->deleteToken($token);

        if (!$deleted) {
            $this->error('Token not found or already invalidated', 404, ['token' => 'Token not found']);
        }

        // Return consistent JSON response
        $this->success([], 'Logout successful');
    }

    /**
     * Get current authenticated user
     * GET /api/auth/me
     * Requires: Authorization header with Bearer token
     * 
     * @return void (JSON response)
     */
    public function me(): void
    {
        // Only accept GET requests
        if ($this->request->method() !== 'GET') {
            $this->error('Method not allowed', 405);
        }

        $token = $this->request->header('Authorization');
        
        if (empty($token)) {
            $this->error('Authentication token required', 401, ['token' => 'Authorization header is required']);
        }
        
        // Remove 'Bearer ' prefix if present
        if (strpos($token, 'Bearer ') === 0) {
            $token = substr($token, 7);
        }
        
        if (empty($token)) {
            $this->error('Invalid token format', 400, ['token' => 'Token format is invalid']);
        }
        
        $tokenModel = new Token();
        $tokenData = $tokenModel->findByToken($token);
        
        if (!$tokenData) {
            $this->error('Invalid or expired token', 401, ['token' => 'Token not found']);
        }
        
        if (time() > strtotime($tokenData['expires_at'])) {
            $this->error('Token expired', 401, ['token' => 'Token expired']);
        }
        
        if (!isset($tokenData['user']) || !is_array($tokenData['user'])) {
            $this->error('Invalid token data', 401, ['token' => 'Token data is invalid']);
        }
        
        $user = $tokenData['user'];
        
        // Return consistent JSON response
        $this->success([
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer'
        ], 'User retrieved successfully');
    }
}
