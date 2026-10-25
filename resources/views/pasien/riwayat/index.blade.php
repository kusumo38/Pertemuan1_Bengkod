<x-layouts.app title="Riwayat Pendaftaran">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Riwayat Pendaftaran Poli</h1>
        <p class="mt-1 text-sm text-slate-500">Daftar seluruh pendaftaran pemeriksaan Anda.</p>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-md">
        <table class="table w-full">
            <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-5 py-4">Tanggal Jadwal</th>
                    <th class="px-5 py-4">Poli</th>
                    <th class="px-5 py-4">Dokter</th>
                    <th class="px-5 py-4">Jadwal</th>
                    <th class="px-5 py-4">Antrian</th>
                    <th class="px-5 py-4">Status</th>
                    <th class="px-5 py-4">Detail</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendaftaran as $item)
                    @php($periksa = $item->periksas->first())
                    <tr class="border-t border-slate-100">
                        <td class="px-5 py-4">{{ $item->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-5 py-4">{{ $item->jadwalPeriksa->dokter->poli->nama_poli ?? 'Poli belum ditentukan' }}</td>
                        <td class="px-5 py-4">Dr. {{ $item->jadwalPeriksa->dokter->nama }}</td>
                        <td class="px-5 py-4">
                            {{ $item->jadwalPeriksa->hari }}
                            {{ substr($item->jadwalPeriksa->jam_mulai, 0, 5) }}–{{ substr($item->jadwalPeriksa->jam_selesai, 0, 5) }}
                        </td>
                        <td class="px-5 py-4 font-semibold">{{ $item->no_antrian }}</td>
                        <td class="px-5 py-4">
                            @if($periksa)
                                <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">Selesai diperiksa</span>
                            @elseif($item->status_antrian === 'dipanggil')
                                <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">Sedang dipanggil</span>
                            @else
                                <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">Menunggu</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            @if($periksa)
                                <a href="{{ route('pasien.riwayat.show', $periksa) }}"
                                    class="rounded-lg bg-primary px-3 py-2 text-xs font-semibold text-white hover:bg-primary/90">
                                    Detail pemeriksaan
                                </a>
                            @else
                                <span class="text-xs text-slate-400">Belum tersedia</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-slate-500">Anda belum memiliki riwayat pendaftaran.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $pendaftaran->links() }}</div>
</x-layouts.app>
