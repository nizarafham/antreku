
@csrf
<!-- Nama Layanan -->
<div>
    <x-input-label for="name" :value="__('Nama Layanan')" />
    <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $service->name ?? '')" required autofocus />
    <x-input-error :messages="$errors->get('name')" class="mt-2" />
</div>

<!-- Deskripsi -->
<div class="mt-4">
    <x-input-label for="description" :value="__('Deskripsi Singkat (Opsional)')" />
    <textarea id="description" name="description" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('description', $service->description ?? '') }}</textarea>
    <x-input-error :messages="$errors->get('description')" class="mt-2" />
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
    <!-- Harga -->
    <div>
        <x-input-label for="price" :value="__('Harga (Rp)')" />
        <x-text-input id="price" class="block mt-1 w-full" type="number" name="price" :value="old('price', $service->price ?? '')" required />
        <x-input-error :messages="$errors->get('price')" class="mt-2" />
    </div>
    <!-- Durasi -->
    <div>
        <x-input-label for="duration_minutes" :value="__('Durasi (Menit)')" />
        <x-text-input id="duration_minutes" class="block mt-1 w-full" type="number" name="duration_minutes" :value="old('duration_minutes', $service->duration_minutes ?? '')" required />
        <x-input-error :messages="$errors->get('duration_minutes')" class="mt-2" />
    </div>
    <!-- Persentase DP -->
    <div>
        <x-input-label for="dp_percentage" :value="__('DP (%)')" />
        <x-text-input id="dp_percentage" class="block mt-1 w-full" type="number" name="dp_percentage" :value="old('dp_percentage', $service->dp_percentage ?? '100')" required />
        <x-input-error :messages="$errors->get('dp_percentage')" class="mt-2" />
    </div>
</div>

<div class="flex items-center justify-end mt-4">
    <a href="{{ route('umkm.services.index') }}" class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
        Batal
    </a>
    <x-primary-button class="ml-4">
        {{ $tombol ?? 'Simpan' }}
    </x-primary-button>
</div>
