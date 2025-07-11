<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Admin Settings') ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }
        .container { max-width: 800px; margin: 0 auto; }
        .header { background: #dc3545; color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .content { background: #f9f9f9; padding: 20px; border-radius: 8px; }
        .nav { margin-bottom: 20px; }
        .nav a { margin-right: 15px; color: #007cba; text-decoration: none; }
        .nav a:hover { text-decoration: underline; }
        .settings-form { background: white; padding: 20px; border-radius: 8px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #333; }
        .form-group input, .form-group select, .form-group textarea { 
            width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; 
        }
        .btn { background: #dc3545; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        .btn:hover { background: #c82333; }
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
            <h1><?= htmlspecialchars($title ?? 'Admin Settings') ?></h1>
        </div>
        
        <div class="content">
            <p><strong>Protected admin settings page</strong></p>
            <p>Controller: AdminController@settings</p>
            
            <div class="settings-form">
                <h3>Application Settings</h3>
                <form method="post" action="/admin/settings">
                    <div class="form-group">
                        <label for="app_name">Application Name</label>
                        <input type="text" id="app_name" name="app_name" value="Tool Site" />
                    </div>
                    
                    <div class="form-group">
                        <label for="debug_mode">Debug Mode</label>
                        <select id="debug_mode" name="debug_mode">
                            <option value="true" selected>Enabled</option>
                            <option value="false">Disabled</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="maintenance_message">Maintenance Message</label>
                        <textarea id="maintenance_message" name="maintenance_message" rows="3" placeholder="Enter maintenance message..."></textarea>
                    </div>
                    
                    <button type="submit" class="btn">Save Settings</button>
                </form>
            </div>
            
            <div style="margin-top: 20px; padding: 15px; background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 5px; color: #0c5460;">
                <strong>Note:</strong> This form is just for demonstration. Form handling would be implemented in the controller.
            </div>
        </div>
    </div>
</body>
</html>
