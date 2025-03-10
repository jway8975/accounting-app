<?php

use App\Http\Controllers\TransactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/transactions', [TransactionController::class, 'index']); // 获取所有交易
    Route::post('/transactions', [TransactionController::class, 'store']); // 添加交易
    Route::get('/transactions/{id}', [TransactionController::class, 'show']); // 获取单个交易
    Route::put('/transactions/{id}', [TransactionController::class, 'update']); // 更新交易
    Route::delete('/transactions/{id}', [TransactionController::class, 'destroy']); // 删除交易
});