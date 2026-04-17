<?php

use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\AuthController;

/// api read::
Route::middleware('auth:sanctum')->get('/orders/{id}', [OrderController::class, 'show']);
Route::middleware('auth:sanctum')->get('/orders/{id}/history', [OrderController::class, 'history']);
Route::middleware('auth:sanctum')->get('/orders', [OrderController::class, 'index']);
Route::middleware('auth:sanctum')->get('/wallets/{user_id}', [WalletController::class, 'byUser']);

/// api:
Route::middleware('auth:sanctum')->post('/orders/{id}/confirm', [OrderController::class, 'confirm']);
Route::middleware('auth:sanctum')->post('/orders/{id}/ship', [OrderController::class, 'ship']);
Route::middleware('auth:sanctum')->post('/orders/{id}/proof', [OrderController::class, 'submitProof']);
Route::middleware('auth:sanctum')->post('/orders/{id}/dispute', [OrderController::class, 'dispute']);
Route::middleware('auth:sanctum')->post('/orders/{id}/release', [OrderController::class, 'release']);
Route::middleware('auth:sanctum')->post('/orders/{id}/refund', [OrderController::class, 'refund']);
Route::middleware('auth:sanctum')->post('/orders/{id}/dispute/reject', [OrderController::class, 'rejectDispute']);

Route::middleware('auth:sanctum')->post('/orders', [OrderController::class, 'store']);

Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->post('/wallets/add-money', [WalletController::class, 'addMoney']);

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->get('/me', [AuthController::class, 'me']);

Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

Route::middleware('auth:sanctum')->get('/transactions', [WalletController::class, 'transactions']);

Route::middleware('auth:sanctum')->get('/disputes', [OrderController::class, 'listDisputes']);

Route::middleware('auth:sanctum')->get('/courier/profile', [AuthController::class, 'courierProfile']);

Route::middleware('auth:sanctum')->post('/orders/{id}/cancel', [OrderController::class, 'cancel']);

Route::middleware('auth:sanctum')->get('/customers/search', [AuthController::class, 'searchCustomer']);