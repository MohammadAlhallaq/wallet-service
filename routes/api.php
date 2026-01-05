<?php

use App\Http\Controllers\WalletController;
use App\Http\Controllers\TransferController;
use App\Http\Middleware\RequireIdempotencyKey;

Route::get('/health', fn() => ['status' => 'ok']);

Route::post('/wallets', [WalletController::class, 'store']);
Route::get('/wallets', [WalletController::class, 'index']);
Route::get('/wallets/{wallet}', [WalletController::class, 'show']);
Route::get('/wallets/{wallet}/balance', [WalletController::class, 'balance']);
Route::get('/wallets/{wallet}/transactions', [WalletController::class, 'transactions']);

Route::post('/wallets/{wallet}/deposit', [WalletController::class, 'deposit'])->Middleware(RequireIdempotencyKey::class);
Route::post('/wallets/{wallet}/withdraw', [WalletController::class, 'withdraw'])->Middleware(RequireIdempotencyKey::class);
Route::post('/transfers', TransferController::class)->Middleware(RequireIdempotencyKey::class);
