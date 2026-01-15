<?php

use App\Core\Router;
use App\Middleware\AuthMiddleware;

/**
 * API Routes
 */

$router = new Router();

// Authentication routes (no middleware)
$router->post('/auth/register', [\App\Controllers\AuthController::class, 'register']);
$router->post('/auth/login', [\App\Controllers\AuthController::class, 'login']);
$router->post('/auth/logout', [\App\Controllers\AuthController::class, 'logout'], [AuthMiddleware::class]);
$router->get('/auth/me', [\App\Controllers\AuthController::class, 'me'], [AuthMiddleware::class]);

// File routes (require authentication)
$router->post('/files/upload', [\App\Controllers\FileController::class, 'upload'], [AuthMiddleware::class]);
$router->get('/files', [\App\Controllers\FileController::class, 'index'], [AuthMiddleware::class]);
$router->get('/files/{id}', [\App\Controllers\FileController::class, 'show'], [AuthMiddleware::class]);
$router->get('/files/{id}/download', [\App\Controllers\FileController::class, 'download'], [AuthMiddleware::class]);
$router->put('/files/{id}', [\App\Controllers\FileController::class, 'update'], [AuthMiddleware::class]);
$router->delete('/files/{id}', [\App\Controllers\FileController::class, 'delete'], [AuthMiddleware::class]);
$router->post('/files/{id}/restore', [\App\Controllers\FileController::class, 'restore'], [AuthMiddleware::class]);
$router->delete('/files/{id}/permanent', [\App\Controllers\FileController::class, 'permanentDelete'], [AuthMiddleware::class]);
$router->get('/files/storage/stats', [\App\Controllers\FileController::class, 'storage'], [AuthMiddleware::class]);

// Folder routes (require authentication)
$router->post('/folders', [\App\Controllers\FolderController::class, 'create'], [AuthMiddleware::class]);
$router->get('/folders', [\App\Controllers\FolderController::class, 'index'], [AuthMiddleware::class]);
$router->get('/folders/{id}', [\App\Controllers\FolderController::class, 'show'], [AuthMiddleware::class]);
$router->put('/folders/{id}', [\App\Controllers\FolderController::class, 'update'], [AuthMiddleware::class]);
$router->delete('/folders/{id}', [\App\Controllers\FolderController::class, 'delete'], [AuthMiddleware::class]);
$router->post('/folders/{id}/restore', [\App\Controllers\FolderController::class, 'restore'], [AuthMiddleware::class]);
$router->delete('/folders/{id}/permanent', [\App\Controllers\FolderController::class, 'permanentDelete'], [AuthMiddleware::class]);
$router->get('/folders/{id}/path', [\App\Controllers\FolderController::class, 'path'], [AuthMiddleware::class]);

// Share routes (require authentication)
$router->post('/shares', [\App\Controllers\ShareController::class, 'create'], [AuthMiddleware::class]);
$router->get('/shares/resource/{resourceId}/{resourceType}', [\App\Controllers\ShareController::class, 'resourceShares'], [AuthMiddleware::class]);
$router->get('/shares/with-me', [\App\Controllers\ShareController::class, 'sharedWithMe'], [AuthMiddleware::class]);
$router->get('/shares/by-me', [\App\Controllers\ShareController::class, 'sharedByMe'], [AuthMiddleware::class]);
$router->delete('/shares/{id}', [\App\Controllers\ShareController::class, 'delete'], [AuthMiddleware::class]);

return $router;
