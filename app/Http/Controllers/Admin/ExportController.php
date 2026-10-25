<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ExcelSpreadsheet;
use App\Http\Controllers\Controller;
use App\Models\Obat;
use App\Models\User;
use Illuminate\Http\Response;

class ExportController extends Controller
{
    public function doctors(): Response
    {
        $rows = User::where('role', 'dokter')->with('poli')->orderBy('nama')->get()
            ->map(fn (User $dokter) => [
                $dokter->nama,
                $dokter->email,
                $dokter->no_ktp,
                $dokter->no_hp,
                $dokter->alamat,
                $dokter->poli?->nama_poli,
            ]);

        return ExcelSpreadsheet::download(
            'data-dokter.xls',
            ['Nama Dokter', 'Email', 'No. KTP', 'No. HP', 'Alamat', 'Poli'],
            $rows
        );
    }

    public function patients(): Response
    {
        $rows = User::where('role', 'pasien')->orderBy('nama')->get()
            ->map(fn (User $pasien) => [
                $pasien->nama,
                $pasien->email,
                $pasien->no_ktp,
                $pasien->no_hp,
                $pasien->alamat,
                $pasien->no_rm,
            ]);

        return ExcelSpreadsheet::download(
            'data-pasien.xls',
            ['Nama Pasien', 'Email', 'No. KTP', 'No. HP', 'Alamat', 'No. Rekam Medis'],
            $rows
        );
    }

    public function medicines(): Response
    {
        $rows = Obat::orderBy('nama_obat')->get()
            ->map(fn (Obat $obat) => [$obat->nama_obat, $obat->kemasan, $obat->stok, $obat->harga]);

        return ExcelSpreadsheet::download(
            'data-obat.xls',
            ['Nama Obat', 'Kemasan', 'Stok', 'Harga'],
            $rows
        );
    }
}
