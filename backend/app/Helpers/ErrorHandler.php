<?php

namespace App\Helpers;

/**
 * Error Handler Class
 * Handles all errors and exceptions, logs them, and returns JSON responses
 */
class ErrorHandler
{
    private static string $logFile;
    private static bool $initialized = false;

    /**
     * Initialize error handling
     */
    public static function init(): void
    {
        if (self::$initialized) {
            return;
        }

        // Set log file path
        $logDir = __DIR__ . '/../../storage/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        self::$logFile = $logDir . '/error.log';

        // Set error handler
        set_error_handler([self::class, 'handleError']);
        
        // Set exception handler
        set_exception_handler([self::class, 'handleException']);
        
        // Set shutdown handler for fatal errors
        register_shutdown_function([self::class, 'handleShutdown']);

        self::$initialized = true;
    }

    /**
     * Handle PHP errors
     */
    public static function handleError(int $errno, string $errstr, string $errfile, int $errline): bool
    {
        // Don't handle errors that are suppressed with @
        if (!(error_reporting() & $errno)) {
            return false;
        }

        $error = [
            'type' => self::getErrorType($errno),
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        // Log the error
        self::logError($error);

        // Return JSON response for API
        self::sendErrorResponse('PHP Error: ' . $errstr, 500, $error);

        return true; // Don't execute PHP's internal error handler
    }

    /**
     * Handle uncaught exceptions
     */
    public static function handleException(\Throwable $exception): void
    {
        $error = [
            'type' => 'Exception',
            'class' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'timestamp' => date('Y-m-d H:i:s')
        ];

        // Log the exception
        self::logError($error);

        // Return JSON response
        self::sendErrorResponse('Exception: ' . $exception->getMessage(), 500, [
            'error' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine()
        ]);
    }

    /**
     * Handle fatal errors
     */
    public static function handleShutdown(): void
    {
        $error = error_get_last();
        
        if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
            $errorData = [
                'type' => 'Fatal Error',
                'message' => $error['message'],
                'file' => $error['file'],
                'line' => $error['line'],
                'timestamp' => date('Y-m-d H:i:s')
            ];

            // Log the fatal error
            self::logError($errorData);

            // Return JSON response
            self::sendErrorResponse('Fatal Error: ' . $error['message'], 500, [
                'error' => $error['message'],
                'file' => $error['file'],
                'line' => $error['line']
            ]);
        }
    }

    /**
     * Log error to file
     */
    private static function logError(array $error): void
    {
        if (!isset(self::$logFile)) {
            $logDir = __DIR__ . '/../../storage/logs';
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }
            self::$logFile = $logDir . '/error.log';
        }

        $logEntry = sprintf(
            "[%s] %s: %s in %s:%d\n",
            $error['timestamp'],
            $error['type'],
            $error['message'],
            $error['file'],
            $error['line']
        );

        if (isset($error['trace'])) {
            $logEntry .= "Stack trace:\n" . $error['trace'] . "\n";
        }

        $logEntry .= str_repeat('-', 80) . "\n";

        // Write to log file (append mode)
        @file_put_contents(self::$logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }

    /**
     * Send JSON error response
     */
    private static function sendErrorResponse(string $message, int $statusCode, array $details = []): void
    {
        // Clear all output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }

        // Set headers
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');

        // Build response
        $response = [
            'success' => false,
            'message' => $message
        ];

        // Add details (can be filtered based on environment)
        $config = require __DIR__ . '/../../config/app.php';
        if (!empty($details) && ($config['debug'] ?? false)) {
            $response['error'] = $details;
        } elseif (!empty($details)) {
            // In production, only show safe error info
            $response['error'] = [
                'message' => $details['message'] ?? $details['error'] ?? 'An error occurred'
            ];
        }

        // Output JSON
        $json = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        
        if ($json === false) {
            // If JSON encoding fails, send minimal response
            http_response_code(500);
            echo '{"success":false,"message":"Internal server error"}';
        } else {
            echo $json;
        }
        
        exit;
    }

    /**
     * Get error type name
     */
    private static function getErrorType(int $errno): string
    {
        $types = [
            E_ERROR => 'Error',
            E_WARNING => 'Warning',
            E_PARSE => 'Parse Error',
            E_NOTICE => 'Notice',
            E_CORE_ERROR => 'Core Error',
            E_CORE_WARNING => 'Core Warning',
            E_COMPILE_ERROR => 'Compile Error',
            E_COMPILE_WARNING => 'Compile Warning',
            E_USER_ERROR => 'User Error',
            E_USER_WARNING => 'User Warning',
            E_USER_NOTICE => 'User Notice',
            E_STRICT => 'Strict',
            E_RECOVERABLE_ERROR => 'Recoverable Error',
            E_DEPRECATED => 'Deprecated',
            E_USER_DEPRECATED => 'User Deprecated'
        ];

        return $types[$errno] ?? 'Unknown Error';
    }
}
