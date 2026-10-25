<x-layouts.app title="Pasien Dashboard">
    @if($pendaftaranAktif)
        @php
            $jadwalAktif = $pendaftaranAktif->jadwalPeriksa;
            $nomorDipanggil = $jadwalAktif->daftarPolis->first()?->no_antrian;
        @endphp
        <section class="mb-6 rounded-2xl bg-gradient-to-r from-indigo-700 to-blue-600 p-6 text-white shadow-lg">
            <div class="flex flex-wrap items-start justify-between gap-5">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wider text-blue-100">Antrian pemeriksaan Anda</p>
                    <h1 class="mt-1 text-2xl font-bold">
                        {{ $jadwalAktif->dokter->poli->nama_poli ?? 'Poli' }}
                    </h1>
                    <p class="mt-1 text-blue-100">Dr. {{ $jadwalAktif->dokter->nama }}</p>
                    <p class="mt-2 text-sm text-blue-100">
                        {{ $jadwalAktif->hari }}, {{ substr($jadwalAktif->jam_mulai, 0, 5) }}–{{ substr($jadwalAktif->jam_selesai, 0, 5) }}
                    </p>
                </div>
                <div class="flex gap-4">
                    <div class="min-w-28 rounded-xl bg-white/10 p-4 text-center">
                        <p class="text-xs text-blue-100">Nomor Anda</p>
                        <p class="mt-1 text-3xl font-extrabold">{{ $pendaftaranAktif->no_antrian }}</p>
                    </div>
                    <div class="min-w-32 rounded-xl bg-white/10 p-4 text-center" aria-live="polite">
                        <p class="text-xs text-blue-100">Sedang dipanggil</p>
                        <p class="mt-1 text-3xl font-extrabold" id="banner-current-number">
                            {{ $nomorDipanggil ?? '—' }}
                        </p>
                    </div>
                </div>
            </div>
            <p class="mt-4 text-sm text-blue-100">
                Status pendaftaran:
                <span class="font-semibold text-white">
                    {{ $pendaftaranAktif->status_antrian === 'dipanggil' ? 'Sedang dipanggil' : 'Menunggu' }}
                </span>
                · <a class="underline" href="{{ route('pasien.riwayat.index') }}">Lihat riwayat pendaftaran</a>
            </p>
        </section>
    @else
        <section class="mb-6 rounded-2xl border border-blue-100 bg-blue-50 p-5 text-blue-900">
            <h1 class="font-bold">Selamat datang, {{ auth()->user()->nama }}.</h1>
            <p class="mt-1 text-sm">Anda belum memiliki antrian aktif. Silakan pilih jadwal untuk mendaftar pemeriksaan.</p>
            <a href="{{ route('pasien.daftar') }}" class="mt-3 inline-flex rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white">
                Daftar pemeriksaan
            </a>
        </section>
    @endif

    <div class="mb-4">
        <h2 class="text-xl font-bold text-slate-800">Jadwal dan Antrian Poliklinik</h2>
        <p class="mt-1 text-sm text-slate-500">Nomor yang sedang dipanggil diperbarui otomatis.</p>
    </div>

    <div id="patient-queue-board"
        data-refresh-url="{{ route('pasien.antrian.data') }}"
        data-poli-ids="{{ json_encode($jadwals->pluck('dokter.id_poli')->filter()->unique()->values()) }}"
        data-active-poli="{{ $jadwalAktif->dokter->id_poli ?? '' }}"
        class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-md">
        <table class="table w-full">
            <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-5 py-4">No.</th>
                    <th class="px-5 py-4">Poli</th>
                    <th class="px-5 py-4">Dokter</th>
                    <th class="px-5 py-4">Hari</th>
                    <th class="px-5 py-4">Jam Periksa</th>
                    <th class="px-5 py-4">Sedang Dipanggil</th>
                </tr>
            </thead>
            <tbody>
                @forelse($jadwals as $index => $jadwal)
                    <tr class="border-t border-slate-100" data-poli-id="{{ $jadwal->dokter->id_poli }}">
                        <td class="px-5 py-4">{{ $index + 1 }}</td>
                        <td class="px-5 py-4 font-semibold">{{ $jadwal->dokter->poli->nama_poli ?? 'Poli belum ditentukan' }}</td>
                        <td class="px-5 py-4">Dr. {{ $jadwal->dokter->nama }}</td>
                        <td class="px-5 py-4">{{ $jadwal->hari }}</td>
                        <td class="px-5 py-4">{{ substr($jadwal->jam_mulai, 0, 5) }}–{{ substr($jadwal->jam_selesai, 0, 5) }}</td>
                        <td class="px-5 py-4 font-bold" aria-live="polite">
                            <span id="queue-number-{{ $jadwal->id }}" class="queue-number-poli-{{ $jadwal->dokter->id_poli }}">
                                {{ $jadwal->daftarPolis->first()?->no_antrian ?? '—' }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-500">Belum ada jadwal poliklinik aktif.</td>
                    </tr>
                @endempty
            </tbody>
        </table>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const board = document.getElementById('patient-queue-board');
            if (!board || !window.Echo) {
                console.error('Echo atau board antrian tidak ditemukan.');
                return;
            }

            let poliIds = [];
            try {
                poliIds = JSON.parse(board.dataset.poliIds || '[]');
            } catch (error) {
                console.error('Data Poli ID tidak valid:', error);
            }

            console.log('Mendengarkan channel Reverb untuk Poli IDs:', poliIds);

            // Dengarkan event untuk semua Poli yang ada di tabel
            poliIds.forEach(poliId => {
                window.Echo.channel(`poliklinik.queue.${poliId}`)
                    .listen('.queue.updated', (event) => {
                        console.log('Event QueueUpdated Diterima! Data:', event);
                        
                        const numberDisplay = (event.currentQueue && event.currentQueue > 0) ? event.currentQueue : '—';

                        // Update tampilan di tabel
                        const queueCells = document.querySelectorAll(`.queue-number-poli-${event.poliId}`);
                        queueCells.forEach(cell => {
                            cell.textContent = numberDisplay;
                        });

                        // Update tampilan di Banner Pasien
                        const bannerNumber = document.getElementById('banner-current-number');
                        if (bannerNumber) {
                            bannerNumber.textContent = numberDisplay;
                        }
                    });
            });
        });
    </script>
@endpush
</x-layouts.app>