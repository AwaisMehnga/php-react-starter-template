<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Billo Craft</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .navbar h1 {
            font-size: 1.5rem;
        }
        
        .navbar-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .logout-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        
        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
        }
        
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        .welcome-card {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .welcome-card h2 {
            color: #333;
            margin-bottom: 1rem;
        }
        
        .welcome-card p {
            color: #666;
            line-height: 1.6;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-card h3 {
            color: #667eea;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .stat-card p {
            color: #666;
        }
        
        .quick-actions {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .quick-actions h3 {
            color: #333;
            margin-bottom: 1rem;
        }
        
        .action-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }
        
        .action-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            transition: opacity 0.3s;
        }
        
        .action-btn:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <h1>Billo Craft</h1>
        <div class="navbar-right">
            <div class="user-info">
                <span>Welcome, <?= htmlspecialchars($user['name']) ?>!</span>
            </div>
            <a href="/logout" class="logout-btn">Logout</a>
        </div>
    </nav>
    
    <div class="container">
        <div class="welcome-card">
            <h2>Dashboard</h2>
            <p>Welcome to your Billo Craft dashboard. Here you can manage your POS system, generate invoices, and track your business performance.</p>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>15</h3>
                <p>Total Orders Today</p>
            </div>
            <div class="stat-card">
                <h3>₹45,230</h3>
                <p>Revenue This Month</p>
            </div>
            <div class="stat-card">
                <h3>127</h3>
                <p>Active Products</p>
            </div>
            <div class="stat-card">
                <h3>8</h3>
                <p>Pending Invoices</p>
            </div>
        </div>
        
        <div class="quick-actions">
            <h3>Quick Actions</h3>
            <div class="action-buttons">
                <a href="#" class="action-btn">Create New Invoice</a>
                <a href="#" class="action-btn">Add Product</a>
                <a href="#" class="action-btn">View Reports</a>
                <a href="#" class="action-btn">Manage Customers</a>
            </div>
        </div>
    </div>
</body>
</html>
