<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Umkm\BusinessController;
use App\Http\Controllers\Umkm\ServiceController;
use App\Http\Controllers\DashboardRedirectController;
use App\Http\Controllers\Admin\BusinessManagementController;
use App\Http\Controllers\BrowseController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\MidtransNotificationController;
use App\Http\Controllers\MyBookingsController;
use App\Http\Controllers\Umkm\QueueManagementController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/businesses', [BrowseController::class, 'index'])->name('browse.index');
Route::get('/businesses/{business}', [BrowseController::class, 'show'])->name('browse.show');

Route::post('/midtrans/notification', [MidtransNotificationController::class, 'handle']);

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

    Route::resource('services', ServiceController::class);

    Route::get('/queue', [QueueManagementController::class, 'index'])->name('queue.index');
    Route::patch('/queue/{queue}/call', [QueueManagementController::class, 'call'])->name('queue.call');
    Route::patch('/queue/{queue}/complete', [QueueManagementController::class, 'complete'])->name('queue.complete');

});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/booking/service/{service}', [BookingController::class, 'create'])->name('booking.create');
    Route::post('/booking/service/{service}', [BookingController::class, 'store'])->name('booking.store');
    Route::get('/booking/slots/{service}', [BookingController::class, 'getAvailableSlots'])->name('booking.slots');
    Route::get('/booking/success/{queue}', [BookingController::class, 'success'])->name('booking.success');

    Route::get('/my-bookings', [MyBookingsController::class, 'index'])->name('my-bookings.index');

});

require __DIR__.'/auth.php';
