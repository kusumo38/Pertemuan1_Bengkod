@forelse($daftarPoli as $pendaftaran)
    <tr class="border-t border-slate-100 hover:bg-slate-50">
        <td class="px-5 py-4 font-bold">{{ $pendaftaran->no_antrian }}</td>
        <td class="px-5 py-4">
            <div class="font-semibold">{{ $pendaftaran->pasien->nama }}</div>
            <div class="text-xs text-slate-500">No. RM: {{ $pendaftaran->pasien->no_rm ?: '-' }}</div>
        </td>
        <td class="px-5 py-4">
            <div class="font-semibold">{{ $pendaftaran->jadwalPeriksa->dokter->poli->nama_poli ?? 'Poli belum ditentukan' }}</div>
            <div class="text-xs text-slate-500">Dr. {{ $pendaftaran->jadwalPeriksa->dokter->nama }}</div>
        </td>
        <td class="px-5 py-4">
            {{ $pendaftaran->jadwalPeriksa->hari }},
            {{ substr($pendaftaran->jadwalPeriksa->jam_mulai, 0, 5) }}–{{ substr($pendaftaran->jadwalPeriksa->jam_selesai, 0, 5) }}
        </td>
        <td class="px-5 py-4">
            @if($pendaftaran->status_antrian === 'selesai')
                <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">Selesai</span>
            @elseif($pendaftaran->status_antrian === 'dipanggil')
                <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">Sedang dipanggil</span>
            @else
                <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">Menunggu</span>
            @endif
        </td>
    </tr>
@empty
    <tr>
        <td colspan="5" class="px-6 py-12 text-center text-slate-500">
            Belum ada pasien yang terdaftar pada jadwal aktif.
        </td>
    </tr>
@endforelse