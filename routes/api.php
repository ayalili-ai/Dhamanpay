<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\WalletController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::controller(AuthController::class)->group(function () {
        Route::get('/me', 'me');
        Route::post('/logout', 'logout');
        Route::get('/courier/profile', 'courierProfile');
        Route::get('/customers/search', 'searchCustomer');
    });

    Route::controller(OrderController::class)->group(function () {
        Route::get('/orders', 'index');
        Route::post('/orders', 'store');
        Route::get('/orders/{id}', 'show');
        Route::get('/orders/{id}/history', 'history');

        Route::post('/orders/{id}/confirm', 'confirm');
        Route::post('/orders/{id}/cancel', 'cancel');
        Route::post('/orders/{id}/ship', 'ship');
        Route::post('/orders/{id}/proof', 'submitProof');
        Route::post('/orders/{id}/dispute', 'dispute');
        Route::post('/orders/{id}/release', 'release');
        Route::post('/orders/{id}/refund', 'refund');

        Route::get('/disputes', 'listDisputes');
    });

    Route::controller(WalletController::class)->group(function () {
        Route::get('/wallets/{user_id}', 'byUser');
        Route::post('/wallets/add-money', 'addMoney');
        Route::get('/transactions', 'transactions');
    });
});