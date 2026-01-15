<?php

/**
 * Public Entry Point
 * All API requests are routed through this file
 * 
 * Access via: /api/{route}
 * Example: POST /api/auth/register
 */

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

// Load bootstrap first
require_once __DIR__ . '/../bootstrap.php';

// Load routes
$router = require __DIR__ . '/../routes/api.php';

// Dispatch request
try {
    $router->dispatch();
} catch (Exception $e) {
    // Handle any errors gracefully
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error',
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
