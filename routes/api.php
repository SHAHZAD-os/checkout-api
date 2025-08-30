<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

require base_path('routes/apis/cart.php');
require base_path('routes/apis/product.php');
require base_path('routes/apis/auth.php');
require base_path('routes/apis/payment.php');
require base_path('routes/apis/order.php');