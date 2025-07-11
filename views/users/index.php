<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Users') ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }
        .container { max-width: 800px; margin: 0 auto; }
        .header { background: #007cba; color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .content { background: #f9f9f9; padding: 20px; border-radius: 8px; }
        .nav { margin-bottom: 20px; }
        .nav a { margin-right: 15px; color: #007cba; text-decoration: none; }
        .nav a:hover { text-decoration: underline; }
        .user-card { background: white; padding: 15px; margin-bottom: 10px; border-radius: 5px; border-left: 4px solid #007cba; }
        .user-card h3 { margin: 0 0 10px 0; }
        .user-card p { margin: 5px 0; color: #666; }
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
            <h1><?= htmlspecialchars($title ?? 'Users') ?></h1>
        </div>
        
        <div class="content">
            <p><strong>This page demonstrates the MVC pattern with models!</strong></p>
            <p>Controller: UserController@index | Model: User</p>
            
            <?php if (empty($users)): ?>
                <p>No users found.</p>
            <?php else: ?>
                <?php foreach ($users as $user): ?>
                    <div class="user-card">
                        <h3><?= htmlspecialchars($user->name) ?></h3>
                        <p><strong>Email:</strong> <?= htmlspecialchars($user->email) ?></p>
                        <p><strong>ID:</strong> <?= htmlspecialchars($user->id) ?></p>
                        <p><a href="/users/<?= $user->id ?>">View Profile</a></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
