<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/logs', function () {
    $logFile = storage_path('logs/laravel.log');
    if (!file_exists($logFile)) {
        abort(404, 'Log file not found.');
    }
    return response()->make(file_get_contents($logFile), 200, [
        'Content-Type' => 'text/plain',
    ]);
});


Route::prefix('payment')
    ->controller(PaymentController::class)
    ->group(function () {
        Route::get('/success', 'handleSuccess');
        Route::get('/cancel', 'handleCancel'); 
    });