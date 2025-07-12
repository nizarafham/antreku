<x-app-layout>
    <!-- Tambahkan script Snap.js di header -->
    <x-slot name="header">
        <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Booking Berhasil Dibuat
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 text-center">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-8 text-gray-900 dark:text-gray-100">
                    <h3 class="text-2xl font-bold text-green-500">Terima Kasih!</h3>
                    <p class="mt-2">Pesanan Anda telah kami catat. Silakan selesaikan pembayaran untuk mengonfirmasi antrean Anda.</p>

                    <div class="mt-6 text-left border-t border-gray-200 dark:border-gray-700 pt-6 space-y-2">
                        <p><strong>ID Pesanan:</strong> {{ $queue->transaction->midtrans_order_id }}</p>
                        <p><strong>Bisnis:</strong> {{ $queue->business->name }}</p>
                        <p><strong>Layanan:</strong> {{ $queue->service->name }}</p>
                        <p><strong>Jadwal:</strong> {{ \Carbon\Carbon::parse($queue->booking_date)->isoFormat('dddd, D MMMM Y') }} jam {{ $queue->booking_time }}</p>
                        <p class="text-xl font-bold"><strong>Total DP:</strong> Rp {{ number_format($queue->transaction->amount, 0, ',', '.') }}</p>
                    </div>

                    <div class="mt-8">
                        <x-primary-button id="pay-button">
                            Bayar Sekarang
                        </x-primary-button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script type="text/javascript">
      // Untuk menampilkan popup pembayaran Midtrans
      var payButton = document.getElementById('pay-button');
      payButton.addEventListener('click', function () {
        window.snap.pay('{{ $queue->transaction->snap_token }}', {
          onSuccess: function(result){
            /* Anda bisa tambahkan aksi di sini, misalnya redirect ke halaman status */
            window.location.href = '/my-bookings'; // Ganti dengan rute riwayat booking Anda
            alert("payment success!"); console.log(result);
          },
          onPending: function(result){
            /* Pelanggan belum menyelesaikan pembayaran */
            alert("wating your payment!"); console.log(result);
          },
          onError: function(result){
            /* Terjadi kesalahan */
            alert("payment failed!"); console.log(result);
          },
          onClose: function(){
            /* Pelanggan menutup popup tanpa menyelesaikan pembayaran */
            alert('you closed the popup without finishing the payment');
          }
        })
      });
    </script>
    @endpush
</x-app-layout>
