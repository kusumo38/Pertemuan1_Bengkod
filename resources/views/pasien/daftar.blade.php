<x-layouts.app title="Pendaftaran Pemeriksaan">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-800">Pendaftaran Pemeriksaan</h2>
        <p class="mt-1 text-sm text-slate-500">Pilih jadwal praktik yang tersedia dan jelaskan keluhan Anda.</p>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-md">
        @if($jadwals->isEmpty())
            <div class="py-8 text-center text-slate-500">
                <i class="fas fa-calendar-xmark mb-3 block text-3xl"></i>
                Saat ini belum ada jadwal praktik yang aktif. Silakan periksa kembali nanti.
            </div>
        @else
            <form action="{{ route('pasien.daftar.submit') }}" method="POST">
                @csrf

                <div class="mb-6">
                    <label for="id_jadwal" class="mb-1 block text-sm font-semibold text-slate-700">
                        Jadwal praktik <span class="text-red-500">*</span>
                    </label>
                    <select id="id_jadwal" name="id_jadwal"
                        class="w-full rounded-lg border-2 border-slate-300 px-4 py-2 focus:border-primary focus:outline-none @error('id_jadwal') border-red-500 @enderror"
                        required>
                        <option value="">Pilih dokter dan jadwal</option>
                        @foreach($jadwals as $jadwal)
                            <option value="{{ $jadwal->id }}" @selected((string) old('id_jadwal') === (string) $jadwal->id)>
                                {{ $jadwal->dokter->poli->nama_poli ?? 'Poli belum ditentukan' }}
                                — Dr. {{ $jadwal->dokter->nama }}
                                — {{ $jadwal->hari }}
                                {{ substr($jadwal->jam_mulai, 0, 5) }}–{{ substr($jadwal->jam_selesai, 0, 5) }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_jadwal')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div class="mb-6">
                    <label for="keluhan" class="mb-1 block text-sm font-semibold text-slate-700">
                        Keluhan <span class="text-red-500">*</span>
                    </label>
                    <textarea id="keluhan" name="keluhan" rows="4" maxlength="2000"
                        class="w-full rounded-lg border-2 border-slate-300 px-4 py-3 focus:border-primary focus:outline-none @error('keluhan') border-red-500 @enderror"
                        placeholder="Jelaskan keluhan yang dirasakan..." required>{{ old('keluhan') }}</textarea>
                    @error('keluhan')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <button type="submit"
                    class="rounded-lg bg-primary px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-primary/90">
                    <i class="fas fa-calendar-check mr-1"></i> Daftar pemeriksaan
                </button>
            </form>
        @endif
    </div>
</x-layouts.app>
