<?php

namespace App\Controllers;

use App\Core\Controller;

/**
 * Home Controller
 * Handles the main pages of the application
 */
class HomeController extends Controller
{
    /**
     * Display the home page
     */
    public function index()
    {
        // Set data for the view
        $this->setData('title', 'Home - Tool Site');
        $this->setData('message', 'Welcome to Tool Site');

        // Render the view
        echo $this->render('index');
    }

    /**
     * Display a specific page
     * 
     * @param string $page
     */
    public function page(string $page = 'home')
    {
        $this->setData('title', ucfirst($page) . ' - Tool Site');
        $this->setData('page', $page);

        // Check if specific view exists, otherwise use a generic page view
        $viewPath = __DIR__ . '/../../views/' . $page . '.php';

        if (file_exists($viewPath)) {
            echo $this->render($page);
        } else {
            // Return 404 if page doesn't exist
            http_response_code(404);
            echo $this->render('404');
        }
    }

    /**
     * API endpoint for React frontend
     */
    public function api()
    {
        $data = [
            'status' => 'success',
            'message' => 'API is working',
            'timestamp' => time(),
            'data' => []
        ];

        $this->json($data);
    }
}
