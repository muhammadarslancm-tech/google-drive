<?php

/**
 * Bootstrap file
 * Autoloads classes and initializes the application
 */

// Define base path (backend directory)
define('BASE_PATH', __DIR__);

// Autoloader with error handling
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = BASE_PATH . '/app/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    
    if (file_exists($file)) {
        try {
            require $file;
        } catch (\Throwable $e) {
            // Log autoloader errors
            $logDir = BASE_PATH . '/storage/logs';
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }
            $logFile = $logDir . '/error.log';
            $logEntry = sprintf(
                "[%s] Autoloader Error: Failed to load %s from %s\nError: %s\n%s\n",
                date('Y-m-d H:i:s'),
                $class,
                $file,
                $e->getMessage(),
                str_repeat('-', 80) . "\n"
            );
            @file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
            
            // Re-throw to be caught by error handler
            throw new \Exception("Failed to load class {$class}: " . $e->getMessage());
        }
    } else {
        // Class file not found - log it
        $logDir = BASE_PATH . '/storage/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        $logFile = $logDir . '/error.log';
        $logEntry = sprintf(
            "[%s] Autoloader Warning: Class file not found for %s\nExpected: %s\n%s\n",
            date('Y-m-d H:i:s'),
            $class,
            $file,
            str_repeat('-', 80) . "\n"
        );
        @file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
});

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Set error log file
$logDir = __DIR__ . '/storage/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}
ini_set('error_log', $logDir . '/php-errors.log');

// Set timezone
date_default_timezone_set('UTC');

// Initialize custom error handler
require_once __DIR__ . '/app/Helpers/ErrorHandler.php';
\App\Helpers\ErrorHandler::init();

// Start output buffering to catch any unexpected output
ob_start();

// API Response Headers - Ensure all API responses are JSON
header('Content-Type: application/json; charset=utf-8');

// CORS headers for API
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Max-Age: 86400');

// Handle preflight OPTIONS requests (only in web context)
if (php_sapi_name() !== 'cli' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(200);
    exit;
}
