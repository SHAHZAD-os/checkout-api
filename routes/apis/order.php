<?php

use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::prefix('order')
    ->middleware('jwt.auth')
    ->controller(OrderController::class)
    ->group(function () {
        Route::post('/', 'createOrder');
    });