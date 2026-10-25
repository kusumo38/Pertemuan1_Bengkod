<x-layouts.app title="Form Pemeriksaan Pasien">
    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('periksa-pasien.index') }}"
            class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-slate-500 transition hover:bg-slate-200"
            aria-label="Kembali ke daftar pasien">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Pemeriksaan Pasien</h2>
            <p class="text-sm text-slate-500">Isi hasil pemeriksaan dan resep obat.</p>
        </div>
    </div>

    <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="mb-4 font-bold text-slate-800">Informasi Pasien</h3>
        <dl class="grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
            <div>
                <dt class="text-slate-500">Nama pasien</dt>
                <dd class="font-semibold text-slate-800">{{ $daftarPoli->pasien->nama }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">Nomor rekam medis</dt>
                <dd class="font-semibold text-slate-800">{{ $daftarPoli->pasien->no_rm ?: '-' }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">Poli dan jadwal</dt>
                <dd class="font-semibold text-slate-800">
                    {{ $daftarPoli->jadwalPeriksa->dokter->poli->nama_poli ?? 'Poli belum ditentukan' }} —
                    {{ $daftarPoli->jadwalPeriksa->hari }},
                    {{ substr($daftarPoli->jadwalPeriksa->jam_mulai, 0, 5) }}–{{ substr($daftarPoli->jadwalPeriksa->jam_selesai, 0, 5) }}
                </dd>
            </div>
            <div>
                <dt class="text-slate-500">Nomor antrian</dt>
                <dd class="font-semibold text-slate-800">{{ $daftarPoli->no_antrian }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-slate-500">Keluhan pasien</dt>
                <dd class="whitespace-pre-line text-slate-800">{{ $daftarPoli->keluhan }}</dd>
            </div>
        </dl>
    </div>

    <form action="{{ route('periksa-pasien.store') }}" method="POST"
        class="rounded-2xl border border-slate-200 bg-white p-6 shadow-md">
        @csrf
        <input type="hidden" name="id_daftar_poli" value="{{ $daftarPoli->id }}">

        <div class="mb-6">
            <label for="catatan" class="mb-1 block text-sm font-semibold text-slate-700">Catatan / diagnosis</label>
            <textarea id="catatan" name="catatan" rows="4" maxlength="5000"
                class="w-full rounded-lg border-2 border-slate-300 px-4 py-3 focus:border-primary focus:outline-none @error('catatan') border-red-500 @enderror"
                placeholder="Tuliskan hasil pemeriksaan, diagnosis, atau anjuran...">{{ old('catatan') }}</textarea>
            @error('catatan')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>

        <fieldset class="mb-6">
            <legend class="mb-2 text-sm font-semibold text-slate-700">Obat yang diresepkan</legend>
            <p class="mb-3 text-xs text-slate-500">Pilih obat yang diberikan. Harga obat akan ditambahkan ke total biaya.</p>
            @error('obat_ids')<p class="mb-2 text-xs text-red-500">{{ $message }}</p>@enderror
            @error('obat_ids.*')<p class="mb-2 text-xs text-red-500">{{ $message }}</p>@enderror

            <div class="overflow-hidden rounded-xl border border-slate-200">
                @forelse($obats as $obat)
                    <label class="flex cursor-pointer items-center justify-between gap-4 border-b border-slate-100 px-4 py-3 last:border-b-0 hover:bg-slate-50">
                        <span class="flex items-center gap-3">
                            <input type="checkbox" name="obat_ids[]" value="{{ $obat->id }}"
                                class="checkbox checkbox-primary"
                                @checked(in_array((string) $obat->id, (array) old('obat_ids', []), true))>
                            <span>
                                <span class="block font-semibold text-slate-800">{{ $obat->nama_obat }}</span>
                                <span class="block text-xs text-slate-500">{{ $obat->kemasan ?: 'Tanpa keterangan kemasan' }}</span>
                            </span>
                        </span>
                        <span class="whitespace-nowrap text-sm font-semibold text-slate-700">
                            Rp {{ number_format($obat->harga, 0, ',', '.') }}
                        </span>
                    </label>
                @empty
                    <p class="px-4 py-5 text-sm text-slate-500">Belum ada data obat. Pemeriksaan tetap dapat disimpan tanpa resep obat.</p>
                @endforelse
            </div>
        </fieldset>

        <div class="mb-6">
            <label for="biaya_periksa" class="mb-1 block text-sm font-semibold text-slate-700">
                Biaya tindakan tambahan (Rp) <span class="text-red-500">*</span>
            </label>
            <input id="biaya_periksa" type="number" name="biaya_periksa" min="0" step="1"
                value="{{ old('biaya_periksa', 0) }}"
                class="w-full rounded-lg border-2 border-slate-300 px-4 py-2 focus:border-primary focus:outline-none @error('biaya_periksa') border-red-500 @enderror"
                required>
            <p class="mt-1 text-xs text-slate-500">
                Tarif jasa dokter Rp 150.000 dan harga obat terpilih ditambahkan otomatis ke total.
            </p>
            @error('biaya_periksa')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>

        <div class="flex flex-wrap gap-3">
            <button type="submit"
                class="rounded-lg bg-primary px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-primary/90">
                <i class="fas fa-save mr-1"></i> Simpan pemeriksaan
            </button>
            <a href="{{ route('periksa-pasien.index') }}"
                class="rounded-lg bg-slate-100 px-6 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-200">
                Batal
            </a>
        </div>
    </form>
</x-layouts.app>