<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use App\Models\JadwalPeriksa;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $jadwals = $this->jadwalQuery()->get();
        $pendaftaranAktif = Auth::user()->daftarPolis()
            ->with([
                'jadwalPeriksa.dokter.poli',
                'jadwalPeriksa.daftarPolis' => fn ($query) => $query
                    ->where('status_antrian', 'dipanggil')
                    ->orderBy('no_antrian'),
            ])
            ->where('status_antrian', '!=', 'selesai')
            ->latest()
            ->first();

        return view('pasien.dashboard', compact('jadwals', 'pendaftaranAktif'));
    }

    public function queueData(): JsonResponse
    {
        return response()->json([
            'queues' => $this->jadwalQuery()->get()->map(fn (JadwalPeriksa $jadwal) => [
                'schedule_id' => $jadwal->id,
                'current_number' => $jadwal->daftarPolis->first()?->no_antrian,
            ]),
        ]);
    }

    private function jadwalQuery()
    {
        return JadwalPeriksa::with([
            'dokter:id,nama,id_poli',
            'dokter.poli:id,nama_poli',
            'daftarPolis' => fn ($query) => $query
                ->where('status_antrian', 'dipanggil')
                ->orderBy('no_antrian')
                ->select(['id', 'id_jadwal', 'no_antrian']),
        ])
            ->where('status', true)
            ->orderBy('hari')
            ->orderBy('jam_mulai');
    }
}
