<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

try {
    echo "Checking sessions table...\n";
    
    $db = Database::getInstance()->getConnection();
    
    // Get all sessions
    $stmt = $db->prepare("SELECT * FROM sessions ORDER BY last_activity DESC");
    $stmt->execute();
    $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Total sessions: " . count($sessions) . "\n\n";
    
    if (count($sessions) > 0) {
        echo "Recent sessions:\n";
        echo str_repeat("-", 80) . "\n";
        printf("%-30s %-15s %-20s %-15s\n", "Session ID", "User ID", "IP Address", "Last Activity");
        echo str_repeat("-", 80) . "\n";
        
        foreach ($sessions as $session) {
            $lastActivity = date('Y-m-d H:i:s', $session['last_activity']);
            printf("%-30s %-15s %-20s %-15s\n", 
                substr($session['id'], 0, 30), 
                $session['user_id'] ?: 'Guest',
                $session['ip_address'] ?: 'Unknown',
                $lastActivity
            );
        }
        echo str_repeat("-", 80) . "\n";
    } else {
        echo "No sessions found. Make sure to log in first.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
