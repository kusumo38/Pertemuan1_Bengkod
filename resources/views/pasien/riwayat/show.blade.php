<x-layouts.app title="Detail Pemeriksaan">
    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('pasien.riwayat.index') }}"
            class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-slate-500 hover:bg-slate-200"
            aria-label="Kembali ke riwayat pendaftaran">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-2xl font-bold text-slate-800">Detail Hasil Pemeriksaan</h1>
    </div>

    <div class="grid gap-5">
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 font-bold text-slate-800">Informasi Pemeriksaan</h2>
            <dl class="grid gap-4 text-sm sm:grid-cols-2">
                <div><dt class="text-slate-500">Poli</dt><dd class="font-semibold">{{ $periksa->daftarPoli->jadwalPeriksa->dokter->poli->nama_poli ?? '-' }}</dd></div>
                <div><dt class="text-slate-500">Dokter</dt><dd class="font-semibold">Dr. {{ $periksa->daftarPoli->jadwalPeriksa->dokter->nama }}</dd></div>
                <div><dt class="text-slate-500">Tanggal pemeriksaan</dt><dd class="font-semibold">{{ $periksa->tgl_periksa->format('d/m/Y H:i') }}</dd></div>
                <div><dt class="text-slate-500">Nomor antrian</dt><dd class="font-semibold">{{ $periksa->daftarPoli->no_antrian }}</dd></div>
                <div class="sm:col-span-2"><dt class="text-slate-500">Keluhan</dt><dd>{{ $periksa->daftarPoli->keluhan }}</dd></div>
                <div class="sm:col-span-2"><dt class="text-slate-500">Catatan dokter</dt><dd class="whitespace-pre-line">{{ $periksa->catatan ?: 'Tidak ada catatan.' }}</dd></div>
            </dl>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <h2 class="border-b border-slate-200 px-6 py-4 font-bold text-slate-800">Obat yang Diresepkan</h2>
            <table class="table w-full">
                <thead><tr><th>Nama Obat</th><th>Kemasan</th><th class="text-right">Harga</th></tr></thead>
                <tbody>
                    @forelse($periksa->detailPeriksas as $detail)
                        <tr>
                            <td>{{ $detail->obat->nama_obat }}</td>
                            <td>{{ $detail->obat->kemasan ?: '-' }}</td>
                            <td class="text-right">Rp {{ number_format($detail->obat->harga, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-slate-500">Tidak ada obat yang diresepkan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <div class="flex items-center justify-between rounded-2xl bg-indigo-50 px-6 py-5">
            <span class="font-bold text-slate-700">Total biaya pemeriksaan</span>
            <span class="text-xl font-extrabold text-indigo-800">Rp {{ number_format($periksa->biaya_periksa, 0, ',', '.') }}</span>
        </div>
    </div>
</x-layouts.app>
