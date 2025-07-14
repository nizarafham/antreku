<?php

namespace App\Http\Controllers\Umkm;

use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BusinessController extends Controller
{
    /**
     * Menampilkan dashboard bisnis atau form pendaftaran jika belum ada.
     */
    public function dashboard()
    {
        $business = Auth::user()->business;

        // Jika user UMKM belum punya bisnis, paksa ke halaman pembuatan
        if (!$business) {
            return redirect()->route('umkm.business.create');
        }

        // Jika sudah punya, tampilkan dashboard bisnisnya
        return view('umkm.dashboard', compact('business'));
    }

    /**
     * Menampilkan form untuk membuat bisnis baru.
     */
    public function create()
    {
        // Cek lagi, jangan sampai bisa buat bisnis kalau sudah punya
        if (Auth::user()->business) {
            return redirect()->route('umkm.dashboard');
        }
        return view('umkm.create_business');
    }

    /**
     * Menyimpan bisnis baru ke database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'description' => 'nullable|string',
            'open_time' => 'required|date_format:H:i',
            'close_time' => 'required|date_format:H:i|after:open_time',
        ]);

        Business::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'address' => $request->address,
            'description' => $request->description,
            'open_time' => $request->open_time,
            'close_time' => $request->close_time,
            'status' => 'pending', // Status awal menunggu persetujuan admin
        ]);

        return redirect()->route('umkm.dashboard')->with('success', 'Bisnis Anda berhasil didaftarkan dan sedang menunggu persetujuan admin.');
    }
}
