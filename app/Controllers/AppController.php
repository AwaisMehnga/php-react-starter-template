<?php

namespace App\Controllers;

use App\Core\Controller;

class AppController extends Controller
{
    public function index()
    {
        return $this->view('app', [
            'title' => 'React App'
        ]);
    }
    
    public function afaq(){
        // Placeholder for future functionality
        return $this->view('afaq', [
            'title' => 'FAQ Page'
        ]);
    }
    
}
