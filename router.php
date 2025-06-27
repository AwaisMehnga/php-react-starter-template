<?php
// Bootstrap the application
require_once __DIR__ . '/bootstrap.php';

$dispatcher = require __DIR__ . '/routes.php';

$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

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
        $handler = $routeInfo[1];   // The handler (controller@method or file path)
        $vars = $routeInfo[2];      // Associative array of route params

        // Extract route parameters as variables
        extract($vars);

        // Check if handler is a controller@method or a direct file path
        if (strpos($handler, '@') !== false) {
            // Controller@method format
            [$controllerName, $methodName] = explode('@', $handler, 2);

            $controllerClass = "App\\Controllers\\{$controllerName}";

            if (class_exists($controllerClass)) {
                $controller = new $controllerClass();
                if (method_exists($controller, $methodName)) {
                    // Call the controller method with route parameters
                    call_user_func_array([$controller, $methodName], array_values($vars));
                } else {
                    http_response_code(500);
                    echo "Method {$methodName} not found in {$controllerClass}";
                }
            } else {
                http_response_code(500);
                echo "Controller {$controllerClass} not found";
            }
        } else {
            // Direct file include (legacy support)
            if (file_exists(__DIR__ . '/' . $handler)) {
                include __DIR__ . '/' . $handler;
            } else {
                http_response_code(500);
                echo "Handler file not found: " . htmlspecialchars($handler);
            }
        }
        break;
}
