<x-guest-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h1 class="text-4xl font-bold text-gray-800 dark:text-gray-200">Temukan Layanan Terbaik</h1>
                <p class="text-lg text-gray-600 dark:text-gray-400 mt-2">Booking antrean Anda dengan mudah dan cepat.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @forelse ($businesses as $business)
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-lg transform hover:scale-105 transition-transform duration-300">
                        <div class="p-6">
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white">{{ $business->name }}</h3>
                            <p class="mt-2 text-gray-600 dark:text-gray-400">{{ $business->address }}</p>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-300">
                                Buka: {{ date('H:i', strtotime($business->open_time)) }} - {{ date('H:i', strtotime($business->close_time)) }}
                            </p>
                            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <a href="{{ route('browse.show', $business) }}" class="w-full text-center inline-flex items-center justify-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:ring focus:ring-indigo-200 active:bg-indigo-600 disabled:opacity-25 transition">
                                    Lihat Layanan
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 text-center py-12">
                        <p class="text-gray-500 dark:text-gray-400 text-xl">Belum ada bisnis yang tersedia saat ini.</p>
                    </div>
                @endforelse
            </div>

            <!-- Pagination Links -->
            <div class="mt-8">
                {{ $businesses->links() }}
            </div>
        </div>
    </div>
</x-guest-layout>
