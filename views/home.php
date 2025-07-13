<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? "My App" ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; text-align: center; }
        p { color: #666; line-height: 1.6; }
        .cta { text-align: center; margin-top: 30px; }
        .btn { display: inline-block; padding: 12px 24px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome to Your PHP React App!</h1>
        <p>Your Laravel-style MVC application with React integration is ready to go.</p>
        <p>This is a placeholder homepage. You can customize it by editing <code>views/home.php</code></p>
        <div class="cta">
            <a href="https://awaismehnga.github.io/php-react-starter-template/" class="btn">View Documentation</a>
        </div>
    </div>
</body>
</html>