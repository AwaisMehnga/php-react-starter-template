<?php

use App\Core\Route;
use App\Controllers\HomeController;

// Homepage
Route::get('/', [HomeController::class, 'index']);

// React SPA example
Route::get('/app', [HomeController::class, 'spa']);

// Add your routes here...
