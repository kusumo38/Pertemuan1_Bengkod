<x-layouts.app title="Jadwal Periksa">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-slate-800">Jadwal Periksa</h2>
        <div class="flex gap-2">
        <a href="{{ route('dokter.export.jadwal') }}" class="inline-flex items-center gap-2 rounded-xl bg-green-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-green-700">
            <i class="fas fa-file-excel"></i> Export Excel
        </a>
        <a href="{{ route('jadwal-periksa.create') }}"
            class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary hover:bg-primary/90 text-white text-sm font-semibold rounded-xl transition">
            <i class="fas fa-plus text-xs"></i>
            Tambah Jadwal
        </a>
        </div>
    </div>

    <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-200">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-xs tracking-wider">
                        <tr>
                            <th class="px-6 py-4">Hari</th>
                            <th class="px-6 py-4">Jam Mulai</th>
                            <th class="px-6 py-4">Jam Selesai</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-slate-700">
                        @forelse($jadwals as $jadwalPeriksa)
                            <tr class="border-t border-slate-100 hover:bg-slate-50 transition">
                                <td class="px-6 py-4 font-semibold text-slate-800">{{ $jadwalPeriksa->hari }}</td>
                                <td class="px-6 py-4">{{ substr($jadwalPeriksa->jam_mulai, 0, 5) }}</td>
                                <td class="px-6 py-4">{{ substr($jadwalPeriksa->jam_selesai, 0, 5) }}</td>
                                <td class="px-6 py-4">
                                    @if($jadwalPeriksa->status)
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">Aktif</span>
                                    @else
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('jadwal-periksa.edit', $jadwalPeriksa) }}"
                                            class="inline-flex items-center gap-1 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold rounded-lg transition">
                                            <i class="fas fa-pen text-xs"></i>
                                            Edit
                                        </a>
                                        <form action="{{ route('jadwal-periksa.destroy', $jadwalPeriksa) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" onclick="return confirm('Yakin ingin menghapus jadwal ini?')"
                                                class="inline-flex items-center gap-1 px-4 py-2 bg-red-500 hover:bg-red-600 text-white text-xs font-semibold rounded-lg transition">
                                                <i class="fas fa-trash text-xs"></i>
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-12 text-slate-400">
                                    <i class="fas fa-inbox text-3xl mb-3 block"></i>
                                    Belum ada jadwal periksa
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
