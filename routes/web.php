<?php

use App\Core\Route;
use App\Controllers\HomeController;
use App\Controllers\AuthController;

// Homepage
Route::get('/', [HomeController::class, 'index']);

// Authentication Routes (Guest only)
Route::get('/login', [AuthController::class, 'showLoginForm'], ['guest']);
Route::post('/login', [AuthController::class, 'login'], ['guest']);
Route::get('/register', [AuthController::class, 'showRegisterForm'], ['guest']);
Route::post('/register', [AuthController::class, 'register'], ['guest']);
Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'], ['guest']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'], ['guest']);

// Protected Routes (Auth required)
Route::get('/dashboard', [AuthController::class, 'dashboard'], ['auth']);
Route::post('/logout', [AuthController::class, 'logout'], ['auth']);
Route::get('/logout', [AuthController::class, 'logout'], ['auth']);
