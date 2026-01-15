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
        $authHeader = $request->header('Authorization');
        
        if (empty($authHeader)) {
            $this->sendError('Authentication required', 401);
        }

        // Remove 'Bearer ' prefix and trim whitespace
        $token = $authHeader;
        if (stripos($token, 'Bearer ') === 0) {
            $token = substr($token, 7);
        }
        $token = trim($token);

        if (empty($token)) {
            $this->sendError('Invalid token format', 401);
        }

        $tokenModel = new Token();
        $tokenData = $tokenModel->findByToken($token);

        if (!$tokenData) {
            // Log for debugging (remove in production)
            error_log("Token not found in database. Token length: " . strlen($token) . ", First 10 chars: " . substr($token, 0, 10));
            $this->sendError('Invalid or expired token', 401);
        }

        if (time() > strtotime($tokenData['expires_at'])) {
            $this->sendError('Token expired', 401);
        }

        if (!isset($tokenData['user']) || !is_array($tokenData['user'])) {
            $this->sendError('Invalid token data', 401);
        }
        
        $request->setUser($tokenData['user']);
        return true;
    }
    
    private function sendError(string $message, int $code): void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => $message
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
