<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard Bisnis Anda') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-bold">{{ $business->name }}</h3>
                    <p class="mt-2">Alamat: {{ $business->address }}</p>
                    <p class="mt-1">Jam Operasional: {{ date('H:i', strtotime($business->open_time)) }} - {{ date('H:i', strtotime($business->close_time)) }}</p>
                    <div class="mt-4">
                        Status Pendaftaran:
                        @if($business->status == 'pending')
                            <span class="px-2 py-1 font-semibold leading-tight text-orange-700 bg-orange-100 rounded-full dark:bg-orange-700 dark:text-orange-100">
                                Menunggu Persetujuan Admin
                            </span>
                        @elseif($business->status == 'approved')
                            <span class="px-2 py-1 font-semibold leading-tight text-green-700 bg-green-100 rounded-full dark:bg-green-700 dark:text-green-100">
                                Disetujui
                            </span>
                        @elseif($business->status == 'rejected')
                             <span class="px-2 py-1 font-semibold leading-tight text-red-700 bg-red-100 rounded-full dark:bg-red-700 dark:text-red-100">
                                Ditolak
                            </span>
                        @endif
                    </div>
                    <p class="mt-4 italic">Selanjutnya Anda bisa mulai menambahkan layanan yang ditawarkan.</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
