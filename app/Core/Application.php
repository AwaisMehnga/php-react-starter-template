<?php

namespace App\Core;

class Application
{
    private static $instance;
    private $middlewareRegistry = [];

    private function __construct()
    {
        // Private constructor for singleton
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Register middleware
     *
     * @param string $name
     * @param string $class
     */
    public function registerMiddleware($name, $class)
    {
        $this->middlewareRegistry[$name] = $class;
    }

    /**
     * Handle the route
     *
     * @param array $routeInfo
     * @param array $vars
     */
    public function handleRoute($routeInfo, $vars = [])
    {
        $routeData = $routeInfo[1];
        $action = $routeData['action'];
        $middleware = $routeData['middleware'] ?? [];

        // Create middleware pipeline
        $pipeline = $this->createMiddlewarePipeline($middleware, function() use ($action, $vars) {
            return $this->callAction($action, $vars);
        });

        // Execute the pipeline
        return $pipeline();
    }

    /**
     * Create middleware pipeline
     *
     * @param array $middleware
     * @param callable $destination
     * @return callable
     */
    private function createMiddlewarePipeline($middleware, $destination)
    {
        $pipeline = array_reduce(
            array_reverse($middleware),
            function ($next, $middlewareName) {
                return function () use ($middlewareName, $next) {
                    $middlewareClass = $this->middlewareRegistry[$middlewareName] ?? null;
                    
                    if (!$middlewareClass) {
                        throw new \Exception("Middleware not found: {$middlewareName}");
                    }
                    
                    $middlewareInstance = new $middlewareClass();
                    return $middlewareInstance->handle($next);
                };
            },
            $destination
        );

        return $pipeline;
    }

    /**
     * Call the controller action
     *
     * @param mixed $action
     * @param array $vars
     * @return mixed
     */
    private function callAction($action, $vars)
    {
        if (is_string($action)) {
            // Handle file includes (backward compatibility)
            extract($vars);
            if (file_exists(__DIR__ . '/../../' . $action)) {
                return require __DIR__ . '/../../' . $action;
            } else {
                throw new \Exception("Handler file not found: " . $action);
            }
        } elseif (is_array($action)) {
            // Handle controller@method format
            [$controllerClass, $method] = $action;
            
            if (!class_exists($controllerClass)) {
                throw new \Exception("Controller not found: {$controllerClass}");
            }
            
            $controller = new $controllerClass();
            
            if (!method_exists($controller, $method)) {
                throw new \Exception("Method {$method} not found in {$controllerClass}");
            }
            
            // Use reflection to get method parameters and match with route vars
            $reflection = new \ReflectionMethod($controller, $method);
            $parameters = $reflection->getParameters();
            $args = [];
            
            foreach ($parameters as $param) {
                $paramName = $param->getName();
                if (isset($vars[$paramName])) {
                    $args[] = $vars[$paramName];
                } elseif ($param->isDefaultValueAvailable()) {
                    $args[] = $param->getDefaultValue();
                } else {
                    throw new \Exception("Required parameter {$paramName} not found in route");
                }
            }
            
            return call_user_func_array([$controller, $method], $args);
        } else {
            throw new \Exception("Invalid action type");
        }
    }
}
