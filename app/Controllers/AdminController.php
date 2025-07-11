<?php

namespace App\Controllers;

use App\Core\Controller;

class AdminController extends Controller
{
    /**
     * Admin dashboard
     *
     * @return void
     */
    public function dashboard()
    {
        $data = [
            'title' => 'Admin Dashboard',
            'stats' => [
                'users' => 150,
                'posts' => 89,
                'views' => 1234
            ]
        ];
        
        $this->view('admin/dashboard', $data);
    }

    /**
     * Admin settings
     *
     * @return void
     */
    public function settings()
    {
        $this->view('admin/settings', [
            'title' => 'Admin Settings'
        ]);
    }
}
