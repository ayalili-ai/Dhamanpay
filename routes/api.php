<?php

use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\WalletController;

/// api read::
Route::get('/orders/{id}', [OrderController::class, 'show']);
Route::get('/orders/{id}/history', [OrderController::class, 'history']);
Route::get('/wallets/{user_id}', [WalletController::class, 'byUser']);

/// api:
Route::post('/orders/{id}/confirm', [OrderController::class, 'confirm']);
Route::post('/orders/{id}/ship', [OrderController::class, 'ship']);
Route::post('/orders/{id}/proof', [OrderController::class, 'submitProof']);
Route::post('/orders/{id}/dispute', [OrderController::class, 'dispute']);
Route::post('/orders/{id}/release', [OrderController::class, 'release']);
Route::post('/orders/{id}/refund', [OrderController::class, 'refund']);
Route::post('/orders/{id}/dispute/reject', [OrderController::class, 'rejectDispute']);

///getting the orders by 'id','order_code','amount' :
Route::get('/orders', [OrderController::class, 'index']);

///using postman instead tinker
Route::post('/orders', [OrderController::class, 'store']);