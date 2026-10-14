<?php

namespace App\Http\Controllers\Dokter;

use App\Http\Controllers\Controller;
use App\Models\JadwalPeriksa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JadwalPeriksaController extends Controller
{
    public function index()
    {
        $jadwals = Auth::user()->jadwalPeriksas()->get();

        return view('dokter.jadwal-periksa.index', compact('jadwals'));
    }

    public function create()
    {
        return view('dokter.jadwal-periksa.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'status' => 'required|boolean',
        ]);

        Auth::user()->jadwalPeriksas()->create($validated);

        return redirect()->route('jadwal-periksa.index')->with('success', 'Jadwal periksa berhasil ditambahkan.');
    }

    public function edit(JadwalPeriksa $jadwalPeriksa)
    {
        $this->authorizeOwnership($jadwalPeriksa);

        return view('dokter.jadwal-periksa.edit', compact('jadwalPeriksa'));
    }

    public function update(Request $request, JadwalPeriksa $jadwalPeriksa)
    {
        $this->authorizeOwnership($jadwalPeriksa);

        $validated = $request->validate([
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'status' => 'required|boolean',
        ]);

        $jadwalPeriksa->update($validated);

        return redirect()->route('jadwal-periksa.index')->with('success', 'Jadwal periksa berhasil diperbarui.');
    }

    public function destroy(JadwalPeriksa $jadwalPeriksa)
    {
        $this->authorizeOwnership($jadwalPeriksa);
        $jadwalPeriksa->delete();

        return redirect()->route('jadwal-periksa.index')->with('success', 'Jadwal periksa berhasil dihapus.');
    }

    private function authorizeOwnership(JadwalPeriksa $jadwalPeriksa): void
    {
        abort_unless((int) $jadwalPeriksa->id_dokter === (int) Auth::id(), 403);
    }
}
