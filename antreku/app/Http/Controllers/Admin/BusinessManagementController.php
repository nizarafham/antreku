<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Http\Request;

class BusinessManagementController extends Controller
{
    /**
     * Menampilkan daftar semua bisnis, terutama yang pending.
     */
    public function index()
    {
        // Ambil semua bisnis, urutkan berdasarkan yang pending dulu
        $businesses = Business::orderByRaw("FIELD(status, 'pending', 'approved', 'rejected')")
                            ->with('owner') // Eager load relasi owner untuk efisiensi
                            ->get();

        return view('admin.businesses.index', compact('businesses'));
    }

    /**
     * Menyetujui pendaftaran bisnis.
     */
    public function approve(Business $business)
    {
        $business->status = 'approved';
        $business->save();

        return redirect()->route('admin.businesses.index')->with('success', 'Bisnis ' . $business->name . ' berhasil disetujui.');
    }

    /**
     * Menolak pendaftaran bisnis.
     */
    public function reject(Business $business)
    {
        $business->status = 'rejected';
        $business->save();

        return redirect()->route('admin.businesses.index')->with('success', 'Bisnis ' . $business->name . ' telah ditolak.');
    }
}

