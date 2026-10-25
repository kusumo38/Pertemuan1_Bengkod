<?php

namespace App\Http\Controllers\Pasien;

use App\Events\QueueUpdated;
use App\Http\Controllers\Controller;
use App\Models\DaftarPoli;
use App\Models\JadwalPeriksa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PoliController extends Controller
{
    public function get()
    {
        $jadwals = JadwalPeriksa::with(['dokter.poli'])
            ->where('status', true)
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get();

        return view('pasien.daftar', compact('jadwals'));
    }

    public function submit(Request $request)
    {
        $validated = $request->validate([
            'id_jadwal' => 'required|integer|exists:jadwal_periksa,id',
            'keluhan' => 'required|string|max:2000',
        ]);

        $poliId = DB::transaction(function () use ($validated) {
            $patient = User::whereKey(Auth::id())->lockForUpdate()->firstOrFail();

            if ($patient->daftarPolis()->where('status_antrian', '!=', 'selesai')->exists()) {
                throw ValidationException::withMessages([
                    'id_jadwal' => 'Selesaikan pemeriksaan yang sedang berjalan sebelum mendaftar lagi.',
                ]);
            }

            $jadwal = JadwalPeriksa::where('status', true)
                ->lockForUpdate()
                ->findOrFail($validated['id_jadwal']);
            $jadwal->load('dokter');

            if (! $jadwal->dokter->id_poli) {
                throw ValidationException::withMessages([
                    'id_jadwal' => 'Jadwal ini belum terhubung dengan poli.',
                ]);
            }

            $nomorAntrian = DaftarPoli::where('id_jadwal', $jadwal->id)
                ->max('no_antrian') + 1;

            $jadwal->daftarPolis()->create([
                'id_pasien' => Auth::id(),
                'keluhan' => $validated['keluhan'],
                'no_antrian' => $nomorAntrian,
            ]);

            return (int) $jadwal->dokter->id_poli;
        });

        QueueUpdated::dispatch($poliId, 0);

        return redirect()
            ->route('pasien.daftar')
            ->with('success', 'Pendaftaran pemeriksaan berhasil dibuat.');
    }
}
