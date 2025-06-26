<div class="flex flex-col flex-grow justify-center items-center h-full bg-gray-100" wire:poll.30s="callNextQueue">
    <!-- Bagian Atas -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6 w-full">
        @php
            $lastDipanggil = $counters->filter(fn($item) => $item->activeQueue?->kiosk_status === 'Dipanggil')->last();
        @endphp

        @if($lastDipanggil)
            <div class="p-6 rounded-lg shadow-lg text-center bg-slate-200 w-full">
                <div class="mb-4">
                    <h2 class="text-2xl font-bold mb-1">
                        Antrian Terbaru
                    </h2>
                    <p class="text-gray-600">{{ $lastDipanggil->service->name  }}</p>
                </div>
                <div class="space-y-2">
                    <div class="text-4xl font-bold text-blue-600">
                        {{ $lastDipanggil->activeQueue->number }}
                    </div>

                    <p class="font-medium text-sm rounded-full px-3 py-1 bg-red-500 text-white">Segera Menuju {{ $lastDipanggil->name }}</p>
                </div>
            </div>
        @else
            <div class="p-6 rounded-lg shadow-lg text-center bg-slate-200 w-full">
                <div class="mb-4">
                    <h2 class="text-2xl font-bold mb-1">
                        ---
                    </h2>
                    <p class="text-gray-600">---</p>
                </div>
                <div class="space-y-2">
                    <div class="text-4xl font-bold text-blue-600">
                        ---
                    </div>

                    <p class="font-medium text-sm rounded-full px-3 py-1 bg-red-500 text-white">---</p>
                </div>
            </div>
        @endif

        <div class="p-6 rounded-lg shadow-lg text-center bg-slate-200 w-full flex flex-col items-center justify-center space-y-2" wire:ignore>
            <div id="tanggal" class="text-lg font-semibold text-gray-700">--</div>
            <div id="jam" class="text-4xl font-bold text-blue-600">--:--:--</div>
        </div>  
    </div>

    <!-- Bagian Loket -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 p-6 w-full">
        @foreach ($counters as $counter)
            <div class="p-6 rounded-lg shadow-lg text-center bg-slate-200 flex flex-col justify-between">
                <div class="mb-4">
                    <h2 class="text-2xl font-bold mb-1">{{ $counter->name }}</h2>
                    <p class="text-gray-600">{{ $counter->service->name }}</p>
                </div>

                <div class="space-y-2">
                    @if ($counter->activeQueue)
                        <div class="text-4xl font-bold text-blue-600">{{ $counter->activeQueue->number }}</div>
                        <div class="text-lg font-semibold px-4 py-1 rounded-full inline-block">
                            {{ $counter->activeQueue->kiosk_status }}
                        </div>
                    @else
                        <div class="text-4xl font-bold text-blue-600">---</div>
                        <div class="text-lg font-semibold px-4 py-1 rounded-full inline-block">Tidak Ada Antrian</div>
                    @endif

                    @if(!$counter->is_active)
                        <p class="font-medium text-sm rounded-full px-3 py-1 bg-red-500 text-white">Loket Tidak Aktif</p>
                    @elseif($counter->is_available)
                        <p class="font-medium text-sm rounded-full px-3 py-1 bg-blue-400 text-white">Siap Melayani</p>
                    @else
                        <p class="font-medium text-sm rounded-full px-3 py-1 bg-green-400 text-white">Sedang Melayani</p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>

@push('scripts')
    <script>
        function updateTanggalJam() {
            const now = new Date();

            const optionsTanggal = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            };

            const tanggal = now.toLocaleDateString('id-ID', optionsTanggal);
            const jam = now.toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });

            document.getElementById('tanggal').textContent = tanggal;
            document.getElementById('jam').textContent = jam;
        }

        setInterval(updateTanggalJam, 1000);
        updateTanggalJam(); // panggil pertama kali
    </script>   
@endpush
