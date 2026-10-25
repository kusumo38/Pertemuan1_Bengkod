<x-layouts.app title="Admin Dashboard">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Halo Selamat Datang Admin</h1>
        <p class="mt-1 text-sm text-slate-500">Daftar dan status antrean seluruh poli aktif diperbarui otomatis setiap 5 detik.</p>
    </div>

    <section id="admin-queue-board"
        data-refresh-url="{{ route('admin.antrian.data') }}"
        data-poli-ids="{{ $poliIds->toJson() }}"
        class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-md">
        <table class="table w-full">
            <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-5 py-4">No. Antrian</th>
                    <th class="px-5 py-4">Pasien</th>
                    <th class="px-5 py-4">Poli / Dokter</th>
                    <th class="px-5 py-4">Jadwal</th>
                    <th class="px-5 py-4">Status</th>
                </tr>
            </thead>
            <tbody id="admin-queue-rows">
                @include('admin.partials.queue-rows', ['daftarPoli' => $daftarPoli])
            </tbody>
        </table>
    </section>

    @push('scripts')
        <script>
            (() => {
                const board = document.getElementById('admin-queue-board');
                const rows = document.getElementById('admin-queue-rows');
                if (!board || !rows || !window.subscribeToQueueUpdates) return;

                const refreshQueue = async () => {
                    const response = await fetch(board.dataset.refreshUrl, {
                        headers: { 'Accept': 'application/json' },
                        credentials: 'same-origin'
                    });
                    if (!response.ok) throw new Error(`Gagal memperbarui daftar antrean: ${response.status}`);
                    const data = await response.json();
                    rows.innerHTML = data.html;
                };

                let poliIds;
                try {
                    poliIds = JSON.parse(board.dataset.poliIds);
                } catch (error) {
                    console.error('Daftar poli untuk update antrean tidak valid.', error);
                    return;
                }

                window.subscribeToQueueUpdates(poliIds, refreshQueue);
            })();
        </script>
    @endpush
</x-layouts.app>