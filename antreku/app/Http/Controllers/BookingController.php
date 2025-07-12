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
        // Eager load relasi bisnis untuk efisiensi
        $service->load('business');
        return view('booking.create', compact('service'));
    }

    /**
     * API endpoint untuk mendapatkan slot waktu yang tersedia.
     * Ini akan dipanggil oleh JavaScript (AJAX/Fetch).
     */
    public function getAvailableSlots(Request $request, Service $service)
    {
        // PERUBAHAN: Validasi diubah menjadi 'after:today' untuk H-1
        $request->validate(['date' => 'required|date|after:today']);

        $selectedDate = $request->date;
        $business = $service->business;
        $tz = 'Asia/Jakarta'; // Tentukan timezone secara eksplisit untuk konsistensi

        if (!$business->open_time || !$business->close_time) {
            return response()->json([]);
        }

        // Gabungkan tanggal yang dipilih pengguna dengan jam dari database
        $openTime = Carbon::parse($selectedDate . ' ' . $business->open_time, $tz);
        $closeTime = Carbon::parse($selectedDate . ' ' . $business->close_time, $tz);

        // Ambil semua antrean yang sudah dikonfirmasi pada tanggal tersebut
        $existingBookings = Queue::where('business_id', $business->id)
            ->where('booking_date', $selectedDate)
            ->whereIn('status', ['confirmed', 'called', 'completed'])
            ->with('service:id,duration_minutes')
            ->get();

        $bookedSlots = [];
        // Loop ini dibuat lebih aman untuk menangani kasus jika layanan terkait sudah dihapus
        foreach ($existingBookings as $booking) {
            // Hanya proses booking jika layanan terkait masih ada
            if ($booking->service) {
                $start = Carbon::parse($selectedDate . ' ' . $booking->booking_time, $tz);
                $end = $start->copy()->addMinutes($booking->service->duration_minutes);
                $bookedSlots[] = ['start' => $start, 'end' => $end];
            }
        }

        $availableSlots = [];
        $slot = $openTime->copy();

        // Jika tanggal yang dipilih adalah hari ini, pastikan slot dimulai dari waktu sekarang
        if (Carbon::parse($selectedDate, $tz)->isToday()) {
            $now = Carbon::now($tz);
            if ($now->gt($slot)) {
                // Mulai dari 15 menit terdekat dari sekarang
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

            // Pindah ke slot berikutnya (interval 15 menit)
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

        $business = $service->business;

        // 1. Buat entri di tabel 'queues'
        $queue = Queue::create([
            'customer_id' => Auth::id(),
            'business_id' => $business->id,
            'service_id' => $service->id,
            'booking_date' => $request->booking_date,
            'booking_time' => $request->booking_time,
            'status' => 'pending_payment',
            'queue_number' => Queue::where('business_id', $business->id)->where('booking_date', $request->booking_date)->count() + 1,
        ]);

        // 2. Hitung jumlah DP
        $dpAmount = $service->price * ($service->dp_percentage / 100);

        // 3. Buat entri di tabel 'transactions'
        $transaction = $queue->transaction()->create([
            // Gunakan ID unik dari tabel transactions sebagai order_id
            'midtrans_order_id' => 'ANTREKU-' . $queue->id . '-' . time(),
            'amount' => $dpAmount,
            'status' => 'pending',
        ]);

        // --- LOGIKA MIDTRANS DIMULAI DI SINI ---
        // Set konfigurasi Midtrans
        \Midtrans\Config::$serverKey = config('midtrans.server_key');
        \Midtrans\Config::$isProduction = false; // Set true jika sudah di production
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;

        // Siapkan parameter untuk Midtrans
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

        // Dapatkan Snap Token
        $snapToken = \Midtrans\Snap::getSnapToken($params);

        // Simpan Snap Token ke transaksi
        $transaction->snap_token = $snapToken;
        $transaction->save();
        // --- AKHIR LOGIKA MIDTRANS ---

        // Redirect ke halaman sukses dengan membawa data transaksi
        return redirect()->route('booking.success', $queue);
    }

    /**
     * Menampilkan halaman konfirmasi setelah booking dibuat.
     */
    public function success(Queue $queue)
    {
        // Pastikan hanya customer yang benar yang bisa melihat halaman ini
        if ($queue->customer_id !== Auth::id()) {
            abort(403);
        }
        $queue->load(['service', 'business', 'transaction']);
        return view('booking.success', compact('queue'));
    }
}
