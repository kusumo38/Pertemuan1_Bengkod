<x-layouts.app title="Pembayaran">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Pembayaran Pemeriksaan</h1>
        <p class="mt-1 text-sm text-slate-500">Unggah bukti transfer untuk tagihan pemeriksaan yang belum lunas.</p>
    </div>

    <div class="space-y-4">
        @forelse($tagihan as $periksa)
            @php($pembayaran = $periksa->pembayaran)
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="font-bold text-slate-800">{{ $periksa->daftarPoli->jadwalPeriksa->dokter->poli->nama_poli ?? 'Poli' }}</h2>
                        <p class="text-sm text-slate-600">Dr. {{ $periksa->daftarPoli->jadwalPeriksa->dokter->nama }}</p>
                        <p class="text-xs text-slate-500">{{ $periksa->tgl_periksa->format('d/m/Y H:i') }} · Antrian {{ $periksa->daftarPoli->no_antrian }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-bold text-indigo-800">Rp {{ number_format($periksa->biaya_periksa, 0, ',', '.') }}</p>
                        @if($pembayaran?->status === \App\Models\Pembayaran::STATUS_LUNAS)
                            <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">Lunas</span>
                        @elseif($pembayaran)
                            <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">Menunggu verifikasi</span>
                        @else
                            <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">Belum dibayar</span>
                        @endif
                    </div>
                </div>

                @if(! $pembayaran || $pembayaran->status !== \App\Models\Pembayaran::STATUS_LUNAS)
                    <form action="{{ route('pasien.pembayaran.store') }}" method="POST" enctype="multipart/form-data" class="mt-5 border-t border-slate-100 pt-4">
                        @csrf
                        <input type="hidden" name="id_periksa" value="{{ $periksa->id }}">
                        <label for="bukti-{{ $periksa->id }}" class="mb-1 block text-sm font-semibold text-slate-700">
                            {{ $pembayaran ? 'Ganti bukti pembayaran' : 'Bukti pembayaran' }}
                        </label>
                        <div class="flex flex-wrap items-end gap-3">
                            <input id="bukti-{{ $periksa->id }}" type="file" name="bukti" accept="image/jpeg,image/png,image/webp"
                                class="file-input file-input-bordered w-full max-w-md @error('bukti') file-input-error @enderror" required>
                            <button type="submit" class="rounded-lg bg-primary px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary/90">
                                Unggah bukti
                            </button>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">Format JPG, PNG, atau WEBP; ukuran maksimal 4 MB.</p>
                        @error('bukti')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </form>
                @endif
            </article>
        @empty
            <div class="rounded-2xl border border-slate-200 bg-white px-6 py-12 text-center text-slate-500">
                Belum ada tagihan pemeriksaan.
            </div>
        @endforelse
    </div>

    <div class="mt-4">{{ $tagihan->links() }}</div>
</x-layouts.app>
