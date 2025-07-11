<?php

namespace App\Middleware;

use App\Core\Middleware;

class AuthMiddleware extends Middleware
{
    /**
     * Handle the request
     *
     * @param callable $next
     * @return mixed
     */
    public function handle($next)
    {
        // Mock authentication check
        // In a real app, you'd check session, JWT token, etc.
        
        $isLoggedIn = isset($_SESSION['user_id']) || isset($_GET['auth']) || true; // Always true for demo
        
        if (!$isLoggedIn) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        
        return $next();
    }
}
