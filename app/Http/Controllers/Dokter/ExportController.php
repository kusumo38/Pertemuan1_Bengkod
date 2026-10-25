<?php

namespace App\Http\Controllers\Dokter;

use App\Exports\ExcelSpreadsheet;
use App\Http\Controllers\Controller;
use App\Models\JadwalPeriksa;
use App\Models\Periksa;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class ExportController extends Controller
{
    public function schedules(): Response
    {
        $rows = JadwalPeriksa::where('id_dokter', Auth::id())
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get()
            ->map(fn (JadwalPeriksa $jadwal) => [
                $jadwal->hari,
                substr($jadwal->jam_mulai, 0, 5),
                substr($jadwal->jam_selesai, 0, 5),
                $jadwal->status ? 'Aktif' : 'Nonaktif',
            ]);

        return ExcelSpreadsheet::download(
            'jadwal-periksa.xls',
            ['Hari', 'Jam Mulai', 'Jam Selesai', 'Status'],
            $rows
        );
    }

    public function patientHistory(): Response
    {
        $rows = Periksa::whereHas('daftarPoli.jadwalPeriksa', fn ($query) => $query->where('id_dokter', Auth::id()))
            ->with(['daftarPoli.pasien', 'daftarPoli.jadwalPeriksa.dokter.poli'])
            ->latest('tgl_periksa')
            ->get()
            ->map(fn (Periksa $periksa) => [
                $periksa->daftarPoli->no_antrian,
                $periksa->daftarPoli->pasien->nama,
                $periksa->daftarPoli->keluhan,
                $periksa->daftarPoli->jadwalPeriksa->dokter->poli?->nama_poli,
                $periksa->tgl_periksa->format('Y-m-d H:i:s'),
                $periksa->biaya_periksa,
            ]);

        return ExcelSpreadsheet::download(
            'riwayat-pasien.xls',
            ['No. Antrian', 'Nama Pasien', 'Keluhan', 'Poli', 'Tanggal Periksa', 'Total Biaya'],
            $rows
        );
    }
}
