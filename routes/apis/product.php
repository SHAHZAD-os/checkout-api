<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('products')
    ->controller(ProductController::class)
    ->group(function () {
        Route::get('/', 'getProducts');
    });