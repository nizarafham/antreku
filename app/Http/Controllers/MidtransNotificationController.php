<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Notification;
use Illuminate\Support\Facades\Log;

class MidtransNotificationController extends Controller
{
    /**
     * Handle Midtrans notification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function handle(Request $request)
    {
        Log::info('[MIDTRANS] Notifikasi masuk', [
            'data' => $request->all(),
        ]);
        // Set your Merchant Server Key
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = false; // Set to true for production
        Config::$isSanitized = true;
        Config::$is3ds = true;

        try {
            $notif = new Notification();
        } catch (\Exception $e) {
            // Log the error or return a specific error response
            // For example: \Log::error('Midtrans Notification Error: ' . $e->getMessage());
            return response()->json(['message' => 'Midtrans Notification Error: ' . $e->getMessage()], 500);
        }

        $transactionStatus = $notif->transaction_status;
        $orderId = $notif->order_id;
        $fraudStatus = $notif->fraud_status;

        // Cari transaksi di database
        $transaction = Transaction::where('midtrans_order_id', $orderId)->first();

        if (!$transaction) {
            // Jika transaksi tidak ditemukan, kirim respons agar Midtrans tidak mengirim ulang.
            return response()->json(['message' => 'Transaction not found.'], 404);
        }

        // Jangan proses notifikasi untuk transaksi yang statusnya sudah final (success/failed).
        if ($transaction->status === 'success' || $transaction->status === 'failed') {
            return response()->json(['message' => 'Transaction already processed.']);
        }

        // Logika untuk update status berdasarkan notifikasi dari Midtrans
        if ($transactionStatus == 'capture') {
            if ($fraudStatus == 'accept') {
                // Set transaction status on your database to 'success'
                $transaction->status = 'success';
                $transaction->queue->status = 'confirmed';
            }
        } else if ($transactionStatus == 'settlement') {
            // Set transaction status on your database to 'success'
            $transaction->status = 'success';
            $transaction->queue->status = 'confirmed';
        } else if ($transactionStatus == 'pending') {
            // Set transaction status on your database to 'pending'
            $transaction->status = 'pending';
        } else if ($transactionStatus == 'deny' || $transactionStatus == 'expire' || $transactionStatus == 'cancel') {
            // Set transaction status on your database to 'failure'
            $transaction->status = 'failed';
            $transaction->queue->status = 'cancelled';
        }

        // Simpan perubahan status
        $transaction->save();

        // Simpan juga perubahan status pada antrean jika relasinya ada
        if ($transaction->queue) {
            $transaction->queue->save();
        }

        return response()->json(['message' => 'Notification successfully processed.']);
    }
}
