<?php

namespace App\Middleware;

use App\Core\Middleware;
use App\Core\Auth;

class GuestMiddleware extends Middleware
{
    /**
     * Handle the request
     *
     * @param callable $next
     * @return mixed
     */
    public function handle($next)
    {
        if (Auth::check()) {
            // Redirect authenticated users to dashboard
            header('Location: /dashboard');
            exit;
        }
        
        return $next();
    }
}
