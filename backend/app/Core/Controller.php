<?php

namespace App\Core;

/**
 * Base Controller Class
 * All controllers must extend this class
 */
abstract class Controller
{
    protected Request $request;

    public function __construct()
    {
        $this->request = new Request();
    }

    /**
     * Send JSON response
     * Ensures consistent JSON API responses
     */
    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        
        // Encode JSON with proper flags for UTF-8 and pretty print in development
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Fallback error response if JSON encoding fails
            http_response_code(500);
            $json = json_encode([
                'success' => false,
                'message' => 'Internal server error: Failed to encode response'
            ]);
        }
        
        echo $json;
        exit;
    }

    /**
     * Send success response
     */
    protected function success(array $data = [], string $message = 'Success', int $statusCode = 200): void
    {
        $this->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }

    /**
     * Send error response
     */
    protected function error(string $message, int $statusCode = 400, array $errors = []): void
    {
        $response = [
            'success' => false,
            'message' => $message
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        $this->json($response, $statusCode);
    }

    /**
     * Validate required fields
     */
    protected function validateRequired(array $data, array $required): array
    {
        $errors = [];
        
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $errors[$field] = ucfirst($field) . ' is required';
            }
        }
        
        return $errors;
    }
}
