<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Business;
use Illuminate\Support\Str;

class BusinessController extends Controller
{
    /**
     * Menyimpan profil bisnis baru yang didaftarkan dari aplikasi Flutter.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $user = $request->user();

        // Pastikan user belum punya bisnis untuk mencegah duplikat
        if ($user->business) {
            return response()->json(['message' => 'Anda sudah memiliki bisnis terdaftar.'], 409); // 409 Conflict
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:businesses,slug',
            'address' => 'required|string',
            'dp_percentage' => 'required|integer|min:0|max:100',
        ]);

        $business = Business::create([
            'user_id' => $user->id,
            'name' => $validated['name'],
            'slug' => Str::slug($validated['slug']), // Pastikan slug dalam format yang benar
            'address' => $validated['address'],
            'dp_percentage' => $validated['dp_percentage'],
            'is_open' => true, // Default bisnis langsung buka saat dibuat
        ]);

        // Muat relasi user agar bisa dikirim kembali ke Flutter
        $business->load('user');

        return response()->json($business, 201); // 201 Created
    }

    /**
     * Mengubah status buka/tutup antrean untuk sebuah bisnis.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleStatus(Request $request)
    {
        $business = $request->user()->business;

        if (!$business) {
            return response()->json(['message' => 'Bisnis tidak ditemukan.'], 404); // 404 Not Found
        }

        // Ubah status ke nilai sebaliknya
        $business->update(['is_open' => !$business->is_open]);

        return response()->json([
            'message' => 'Status bisnis berhasil diubah.',
            'is_open' => $business->is_open,
        ]);
    }
}
