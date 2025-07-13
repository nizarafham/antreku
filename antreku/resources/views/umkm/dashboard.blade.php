<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard Bisnis Anda') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Notifikasi -->
            @if (session('success'))
                <div class="mb-4 p-4 text-sm text-green-700 bg-green-100 rounded-lg dark:bg-green-200 dark:text-green-800" role="alert">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mb-4 p-4 text-sm text-red-700 bg-red-100 rounded-lg dark:bg-red-200 dark:text-red-800" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-bold">{{ $business->name }}</h3>
                    <p class="mt-2">Alamat: {{ $business->address }}</p>
                    <p class="mt-1">Jam Operasional: {{ date('H:i', strtotime($business->open_time)) }} - {{ date('H:i', strtotime($business->close_time)) }}</p>
                    <div class="mt-4">
                        Status Pendaftaran:
                        @if($business->status == 'pending')
                            <span class="px-2 py-1 font-semibold leading-tight text-orange-700 bg-orange-100 rounded-full dark:bg-orange-700 dark:text-orange-100">Menunggu Persetujuan Admin</span>
                        @elseif($business->status == 'approved')
                            <span class="px-2 py-1 font-semibold leading-tight text-green-700 bg-green-100 rounded-full dark:bg-green-700 dark:text-green-100">Disetujui</span>
                        @elseif($business->status == 'rejected')
                             <span class="px-2 py-1 font-semibold leading-tight text-red-700 bg-red-100 rounded-full dark:bg-red-700 dark:text-red-100">Ditolak</span>
                        @endif
                    </div>

                    <!-- Tampilkan link hanya jika bisnis disetujui -->
                    @if($business->status == 'approved')
                    <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-4 flex space-x-4">
                        <a href="{{ route('umkm.queue.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:border-blue-800 focus:ring focus:ring-blue-300 active:bg-blue-700 disabled:opacity-25 transition">
                            Kelola Antrean Hari Ini
                        </a>
                        <a href="{{ route('umkm.services.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                            Kelola Layanan Anda
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
