<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Method ini akan menampilkan halaman dashboard admin
        return view('admin.dashboard');
    }
}
