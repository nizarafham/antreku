<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Umkm\BusinessController;
use App\Http\Controllers\DashboardRedirectController;
use App\Http\Controllers\Admin\BusinessManagementController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardRedirectController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Route untuk dashboard admin
    // URL: /admin/dashboard
    // Nama Route: admin.dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::get('/businesses', [BusinessManagementController::class, 'index'])->name('businesses.index');
    Route::patch('/businesses/{business}/approve', [BusinessManagementController::class, 'approve'])->name('businesses.approve');
    Route::patch('/businesses/{business}/reject', [BusinessManagementController::class, 'reject'])->name('businesses.reject');
});

Route::middleware(['auth', 'verified', 'umkm'])->prefix('umkm')->name('umkm.')->group(function () {
    // Rute utama dashboard UMKM
    Route::get('/dashboard', [BusinessController::class, 'dashboard'])->name('dashboard');

    // Rute untuk membuat dan menyimpan bisnis
    Route::get('/business/create', [BusinessController::class, 'create'])->name('business.create');
    Route::post('/business', [BusinessController::class, 'store'])->name('business.store');

    // Nanti kita bisa tambahkan rute untuk edit, update, kelola layanan, dll di sini
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
