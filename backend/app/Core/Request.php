<?php

namespace App\Core;

/**
 * Request Class
 * Handles HTTP request data
 */
class Request
{
    private array $data = [];
    private ?array $user = null;

    public function __construct()
    {
        $this->data = array_merge($_GET, $_POST);
        
        // Handle JSON body
        $json = file_get_contents('php://input');
        if (!empty($json)) {
            $decoded = json_decode($json, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $this->data = array_merge($this->data, $decoded);
            }
        }
    }

    /**
     * Get request data by key
     */
    public function get(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * Get all request data
     */
    public function all(): array
    {
        return $this->data;
    }

    /**
     * Get request method
     */
    public function method(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    /**
     * Get request URI
     */
    public function uri(): string
    {
        return $_SERVER['REQUEST_URI'] ?? '/';
    }

    /**
     * Get request path
     */
    public function path(): string
    {
        // Priority 1: REQUEST_URI (most reliable)
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        
        // Priority 2: REDIRECT_URL (from mod_rewrite)
        if (empty($uri) && isset($_SERVER['REDIRECT_URL'])) {
            $uri = $_SERVER['REDIRECT_URL'];
        }
        
        // Priority 3: PATH_INFO (from rewrite)
        if (empty($uri) && isset($_SERVER['PATH_INFO'])) {
            $uri = $_SERVER['PATH_INFO'];
        }
        
        // Priority 4: Default to root
        if (empty($uri)) {
            return '/';
        }
        
        // Remove query string
        $path = parse_url($uri, PHP_URL_PATH);
        
        // If path is empty, return root
        if (empty($path) || $path === '') {
            return '/';
        }
        
        // Ensure path starts with /
        if ($path[0] !== '/') {
            $path = '/' . $path;
        }
        
        return $path;
    }

    /**
     * Get headers
     */
    public function headers(): array
    {
        $headers = [];
        
        // Try getallheaders() first (works in Apache)
        if (function_exists('getallheaders')) {
            $headers = getallheaders() ?: [];
        }
        
        // Fallback: manually extract from $_SERVER
        if (empty($headers)) {
            foreach ($_SERVER as $key => $value) {
                if (strpos($key, 'HTTP_') === 0) {
                    $headerName = str_replace('_', '-', substr($key, 5));
                    $headers[$headerName] = $value;
                }
            }
        }
        
        return $headers;
    }

    /**
     * Get header by name
     */
    public function header(string $name, $default = null)
    {
        $name = strtolower($name);
        
        // Check getallheaders() first
        $headers = $this->headers();
        foreach ($headers as $key => $value) {
            if (strtolower($key) === $name) {
                return $value;
            }
        }
        
        // Fallback: Check $_SERVER directly (for Authorization header especially)
        $serverKey = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        if (isset($_SERVER[$serverKey])) {
            return $_SERVER[$serverKey];
        }
        
        // Check REDIRECT_HTTP_AUTHORIZATION (for mod_rewrite)
        if ($name === 'authorization') {
            if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
                return $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
            }
            if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
                return $_SERVER['HTTP_AUTHORIZATION'];
            }
        }
        
        return $default;
    }

    /**
     * Get authenticated user
     */
    public function user(): ?array
    {
        return $this->user;
    }

    /**
     * Set authenticated user
     */
    public function setUser(array $user): void
    {
        $this->user = $user;
    }

    /**
     * Get client IP
     */
    public function ip(): string
    {
        return $_SERVER['HTTP_X_FORWARDED_FOR'] ?? 
               $_SERVER['HTTP_X_REAL_IP'] ?? 
               $_SERVER['REMOTE_ADDR'] ?? 
               'unknown';
    }

    /**
     * Get user agent
     */
    public function userAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    }
}
