<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Login - Google Drive Manager</title>
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
            padding: 20px 0;
        }

        .auth-container {
            width: 100%;
            max-width: 400px;
            padding: 15px;
        }

        .auth-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 2px 3px #e4e8f0;
            overflow: hidden;
            border: 1px solid #eff0f2;
        }

        .auth-header {
            background: white;
            border-bottom: 1px solid #eff0f2;
            padding: 2rem 1.5rem;
            text-align: center;
        }

        .auth-header h2 {
            margin: 0;
            font-weight: 600;
            font-size: 1.5rem;
            color: #333;
        }

        .auth-header p {
            margin: 0.5rem 0 0 0;
            color: #666;
            font-size: 0.9rem;
        }

        .auth-body {
            padding: 2rem 1.5rem;
        }

        .form-group {
            margin-bottom: 1.2rem;
        }

        .form-control {
            border-radius: 0.25rem;
            padding: 0.5rem 0.75rem;
            border: 1px solid #e0e0e0;
            transition: border-color 0.2s ease;
            font-size: 0.9rem;
            background: #fff;
        }

        .form-control:focus {
            border-color: #999;
            box-shadow: none;
            outline: none;
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 0.4rem;
            color: #555;
            font-size: 0.85rem;
        }

        .btn-login {
            background: #3b76e1;
            border: none;
            color: white;
            font-weight: 500;
            padding: 0.6rem 1rem;
            border-radius: 0.25rem;
            transition: background 0.2s ease;
            width: 100%;
            font-size: 0.95rem;
        }

        .btn-login:hover {
            background: #2d5aa8;
            color: white;
        }

        .btn-login:active {
            background: #245590;
        }

        .form-check-input {
            border: 1px solid #ddd;
            width: 1.1rem;
            height: 1.1rem;
            margin-top: 0.15rem;
            border-radius: 0.2rem;
        }

        .form-check-input:checked {
            background-color: #3b76e1;
            border-color: #3b76e1;
        }

        .form-check-label {
            margin-left: 0.5rem;
            color: #666;
            font-size: 0.85rem;
            font-weight: 400;
        }

        .auth-footer {
            background: #f8f9fa;
            padding: 1.2rem;
            text-align: center;
            border-top: 1px solid #eff0f2;
        }

        .auth-footer p {
            margin: 0;
            color: #666;
            font-size: 0.85rem;
        }

        .auth-footer a {
            color: #3b76e1;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .auth-footer a:hover {
            color: #2d5aa8;
        }

        .forgot-password-link {
            color: #3b76e1;
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 400;
            transition: color 0.2s ease;
        }

        .forgot-password-link:hover {
            color: #2d5aa8;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h2>Sign In</h2>
                <p>Access your account</p>
            </div>

            <div class="auth-body">
                <form method="POST" action="#" id="loginForm">
                    <div class="form-group">
                        <label class="form-label" for="email">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="your@email.com" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password">Password</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                    </div>

                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="remember" name="remember">
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>
                        <a href="forgot-password.php" class="forgot-password-link">Forgot Password?</a>
                    </div>

                    <button type="submit" class="btn btn-login">Sign In</button>
                </form>

                <div style="text-align: center; margin-top: 1.5rem; font-size: 0.85rem; color: #666;">
                    Don't have an account? <a href="register.php" style="color: #3b76e1; text-decoration: none; font-weight: 500;">Create one</a>
                </div>
            </div>

            <div class="auth-footer">
                <p>&copy; 2026 Google Drive Manager. All rights reserved.</p>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script type="text/javascript">
        // API Configuration
        const API_BASE_URL = '/api';
        
        // Axios interceptor for error handling
        axios.interceptors.response.use(
            response => response,
            error => {
                // Handle network errors
                if (!error.response) {
                    console.error('Network error:', error);
                    return Promise.reject(error);
                }
                return Promise.reject(error);
            }
        );
        
        $(document).ready(async function() {
            // Check if already logged in and verify token
            const token = localStorage.getItem('token');
            if (token) {
                try {
                    const response = await axios.get(API_BASE_URL + '/auth/me', {
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Content-Type': 'application/json'
                        }
                    });
                    
                    // If token is valid, redirect to dashboard
                    if (response.data && response.data.success && response.data.data && response.data.data.user) {
                        window.location.href = '/frontend/dashboard.html';
                        return;
                    }
                } catch (error) {
                    // Token is invalid, clear it and continue with login
                    console.log('Token verification failed, continuing with login');
                    localStorage.removeItem('token');
                    localStorage.removeItem('user');
                }
            }
            
            // Form validation
            $('#loginForm').on('submit', function(e) {
                e.preventDefault();
                var email = $('#email').val();
                var password = $('#password').val();

                if (email == '' || password == '') {
                    alert('Please fill in all fields');
                    return false;
                }

                // Show loading state
                var submitBtn = $(this).find('button[type="submit"]');
                var originalText = submitBtn.text();
                submitBtn.prop('disabled', true).text('Signing in...');

                // Send login request to API
                axios.post(API_BASE_URL + '/auth/login', {
                    email: email,
                    password: password
                }, {
                    headers: {
                        'Content-Type': 'application/json'
                    }
                })
                .then(function(response) {
                    // Check if response is successful
                    if (response.data && response.data.success) {
                        // Store token and user data
                        if (response.data.data && response.data.data.token) {
                            localStorage.setItem('token', response.data.data.token);
                            if (response.data.data.user) {
                                localStorage.setItem('user', JSON.stringify(response.data.data.user));
                            }
                            window.location.href = '/frontend/dashboard.html';
                        } else {
                            alert('Login successful but no token received. Please try again.');
                            submitBtn.prop('disabled', false).text(originalText);
                        }
                    } else {
                        // Unexpected response format
                        var message = response.data?.message || 'Login failed. Please try again.';
                        alert(message);
                        submitBtn.prop('disabled', false).text(originalText);
                    }
                })
                .catch(function(error) {
                    // Handle different error types
                    var message = 'Login failed. Please try again.';
                    
                    if (error.response) {
                        // Server responded with error status
                        if (error.response.data && error.response.data.message) {
                            message = error.response.data.message;
                            
                            // Show validation errors if available
                            if (error.response.data.errors) {
                                var errorMessages = Object.values(error.response.data.errors).join('\n');
                                message = errorMessages || message;
                            }
                        } else {
                            message = `Error ${error.response.status}: ${error.response.statusText}`;
                        }
                    } else if (error.request) {
                        // Request made but no response
                        message = 'Network error: Could not connect to server. Please check your connection.';
                    } else {
                        // Something else happened
                        message = error.message || message;
                    }
                    
                    alert(message);
                    submitBtn.prop('disabled', false).text(originalText);
                });
            });
        });
    </script>
</body>
</html>
