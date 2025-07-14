<?php

namespace App\Http\Controllers\Umkm;

use App\Http\Controllers\Controller;
use App\Models\Queue;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QueueManagementController extends Controller
{
    /**
     * Menampilkan daftar antrean untuk hari ini.
     */
    public function index()
    {
        $business = Auth::user()->business;

        // Pastikan bisnis ada dan sudah disetujui
        if (!$business || $business->status !== 'approved') {
            return redirect()->route('umkm.dashboard')->with('error', 'Fitur ini hanya untuk bisnis yang sudah disetujui.');
        }

        $today = Carbon::today()->format('Y-m-d');

        $queues = $business->queues()
            ->where('booking_date', $today)
            ->whereIn('status', ['confirmed', 'called', 'completed', 'no_show']) // Hanya tampilkan yang relevan
            ->with('customer:id,name', 'service:id,name') // Eager load untuk efisiensi
            ->orderBy('booking_time', 'asc')
            ->get();

        return view('umkm.queue.index', compact('queues'));
    }

    /**
     * Mengubah status antrean menjadi 'called'.
     */
    public function call(Queue $queue)
    {
        $this->authorizeQueueAction($queue);
        $queue->status = 'called';
        $queue->save();
        // TODO: Kirim notifikasi WhatsApp ke pelanggan
        return redirect()->route('umkm.queue.index')->with('success', 'Pelanggan berhasil dipanggil.');
    }

    /**
     * Mengubah status antrean menjadi 'completed'.
     */
    public function complete(Queue $queue)
    {
        $this->authorizeQueueAction($queue);
        $queue->status = 'completed';
        $queue->save();
        return redirect()->route('umkm.queue.index')->with('success', 'Layanan untuk pelanggan telah selesai.');
    }

    /**
     * Mengubah status antrean menjadi 'no_show'.
     */
    public function noShow(Queue $queue)
    {
        $this->authorizeQueueAction($queue);
        $queue->status = 'no_show';
        $queue->save();
        return redirect()->route('umkm.queue.index')->with('success', 'Pelanggan ditandai tidak hadir.');
    }

    /**
     * Helper function untuk otorisasi.
     */
    private function authorizeQueueAction(Queue $queue)
    {
        if ($queue->business_id !== Auth::user()->business->id) {
            abort(403, 'Akses Ditolak.');
        }
    }
}
