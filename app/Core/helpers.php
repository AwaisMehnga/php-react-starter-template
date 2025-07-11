<?php

if (!function_exists('view')) {
    /**
     * Render a view with data
     *
     * @param string $view
     * @param array $data
     * @return void
     */
    function view($view, $data = [])
    {
        extract($data);
        $viewPath = __DIR__ . '/../views/' . $view . '.php';
        
        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            throw new \Exception("View not found: {$view}");
        }
    }
}

if (!function_exists('redirect')) {
    /**
     * Redirect to a URL
     *
     * @param string $url
     * @param int $statusCode
     * @return void
     */
    function redirect($url, $statusCode = 302)
    {
        http_response_code($statusCode);
        header("Location: {$url}");
        exit;
    }
}

if (!function_exists('json_response')) {
    /**
     * Return JSON response
     *
     * @param mixed $data
     * @param int $statusCode
     * @return void
     */
    function json_response($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}

if (!function_exists('request')) {
    /**
     * Get request data
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    function request($key = null, $default = null)
    {
        $data = array_merge($_GET, $_POST);
        
        if ($key === null) {
            return $data;
        }
        
        return isset($data[$key]) ? $data[$key] : $default;
    }
}

if (!function_exists('config')) {
    /**
     * Get configuration value (simple implementation)
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function config($key, $default = null)
    {
        // Simple config implementation
        $config = [
            'app.name' => 'Tool Site',
            'app.debug' => true,
        ];
        
        return isset($config[$key]) ? $config[$key] : $default;
    }
}
