<?php
require_once __DIR__ . '/vendor/autoload.php'; // Composer autoload

use FastRoute\RouteCollector;
use App\Core\Route;

$routeFiles = glob(__DIR__ . '/routes/*.php');

$dispatcher = FastRoute\simpleDispatcher(function(RouteCollector $r) use ($routeFiles) {
    // Set the route collector for our Route class
    Route::setRouteCollector($r);
    
    foreach ($routeFiles as $file) {
        $addRoutes = require $file;
        if (is_callable($addRoutes)) {
            $addRoutes($r);
        }
    }
});

return $dispatcher;
