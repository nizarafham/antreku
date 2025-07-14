<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MyBookingsController extends Controller
{
    /**
     * Menampilkan halaman riwayat booking milik pengguna.
     */
    public function index()
    {
        $bookings = Auth::user()
                        ->queues() // Mengambil dari relasi 'queues' di model User
                        ->with(['business:id,name', 'service:id,name', 'transaction']) // Eager loading untuk efisiensi
                        ->latest() // Urutkan dari yang terbaru
                        ->paginate(10); // Gunakan pagination

        return view('my-bookings.index', compact('bookings'));
    }
}
