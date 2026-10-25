<?php

namespace App\Http\Controllers\Dokter;

use App\Events\QueueUpdated;
use App\Http\Controllers\Controller;
use App\Models\DaftarPoli;
use App\Models\Obat;
use App\Models\Periksa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PeriksaPasienController extends Controller
{
    private const BIAYA_JASA_DOKTER = 150000;

    public function index(): View
    {
        $dokterId = Auth::id();

        $daftarPasien = DaftarPoli::with(['pasien', 'jadwalPeriksa.dokter.poli', 'periksas'])
            ->whereHas('jadwalPeriksa', function ($query) use ($dokterId) {
                $query->where('id_dokter', $dokterId);
            })
            ->orderBy('no_antrian')
            ->get();

        return view('dokter.periksa-pasien.index', compact('daftarPasien'));
    }

    public function create(int $id): View|RedirectResponse
    {
        $daftarPoli = $this->findDoctorsRegistration($id);

        if ($daftarPoli->periksas()->exists()) {
            return redirect()
                ->route('periksa-pasien.index')
                ->with('error', 'Pasien ini sudah selesai diperiksa.');
        }

        if ($daftarPoli->status_antrian !== 'dipanggil') {
            return redirect()
                ->route('periksa-pasien.index')
                ->with('error', 'Panggil pasien dari daftar antrian sebelum memulai pemeriksaan.');
        }

        $daftarPoli->load(['pasien', 'jadwalPeriksa.dokter.poli']);
        $obats = Obat::orderBy('nama_obat')->get();

        return view('dokter.periksa-pasien.create', compact('daftarPoli', 'obats'));
    }

    public function call(int $id): RedirectResponse
    {
        $result = DB::transaction(function () use ($id) {
            $daftarPoli = $this->findDoctorsRegistration($id, true);
            $jadwal = $daftarPoli->jadwalPeriksa()->lockForUpdate()->firstOrFail();

            if ($daftarPoli->periksas()->exists()) {
                return ['error' => 'Pasien ini sudah selesai diperiksa.'];
            }

            $pasienDipanggil = $jadwal->daftarPolis()
                ->where('status_antrian', 'dipanggil')
                ->where('id', '!=', $daftarPoli->id)
                ->exists();

            if ($pasienDipanggil) {
                return ['error' => 'Selesaikan pemeriksaan pasien yang sedang dipanggil terlebih dahulu.'];
            }

            $daftarPoli->update(['status_antrian' => 'dipanggil']);

            return [
                'poli_id' => (int) $jadwal->dokter->id_poli,
                'no_antrian' => (int) $daftarPoli->no_antrian,
            ];
        });

        if (isset($result['error'])) {
            return redirect()->route('periksa-pasien.index')->with('error', $result['error']);
        }

        if ($result['poli_id'] > 0) {
            QueueUpdated::dispatch($result['poli_id'], $result['no_antrian']);
        }

        return redirect()->route('periksa-pasien.index')->with('success', 'Nomor antrian berhasil dipanggil.');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id_daftar_poli' => 'required|integer|exists:daftar_poli,id',
            'obat_ids' => 'nullable|array',
            'obat_ids.*' => 'required|integer|distinct|exists:obat,id',
            'catatan' => 'nullable|string|max:5000',
            'biaya_periksa' => 'required|integer|min:0',
        ]);

        $result = DB::transaction(function () use ($validated) {
            $daftarPoli = $this->findDoctorsRegistration((int) $validated['id_daftar_poli'], true);
            $jadwal = $daftarPoli->jadwalPeriksa()->lockForUpdate()->firstOrFail();

            if ($daftarPoli->periksas()->exists()) {
                return ['error' => 'Pasien ini sudah selesai diperiksa.'];
            }

            if ($daftarPoli->status_antrian !== 'dipanggil') {
                return ['error' => 'Panggil pasien dari daftar antrian sebelum menyimpan pemeriksaan.'];
            }

            $obatIds = $validated['obat_ids'] ?? [];
            $biayaObat = $obatIds
                ? Obat::whereIn('id', $obatIds)->sum('harga')
                : 0;

            $periksa = Periksa::create([
                'id_daftar_poli' => $daftarPoli->id,
                'tgl_periksa' => now(),
                'catatan' => $validated['catatan'] ?? null,
                'biaya_periksa' => self::BIAYA_JASA_DOKTER + $validated['biaya_periksa'] + $biayaObat,
            ]);

            $periksa->detailPeriksas()->createMany(
                collect($obatIds)->map(fn ($obatId) => ['id_obat' => $obatId])->all()
            );

            $daftarPoli->update(['status_antrian' => 'selesai']);

            // Cari nomor antrean pasien berikutnya yang sedang dipanggil jika ada, atau 0 jika tidak ada
            $nextServing = $jadwal->daftarPolis()
                ->where('status_antrian', 'dipanggil')
                ->value('no_antrian') ?? 0;

            return [
                'poli_id' => (int) $jadwal->dokter->id_poli,
                'no_antrian' => (int) $nextServing,
            ];
        });

        if (isset($result['error'])) {
            return redirect()
                ->route('periksa-pasien.index')
                ->with('error', $result['error']);
        }

        if ($result['poli_id'] > 0) {
            QueueUpdated::dispatch($result['poli_id'], $result['no_antrian']);
        }

        return redirect()
            ->route('periksa-pasien.index')
            ->with('success', 'Data pemeriksaan berhasil disimpan.');
    }

    private function findDoctorsRegistration(int $id, bool $lock = false): DaftarPoli
    {
        $query = DaftarPoli::whereHas('jadwalPeriksa', function ($query) {
            $query->where('id_dokter', Auth::id());
        });

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->findOrFail($id);
    }
}