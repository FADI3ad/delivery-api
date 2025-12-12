<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderController;






Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');





Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);





Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [UserController::class, 'profile']);
    Route::post('/logout', [UserController::class, 'logout']);

    Route::post('/orders/create', [OrderController::class, 'store']);
    Route::get('/orders/my-orders', [OrderController::class, 'myOrders']);

    Route::get('/orders/available', [OrderController::class, 'availableOrders']);

    Route::post('/orders/{id}/accept', [OrderController::class, 'acceptOrder']);

    Route::get('/orders/my-taken-orders', [OrderController::class, 'myTakenOrders']);

    Route::delete('/orders/{id}', [OrderController::class, 'deleteOrder']);
    Route::get('/order-status/{id}', [OrderController::class, 'orderStatus']);




    Route::get('/drivers', [UserController::class, 'drivers']);
    Route::get('/users', [UserController::class, 'customers']);
    Route::delete('/delete-user/{id}', [UserController::class, 'deleteUser']);
    Route::get('/orders', [OrderController::class, 'allOrders']);
});
