<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Forgot Password - Google Drive Manager</title>
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

        .info-box {
            background: #f8f9fa;
            border-left: 3px solid #3b76e1;
            padding: 0.85rem;
            border-radius: 0.25rem;
            margin-bottom: 1.5rem;
        }

        .info-box p {
            margin: 0;
            color: #555;
            font-size: 0.85rem;
            line-height: 1.5;
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
                <h2>Forgot Password?</h2>
                <p>We'll help you reset it</p>
            </div>

            <div class="auth-body">
                <div class="info-box">
                    <p>Enter your email address and we'll send you a link to reset your password.</p>
                </div>

                <form method="POST" action="#" id="forgotPasswordForm">
                    <div class="form-group">
                        <label class="form-label" for="email">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="your@email.com" required>
                    </div>

                    <button type="submit" class="btn btn-submit">Send Reset Link</button>
                </form>

                <a href="login.php" class="back-link">Back to Sign In</a>
            </div>

            <div class="auth-footer">
                <p>Don't have an account? <a href="register.php">Create one</a></p>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $('#forgotPasswordForm').on('submit', function(e) {
                e.preventDefault();
                var email = $('#email').val();

                if (email == '') {
                    alert('Please enter your email address');
                    return false;
                }

                var successHtml = `
                    <div class="success-box">
                        <div class="success-icon">✓</div>
                        <h4>Check Your Email</h4>
                        <p>We've sent a password reset link to:<br><strong>${email}</strong></p>
                        <p style="font-size: 0.8rem; color: #999; margin-top: 0.5rem;">The link will expire in 24 hours.</p>
                    </div>
                `;
                
                $('#forgotPasswordForm').html(successHtml);
            });
        });
    </script>
</body>
</html>
