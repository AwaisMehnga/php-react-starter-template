<?php

namespace App\Core;

use FastRoute\RouteCollector;

class Route
{
    private static $routeCollector;
    private static $middleware = [];
    private static $groupOptions = [];

    public static function setRouteCollector(RouteCollector $collector)
    {
        self::$routeCollector = $collector;
    }

    public static function get($uri, $action)
    {
        self::addRoute('GET', $uri, $action);
    }

    public static function post($uri, $action)
    {
        self::addRoute('POST', $uri, $action);
    }

    public static function put($uri, $action)
    {
        self::addRoute('PUT', $uri, $action);
    }

    public static function delete($uri, $action)
    {
        self::addRoute('DELETE', $uri, $action);
    }

    public static function patch($uri, $action)
    {
        self::addRoute('PATCH', $uri, $action);
    }

    public static function group($options, $callback)
    {
        $prevGroupOptions = self::$groupOptions;
        
        // Merge group options
        self::$groupOptions = array_merge(self::$groupOptions, $options);
        
        // Execute the callback with the route instance
        $callback(new static());
        
        // Restore previous group options
        self::$groupOptions = $prevGroupOptions;
    }

    public static function middleware($middleware)
    {
        if (is_string($middleware)) {
            $middleware = [$middleware];
        }
        
        self::$middleware = array_merge(self::$middleware, $middleware);
        return new static();
    }

    private static function addRoute($method, $uri, $action)
    {
        // Apply group prefix
        if (isset(self::$groupOptions['prefix'])) {
            $uri = '/' . trim(self::$groupOptions['prefix'], '/') . '/' . ltrim($uri, '/');
            $uri = rtrim($uri, '/') ?: '/';
        }

        // Collect middleware
        $middleware = [];
        
        // Add group middleware
        if (isset(self::$groupOptions['middleware'])) {
            $groupMiddleware = is_array(self::$groupOptions['middleware']) 
                ? self::$groupOptions['middleware'] 
                : [self::$groupOptions['middleware']];
            $middleware = array_merge($middleware, $groupMiddleware);
        }
        
        // Add route-specific middleware
        $middleware = array_merge($middleware, self::$middleware);

        // Create route data
        $routeData = [
            'action' => $action,
            'middleware' => $middleware
        ];

        self::$routeCollector->addRoute($method, $uri, $routeData);
        
        // Clear route-specific middleware
        self::$middleware = [];
    }
}
