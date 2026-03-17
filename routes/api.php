<?php

use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\WalletController;

Route::get('/orders/{id}', [OrderController::class, 'show']);
Route::get('/orders/{id}/history', [OrderController::class, 'history']);
Route::get('/wallets/{user_id}', [WalletController::class, 'byUser']);

Route::post('/orders/{id}/confirm', [OrderController::class, 'confirm']);
Route::post('/orders/{id}/ship', [OrderController::class, 'ship']);
Route::post('/orders/{id}/proof', [OrderController::class, 'submitProof']);
Route::post('/orders/{id}/dispute', [OrderController::class, 'dispute']);
Route::post('/orders/{id}/release', [OrderController::class, 'release']);