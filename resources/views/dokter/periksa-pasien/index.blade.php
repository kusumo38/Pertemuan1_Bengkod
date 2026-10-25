<x-layouts.app title="Periksa Pasien">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-800">Daftar Pasien untuk Diperiksa</h2>
        <p class="mt-1 text-sm text-slate-500">Pasien yang terdaftar pada jadwal praktik Anda.</p>
    </div>

    <div class="card rounded-2xl border border-slate-200 bg-base-100 shadow-md">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-6 py-4">No. Antrian</th>
                            <th class="px-6 py-4">Nama Pasien</th>
                            <th class="px-6 py-4">Poli / Jadwal</th>
                            <th class="px-6 py-4">Keluhan</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-slate-700">
                        @forelse($daftarPasien as $daftarPoli)
                            @php($periksa = $daftarPoli->periksas->first())
                            <tr class="border-t border-slate-100 hover:bg-slate-50">
                                <td class="px-6 py-4 font-bold text-slate-800">{{ $daftarPoli->no_antrian }}</td>
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-slate-800">{{ $daftarPoli->pasien->nama }}</div>
                                    <div class="text-xs text-slate-500">No. RM: {{ $daftarPoli->pasien->no_rm ?: '-' }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div>{{ $daftarPoli->jadwalPeriksa->dokter->poli->nama_poli ?? 'Poli belum ditentukan' }}</div>
                                    <div class="text-xs text-slate-500">
                                        {{ $daftarPoli->jadwalPeriksa->hari }},
                                        {{ substr($daftarPoli->jadwalPeriksa->jam_mulai, 0, 5) }}–{{ substr($daftarPoli->jadwalPeriksa->jam_selesai, 0, 5) }}
                                    </div>
                                </td>
                                <td class="max-w-xs px-6 py-4">{{ $daftarPoli->keluhan }}</td>
                                <td class="px-6 py-4">
                                    @if($periksa)
                                        <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">Sudah diperiksa</span>
                                    @elseif($daftarPoli->status_antrian === 'dipanggil')
                                        <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">Sedang dipanggil</span>
                                    @else
                                        <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">Menunggu</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @if($periksa)
                                        <a href="{{ route('riwayat-pasien.show', $periksa) }}"
                                            class="inline-flex items-center gap-2 rounded-lg bg-slate-700 px-4 py-2 text-xs font-semibold text-white hover:bg-slate-800">
                                            <i class="fas fa-eye"></i> Lihat hasil
                                        </a>
                                    @elseif($daftarPoli->status_antrian === 'dipanggil')
                                        <a href="{{ route('periksa-pasien.create', $daftarPoli) }}"
                                            class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-xs font-semibold text-white hover:bg-primary/90">
                                            <i class="fas fa-stethoscope"></i> Periksa
                                        </a>
                                    @else
                                        <form action="{{ route('periksa-pasien.call', $daftarPoli) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-xs font-semibold text-white hover:bg-blue-700">
                                                <i class="fas fa-bullhorn"></i> Panggil
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-14 text-center text-slate-400">
                                    <i class="fas fa-inbox mb-3 block text-3xl"></i>
                                    Belum ada pasien yang terdaftar pada jadwal Anda.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>