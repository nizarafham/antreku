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

        // TAMBAHKAN INI: Ambil semua layanan yang tersedia
        $services = $business->services()->where('is_available', true)->get();

        $selectedDate = $request->input('date', Carbon::today()->format('Y-m-d'));
        $slots = $business->queueSlots()
            ->whereDate('slot_datetime', $selectedDate)
            ->where('is_available', true)
            ->orderBy('slot_datetime', 'asc')
            ->get();

        // UBAH INI: Kirim variabel $services ke view
        return view('pages.businesses.show', compact('business', 'services', 'slots', 'selectedDate'));
    }
}
