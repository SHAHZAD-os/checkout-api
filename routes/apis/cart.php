<?php

use App\Http\Controllers\CartController;
use Illuminate\Support\Facades\Route;

Route::prefix('cart')
    ->middleware('jwt.auth')
    ->controller(CartController::class)
    ->group(function () {
        Route::get('/', 'viewCart');
        Route::post('/add', 'addToCart');
        Route::delete('/remove/{id}', 'removeFromCart');
        Route::delete('/clear', 'clearCart');
    });