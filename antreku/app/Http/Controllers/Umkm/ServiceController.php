<?php

namespace App\Http\Controllers\Umkm;

use Illuminate\Routing\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $business = Auth::user()->business;
            if (!$business || $business->status !== 'approved') {
                // Jika tidak punya bisnis atau belum disetujui, kembalikan ke dashboard
                return redirect()->route('umkm.dashboard')->with('error', 'Bisnis Anda harus disetujui untuk mengelola layanan.');
            }
            return $next($request);
        });
    }
    /**
     * Menampilkan daftar semua layanan milik bisnis.
     */
    public function index()
    {
        $business = Auth::user()->business;
        $services = $business->services()->get();
        return view('umkm.services.index', compact('services'));
    }

    /**
     * Menampilkan form untuk membuat layanan baru.
     */
    public function create()
    {
        return view('umkm.services.create');
    }

    /**
     * Menyimpan layanan baru ke database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:5',
            'dp_percentage' => 'required|integer|min:0|max:100',
        ]);

        $business = Auth::user()->business;
        $business->services()->create($request->all());

        return redirect()->route('umkm.services.index')->with('success', 'Layanan baru berhasil ditambahkan.');
    }

    /**
     * Menampilkan form untuk mengedit layanan.
     */
    public function edit(Service $service)
    {
        // Pastikan layanan ini milik bisnis user yang sedang login
        if ($service->business_id !== Auth::user()->business->id) {
            abort(403, 'Akses Ditolak');
        }
        return view('umkm.services.edit', compact('service'));
    }

    /**
     * Memperbarui layanan di database.
     */
    public function update(Request $request, Service $service)
    {
        // Pastikan layanan ini milik bisnis user yang sedang login
        if ($service->business_id !== Auth::user()->business->id) {
            abort(403, 'Akses Ditolak');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:5',
            'dp_percentage' => 'required|integer|min:0|max:100',
        ]);

        $service->update($request->all());

        return redirect()->route('umkm.services.index')->with('success', 'Layanan berhasil diperbarui.');
    }

    /**
     * Menghapus layanan dari database.
     */
    public function destroy(Service $service)
    {
        // Pastikan layanan ini milik bisnis user yang sedang login
        if ($service->business_id !== Auth::user()->business->id) {
            abort(403, 'Akses Ditolak');
        }

        $service->delete();

        return redirect()->route('umkm.services.index')->with('success', 'Layanan berhasil dihapus.');
    }
}
