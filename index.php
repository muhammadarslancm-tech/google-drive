<?php
/**
 * Root Entry Point
 * Handles frontend routing and authentication checks
 * 
 * API routes (/api/*) are routed to backend/public/index.php
 */

// CRITICAL: Handle API requests first, before any HTML output
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Check if this is an API request
if (strpos($requestUri, '/api/') === 0 || $requestUri === '/api') {
    // Route to backend API handler
    // CRITICAL: Preserve the original REQUEST_URI exactly as received
    // Don't modify it, let the backend handle path processing
    $_SERVER['REQUEST_URI'] = $requestUri;
    $_SERVER['REQUEST_METHOD'] = $requestMethod;
    
    // Don't set SCRIPT_NAME to avoid path processing issues
    // The backend will handle path extraction correctly
    
    // Include the API handler
    require_once __DIR__ . '/backend/public/index.php';
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Google Drive Manager</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style type="text/css">
        body {
            background-color: #f1f3f7;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }

        .loading-container {
            text-align: center;
            padding: 2rem;
        }

        .spinner-border {
            width: 3rem;
            height: 3rem;
            border-width: 0.3em;
            color: #3b76e1;
        }

        .loading-text {
            margin-top: 1rem;
            color: #666;
            font-size: 1.1rem;
        }
    </style>
</head>
<body>
    <div class="loading-container">
        <div class="spinner-border" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <div class="loading-text">Checking authentication...</div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script type="text/javascript">
        const API_BASE_URL = '/api';

        /**
         * Check authentication status and redirect accordingly
         */
        async function checkAuthentication() {
            const token = localStorage.getItem('token');
            const user = localStorage.getItem('user');

            // If no token or user, redirect to login
            if (!token || !user) {
                window.location.href = '/login.php';
                return;
            }

            // Verify token with API
            try {
                const response = await axios.get(API_BASE_URL + '/auth/me', {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    }
                });

                // If token is valid, redirect to dashboard
                if (response.data.success && response.data.data.user) {
                    // Update user data in localStorage
                    localStorage.setItem('user', JSON.stringify(response.data.data.user));
                    window.location.href = '/frontend/dashboard.html';
                } else {
                    // Invalid response, clear storage and redirect to login
                    localStorage.removeItem('token');
                    localStorage.removeItem('user');
                    window.location.href = '/login.php';
                }
            } catch (error) {
                // Token is invalid or expired
                console.error('Authentication error:', error);
                
                // Clear invalid token
                localStorage.removeItem('token');
                localStorage.removeItem('user');
                
                // Redirect to login
                window.location.href = '/login.php';
            }
        }

        // Check authentication on page load
        checkAuthentication();
    </script>
</body>
</html>
