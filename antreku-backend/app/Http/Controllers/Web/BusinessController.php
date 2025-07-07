<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Business;
use Carbon\Carbon;

class BusinessController extends Controller
{
    public function index()
    {
        $businesses = Business::select('id', 'name', 'slug', 'address', 'logo_url')->get();
        return view('pages.businesses.index', compact('businesses'));
    }

    public function show(Request $request, $slug)
    {
        $business = Business::where('slug', $slug)->firstOrFail();

        // Ambil tanggal dari request, atau default ke hari ini
        $selectedDate = $request->input('date', Carbon::today()->format('Y-m-d'));

        // Ambil slot yang tersedia untuk tanggal yang dipilih
        $slots = $business->queueSlots()
            ->whereDate('slot_datetime', $selectedDate)
            ->where('is_available', true)
            ->orderBy('slot_datetime', 'asc')
            ->get();

        return view('pages.businesses.show', compact('business', 'slots', 'selectedDate'));
    }
}
