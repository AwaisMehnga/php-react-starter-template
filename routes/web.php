<?php

use App\Core\Route;
use App\Controllers\HomeController;

// Homepage
Route::get('/', [HomeController::class, 'index']);

