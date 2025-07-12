<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Riwayat Booking Saya') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="overflow-x-auto relative">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="py-3 px-6">Bisnis</th>
                                    <th scope="col" class="py-3 px-6">Layanan</th>
                                    <th scope="col" class="py-3 px-6">Jadwal</th>
                                    <th scope="col" class="py-3 px-6">Status Antrean</th>
                                    <th scope="col" class="py-3 px-6">Status Pembayaran</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($bookings as $booking)
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                    <th scope="row" class="py-4 px-6 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                        {{ $booking->business->name }}
                                    </th>
                                    <td class="py-4 px-6">{{ $booking->service->name }}</td>
                                    <td class="py-4 px-6">{{ \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') }}, {{ $booking->booking_time }}</td>
                                    <td class="py-4 px-6">
                                        <span class="px-2 py-1 font-semibold leading-tight rounded-full
                                            @if($booking->status == 'pending_payment') text-yellow-700 bg-yellow-100 @endif
                                            @if($booking->status == 'confirmed') text-blue-700 bg-blue-100 @endif
                                            @if($booking->status == 'called') text-indigo-700 bg-indigo-100 @endif
                                            @if($booking->status == 'completed') text-green-700 bg-green-100 @endif
                                            @if($booking->status == 'cancelled' || $booking->status == 'no_show') text-red-700 bg-red-100 @endif
                                        ">
                                            {{ str_replace('_', ' ', ucfirst($booking->status)) }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6">
                                        @if ($booking->transaction)
                                            <span class="px-2 py-1 font-semibold leading-tight rounded-full
                                                @if($booking->transaction->status == 'pending') text-yellow-700 bg-yellow-100 @endif
                                                @if($booking->transaction->status == 'success') text-green-700 bg-green-100 @endif
                                                @if($booking->transaction->status == 'failed' || $booking->transaction->status == 'expired') text-red-700 bg-red-100 @endif
                                            ">
                                                {{ ucfirst($booking->transaction->status) }}
                                            </span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">Anda belum memiliki riwayat booking.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Links -->
                    <div class="mt-4">
                        {{ $bookings->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
