<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Queue;
use App\Models\Business;
use App\Models\QueueSlot;
use App\Models\Transaction; // Import model Transaction
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Midtrans\Config as MidtransConfig;
use Midtrans\Snap as MidtransSnap;

class QueueController extends Controller
{
    public function __construct()
    {
        // Set konfigurasi Midtrans saat controller diinisialisasi
        MidtransConfig::$serverKey = config('midtrans.server_key');
        MidtransConfig::$isProduction = config('midtrans.is_production');
        MidtransConfig::$isSanitized = config('midtrans.is_sanitized');
        MidtransConfig::$is3ds = config('midtrans.is_3ds');
    }

    /**
     * Melihat riwayat antrean pelanggan yang sedang login
     */
    public function history(Request $request)
    {
        $queues = $request->user()->queues()
            ->with(['business:id,name,slug', 'transaction:queue_id,status,amount']) // Ambil data relasi sesuai skema baru
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($queues);
    }

    /**
     * Membuat antrean baru dan menginisiasi pembayaran
     */
    public function store(Request $request)
    {
        $request->validate([
            'business_id' => 'required|exists:businesses,id',
            'queue_slot_id' => 'required|exists:queue_slots,id',
            'booking_fee' => 'required|numeric|min:1000' // Asumsi ada biaya booking
        ]);

        $customer = $request->user();
        $slot = QueueSlot::where('id', $request->queue_slot_id)->lockForUpdate()->first();

        // Double check jika slot masih tersedia (menggunakan lock untuk mencegah race condition)
        if (!$slot || !$slot->is_available) {
            return response()->json(['message' => 'Maaf, slot waktu ini sudah tidak tersedia.'], 409);
        }

        $business = Business::findOrFail($request->business_id);

        $result = DB::transaction(function () use ($customer, $business, $slot, $request) {
            // 1. Update slot menjadi tidak tersedia
            $slot->update(['is_available' => false]);

            // 2. Buat nomor antrean
            $queueNumber = Queue::where('business_id', $business->id)
                ->whereDate('scheduled_at', Carbon::parse($slot->slot_datetime)->toDateString())
                ->count() + 1;

            // 3. Buat antrean baru
            $queue = Queue::create([
                'business_id' => $business->id,
                'customer_id' => $customer->id,
                'queue_slot_id' => $slot->id,
                'queue_number' => $queueNumber,
                'scheduled_at' => $slot->slot_datetime,
                'status' => 'waiting_payment',
            ]);

            // 4. Buat transaksi Midtrans
            $midtransOrderId = 'BOOK-' . $queue->id . '-' . time();
            $payload = [
                'transaction_details' => [
                    'order_id' => $midtransOrderId,
                    'gross_amount' => $request->booking_fee,
                ],
                'item_details' => [[
                    'id' => $slot->id,
                    'price' => $request->booking_fee,
                    'quantity' => 1,
                    'name' => 'Biaya Booking ' . $business->name,
                ]],
                'customer_details' => [
                    'first_name' => $customer->name,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                ],
            ];

            $snapToken = MidtransSnap::getSnapToken($payload);

            // 5. Simpan data transaksi ke database kita sesuai skema baru
            $queue->transaction()->create([
                'midtrans_order_id' => $midtransOrderId,
                'amount' => $request->booking_fee,
                'status' => 'pending', // Enum default
            ]);

            return [
                'queue' => $queue,
                'payment_token' => $snapToken
            ];
        });

        return response()->json([
            'message' => 'Booking berhasil! Silakan selesaikan pembayaran.',
            'queue' => $result['queue']->load('transaction'), // Muat relasi transaksi
            'payment_token' => $result['payment_token'], // Kirim token ke frontend
        ], 201);
    }
}
