<?php

namespace App\Core;

class Controller
{
    /**
     * Render a view with data
     *
     * @param string $view The view file name (without .php extension)
     * @param array $data Data to pass to the view
     * @return void
     */
    protected function view($view, $data = [])
    {
        // Extract data to variables
        extract($data);
        
        // Build view path
        $viewPath = __DIR__ . '/../../views/' . $view . '.php';
        
        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            throw new \Exception("View not found: {$view}");
        }
    }

    /**
     * Return JSON response
     *
     * @param mixed $data
     * @param int $statusCode
     * @return void
     */
    protected function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Redirect to a URL
     *
     * @param string $url
     * @param int $statusCode
     * @return void
     */
    protected function redirect($url, $statusCode = 302)
    {
        http_response_code($statusCode);
        header("Location: {$url}");
        exit;
    }

    /**
     * Redirect back to previous page with optional flash data
     *
     * @param array $flashData
     * @param int $statusCode
     * @return void
     */
    protected function redirectBack($flashData = [], $statusCode = 302)
    {
        // Store flash data in session using Session helper
        foreach ($flashData as $key => $value) {
            Session::flash($key, $value);
        }

        // Get previous URL from HTTP_REFERER or fallback to home
        $previousUrl = $_SERVER['HTTP_REFERER'] ?? '/';
        
        http_response_code($statusCode);
        header("Location: {$previousUrl}");
        exit;
    }

    /**
     * Get flash data from session
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function getFlash($key, $default = null)
    {
        return Session::getFlash($key, $default);
    }

    /**
     * Get request data
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    protected function request($key = null, $default = null)
    {
        $data = array_merge($_GET, $_POST);
        
        if ($key === null) {
            return $data;
        }
        
        return isset($data[$key]) ? $data[$key] : $default;
    }
}
