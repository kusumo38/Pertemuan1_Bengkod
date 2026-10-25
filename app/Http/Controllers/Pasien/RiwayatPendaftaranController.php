<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use App\Models\Periksa;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RiwayatPendaftaranController extends Controller
{
    public function index(): View
    {
        $pendaftaran = Auth::user()->daftarPolis()
            ->with([
                'jadwalPeriksa.dokter.poli',
                'periksas.pembayaran',
            ])
            ->latest()
            ->paginate(15);

        return view('pasien.riwayat.index', compact('pendaftaran'));
    }

    public function show(int $id): View
    {
        $periksa = Periksa::whereHas('daftarPoli', function ($query) {
            $query->where('id_pasien', Auth::id());
        })
            ->with([
                'daftarPoli.jadwalPeriksa.dokter.poli',
                'detailPeriksas.obat',
                'pembayaran',
            ])
            ->findOrFail($id);

        return view('pasien.riwayat.show', compact('periksa'));
    }
}
