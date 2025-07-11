<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardRedirectController extends Controller
{
    public function index()
    {
        $role = Auth::user()->role;

        switch ($role) {
            case 'admin':
                return redirect()->route('admin.dashboard');
                break;
            case 'umkm':
                return redirect()->route('umkm.dashboard');
                break;
            default: // Untuk 'pelanggan' atau peran lainnya
                return view('dashboard'); // Tampilkan dashboard bawaan
                break;
        }
    }
}
