<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Register - Google Drive Manager</title>
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
            padding: 2rem 0;
        }

        .auth-container {
            width: 100%;
            max-width: 450px;
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

        .btn-register {
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

        .btn-register:hover {
            background: #2d5aa8;
            color: white;
        }

        .btn-register:active {
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

        .password-strength {
            height: 3px;
            border-radius: 2px;
            margin-top: 0.4rem;
            background: #e0e0e0;
            overflow: hidden;
        }

        .password-strength-bar {
            height: 100%;
            width: 0;
            transition: all 0.3s ease;
        }

        .password-strength-weak {
            background: #dc3545;
            width: 33%;
        }

        .password-strength-fair {
            background: #ffc107;
            width: 66%;
        }

        .password-strength-good {
            background: #28a745;
            width: 100%;
        }

        .password-hint {
            font-size: 0.8rem;
            color: #666;
            line-height: 1.4;
            margin-top: 0.4rem;
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
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        @media (max-width: 576px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h2>Create Account</h2>
                <p>Join and manage your files</p>
            </div>

            <div class="auth-body">
                <form method="POST" action="#" id="registerForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="firstName">First Name</label>
                            <input type="text" class="form-control" id="firstName" name="firstName" placeholder="First name" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="lastName">Last Name</label>
                            <input type="text" class="form-control" id="lastName" name="lastName" placeholder="Last name" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="email">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="your@email.com" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password">Password</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Create a strong password" required>
                        <div class="password-strength">
                            <div class="password-strength-bar" id="strengthBar"></div>
                        </div>
                        <div class="password-hint">At least 8 characters with uppercase, lowercase, numbers and symbols</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="confirmPassword">Confirm Password</label>
                        <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" placeholder="Confirm your password" required>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                        <label class="form-check-label" for="terms">
                            I agree to the <a href="#" style="color: #3b76e1; text-decoration: none;">Terms</a> and <a href="#" style="color: #3b76e1; text-decoration: none;">Privacy Policy</a>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-register">Create Account</button>
                </form>

                <div style="text-align: center; margin-top: 1.5rem; font-size: 0.85rem; color: #666;">
                    Already have an account? <a href="login.php" style="color: #3b76e1; text-decoration: none; font-weight: 500;">Sign In</a>
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
        
        $(document).ready(async function() {
            // Check if already logged in
            const token = localStorage.getItem('token');
            if (token) {
                try {
                    const response = await axios.get(API_BASE_URL + '/auth/me', {
                        headers: { 'Authorization': `Bearer ${token}` }
                    });
                    
                    if (response.data?.success && response.data.data?.user) {
                        window.location.href = '/frontend/dashboard.html';
                        return;
                    }
                } catch (error) {
                    localStorage.removeItem('token');
                    localStorage.removeItem('user');
                }
            }
            
            // Password strength indicator
            $('#password').on('input', function() {
                const password = $(this).val();
                let strength = 0;
                
                if (password.length >= 8) strength++;
                if (/[a-z]/.test(password)) strength++;
                if (/[A-Z]/.test(password)) strength++;
                if (/[0-9]/.test(password)) strength++;
                if (/[^a-zA-Z0-9]/.test(password)) strength++;

                const strengthBar = $('#strengthBar');
                strengthBar.removeClass('password-strength-weak password-strength-fair password-strength-good');
                
                if (strength < 2) {
                    strengthBar.addClass('password-strength-weak');
                } else if (strength < 4) {
                    strengthBar.addClass('password-strength-fair');
                } else {
                    strengthBar.addClass('password-strength-good');
                }
            });

            // Handle form submission
            $('#registerForm').on('submit', function(e) {
                e.preventDefault();
                const firstName = $('#firstName').val();
                const lastName = $('#lastName').val();
                const email = $('#email').val();
                const password = $('#password').val();
                const confirmPassword = $('#confirmPassword').val();
                const terms = $('#terms').is(':checked');

                if (!firstName || !lastName || !email || !password) {
                    alert('Please fill in all fields');
                    return;
                }

                if (password !== confirmPassword) {
                    alert('Passwords do not match');
                    return;
                }

                if (password.length < 8) {
                    alert('Password must be at least 8 characters long');
                    return;
                }

                if (!terms) {
                    alert('Please agree to the terms and conditions');
                    return;
                }

                const submitBtn = $(this).find('button[type="submit"]');
                const originalText = submitBtn.text();
                submitBtn.prop('disabled', true).text('Creating account...');

                axios.post(API_BASE_URL + '/auth/register', {
                    first_name: firstName,
                    last_name: lastName,
                    email: email,
                    password: password
                })
                    .then(function(response) {
                        if (response.data?.success && response.data.data?.token) {
                            const token = response.data.data.token;
                            localStorage.setItem('token', token);
                            if (response.data.data.user) {
                                localStorage.setItem('user', JSON.stringify(response.data.data.user));
                            }
                            // Small delay to ensure token is saved in database
                            setTimeout(() => {
                                window.location.href = '/frontend/dashboard.html';
                            }, 100);
                        } else {
                            alert(response.data?.message || 'Registration failed. Please try again.');
                            submitBtn.prop('disabled', false).text(originalText);
                        }
                    })
                    .catch(function(error) {
                        const message = error.response?.data?.message || 'Registration failed. Please try again.';
                        alert(message);
                        submitBtn.prop('disabled', false).text(originalText);
                    });
            });
        });
    </script>
</body>
</html>
