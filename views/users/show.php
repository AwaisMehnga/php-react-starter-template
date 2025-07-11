<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'User Profile') ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }
        .container { max-width: 800px; margin: 0 auto; }
        .header { background: #007cba; color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .content { background: #f9f9f9; padding: 20px; border-radius: 8px; }
        .nav { margin-bottom: 20px; }
        .nav a { margin-right: 15px; color: #007cba; text-decoration: none; }
        .nav a:hover { text-decoration: underline; }
        .profile-card { background: white; padding: 20px; border-radius: 8px; border-left: 4px solid #28a745; }
        .profile-card h2 { margin: 0 0 15px 0; color: #333; }
        .profile-info { margin-bottom: 10px; }
        .profile-info strong { color: #555; }
        .badge { padding: 3px 8px; border-radius: 3px; font-size: 12px; font-weight: bold; }
        .badge-admin { background: #dc3545; color: white; }
        .badge-user { background: #6c757d; color: white; }
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
            <h1><?= htmlspecialchars($title ?? 'User Profile') ?></h1>
        </div>
        
        <div class="content">
            <p><strong>This page demonstrates route parameters and model methods!</strong></p>
            <p>Controller: UserController@show | Model: User::find()</p>
            
            <?php if ($user): ?>
                <div class="profile-card">
                    <h2><?= htmlspecialchars($user->getFullName()) ?></h2>
                    
                    <div class="profile-info">
                        <strong>ID:</strong> <?= htmlspecialchars($user->id) ?>
                    </div>
                    
                    <div class="profile-info">
                        <strong>Email:</strong> <?= htmlspecialchars($user->email) ?>
                    </div>
                    
                    <div class="profile-info">
                        <strong>Role:</strong> 
                        <span class="badge <?= $user->isAdmin() ? 'badge-admin' : 'badge-user' ?>">
                            <?= $user->isAdmin() ? 'Admin' : 'User' ?>
                        </span>
                    </div>
                    
                    <div style="margin-top: 20px;">
                        <a href="/api/users/<?= $user->id ?>" target="_blank">View JSON API</a>
                    </div>
                </div>
            <?php else: ?>
                <p>User not found.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
