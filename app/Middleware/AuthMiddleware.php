<?php

namespace App\Middleware;

use App\Core\Middleware;
use App\Core\Auth;

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
        if (!Auth::check()) {
            // Check if this is an API request
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            $acceptHeader = $_SERVER['HTTP_ACCEPT'] ?? '';
            
            if (strpos($contentType, 'application/json') !== false || 
                strpos($acceptHeader, 'application/json') !== false) {
                // Return JSON response for API requests
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Unauthorized']);
                exit;
            } else {
                // Redirect to login for web requests
                header('Location: /login');
                exit;
            }
        }
        
        return $next();
    }
}
