<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? "Billo Craft - POS & Invoice Generator" ?></title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            margin: 0; 
            padding: 0; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .navbar {
            background: rgba(255,255,255,0.1);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .navbar h1 {
            color: white;
            margin: 0;
        }
        
        .navbar-links {
            display: flex;
            gap: 1rem;
        }
        
        .navbar-links a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        
        .navbar-links a:hover {
            background: rgba(255,255,255,0.2);
        }
        
        .container { 
            max-width: 800px; 
            margin: 4rem auto; 
            background: white; 
            padding: 40px; 
            border-radius: 8px; 
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        h1 { color: #333; margin-bottom: 1rem; }
        h2 { color: #667eea; margin-bottom: 1rem; }
        p { color: #666; line-height: 1.6; margin-bottom: 1rem; }
        
        .cta { 
            margin-top: 30px; 
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn { 
            display: inline-block; 
            padding: 12px 24px; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white; 
            text-decoration: none; 
            border-radius: 5px;
            transition: opacity 0.3s;
        }
        
        .btn:hover {
            opacity: 0.9;
        }
        
        .btn-outline {
            background: transparent;
            border: 2px solid #667eea;
            color: #667eea;
        }
        
        .btn-outline:hover {
            background: #667eea;
            color: white;
        }
        
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin: 3rem 0;
        }
        
        .feature {
            padding: 1.5rem;
            border-radius: 8px;
            background: #f8f9fa;
        }
        
        .feature h3 {
            color: #333;
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>
    <?php
    // Session is already started by the framework/controller
    $isLoggedIn = isset($_SESSION['user_id']);
    ?>
    
    <nav class="navbar">
        <h1>Billo Craft</h1>
        <div class="navbar-links">
            <?php if ($isLoggedIn): ?>
                <a href="/dashboard">Dashboard</a>
                <a href="/logout">Logout</a>
            <?php else: ?>
                <a href="/login">Login</a>
                <a href="/register">Register</a>
            <?php endif; ?>
        </div>
    </nav>
    
    <div class="container">
        <h1>Welcome to Billo Craft!</h1>
        <h2>Personalized POS and Invoice Generator</h2>
        <p>Your complete business management solution with modern web technology. Manage your inventory, generate professional invoices, and track your business performance.</p>
        
        <div class="features">
            <div class="feature">
                <h3>🏪 Point of Sale</h3>
                <p>Fast and intuitive POS system for quick transactions</p>
            </div>
            <div class="feature">
                <h3>📄 Invoice Generation</h3>
                <p>Create professional invoices with custom branding</p>
            </div>
            <div class="feature">
                <h3>📊 Analytics</h3>
                <p>Track sales, inventory, and business performance</p>
            </div>
            <div class="feature">
                <h3>👥 Customer Management</h3>
                <p>Maintain customer records and purchase history</p>
            </div>
        </div>
        
        <div class="cta">
            <?php if ($isLoggedIn): ?>
                <a href="/dashboard" class="btn">Go to Dashboard</a>
            <?php else: ?>
                <a href="/register" class="btn">Get Started</a>
                <a href="/login" class="btn-outline">Login</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>