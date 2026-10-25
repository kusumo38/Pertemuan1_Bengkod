<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\Periksa;
use App\Notifications\PaymentSubmittedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;

class PembayaranController extends Controller
{
    public function index(): View
    {
        $tagihan = Periksa::whereHas('daftarPoli', fn ($query) => $query->where('id_pasien', Auth::id()))
            ->with(['daftarPoli.jadwalPeriksa.dokter.poli', 'pembayaran'])
            ->latest('tgl_periksa')
            ->paginate(15);

        return view('pasien.pembayaran.index', compact('tagihan'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id_periksa' => 'required|integer|exists:periksa,id',
            'bukti' => 'required|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $periksa = Periksa::whereHas('daftarPoli', fn ($query) => $query->where('id_pasien', Auth::id()))
            ->with('daftarPoli.jadwalPeriksa.dokter')
            ->findOrFail($validated['id_periksa']);

        $path = $request->file('bukti')->store('bukti-pembayaran', 'local');

        if (! $path) {
            throw new RuntimeException('Bukti pembayaran gagal disimpan.');
        }

        $result = DB::transaction(function () use ($periksa, $path) {
            Periksa::whereKey($periksa->id)->lockForUpdate()->firstOrFail();
            $pembayaran = Pembayaran::where('id_periksa', $periksa->id)->first();

            if ($pembayaran?->status === Pembayaran::STATUS_LUNAS) {
                return ['lunas' => true];
            }

            $baruDibuat = ! $pembayaran;
            $pathLama = $pembayaran?->bukti_path;
            $pembayaran ??= new Pembayaran(['id_periksa' => $periksa->id]);
            $pembayaran->bukti_path = $path;
            $pembayaran->status = Pembayaran::STATUS_MENUNGGU;
            $pembayaran->waktu_upload = now();
            $pembayaran->save();

            if ($baruDibuat) {
                $periksa->daftarPoli->jadwalPeriksa->dokter
                    ->notify(new PaymentSubmittedNotification($pembayaran));
            }

            return ['lunas' => false, 'path_lama' => $pathLama];
        });

        if ($result['lunas']) {
            Storage::disk('local')->delete($path);

            return back()->with('error', 'Tagihan ini sudah lunas.');
        }

        if ($result['path_lama']) {
            Storage::disk('local')->delete($result['path_lama']);
        }

        return redirect()->route('pasien.pembayaran.index')->with('success', 'Bukti pembayaran berhasil diunggah.');
    }
}
