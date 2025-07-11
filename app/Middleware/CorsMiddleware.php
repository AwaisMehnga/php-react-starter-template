<?php

namespace App\Middleware;

use App\Core\Middleware;

class CorsMiddleware extends Middleware
{
    /**
     * Handle the request
     *
     * @param callable $next
     * @return mixed
     */
    public function handle($next)
    {
        // Set CORS headers
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        
        // Handle preflight requests
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
        
        return $next();
    }
}
