<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Billo Craft</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .auth-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        
        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .auth-header h1 {
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .auth-header p {
            color: #666;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn {
            width: 100%;
            padding: 0.75rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: opacity 0.3s;
        }
        
        .btn:hover {
            opacity: 0.9;
        }
        
        .auth-footer {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #eee;
        }
        
        .auth-footer a {
            color: #667eea;
            text-decoration: none;
        }
        
        .auth-footer a:hover {
            text-decoration: underline;
        }
        
        .alert {
            padding: 0.75rem;
            margin-bottom: 1rem;
            border-radius: 5px;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <h1>Forgot Password</h1>
            <p>Enter your email to receive a reset link</p>
        </div>
        
        <?php
        if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($_SESSION['flash_error']) ?>
                <?php unset($_SESSION['flash_error']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($_SESSION['flash_success']) ?>
                <?php unset($_SESSION['flash_success']); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="/forgot-password">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <button type="submit" class="btn">Send Reset Link</button>
        </form>
        
        <div class="auth-footer">
            <p><a href="/login">Back to login</a></p>
        </div>
    </div>
</body>
</html>
