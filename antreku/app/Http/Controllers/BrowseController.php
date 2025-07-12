<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;

class BrowseController extends Controller
{
    /**
     * Menampilkan halaman daftar semua bisnis yang telah disetujui.
     */
    public function index()
    {
        // Ambil semua bisnis yang statusnya 'approved'
        // Gunakan paginate untuk membatasi jumlah data per halaman
        $businesses = Business::where('status', 'approved')->paginate(9);

        return view('browse.index', compact('businesses'));
    }

    /**
     * Menampilkan halaman detail untuk satu bisnis.
     * (Akan kita kembangkan di langkah selanjutnya)
     */
    public function show(Business $business)
    {
        // Pastikan hanya bisnis yang approved yang bisa dilihat
        if ($business->status !== 'approved') {
            abort(404);
        }

        // Eager load services untuk efisiensi query
        $business->load('services');

        return view('browse.show', compact('business'));
    }
}
