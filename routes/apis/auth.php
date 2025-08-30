<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->group(function () {
    Route::post('/login', 'login');

    Route::middleware('jwt.auth')->group(function () {
        Route::post('/logout', 'logout');
        Route::get('/login-duration', 'loginDuration');
        Route::get('/online-duration', 'onlineDuration');
    });
});
