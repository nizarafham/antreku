<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Manajemen & Verifikasi Bisnis') }}
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
                                    <th scope="col" class="py-3 px-6">Nama Bisnis</th>
                                    <th scope="col" class="py-3 px-6">Pemilik</th>
                                    <th scope="col" class="py-3 px-6">Status</th>
                                    <th scope="col" class="py-3 px-6">Tanggal Daftar</th>
                                    <th scope="col" class="py-3 px-6">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($businesses as $business)
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                    <th scope="row" class="py-4 px-6 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                        {{ $business->name }}
                                    </th>
                                    <td class="py-4 px-6">
                                        {{ $business->owner->name }}
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="px-2 py-1 font-semibold leading-tight rounded-full
                                            @if($business->status == 'pending') text-orange-700 bg-orange-100 dark:bg-orange-700 dark:text-orange-100 @endif
                                            @if($business->status == 'approved') text-green-700 bg-green-100 dark:bg-green-700 dark:text-green-100 @endif
                                            @if($business->status == 'rejected') text-red-700 bg-red-100 dark:bg-red-700 dark:text-red-100 @endif
                                        ">
                                            {{ ucfirst($business->status) }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6">
                                        {{ $business->created_at->format('d M Y') }}
                                    </td>
                                    <td class="py-4 px-6">
                                        @if ($business->status == 'pending')
                                            <div class="flex space-x-2">
                                                <!-- Tombol Approve -->
                                                <form action="{{ route('admin.businesses.approve', $business) }}" method="POST" onsubmit="return confirm('Anda yakin ingin menyetujui bisnis ini?');">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">Approve</button>
                                                </form>
                                                <!-- Tombol Reject -->
                                                <form action="{{ route('admin.businesses.reject', $business) }}" method="POST" onsubmit="return confirm('Anda yakin ingin menolak bisnis ini?');">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="font-medium text-red-600 dark:text-red-500 hover:underline">Reject</button>
                                                </form>
                                            </div>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">Tidak ada pendaftaran bisnis baru.</td>
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
