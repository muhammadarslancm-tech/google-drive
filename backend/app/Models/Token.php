<?php

namespace App\Models;

use App\Core\Model;
use App\Models\User;

/**
 * Token Model
 * Manages authentication tokens
 */
class Token extends Model
{
    protected string $collectionName = 'tokens';

    /**
     * Generate a secure random token
     */
    private function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Create a new token for user
     */
    public function createToken(string $userId, string $ipAddress, string $userAgent): string
    {
        $config = require __DIR__ . '/../../config/app.php';
        $expiresAt = time() + $config['token_expiry'];

        $token = $this->generateToken();

        $userModel = new User();
        $user = $userModel->findByIdSafe($userId);

        if (!$user) {
            throw new \Exception('User not found');
        }

        $data = [
            'token' => $token,
            'user_id' => $userId,
            'user' => $user,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'expires_at' => date('Y-m-d H:i:s', $expiresAt),
            'created_at' => new \MongoDB\BSON\UTCDateTime(),
            'updated_at' => new \MongoDB\BSON\UTCDateTime()
        ];

        $insertedId = $this->create($data);
        
        if (!$insertedId) {
            error_log('Token creation failed: Insert returned null. Token: ' . substr($token, 0, 10) . '...');
            throw new \Exception('Failed to save token to database');
        }
        
        return $token;
    }

    /**
     * Find token by token string
     */
    public function findByToken(string $token): ?array
    {
        return $this->findOne(['token' => $token]);
    }

    /**
     * Delete token
     */
    public function deleteToken(string $token): bool
    {
        $tokenData = $this->findByToken($token);
        
        if (!$tokenData) {
            return false;
        }

        return $this->delete($tokenData['_id']);
    }

    /**
     * Delete all tokens for a user
     */
    public function deleteUserTokens(string $userId): bool
    {
        try {
            $deletedCount = $this->deleteMany(['user_id' => $userId]);
            return $deletedCount > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Clean expired tokens
     */
    public function cleanExpiredTokens(): int
    {
        try {
            $now = date('Y-m-d H:i:s');
            return $this->deleteMany([
                'expires_at' => ['$lt' => $now]
            ]);
        } catch (\Exception $e) {
            return 0;
        }
    }
}
