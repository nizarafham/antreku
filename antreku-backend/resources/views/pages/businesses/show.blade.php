<?php
// File: resources/views/pages/businesses/show.blade.php
?>
@extends('layouts.app')
@section('title', $business->name)

@section('content')
    <h1 class="text-3xl font-bold">{{ $business->name }}</h1>
    <p class="text-gray-600 mt-1">{{ $business->address }}</p>
    <p class="text-lg font-semibold text-green-600 mt-2">DP: Rp {{ number_format($business->dp_amount, 0, ',', '.') }}</p>
    
    @if(session('error'))
        <div class="mt-4 p-3 text-sm text-red-700 bg-red-100 rounded-lg">{{ session('error') }}</div>
    @endif

    <div class="mt-8">
        <h2 class="text-xl font-semibold">Pilih Tanggal & Waktu Antrean</h2>
        
        <form method="GET" action="{{ route('business.show', $business->slug) }}" class="my-4">
            <input type="date" name="date" value="{{ $selectedDate }}" onchange="this.form.submit()" class="px-3 py-2 border rounded-md">
        </form>

        @auth('customer')
        <form method="POST" action="{{ route('queue.book') }}">
            @csrf
            <input type="hidden" name="business_id" value="{{ $business->id }}">
            
            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-3 mt-4">
                @forelse ($slots as $slot)
                    <div class="relative">
                        <input type="radio" name="queue_slot_id" id="slot_{{ $slot->id }}" value="{{ $slot->id }}" class="hidden peer" required>
                        <label for="slot_{{ $slot->id }}" class="block p-3 text-center border rounded-md transition cursor-pointer peer-checked:bg-blue-600 peer-checked:text-white hover:bg-gray-100">
                            {{ \Carbon\Carbon::parse($slot->slot_datetime)->format('H:i') }}
                        </label>
                    </div>
                @empty
                    <p class="col-span-full text-gray-500">Tidak ada slot tersedia untuk tanggal ini.</p>
                @endforelse
            </div>

            @if(count($slots) > 0)
            <div class="mt-6">
                <button type="submit" class="w-full max-w-xs px-4 py-3 font-semibold text-white bg-green-600 rounded-md hover:bg-green-700">
                    Pesan Sekarang
                </button>
            </div>
            @endif
        </form>
        @else
        <div class="mt-6 p-4 bg-yellow-100 text-yellow-800 rounded-md">
            Silakan <a href="{{ route('login') }}" class="font-bold underline">login</a> atau <a href="{{ route('register') }}" class="font-bold underline">daftar</a> untuk memesan antrean.
        </div>
        @endauth
    </div>
@endsection