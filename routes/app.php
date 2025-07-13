<?php

use App\Core\Route;
use App\Controllers\AppController;

Route::get('/app', [AppController::class, 'index']);


