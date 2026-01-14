<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Reset Password - Google Drive Manager</title>
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

        .btn-submit {
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

        .btn-submit:hover {
            background: #2d5aa8;
            color: white;
        }

        .btn-submit:active {
            background: #245590;
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

        .password-requirements {
            background: #f8f9fa;
            border: 1px solid #e0e0e0;
            border-radius: 0.25rem;
            padding: 0.85rem;
            margin-bottom: 1.2rem;
            font-size: 0.85rem;
        }

        .password-requirements h6 {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
            font-size: 0.85rem;
        }

        .requirement-item {
            display: flex;
            align-items: center;
            margin-bottom: 0.4rem;
            color: #666;
        }

        .requirement-item i {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #ddd;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 0.6rem;
            font-size: 0.65rem;
            font-weight: bold;
            flex-shrink: 0;
        }

        .requirement-item.met i {
            background: #28a745;
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

        .back-link {
            display: inline-block;
            margin-top: 1rem;
            color: #3b76e1;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .success-box {
            text-align: center;
            padding: 1.5rem 0;
        }

        .success-icon {
            font-size: 3rem;
            color: #28a745;
            margin-bottom: 1rem;
        }

        .success-box h4 {
            color: #333;
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }

        .success-box p {
            color: #666;
            font-size: 0.9rem;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h2>Reset Password</h2>
                <p>Create a new strong password</p>
            </div>

            <div class="auth-body">
                <form method="POST" action="#" id="resetPasswordForm">
                    <div class="form-group">
                        <label class="form-label" for="newPassword">New Password</label>
                        <input type="password" class="form-control" id="newPassword" name="newPassword" placeholder="Enter new password" required>
                        <div class="password-strength">
                            <div class="password-strength-bar" id="strengthBar"></div>
                        </div>
                    </div>

                    <div class="password-requirements">
                        <h6>Password Requirements:</h6>
                        <div class="requirement-item" id="req-length">
                            <i>✓</i>
                            <span>At least 8 characters</span>
                        </div>
                        <div class="requirement-item" id="req-uppercase">
                            <i>✓</i>
                            <span>One uppercase letter</span>
                        </div>
                        <div class="requirement-item" id="req-lowercase">
                            <i>✓</i>
                            <span>One lowercase letter</span>
                        </div>
                        <div class="requirement-item" id="req-number">
                            <i>✓</i>
                            <span>One number</span>
                        </div>
                        <div class="requirement-item" id="req-special">
                            <i>✓</i>
                            <span>One special character</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="confirmPassword">Confirm Password</label>
                        <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" placeholder="Confirm your password" required>
                    </div>

                    <button type="submit" class="btn btn-submit">Update Password</button>
                </form>

                <a href="login.php" class="back-link">Back to Sign In</a>
            </div>

            <div class="auth-footer">
                <p>&copy; 2026 Google Drive Manager. All rights reserved.</p>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $('#newPassword').on('input', function() {
                var password = $(this).val();
                var strength = 0;
                
                var hasLength = password.length >= 8;
                if (hasLength) {
                    strength++;
                    $('#req-length').addClass('met');
                } else {
                    $('#req-length').removeClass('met');
                }

                var hasUppercase = /[A-Z]/.test(password);
                if (hasUppercase) {
                    strength++;
                    $('#req-uppercase').addClass('met');
                } else {
                    $('#req-uppercase').removeClass('met');
                }

                var hasLowercase = /[a-z]/.test(password);
                if (hasLowercase) {
                    strength++;
                    $('#req-lowercase').addClass('met');
                } else {
                    $('#req-lowercase').removeClass('met');
                }

                var hasNumber = /[0-9]/.test(password);
                if (hasNumber) {
                    strength++;
                    $('#req-number').addClass('met');
                } else {
                    $('#req-number').removeClass('met');
                }

                var hasSpecial = /[^a-zA-Z0-9]/.test(password);
                if (hasSpecial) {
                    strength++;
                    $('#req-special').addClass('met');
                } else {
                    $('#req-special').removeClass('met');
                }

                var strengthBar = $('#strengthBar');
                strengthBar.removeClass('password-strength-weak password-strength-fair password-strength-good');
                
                if (strength < 2) {
                    strengthBar.addClass('password-strength-weak');
                } else if (strength < 4) {
                    strengthBar.addClass('password-strength-fair');
                } else {
                    strengthBar.addClass('password-strength-good');
                }
            });

            $('#resetPasswordForm').on('submit', function(e) {
                e.preventDefault();
                var newPassword = $('#newPassword').val();
                var confirmPassword = $('#confirmPassword').val();

                if (newPassword == '' || confirmPassword == '') {
                    alert('Please fill in all fields');
                    return false;
                }

                if (newPassword != confirmPassword) {
                    alert('Passwords do not match');
                    return false;
                }

                if (newPassword.length < 8) {
                    alert('Password must be at least 8 characters long');
                    return false;
                }

                if (!/[A-Z]/.test(newPassword) || !/[a-z]/.test(newPassword) || 
                    !/[0-9]/.test(newPassword) || !/[^a-zA-Z0-9]/.test(newPassword)) {
                    alert('Password does not meet all requirements');
                    return false;
                }

                var successHtml = `
                    <div class="success-box">
                        <div class="success-icon">✓</div>
                        <h4>Password Reset Successful</h4>
                        <p>Your password has been reset successfully. You can now sign in with your new password.</p>
                        <a href="login.php" style="display: inline-block; margin-top: 1.5rem; background: #3b76e1; color: white; padding: 0.6rem 1.5rem; text-decoration: none; border-radius: 0.25rem; font-weight: 500;">Go to Sign In</a>
                    </div>
                `;
                
                $('#resetPasswordForm').replaceWith(successHtml);
            });
        });
    </script>
</body>
</html>
