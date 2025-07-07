<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Business;
use App\Models\QueueSlot; 
use Carbon\Carbon;

class BusinessController extends Controller
{
    /**
     * Mendapatkan daftar semua bisnis yang aktif.
     */
    public function index()
    {
        // Ambil hanya bisnis yang relevan, mungkin yang aktif
        $businesses = Business::select('id', 'name', 'slug', 'address', 'logo_url')
            ->where('is_active', true) // Contoh penambahan kondisi
            ->get();
        return response()->json($businesses);
    }

    /**
     * Mendapatkan detail satu bisnis berdasarkan slug.
     */
    public function show($slug)
    {
        $business = Business::where('slug', $slug)->firstOrFail();
        return response()->json($business);
    }

    /**
     * Mendapatkan atau membuat slot waktu yang tersedia untuk bisnis pada tanggal tertentu.
     */
    public function getAvailableSlots(Request $request, $slug)
    {
        // 1. Validasi input
        $request->validate([
            'date' => 'required|date_format:Y-m-d'
        ]);

        // 2. Ambil data bisnis dan tanggal yang diminta
        $business = Business::where('slug', $slug)->firstOrFail();
        $requestedDate = Carbon::parse($request->date)->startOfDay();

        // Validasi tambahan: jangan izinkan memilih tanggal di masa lalu
        if ($requestedDate->isPast()) {
            return response()->json(['message' => 'Tidak dapat memilih tanggal di masa lalu.'], 400);
        }

        // 3. Cek apakah slot untuk tanggal ini sudah ada di database
        $slotsExist = $business->queueSlots()->whereDate('slot_datetime', $requestedDate)->exists();

        // 4. Jika slot belum ada, generate slot baru
        if (!$slotsExist) {
            // Pastikan bisnis memiliki informasi jam operasional
            if (!$business->open_time || !$business->close_time || !$business->slot_duration_minutes) {
                return response()->json(['message' => 'Jadwal operasional untuk bisnis ini belum diatur.'], 404);
            }

            $startTime = $requestedDate->copy()->setTimeFromTimeString($business->open_time);
            $endTime = $requestedDate->copy()->setTimeFromTimeString($business->close_time);
            $slotDuration = $business->slot_duration_minutes;

            $slotsToInsert = [];
            $currentSlotTime = $startTime->copy();

            // Loop untuk membuat slot dari jam buka sampai sebelum jam tutup
            while ($currentSlotTime < $endTime) {
                $slotsToInsert[] = [
                    'business_id' => $business->id,
                    'slot_datetime' => $currentSlotTime->toDateTimeString(),
                    'is_available' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $currentSlotTime->addMinutes($slotDuration);
            }

            // Masukkan semua slot yang telah dibuat ke database sekaligus (lebih efisien)
            if (!empty($slotsToInsert)) {
                QueueSlot::insert($slotsToInsert);
            }
        }

        // 5. Ambil semua slot yang tersedia untuk tanggal tersebut dan kirim sebagai response
        $availableSlots = $business->queueSlots()
            ->whereDate('slot_datetime', $requestedDate)
            ->where('is_available', true)
            ->orderBy('slot_datetime', 'asc')
            ->select('id', 'slot_datetime', 'is_available') // Pilih kolom yang relevan saja
            ->get();

        return response()->json($availableSlots);
    }
}
