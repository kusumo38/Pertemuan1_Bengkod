<x-layouts.app title="Verifikasi Pembayaran">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Verifikasi Pembayaran</h1>
        <p class="mt-1 text-sm text-slate-500">Periksa bukti transfer sebelum mengonfirmasi pembayaran.</p>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-md">
        <table class="table w-full">
            <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-5 py-4">Pasien</th>
                    <th class="px-5 py-4">Poli / Dokter</th>
                    <th class="px-5 py-4">Tanggal Periksa</th>
                    <th class="px-5 py-4">Tagihan</th>
                    <th class="px-5 py-4">Status</th>
                    <th class="px-5 py-4">Bukti</th>
                    <th class="px-5 py-4">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pembayaran as $item)
                    <tr class="border-t border-slate-100">
                        <td class="px-5 py-4 font-semibold">{{ $item->periksa->daftarPoli->pasien->nama }}</td>
                        <td class="px-5 py-4">
                            <div>{{ $item->periksa->daftarPoli->jadwalPeriksa->dokter->poli->nama_poli ?? '-' }}</div>
                            <div class="text-xs text-slate-500">Dr. {{ $item->periksa->daftarPoli->jadwalPeriksa->dokter->nama }}</div>
                        </td>
                        <td class="px-5 py-4">{{ $item->periksa->tgl_periksa->format('d/m/Y H:i') }}</td>
                        <td class="px-5 py-4">Rp {{ number_format($item->periksa->biaya_periksa, 0, ',', '.') }}</td>
                        <td class="px-5 py-4">
                            @if($item->status === \App\Models\Pembayaran::STATUS_LUNAS)
                                <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">Lunas</span>
                            @else
                                <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">Menunggu</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <a href="{{ route('admin.pembayaran.bukti', $item) }}" target="_blank" rel="noopener"
                                class="font-semibold text-blue-700 underline">Lihat bukti</a>
                        </td>
                        <td class="px-5 py-4">
                            @if($item->status !== \App\Models\Pembayaran::STATUS_LUNAS)
                                <form action="{{ route('admin.pembayaran.verify', $item) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="rounded-lg bg-green-600 px-4 py-2 text-xs font-semibold text-white hover:bg-green-700">
                                        Konfirmasi lunas
                                    </button>
                                </form>
                            @else
                                <span class="text-xs text-slate-500">{{ $item->waktu_verifikasi?->format('d/m/Y H:i') }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-6 py-12 text-center text-slate-500">Belum ada bukti pembayaran yang masuk.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $pembayaran->links() }}</div>
</x-layouts.app>
