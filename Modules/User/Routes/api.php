<?php

use Illuminate\Support\Facades\Route;
use Modules\User\Controllers\UserController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/users', [UserController::class, 'index']);       // List user
    Route::post('/users', [UserController::class, 'store']);       // Tambah user
    Route::put('/users/{id}', [UserController::class, 'update']);  // Update user
    Route::delete('/users/{id}', [UserController::class, 'destroy']); // Hapus user
});
