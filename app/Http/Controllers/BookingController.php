<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Queue;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    /**
     * Menampilkan halaman booking untuk layanan tertentu.
     */
    public function create(Service $service)
    {
        $service->load('business');
        return view('booking.create', compact('service'));
    }

    /**
     * API endpoint untuk mendapatkan slot waktu yang tersedia.
     * Ini akan dipanggil oleh JavaScript (AJAX/Fetch).
     */
    public function getAvailableSlots(Request $request, Service $service)
    {
        $request->validate(['date' => 'required|date|after:today']);

        $selectedDate = $request->date;
        $business = $service->business;
        $tz = 'Asia/Jakarta';

        if (!$business->open_time || !$business->close_time) {
            return response()->json([]);
        }

        $openTime = Carbon::parse($selectedDate . ' ' . $business->open_time, $tz);
        $closeTime = Carbon::parse($selectedDate . ' ' . $business->close_time, $tz);

        $existingBookings = Queue::where('business_id', $business->id)
            ->where('booking_date', $selectedDate)
            ->whereIn('status', ['confirmed', 'called', 'completed'])
            ->with('service:id,duration_minutes')
            ->get();

        $bookedSlots = [];
        foreach ($existingBookings as $booking) {

            if ($booking->service) {
                $start = Carbon::parse($selectedDate . ' ' . $booking->booking_time, $tz);
                $end = $start->copy()->addMinutes($booking->service->duration_minutes);
                $bookedSlots[] = ['start' => $start, 'end' => $end];
            }
        }

        $availableSlots = [];
        $slot = $openTime->copy();
        if (Carbon::parse($selectedDate, $tz)->isToday()) {
            $now = Carbon::now($tz);
            if ($now->gt($slot)) {
                $slot = $now->copy()->ceilMinute(15);
            }
        }

        while ($slot->copy()->addMinutes($service->duration_minutes)->lte($closeTime)) {
            $isAvailable = true;
            $potentialSlotEnd = $slot->copy()->addMinutes($service->duration_minutes);

            foreach ($bookedSlots as $bookedSlot) {
                if ($slot->lt($bookedSlot['end']) && $potentialSlotEnd->gt($bookedSlot['start'])) {
                    $isAvailable = false;
                    break;
                }
            }

            if ($isAvailable) {
                $availableSlots[] = $slot->format('H:i');
            }

            $slot->addMinutes(15);
        }

        return response()->json($availableSlots);
    }

    /**
     * Menyimpan data booking ke database.
     */
    public function store(Request $request, Service $service)
    {
        $request->validate([
            'booking_date' => 'required|date|after:today',
            'booking_time' => 'required|date_format:H:i',
        ]);

        $user = Auth::user();
        $previousPendingQueues = $user->queues()
                                    ->where('status', 'pending_payment')
                                    ->with('transaction')
                                    ->get();

        foreach ($previousPendingQueues as $oldQueue) {
            $oldQueue->status = 'cancelled';
            $oldQueue->save();

            if ($oldQueue->transaction) {
                $oldQueue->transaction->status = 'failed';
                $oldQueue->transaction->save();
            }
        }

        $business = $service->business;

        $queue = Queue::create([
            'customer_id' => Auth::id(),
            'business_id' => $business->id,
            'service_id' => $service->id,
            'booking_date' => $request->booking_date,
            'booking_time' => $request->booking_time,
            'status' => 'pending_payment',
            'queue_number' => Queue::where('business_id', $business->id)->where('booking_date', $request->booking_date)->count() + 1,
        ]);

        $dpAmount = $service->price * ($service->dp_percentage / 100);

        $transaction = $queue->transaction()->create([
            'midtrans_order_id' => 'ANTREKU-' . $queue->id . '-' . time(),
            'amount' => $dpAmount,
            'status' => 'pending',
        ]);

        \Midtrans\Config::$serverKey = config('midtrans.server_key');
        \Midtrans\Config::$isProduction = false;
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;

        $params = [
            'transaction_details' => [
                'order_id' => $transaction->midtrans_order_id,
                'gross_amount' => $transaction->amount,
            ],
            'customer_details' => [
                'first_name' => Auth::user()->name,
                'email' => Auth::user()->email,
            ],
            'item_details' => [
                [
                    'id' => $service->id,
                    'price' => $transaction->amount,
                    'quantity' => 1,
                    'name' => 'DP untuk ' . $service->name,
                ]
            ]
        ];

        $snapToken = \Midtrans\Snap::getSnapToken($params);

        $transaction->snap_token = $snapToken;
        $transaction->save();

        return redirect()->route('booking.success', $queue);
    }

    /**
     * Menampilkan halaman konfirmasi setelah booking dibuat.
     */
    public function success(Queue $queue)
    {
        if ($queue->customer_id !== Auth::id()) {
            abort(403);
        }
        $queue->load(['service', 'business', 'transaction']);
        return view('booking.success', compact('queue'));
    }
}
