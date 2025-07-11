<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/Core/helpers.php';

use App\Core\Application;

// Initialize the application
$app = Application::getInstance();

// Register middleware
$app->registerMiddleware('auth', \App\Middleware\AuthMiddleware::class);
$app->registerMiddleware('admin', \App\Middleware\AdminMiddleware::class);
$app->registerMiddleware('cors', \App\Middleware\CorsMiddleware::class);

$dispatcher = require __DIR__ . '/routes.php';

$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

try {
    switch ($routeInfo[0]) {
        case FastRoute\Dispatcher::NOT_FOUND:
            http_response_code(404);
            include __DIR__ . '/views/404.php';
            break;

        case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
            http_response_code(405);
            echo "405 Method Not Allowed";
            break;

        case FastRoute\Dispatcher::FOUND:
            $vars = $routeInfo[2]; // Associative array of route params
            
            // Handle the route using the Application class
            $app->handleRoute($routeInfo, $vars);
            break;
    }
} catch (Exception $e) {
    // Handle errors gracefully
    http_response_code(500);
    
    if (config('app.debug', false)) {
        echo "<h1>Error</h1>";
        echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    } else {
        echo "Internal Server Error";
    }
}
