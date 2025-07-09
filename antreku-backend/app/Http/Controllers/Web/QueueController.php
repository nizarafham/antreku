<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Queue;
use App\Models\Business;
use App\Models\QueueSlot;
use App\Models\Transaction;
use App\Models\Customer;
use Carbon\Carbon;
use Midtrans\Config as MidtransConfig;
use Midtrans\Snap as MidtransSnap;

class QueueController extends Controller
{
    public function history()
    {
        $customer = Auth::guard('customer')->user();
        
        if (!$customer) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $queues = $customer->queues()
            ->with(['business:id,name,slug', 'transaction:queue_id,status', 'service:id,name,price'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pages.queues.history', compact('queues'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'business_id' => 'required|exists:businesses,id',
            'service_id' => 'required|exists:services,id',
            'queue_slot_id' => 'required|exists:queue_slots,id',
        ]);

        $customer = Auth::guard('customer')->user();
        $slot = QueueSlot::findOrFail($request->queue_slot_id);
        $business = Business::findOrFail($request->business_id);
        $service = \App\Models\Service::findOrFail($request->service_id);

        // Hitung DP
        $dpAmount = $service->price * ($business->dp_percentage / 100);

        // Konfigurasi Midtrans
        MidtransConfig::$serverKey = config('services.midtrans.server_key');
        MidtransConfig::$isProduction = config('services.midtrans.is_production');
        MidtransConfig::$isSanitized = true;
        MidtransConfig::$is3ds = true;

        if (!$slot->is_available) {
            return back()->with('error', 'Maaf, slot waktu ini sudah tidak tersedia.');
        }

        // PERBAIKAN DI SINI: Tambahkan $service dan $dpAmount ke dalam `use()`
        $result = DB::transaction(function () use ($customer, $business, $slot, $service, $dpAmount) {
            $slot->update(['is_available' => false]);
            $queueNumber = Queue::where('business_id', $business->id)->whereDate('created_at', Carbon::today())->count() + 1;

            $newQueue = Queue::create([
                'business_id' => $business->id,
                'customer_id' => $customer->id,
                'service_id' => $service->id, // Sekarang variabel $service dikenali
                'queue_slot_id' => $slot->id,
                'queue_number' => $queueNumber,
                'scheduled_at' => $slot->slot_datetime,
                'status' => 'waiting_payment',
            ]);

            $orderId = 'ANTREKU-' . $newQueue->id . '-' . time();
            $transaction = Transaction::create([
                'queue_id' => $newQueue->id,
                'midtrans_order_id' => $orderId,
                'amount' => $dpAmount, // Sekarang variabel $dpAmount dikenali
                'status' => 'pending',
            ]);

            $params = [
                'transaction_details' => ['order_id' => $orderId, 'gross_amount' => $transaction->amount],
                'customer_details' => ['first_name' => $customer->name, 'phone' => $customer->phone_number],
            ];

            return MidtransSnap::getSnapToken($params);
        });

        return redirect()->route('history')->with('snap_token', $result);
    }
}
