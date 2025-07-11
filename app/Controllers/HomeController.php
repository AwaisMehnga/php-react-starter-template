<?php

namespace App\Controllers;

use App\Core\Controller;

class HomeController extends Controller
{
    public function index()
    {
        return $this->view('home', [
            'title' => 'Welcome to My App'
        ]);
    }
    
    public function spa()
    {
        return $this->view('template/react_shell', [
            'title' => 'React SPA',
            'scripts' => ['/build/App/main.js'],
            'styles' => ['/build/App/App.css']
        ]);
    }
}
