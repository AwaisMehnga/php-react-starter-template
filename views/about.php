<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'About') ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }
        .container { max-width: 800px; margin: 0 auto; }
        .header { background: #007cba; color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .content { background: #f9f9f9; padding: 20px; border-radius: 8px; }
        .nav { margin-bottom: 20px; }
        .nav a { margin-right: 15px; color: #007cba; text-decoration: none; }
        .nav a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <div class="nav">
            <a href="/">Home</a>
            <a href="/home">MVC Home</a>
            <a href="/about">About</a>
            <a href="/users">Users</a>
            <a href="/admin/dashboard">Admin</a>
        </div>
        
        <div class="header">
            <h1><?= htmlspecialchars($title ?? 'About') ?></h1>
        </div>
        
        <div class="content">
            <p><?= htmlspecialchars($content ?? 'Welcome to our site!') ?></p>
            <p><strong>This page was rendered using the MVC architecture!</strong></p>
            <p>Controller: HomeController@about</p>
        </div>
    </div>
</body>
</html>
