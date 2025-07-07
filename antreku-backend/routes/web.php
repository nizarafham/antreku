<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\BusinessController;
use App\Http\Controllers\Web\QueueController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Rute-rute ini khusus untuk frontend pelanggan yang menggunakan Blade.
| Setiap rute akan memanggil sebuah method di dalam Controller yang
| kemudian akan menampilkan sebuah file view (halaman HTML).
*/

// Halaman utama, menampilkan daftar semua bisnis
Route::get('/', [BusinessController::class, 'index'])->name('home');

// Rute untuk Autentikasi Pelanggan
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

// Rute untuk menampilkan detail satu bisnis dan slot antreannya
Route::get('/business/{slug}', [BusinessController::class, 'show'])->name('business.show');

// Grup rute yang hanya bisa diakses setelah pelanggan login
Route::middleware(['auth:customer'])->group(function () {
    
    // Rute untuk memproses booking antrean yang di-submit dari form
    Route::post('/queue/book', [QueueController::class, 'store'])->name('queue.book');
    
    // Rute untuk melihat halaman riwayat antrean
    Route::get('/history', [QueueController::class, 'history'])->name('history');
});