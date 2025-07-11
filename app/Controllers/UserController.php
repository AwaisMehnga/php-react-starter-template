<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Display all users
     *
     * @return void
     */
    public function index()
    {
        $users = User::all();
        
        $this->view('users/index', [
            'title' => 'All Users',
            'users' => $users
        ]);
    }

    /**
     * Show a specific user
     *
     * @param int $id
     * @return void
     */
    public function show($id)
    {
        $user = User::find($id);
        
        if (!$user) {
            http_response_code(404);
            $this->view('404');
            return;
        }
        
        $this->view('users/show', [
            'title' => 'User Profile',
            'user' => $user
        ]);
    }

    /**
     * API endpoint to get user data
     *
     * @param int $id
     * @return void
     */
    public function apiShow($id)
    {
        $user = User::find($id);
        
        if (!$user) {
            $this->json(['error' => 'User not found'], 404);
            return;
        }
        
        $this->json(['user' => $user->toArray()]);
    }
}
