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
        // Clear any output buffers first
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        
        // Encode JSON with proper flags for UTF-8
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Log JSON encoding error
            $logDir = __DIR__ . '/../../storage/logs';
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }
            $logFile = $logDir . '/error.log';
            $logEntry = sprintf(
                "[%s] JSON Encoding Error: %s\nData: %s\n%s\n",
                date('Y-m-d H:i:s'),
                json_last_error_msg(),
                print_r($data, true),
                str_repeat('-', 80) . "\n"
            );
            @file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
            
            // Fallback error response if JSON encoding fails
            http_response_code(500);
            $json = json_encode([
                'success' => false,
                'message' => 'Internal server error: Failed to encode response',
                'error' => json_last_error_msg()
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
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
