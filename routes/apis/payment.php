<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

Route::prefix('payment')
    ->middleware('jwt.auth')
    ->controller(PaymentController::class)
    ->group(function () {
        Route::post('/', 'processPayment');
    });