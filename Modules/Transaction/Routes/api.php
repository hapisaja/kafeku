<?php

use Illuminate\Support\Facades\Route;
use Modules\Transaction\Controllers\TransactionController;

Route::middleware(['auth:sanctum'])->prefix('transaction')->group(function () {
    Route::post('/', [TransactionController::class, 'store']); // buat transaksi
    Route::get('/', [TransactionController::class, 'index']); // list semua transaksi
    Route::get('/{id}', [TransactionController::class, 'show']); // detail transaksi
    Route::get('/report/sales', [TransactionController::class, 'salesReport']);
    Route::get('/report/sales/monthly', [TransactionController::class, 'monthlySalesReport']);
    Route::get('/report/best-products', [TransactionController::class, 'bestSellingProducts']);
});
