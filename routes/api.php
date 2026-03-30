<?php

use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\AuthController;

/// api read::
Route::get('/orders/{id}', [OrderController::class, 'show']);
Route::get('/orders/{id}/history', [OrderController::class, 'history']);
Route::get('/wallets/{user_id}', [WalletController::class, 'byUser']);
Route::get('/orders', [OrderController::class, 'index']);
/// api:
Route::post('/orders/{id}/confirm', [OrderController::class, 'confirm']);
Route::post('/orders/{id}/ship', [OrderController::class, 'ship']);
Route::post('/orders/{id}/proof', [OrderController::class, 'submitProof']);
Route::post('/orders/{id}/dispute', [OrderController::class, 'dispute']);
Route::post('/orders/{id}/release', [OrderController::class, 'release']);
Route::post('/orders/{id}/refund', [OrderController::class, 'refund']);
Route::post('/orders/{id}/dispute/reject', [OrderController::class, 'rejectDispute']);

Route::post('/orders', [OrderController::class, 'store']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/wallets/add-money', [WalletController::class, 'addMoney']);

