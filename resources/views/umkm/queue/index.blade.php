<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Manajemen Antrean Hari Ini') }} ({{ \Carbon\Carbon::today()->isoFormat('dddd, D MMMM Y') }})
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <!-- Notifikasi Sukses -->
                    @if (session('success'))
                        <div class="mb-4 p-4 text-sm text-green-700 bg-green-100 rounded-lg dark:bg-green-200 dark:text-green-800" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="overflow-x-auto relative">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="py-3 px-6">Jam</th>
                                    <th scope="col" class="py-3 px-6">Pelanggan</th>
                                    <th scope="col" class="py-3 px-6">Layanan</th>
                                    <th scope="col" class="py-3 px-6">Status</th>
                                    <th scope="col" class="py-3 px-6 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($queues as $queue)
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                    <td class="py-4 px-6 font-bold">{{ date('H:i', strtotime($queue->booking_time)) }}</td>
                                    <td class="py-4 px-6">{{ $queue->customer->name }}</td>
                                    <td class="py-4 px-6">{{ $queue->service->name }}</td>
                                    <td class="py-4 px-6">
                                        <span class="px-2 py-1 font-semibold leading-tight rounded-full
                                            @if($queue->status == 'confirmed') text-blue-700 bg-blue-100 @endif
                                            @if($queue->status == 'called') text-indigo-700 bg-indigo-100 @endif
                                            @if($queue->status == 'completed') text-green-700 bg-green-100 @endif
                                            @if($queue->status == 'no_show') text-red-700 bg-red-100 @endif
                                        ">
                                            {{ str_replace('_', ' ', ucfirst($queue->status)) }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <div class="flex justify-center space-x-2">
                                            @if($queue->status == 'confirmed')
                                                <form action="{{ route('umkm.queue.call', $queue) }}" method="POST">
                                                    @csrf @method('PATCH')
                                                    <button type="submit" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-3 py-1.5">Panggil</button>
                                                </form>
                                            @elseif($queue->status == 'called')
                                                <form action="{{ route('umkm.queue.complete', $queue) }}" method="POST">
                                                    @csrf @method('PATCH')
                                                    <button type="submit" class="text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-3 py-1.5">Selesai</button>
                                                </form>
                                                <form action="{{ route('umkm.queue.no-show', $queue) }}" method="POST">
                                                    @csrf @method('PATCH')
                                                    <button type="submit" class="text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-3 py-1.5">Tidak Hadir</button>
                                                </form>
                                            @else
                                                -
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-6">Tidak ada antrean untuk hari ini.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
