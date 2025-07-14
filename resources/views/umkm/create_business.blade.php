<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Daftarkan Bisnis Anda') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('umkm.business.store') }}">
                        @csrf

                        <!-- Nama Bisnis -->
                        <div>
                            <x-input-label for="name" :value="__('Nama Bisnis')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Alamat -->
                        <div class="mt-4">
                            <x-input-label for="address" :value="__('Alamat Lengkap')" />
                            <textarea id="address" name="address" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('address') }}</textarea>
                            <x-input-error :messages="$errors->get('address')" class="mt-2" />
                        </div>

                        <!-- Deskripsi -->
                        <div class="mt-4">
                            <x-input-label for="description" :value="__('Deskripsi Singkat Bisnis (Opsional)')" />
                            <textarea id="description" name="description" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('description') }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <!-- Jam Buka & Tutup -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <x-input-label for="open_time" :value="__('Jam Buka')" />
                                <x-text-input id="open_time" class="block mt-1 w-full" type="time" name="open_time" :value="old('open_time')" required />
                                <x-input-error :messages="$errors->get('open_time')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="close_time" :value="__('Jam Tutup')" />
                                <x-text-input id="close_time" class="block mt-1 w-full" type="time" name="close_time" :value="old('close_time')" required />
                                <x-input-error :messages="$errors->get('close_time')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button class="ml-4">
                                {{ __('Daftarkan Bisnis') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
