<?php
// File: resources/views/pages/queues/history.blade.php
?>
@extends('layouts.app')
@section('title', 'Riwayat Antrean')

@section('content')
<h1 class="text-3xl font-bold mb-6">Riwayat Antrean Saya</h1>

@if(session('snap_token'))
    <div class="mb-6 p-4 bg-blue-100 text-blue-800 rounded-lg">
        Booking Anda berhasil! Klik tombol di bawah untuk menyelesaikan pembayaran DP.
        <button id="pay-button" class="mt-2 px-4 py-2 font-semibold text-white bg-blue-600 rounded-md hover:bg-blue-700">Bayar Sekarang</button>
    </div>
@endif

<div class="space-y-4">
    @forelse ($queues as $queue)
        <div class="p-4 bg-white border rounded-lg shadow-sm">
            <div class="flex justify-between items-start">
                <div>
                    <h2 class="text-xl font-semibold">{{ $queue->business->name }}</h2>
                    <p class="text-sm text-gray-500">No. Antrean: {{ $queue->queue_number }}</p>
                    <p class="text-sm text-gray-500">Jadwal: {{ \Carbon\Carbon::parse($queue->scheduled_at)->isoFormat('dddd, D MMMM YYYY - HH:mm') }}</p>
                </div>
                <span class="px-3 py-1 text-sm font-medium rounded-full 
                    @if(in_array($queue->status, ['waiting_payment', 'confirmed'])) bg-yellow-100 text-yellow-800 @endif
                    @if(in_array($queue->status, ['called', 'in_progress'])) bg-blue-100 text-blue-800 @endif
                    @if($queue->status == 'completed') bg-green-100 text-green-800 @endif
                    @if(in_array($queue->status, ['no_show', 'cancelled'])) bg-red-100 text-red-800 @endif
                ">
                    {{ str_replace('_', ' ', Str::title($queue->status)) }}
                </span>
            </div>
            @if($queue->transaction)
            <div class="mt-2 pt-2 border-t">
                <p class="text-sm">Status Pembayaran: 
                    <span class="font-semibold 
                        @if($queue->transaction->status == 'pending') text-yellow-600 @endif
                        @if($queue->transaction->status == 'success') text-green-600 @endif
                        @if($queue->transaction->status == 'failed') text-red-600 @endif
                    ">
                        {{ ucfirst($queue->transaction->status) }}
                    </span>
                </p>
            </div>
            @endif
        </div>
    @empty
        <p class="text-gray-500">Anda belum memiliki riwayat antrean.</p>
    @endforelse
</div>
@endsection

@push('scripts')
@if (session('snap_token'))
<script type="text/javascript">
    var payButton = document.getElementById('pay-button');
    payButton.addEventListener('click', function () {
        window.snap.pay('{{ session('snap_token') }}', {
            onSuccess: function(result){
                alert("Pembayaran berhasil!"); window.location.reload();
            },
            onPending: function(result){
                alert("Menunggu pembayaran Anda!");
            },
            onError: function(result){
                alert("Pembayaran gagal!");
            },
            onClose: function(){
                alert('Anda menutup popup tanpa menyelesaikan pembayaran');
            }
        });
    });
    // Langsung klik tombol bayar saat halaman dimuat
    payButton.click();
</script>
@endif
@endpush