<?php
require_once __DIR__ . '/vendor/autoload.php'; // Composer autoload

use FastRoute\RouteCollector;

$routeFiles = glob(__DIR__ . '/routes/*.php');

$dispatcher = FastRoute\simpleDispatcher(function(RouteCollector $r) use ($routeFiles) {
    foreach ($routeFiles as $file) {
        $addRoutes = require $file;
        if (is_callable($addRoutes)) {
            $addRoutes($r);
        }
    }
});

return $dispatcher;
