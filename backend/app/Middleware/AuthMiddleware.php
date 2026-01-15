<?php

namespace App\Middleware;

use App\Core\Request;
use App\Models\Token;

/**
 * Authentication Middleware
 * Validates token and injects user into request
 */
class AuthMiddleware
{
    public function handle(Request $request): bool
    {
        $token = $request->header('Authorization');
        
        if (empty($token)) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Authentication required'
            ]);
            return false;
        }

        // Remove 'Bearer ' prefix if present
        if (strpos($token, 'Bearer ') === 0) {
            $token = substr($token, 7);
        }

        $tokenModel = new Token();
        $tokenData = $tokenModel->findByToken($token);

        if (!$tokenData) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Invalid or expired token'
            ]);
            return false;
        }

        // Check expiration
        $expiresAt = strtotime($tokenData['expires_at']);
        if (time() > $expiresAt) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Token expired'
            ]);
            return false;
        }

        // Set user in request
        $request->setUser($tokenData['user']);
        
        return true;
    }
}
