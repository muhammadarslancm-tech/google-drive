<?php

namespace App\Core;

/**
 * Router Class
 * Handles routing and middleware
 */
class Router
{
    private array $routes = [];
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

        // Remove /backend/public/index.php if present (for direct access)
        if (strpos($requestPath, '/backend/public/index.php') === 0) {
            $requestPath = substr($requestPath, strlen('/backend/public/index.php'));
        }
        
        // Remove /api prefix if present
        if (strpos($requestPath, '/api') === 0) {
            $requestPath = substr($requestPath, 4);
        }
        
        // Ensure path starts with /
        if (empty($requestPath) || $requestPath[0] !== '/') {
            $requestPath = '/' . $requestPath;
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
                if (is_array($route['handler']) && count($route['handler']) === 2) {
                    [$controllerClass, $method] = $route['handler'];
                    $controller = new $controllerClass();
                    $controller->$method(...array_values($params));
                } elseif (is_callable($route['handler'])) {
                    call_user_func($route['handler'], ...array_values($params));
                }
                
                return;
            }
        }

        // No route found
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Route not found'
        ]);
    }
}
