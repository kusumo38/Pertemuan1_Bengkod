<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DaftarPoli;
use App\Models\JadwalPeriksa;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class QueueDashboardController extends Controller
{
    public function index(): View
    {
        $jadwals = JadwalPeriksa::with('dokter:id,id_poli')
            ->where('status', true)
            ->get();
        $poliIds = $jadwals
            ->pluck('dokter.id_poli')
            ->filter()
            ->unique()
            ->values();
        $daftarPoli = $this->queueQuery()->get();

        return view('admin.dashboard', compact('daftarPoli', 'poliIds'));
    }

    public function queueData(): JsonResponse
    {
        $daftarPoli = $this->queueQuery()->get();

        return response()->json([
            'html' => view('admin.partials.queue-rows', compact('daftarPoli'))->render(),
        ]);
    }

    private function queueQuery()
    {
        return DaftarPoli::with([
            'pasien:id,nama,no_rm',
            'jadwalPeriksa:id,id_dokter,hari,jam_mulai,jam_selesai',
            'jadwalPeriksa.dokter:id,nama,id_poli',
            'jadwalPeriksa.dokter.poli:id,nama_poli',
        ])
            ->whereHas('jadwalPeriksa', fn ($query) => $query->where('status', true))
            ->orderBy('id_jadwal')
            ->orderBy('no_antrian');
    }
}