<?php
// File: resources/views/pages/businesses/index.blade.php
?>
@extends('layouts.app')
@section('title', 'Pilih Tempat Usaha')

@section('content')
    <h1 class="text-3xl font-bold mb-6">Pilih Tempat Usaha</h1>
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
        @forelse ($businesses as $business)
            <div class="p-4 bg-white border rounded-lg shadow-sm hover:shadow-lg transition-shadow">
                <img src="{{ $business->logo_url ?: 'https://placehold.co/600x400/e2e8f0/e2e8f0?text=Logo' }}" class="object-cover w-full h-40 mb-4 rounded-md bg-gray-200">
                <h2 class="text-xl font-semibold">{{ $business->name }}</h2>
                <p class="text-sm text-gray-600">{{ $business->address }}</p>
                <a href="{{ route('business.show', $business->slug) }}" class="inline-block w-full px-4 py-2 mt-4 font-semibold text-center text-white bg-blue-600 rounded-md hover:bg-blue-700">
                    Lihat Jadwal & Antre
                </a>
            </div>
        @empty
            <p class="text-gray-500">Belum ada usaha yang terdaftar.</p>
        @endforelse
    </div>
@endsection