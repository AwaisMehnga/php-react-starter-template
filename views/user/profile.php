<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'User Profile') ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }

        .profile-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .profile-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .profile-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .info-item {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }

        .info-label {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        .info-value {
            color: #666;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #007bff;
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <a href="/" class="back-link">← Back to Home</a>

    <div class="profile-card">
        <div class="profile-header">
            <h1>User Profile</h1>
            <h2><?= htmlspecialchars($name ?? 'Unknown User') ?></h2>
        </div>

        <?php if (!empty($user_data)): ?>
            <div class="profile-info">
                <div class="info-item">
                    <div class="info-label">Name</div>
                    <div class="info-value"><?= htmlspecialchars($user_data['name'] ?? 'N/A') ?></div>
                </div>

                <div class="info-item">
                    <div class="info-label">Email</div>
                    <div class="info-value"><?= htmlspecialchars($user_data['email'] ?? 'N/A') ?></div>
                </div>

                <div class="info-item">
                    <div class="info-label">Member Since</div>
                    <div class="info-value"><?= htmlspecialchars($user_data['joined'] ?? 'N/A') ?></div>
                </div>

                <div class="info-item">
                    <div class="info-label">Bio</div>
                    <div class="info-value"><?= htmlspecialchars($user_data['bio'] ?? 'No bio available') ?></div>
                </div>
            </div>
        <?php else: ?>
            <p>No user data available.</p>
        <?php endif; ?>
    </div>
</body>

</html>