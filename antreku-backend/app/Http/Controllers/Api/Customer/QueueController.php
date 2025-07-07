<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Queue;
use App\Models\Business;
use App\Models\QueueSlot;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class QueueController extends Controller
{
    // Melihat riwayat antrean pelanggan yang sedang login
    public function history(Request $request)
    {
        $queues = $request->user()->queues()
            ->with('business:id,name,slug') // Ambil data relasi bisnis
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($queues);
    }

    // Membuat antrean baru
    public function store(Request $request)
    {
        $request->validate([
            'business_id' => 'required|exists:businesses,id',
            'queue_slot_id' => 'required|exists:queue_slots,id',
        ]);

        $customer = $request->user();
        $slot = QueueSlot::findOrFail($request->queue_slot_id);
        $business = Business::findOrFail($request->business_id);

        // Double check jika slot masih tersedia
        if (!$slot->is_available) {
            return response()->json(['message' => 'Maaf, slot waktu ini sudah tidak tersedia.'], 409);
        }

        // Gunakan transaction untuk memastikan konsistensi data
        $queue = DB::transaction(function () use ($customer, $business, $slot) {
            // Update slot menjadi tidak tersedia
            $slot->update(['is_available' => false]);

            // Buat nomor antrean (logika sederhana)
            $queueNumber = Queue::where('business_id', $business->id)
                ->whereDate('created_at', Carbon::today())
                ->count() + 1;

            // Buat antrean baru
            $newQueue = Queue::create([
                'business_id' => $business->id,
                'customer_id' => $customer->id,
                'queue_slot_id' => $slot->id,
                'queue_number' => $queueNumber,
                'scheduled_at' => $slot->slot_datetime,
                'status' => 'waiting_payment', // Status awal
            ]);

            // TODO: Logika untuk membuat transaksi di tabel 'transactions'
            // dan inisiasi pembayaran ke Midtrans.

            return $newQueue;
        });

        return response()->json([
            'message' => 'Booking berhasil! Silakan selesaikan pembayaran.',
            'queue' => $queue
        ], 201);
    }
}
