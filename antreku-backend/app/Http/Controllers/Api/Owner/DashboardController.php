<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Queue;
use Carbon\Carbon;

class DashboardController extends Controller
{
    // Mengambil semua antrean untuk hari ini
    public function getTodaysQueues(Request $request)
    {
        $business = $request->user()->business;
        if (!$business) {
            return response()->json(['message' => 'Anda tidak memiliki bisnis terdaftar.'], 403);
        }

        $queues = $business->queues()
            ->with('customer:id,name,phone_number') // Ambil data pelanggan
            ->whereDate('scheduled_at', Carbon::today())
            ->orderBy('scheduled_at', 'asc')
            ->get();

        return response()->json($queues);
    }

    // Mengubah status antrean menjadi 'called'
    public function callCustomer(Request $request, Queue $queue)
    {
        $queue->update(['status' => 'called']);
        // TODO: Kirim notifikasi ke pelanggan
        return response()->json(['message' => 'Pelanggan dipanggil.', 'queue' => $queue]);
    }

    // Mengubah status antrean menjadi 'completed'
    public function markAsCompleted(Request $request, Queue $queue)
    {
        $queue->update(['status' => 'completed']);
        return response()->json(['message' => 'Antrean selesai.', 'queue' => $queue]);
    }

    // Mengubah status antrean menjadi 'no_show'
    public function markAsNoShow(Request $request, Queue $queue)
    {
        $queue->update(['status' => 'no_show']);
        // TODO: Logika DP hangus
        return response()->json(['message' => 'Pelanggan ditandai tidak hadir.', 'queue' => $queue]);
    }
}
