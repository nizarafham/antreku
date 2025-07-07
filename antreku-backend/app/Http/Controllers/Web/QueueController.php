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
use Carbon\Carbon;
use Midtrans\Config as MidtransConfig;
use Midtrans\Snap as MidtransSnap;

class QueueController extends Controller
{
    public function history()
    {
        $customer = Auth::guard('customer')->user();
        $queues = $customer->queues()
            ->with(['business:id,name,slug', 'transaction:queue_id,status'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pages.queues.history', compact('queues'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'business_id' => 'required|exists:businesses,id',
            'queue_slot_id' => 'required|exists:queue_slots,id',
        ]);

        $customer = Auth::guard('customer')->user();
        $slot = QueueSlot::findOrFail($request->queue_slot_id);
        $business = Business::findOrFail($request->business_id);

        // Konfigurasi Midtrans
        MidtransConfig::$serverKey = config('services.midtrans.server_key');
        MidtransConfig::$isProduction = config('services.midtrans.is_production');
        MidtransConfig::$isSanitized = true;
        MidtransConfig::$is3ds = true;

        if (!$slot->is_available) {
            return back()->with('error', 'Maaf, slot waktu ini sudah tidak tersedia.');
        }

        $result = DB::transaction(function () use ($customer, $business, $slot) {
            $slot->update(['is_available' => false]);
            $queueNumber = Queue::where('business_id', $business->id)->whereDate('created_at', Carbon::today())->count() + 1;

            $newQueue = Queue::create([
                'business_id' => $business->id, 'customer_id' => $customer->id,
                'queue_slot_id' => $slot->id, 'queue_number' => $queueNumber,
                'scheduled_at' => $slot->slot_datetime, 'status' => 'waiting_payment',
            ]);

            $orderId = 'ANTREKU-' . $newQueue->id . '-' . time();
            $transaction = Transaction::create([
                'queue_id' => $newQueue->id, 'midtrans_order_id' => $orderId,
                'amount' => $business->dp_amount, 'status' => 'pending',
            ]);

            $params = [
                'transaction_details' => ['order_id' => $orderId, 'gross_amount' => $transaction->amount],
                'customer_details' => ['first_name' => $customer->name, 'phone' => $customer->phone_number],
            ];

            return MidtransSnap::getSnapToken($params);
        });

        // Simpan snap token di session untuk ditampilkan di view
        return redirect()->route('history')->with('snap_token', $result);
    }
}
