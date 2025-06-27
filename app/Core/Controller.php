<?php

namespace App\Core;

/**
 * Base Controller Class
 * All controllers should extend this class
 */
abstract class Controller
{
    protected $data = [];
    protected $view;

    /**
     * Set data to be passed to the view
     * 
     * @param string $key
     * @param mixed $value
     */
    protected function setData(string $key, $value): void
    {
        $this->data[$key] = $value;
    }

    /**
     * Get data by key
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function getData(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * Render a view with data
     * 
     * @param string $view
     * @param array $data
     * @return string
     */
    protected function render(string $view, array $data = []): string
    {
        // Merge controller data with passed data
        $viewData = array_merge($this->data, $data);

        // Extract data as variables for the view
        extract($viewData);

        // Start output buffering
        ob_start();

        // Include the view file
        $viewPath = __DIR__ . '/../../views/' . $view . '.php';
        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            throw new \Exception("View file not found: " . $view);
        }

        // Return the rendered content
        return ob_get_clean();
    }

    /**
     * Redirect to a URL
     * 
     * @param string $url
     * @param int $statusCode
     */
    protected function redirect(string $url, int $statusCode = 302): void
    {
        header("Location: $url", true, $statusCode);
        exit;
    }

    /**
     * Return JSON response
     * 
     * @param array $data
     * @param int $statusCode
     */
    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Get request method
     * 
     * @return string
     */
    protected function getMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Check if request is POST
     * 
     * @return bool
     */
    protected function isPost(): bool
    {
        return $this->getMethod() === 'POST';
    }

    /**
     * Check if request is GET
     * 
     * @return bool
     */
    protected function isGet(): bool
    {
        return $this->getMethod() === 'GET';
    }

    /**
     * Get POST data
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function post(string $key = null, $default = null)
    {
        if ($key === null) {
            return $_POST;
        }
        return $_POST[$key] ?? $default;
    }

    /**
     * Get GET data
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function get(string $key = null, $default = null)
    {
        if ($key === null) {
            return $_GET;
        }
        return $_GET[$key] ?? $default;
    }
}
