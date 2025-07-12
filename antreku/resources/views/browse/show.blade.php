<x-guest-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Business Header -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h1 class="text-3xl font-bold">{{ $business->name }}</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">{{ $business->address }}</p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-300">
                        Jam Operasional: {{ date('H:i', strtotime($business->open_time)) }} - {{ date('H:i', strtotime($business->close_time)) }}
                    </p>
                    @if($business->description)
                        <p class="mt-4">{{ $business->description }}</p>
                    @endif
                </div>
            </div>

            <!-- Services List -->
            <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200 mb-6">Daftar Layanan</h2>
            <div class="space-y-4">
                 @forelse ($business->services as $service)
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 flex justify-between items-center">
                            <div>
                                <h4 class="text-lg font-bold text-gray-900 dark:text-white">{{ $service->name }}</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Durasi: {{ $service->duration_minutes }} menit</p>
                                <p class="text-lg font-semibold text-indigo-600 dark:text-indigo-400 mt-1">Rp {{ number_format($service->price, 0, ',', '.') }}</p>
                            </div>
                            <div>
                                <a href="#" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:ring focus:ring-indigo-200 active:bg-indigo-600 disabled:opacity-25 transition">
                                    Pesan Sekarang
                                </a>
                            </div>
                        </div>
                    </div>
                 @empty
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-center">
                            <p class="text-gray-500 dark:text-gray-400">Bisnis ini belum memiliki layanan yang tersedia.</p>
                        </div>
                    </div>
                 @endforelse
            </div>
        </div>
    </div>
</x-guest-layout>
