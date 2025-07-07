<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Customer\AuthController as CustomerAuthController;
use App\Http\Controllers\Api\Customer\BusinessController as CustomerBusinessController;
use App\Http\Controllers\Api\Customer\QueueController as CustomerQueueController;
use App\Http\Controllers\Api\Owner\AuthController as OwnerAuthController;
use App\Http\Controllers\Api\Owner\DashboardController as OwnerDashboardController;

// --- Rute untuk Pelanggan (Customer Web) ---
Route::prefix('customer')->group(function () {
    // Rute Autentikasi Pelanggan
    Route::post('/register', [CustomerAuthController::class, 'register']);
    Route::post('/login', [CustomerAuthController::class, 'login']);

    // Rute yang memerlukan login pelanggan
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [CustomerAuthController::class, 'logout']);
        Route::get('/me', [CustomerAuthController::class, 'me']);

        // Rute untuk Antrean
        Route::post('/queues/book', [CustomerQueueController::class, 'store']);
        Route::get('/queues/history', [CustomerQueueController::class, 'history']);
    });

    // Rute Publik (tidak perlu login)
    Route::get('/businesses', [CustomerBusinessController::class, 'index']); // Daftar semua usaha
    Route::get('/businesses/{slug}', [CustomerBusinessController::class, 'show']); // Detail satu usaha
    Route::get('/businesses/{slug}/slots', [CustomerBusinessController::class, 'getAvailableSlots']); // Lihat slot tersedia
});


// --- Rute untuk Pemilik Usaha (Flutter App) ---
Route::prefix('owner')->group(function () {
    // Rute Autentikasi Pemilik Usaha
    Route::post('/login', [OwnerAuthController::class, 'login']);

    // Rute yang memerlukan login pemilik usaha
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [OwnerAuthController::class, 'logout']);
        Route::get('/me', [OwnerAuthController::class, 'me']);

        // Rute Dashboard & Manajemen Antrean
        Route::get('/dashboard/queues', [OwnerDashboardController::class, 'getTodaysQueues']);
        Route::post('/dashboard/queues/{queue}/call', [OwnerDashboardController::class, 'callCustomer']);
        Route::post('/dashboard/queues/{queue}/complete', [OwnerDashboardController::class, 'markAsCompleted']);
        Route::post('/dashboard/queues/{queue}/no-show', [OwnerDashboardController::class, 'markAsNoShow']);
    });
});
