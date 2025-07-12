<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Pesan Layanan: {{ $service->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-bold">{{ $service->business->name }}</h3>
                    <p class="mb-6">{{ $service->business->address }}</p>

                    <form method="POST" action="{{ route('booking.store', $service) }}">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Kolom Kiri: Pilih Tanggal -->
                            <div>
                                <x-input-label for="booking_date" value="Pilih Tanggal (Minimal H-1)" />
                                <!-- PERUBAHAN: readonly dihapus. Input manual akan dicegah oleh JS -->
                                <x-text-input type="date" id="booking_date" name="booking_date" class="mt-1 block w-full cursor-pointer" min="{{ \Carbon\Carbon::tomorrow()->format('Y-m-d') }}" required />
                                <x-input-error :messages="$errors->get('booking_date')" class="mt-2" />
                            </div>

                            <!-- Kolom Kanan: Pilih Waktu -->
                            <div>
                                <x-input-label for="booking_time" value="Pilih Waktu Tersedia" />
                                <select id="booking_time" name="booking_time" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                    <option value="">-- Pilih Tanggal Dulu --</option>
                                </select>
                                <div id="loading-slots" class="mt-2 text-sm text-gray-500" style="display: none;">Mencari slot...</div>
                                <x-input-error :messages="$errors->get('booking_time')" class="mt-2" />
                            </div>
                        </div>

                        <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-6 flex justify-end">
                            <x-primary-button>
                                Lanjutkan ke Pembayaran
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        const bookingDateInput = document.getElementById('booking_date');

        // PERUBAHAN: Mencegah input manual dari keyboard
        bookingDateInput.addEventListener('keydown', function(event) {
            event.preventDefault();
        });

        bookingDateInput.addEventListener('change', function() {
            const date = this.value;
            const timeSelect = document.getElementById('booking_time');
            const loading = document.getElementById('loading-slots');

            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            const tomorrowString = tomorrow.toISOString().split('T')[0];

            timeSelect.innerHTML = '<option value="">-- Pilih Tanggal Dulu --</option>';
            if (!date) return;

            if (date < tomorrowString) {
                timeSelect.innerHTML = '<option value="">Hanya bisa booking H-1</option>';
                return;
            }

            const serviceId = {{ $service->id }};
            const url = `/booking/slots/${serviceId}?date=${date}`;

            loading.style.display = 'block';
            timeSelect.disabled = true;

            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok, status: ' + response.status);
                }
                return response.json();
            })
            .then(slots => {
                timeSelect.innerHTML = '<option value="">-- Pilih Waktu --</option>';
                if (slots.length > 0) {
                    slots.forEach(slot => {
                        const option = document.createElement('option');
                        option.value = slot;
                        option.textContent = slot;
                        timeSelect.appendChild(option);
                    });
                } else {
                    timeSelect.innerHTML = '<option value="">-- Tidak ada slot tersedia --</option>';
                }
            })
            .catch(error => {
                console.error('Error fetching slots:', error);
                timeSelect.innerHTML = '<option value="">-- Gagal memuat slot --</option>';
            })
            .finally(() => {
                loading.style.display = 'none';
                timeSelect.disabled = false;
            });
        });
    </script>
    @endpush
</x-app-layout>
