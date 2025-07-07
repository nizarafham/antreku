<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Business;
use Carbon\Carbon;

class BusinessController extends Controller
{
    // Mendapatkan daftar semua bisnis
    public function index()
    {
        $businesses = Business::select('id', 'name', 'slug', 'address', 'logo_url')->get();
        return response()->json($businesses);
    }

    // Mendapatkan detail satu bisnis berdasarkan slug
    public function show($slug)
    {
        $business = Business::where('slug', $slug)->firstOrFail();
        return response()->json($business);
    }

    // Mendapatkan slot waktu yang tersedia
    public function getAvailableSlots(Request $request, $slug)
    {
        $request->validate(['date' => 'required|date_format:Y-m-d']);

        $business = Business::where('slug', $slug)->firstOrFail();
        $date = Carbon::parse($request->date);

        // Logika untuk generate & cek slot (bisa disempurnakan)
        // Contoh sederhana: ambil semua slot untuk tanggal yang diminta yang masih available
        $slots = $business->queueSlots()
            ->whereDate('slot_datetime', $date)
            ->where('is_available', true)
            ->orderBy('slot_datetime', 'asc')
            ->get();

        return response()->json($slots);
    }
}
