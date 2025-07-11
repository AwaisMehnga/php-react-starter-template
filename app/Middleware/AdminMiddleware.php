<?php

namespace App\Middleware;

use App\Core\Middleware;
use App\Models\User;

class AdminMiddleware extends Middleware
{
    /**
     * Handle the request
     *
     * @param callable $next
     * @return mixed
     */
    public function handle($next)
    {
        // Mock admin check
        // In a real app, you'd check user role from database
        
        $userId = $_SESSION['user_id'] ?? 1; // Default to user ID 1 for demo
        $user = User::find($userId);
        
        if (!$user || !$user->isAdmin()) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Access denied. Admin privileges required.']);
            exit;
        }
        
        return $next();
    }
}
