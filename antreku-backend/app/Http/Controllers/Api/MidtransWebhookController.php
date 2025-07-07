<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Queue;
use Midtrans\Config as MidtransConfig;
use Midtrans\Notification as MidtransNotification;

class MidtransWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Konfigurasi Midtrans
        MidtransConfig::$serverKey = config('services.midtrans.server_key');
        MidtransConfig::$isProduction = config('services.midtrans.is_production');

        // Validasi notifikasi
        try {
            $notification = new MidtransNotification();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Webhook Error: ' . $e->getMessage()], 500);
        }

        $transactionStatus = $notification->transaction_status;
        $orderId = $notification->order_id;
        $fraudStatus = $notification->fraud_status;

        $transaction = Transaction::where('midtrans_order_id', $orderId)->first();

        if (!$transaction) {
            return response()->json(['message' => 'Transaksi tidak ditemukan.'], 404);
        }

        // Jangan proses jika status sudah final
        if ($transaction->status === 'success' || $transaction->status === 'failed') {
            return response()->json(['message' => 'Status transaksi sudah final.']);
        }

        // Logika utama untuk handle status pembayaran
        if ($transactionStatus == 'capture' || $transactionStatus == 'settlement') {
            // 'capture' untuk kartu kredit, 'settlement' untuk metode lain
            if ($fraudStatus == 'accept') {
                // Pembayaran berhasil
                $transaction->update(['status' => 'success', 'paid_at' => now()]);
                $transaction->queue->update(['status' => 'confirmed']);
                // TODO: Kirim notifikasi ke pelanggan bahwa pembayaran berhasil
            }
        } else if ($transactionStatus == 'cancel' || $transactionStatus == 'deny' || $transactionStatus == 'expire') {
            // Pembayaran gagal atau dibatalkan
            $transaction->update(['status' => 'failed']);
            $transaction->queue->update(['status' => 'cancelled']);
            // Kembalikan slot menjadi tersedia
            if ($transaction->queue->queueSlot) {
                $transaction->queue->queueSlot->update(['is_available' => true]);
            }
        }

        return response()->json(['message' => 'Webhook berhasil diproses.']);
    }
}
