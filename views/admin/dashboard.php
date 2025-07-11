<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Admin Dashboard') ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }
        .container { max-width: 1000px; margin: 0 auto; }
        .header { background: #dc3545; color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .content { background: #f9f9f9; padding: 20px; border-radius: 8px; }
        .nav { margin-bottom: 20px; }
        .nav a { margin-right: 15px; color: #007cba; text-decoration: none; }
        .nav a:hover { text-decoration: underline; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; text-align: center; border-left: 4px solid #dc3545; }
        .stat-number { font-size: 2em; font-weight: bold; color: #dc3545; margin-bottom: 10px; }
        .stat-label { color: #666; font-size: 0.9em; }
        .admin-notice { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; color: #856404; }
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
            <a href="/admin/settings">Settings</a>
        </div>
        
        <div class="header">
            <h1><?= htmlspecialchars($title ?? 'Admin Dashboard') ?></h1>
        </div>
        
        <div class="content">
            <div class="admin-notice">
                <strong>🔒 Protected Area:</strong> This page demonstrates route grouping and middleware!<br>
                Middleware: ['auth', 'admin'] | Route Group: 'admin' prefix
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?= htmlspecialchars($stats['users'] ?? 0) ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-number"><?= htmlspecialchars($stats['posts'] ?? 0) ?></div>
                    <div class="stat-label">Total Posts</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-number"><?= htmlspecialchars($stats['views'] ?? 0) ?></div>
                    <div class="stat-label">Page Views</div>
                </div>
            </div>
            
            <div style="background: white; padding: 20px; border-radius: 8px;">
                <h3>Admin Actions</h3>
                <p>Controller: AdminController@dashboard</p>
                <p>This is a protected admin area that requires both authentication and admin privileges.</p>
                <p><a href="/admin/settings">Go to Settings</a></p>
            </div>
        </div>
    </div>
</body>
</html>
