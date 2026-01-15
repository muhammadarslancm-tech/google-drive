<?php

/**
 * Public Entry Point
 * All API requests are routed through this file
 */

require_once __DIR__ . '/../bootstrap.php';

// Load routes
$router = require __DIR__ . '/../../routes/api.php';

// Dispatch request
$router->dispatch();
