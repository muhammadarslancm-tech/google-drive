<?php

/**
 * Public Entry Point
 * All API requests are routed through this file
 * 
 * Access via: /api/{route}
 * Example: POST /api/auth/register
 */

// Start output buffering early to catch any unexpected output
if (!ob_get_level()) {
    ob_start();
}

// Ensure REQUEST_URI is set correctly for API routes
// When accessed via rewrite or from root index.php, preserve the original URI
$currentUri = $_SERVER['REQUEST_URI'] ?? '';

// If REQUEST_URI doesn't start with /api/, we need to fix it
if (empty($currentUri) || strpos($currentUri, '/api/') !== 0) {
    // Priority 1: Check REDIRECT_URL (from mod_rewrite)
    if (isset($_SERVER['REDIRECT_URL']) && strpos($_SERVER['REDIRECT_URL'], '/api/') === 0) {
        $_SERVER['REQUEST_URI'] = $_SERVER['REDIRECT_URL'];
    }
    // Priority 2: Check PATH_INFO (from rewrite)
    elseif (isset($_SERVER['PATH_INFO']) && !empty($_SERVER['PATH_INFO'])) {
        $_SERVER['REQUEST_URI'] = '/api' . $_SERVER['PATH_INFO'];
    }
    // Priority 3: If we came from root index.php, it should already be set
    // But if not, try to construct from query string
    elseif (isset($_GET['path'])) {
        $_SERVER['REQUEST_URI'] = '/api/' . ltrim($_GET['path'], '/');
    }
    // Priority 4: If current URI is empty, default to /api/
    elseif (empty($currentUri)) {
        $_SERVER['REQUEST_URI'] = '/api/';
    }
}

// Load bootstrap first (this will initialize error handling)
require_once __DIR__ . '/../bootstrap.php';

// Load routes
try {
    $router = require __DIR__ . '/../routes/api.php';
    
    if (!$router) {
        throw new \Exception('Failed to load routes');
    }
} catch (\Throwable $e) {
    // Log route loading error
    $logDir = __DIR__ . '/../storage/logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    $logFile = $logDir . '/error.log';
    $logEntry = sprintf(
        "[%s] Route Loading Error: %s in %s:%d\n%s\n%s\n",
        date('Y-m-d H:i:s'),
        $e->getMessage(),
        $e->getFile(),
        $e->getLine(),
        $e->getTraceAsString(),
        str_repeat('-', 80) . "\n"
    );
    @file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    
    // Return JSON error
    while (ob_get_level()) {
        ob_end_clean();
    }
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load routes',
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// Dispatch request
try {
    $router->dispatch();
} catch (\Throwable $e) {
    // This catch is a fallback - ErrorHandler should catch most errors
    // But we'll handle it here too to ensure JSON response
    
    // Log the error
    $logDir = __DIR__ . '/../storage/logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    $logFile = $logDir . '/error.log';
    $logEntry = sprintf(
        "[%s] Exception: %s in %s:%d\n%s\n%s\n",
        date('Y-m-d H:i:s'),
        $e->getMessage(),
        $e->getFile(),
        $e->getLine(),
        $e->getTraceAsString(),
        str_repeat('-', 80) . "\n"
    );
    @file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    
    // Return JSON error response
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    
    // Clear any output buffers
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error',
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
