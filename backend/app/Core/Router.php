<?php

namespace App\Core;

/**
 * Router Class
 * Handles routing and middleware
 */
class Router
{
    public array $routes = [];
    private array $middleware = [];
    private Request $request;

    public function __construct()
    {
        $this->request = new Request();
    }

    /**
     * Register a route
     */
    public function add(string $method, string $path, $handler, array $middleware = []): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'handler' => $handler,
            'middleware' => $middleware
        ];
    }

    /**
     * Register GET route
     */
    public function get(string $path, $handler, array $middleware = []): void
    {
        $this->add('GET', $path, $handler, $middleware);
    }

    /**
     * Register POST route
     */
    public function post(string $path, $handler, array $middleware = []): void
    {
        $this->add('POST', $path, $handler, $middleware);
    }

    /**
     * Register PUT route
     */
    public function put(string $path, $handler, array $middleware = []): void
    {
        $this->add('PUT', $path, $handler, $middleware);
    }

    /**
     * Register DELETE route
     */
    public function delete(string $path, $handler, array $middleware = []): void
    {
        $this->add('DELETE', $path, $handler, $middleware);
    }

    /**
     * Match route pattern
     */
    private function matchRoute(string $routePath, string $requestPath): bool
    {
        // Convert route pattern to regex
        $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';
        
        return preg_match($pattern, $requestPath) === 1;
    }

    /**
     * Extract route parameters
     */
    private function extractParams(string $routePath, string $requestPath): array
    {
        $params = [];
        $routeParts = explode('/', trim($routePath, '/'));
        $requestParts = explode('/', trim($requestPath, '/'));
        
        foreach ($routeParts as $index => $part) {
            if (preg_match('/\{([^}]+)\}/', $part, $matches)) {
                $paramName = $matches[1];
                $params[$paramName] = $requestParts[$index] ?? null;
            }
        }
        
        return $params;
    }

    /**
     * Dispatch request
     */
    public function dispatch(): void
    {
        $requestMethod = $this->request->method();
        $requestPath = $this->request->path();
        
        // Get original URI for debugging
        $originalUri = $_SERVER['REQUEST_URI'] ?? '/';
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $pathInfo = $_SERVER['PATH_INFO'] ?? '';

        // Clean up the path for routing
        // The path should be like: /api/auth/login
        // We need to extract: /auth/login
        
        // Step 1: Remove /backend/public/index.php if present (direct access)
        if (strpos($requestPath, '/backend/public/index.php') === 0) {
            $requestPath = substr($requestPath, strlen('/backend/public/index.php'));
        }
        
        // Step 2: Remove script name ONLY if it's actually part of the path
        // For API routes: /api/auth/login, script name /index.php should NOT be removed
        // Only remove if path actually starts with script name
        if (!empty($scriptName) && strpos($requestPath, $scriptName) === 0) {
            // Only remove if script name is NOT /index.php (which is common for root routing)
            // OR if the path doesn't contain /api/ (meaning it's not an API call)
            if ($scriptName !== '/index.php' || strpos($requestPath, '/api/') === false) {
                $requestPath = substr($requestPath, strlen($scriptName));
            }
        }
        
        // Step 3: Remove /api prefix - all API routes are accessed via /api/*
        // This is the critical step - remove /api/ or /api to get the route path
        if (strpos($requestPath, '/api/') === 0) {
            $requestPath = substr($requestPath, 5); // Remove '/api/' -> /auth/login
        } elseif ($requestPath === '/api') {
            $requestPath = '/';
        } elseif (strpos($requestPath, '/api') === 0) {
            $requestPath = substr($requestPath, 4); // Remove '/api' -> /auth/login (if no trailing slash)
        }
        
        // Step 4: If path is empty or just '/', check PATH_INFO (from rewrite)
        if (($requestPath === '/' || empty($requestPath)) && !empty($pathInfo)) {
            $requestPath = $pathInfo;
            // Remove /api if present in PATH_INFO
            if (strpos($requestPath, '/api/') === 0) {
                $requestPath = substr($requestPath, 5);
            } elseif (strpos($requestPath, '/api') === 0) {
                $requestPath = substr($requestPath, 4);
            }
        }
        
        // Step 5: Ensure path starts with /
        if (empty($requestPath) || $requestPath[0] !== '/') {
            $requestPath = '/' . $requestPath;
        }
        
        // Step 6: Normalize path (remove trailing slashes except root)
        if ($requestPath !== '/' && substr($requestPath, -1) === '/') {
            $requestPath = rtrim($requestPath, '/');
        }

        foreach ($this->routes as $route) {
            if ($route['method'] === $requestMethod && $this->matchRoute($route['path'], $requestPath)) {
                // Execute middleware
                foreach ($route['middleware'] as $middlewareClass) {
                    $middleware = new $middlewareClass();
                    if (!$middleware->handle($this->request)) {
                        return; // Middleware stopped execution
                    }
                }

                // Extract parameters
                $params = $this->extractParams($route['path'], $requestPath);
                
                // Execute handler
                try {
                    if (is_array($route['handler']) && count($route['handler']) === 2) {
                        [$controllerClass, $method] = $route['handler'];
                        
                        // Check if controller class exists
                        if (!class_exists($controllerClass)) {
                            throw new \Exception("Controller class not found: {$controllerClass}");
                        }
                        
                        $controller = new $controllerClass();
                        
                        // Check if method exists
                        if (!method_exists($controller, $method)) {
                            throw new \Exception("Method not found: {$controllerClass}::{$method}()");
                        }
                        
                        $controller->$method(...array_values($params));
                    } elseif (is_callable($route['handler'])) {
                        call_user_func($route['handler'], ...array_values($params));
                    } else {
                        throw new \Exception("Invalid route handler");
                    }
                } catch (\Exception $e) {
                    http_response_code(500);
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => false,
                        'message' => 'Internal server error',
                        'error' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]);
                    return;
                }
                
                return;
            }
        }

        // No route found - return detailed error for debugging
        http_response_code(404);
        header('Content-Type: application/json');
        
        // Build available routes list
        $availableRoutes = [];
        foreach ($this->routes as $route) {
            $availableRoutes[] = $route['method'] . ' /api' . $route['path'];
        }
        
        // Check if user might have called wrong endpoint
        $suggestedRoute = null;
        if ($requestPath === '/login' && $requestMethod === 'POST') {
            $suggestedRoute = 'POST /api/auth/login';
        } elseif ($requestPath === '/register' && $requestMethod === 'POST') {
            $suggestedRoute = 'POST /api/auth/register';
        }
        
        $response = [
            'success' => false,
            'message' => 'Route not found',
            'error' => "No route found for {$requestMethod} {$requestPath}",
            'debug' => [
                'method' => $requestMethod,
                'request_path' => $requestPath,
                'original_uri' => $originalUri,
                'script_name' => $scriptName,
                'server_request_uri' => $_SERVER['REQUEST_URI'] ?? 'not set',
                'path_info' => $pathInfo,
            ],
            'available_routes' => $availableRoutes,
            'routes_count' => count($this->routes)
        ];
        
        if ($suggestedRoute) {
            $response['suggestion'] = "Did you mean: {$suggestedRoute}?";
        }
        
        echo json_encode($response, JSON_PRETTY_PRINT);
    }
}
