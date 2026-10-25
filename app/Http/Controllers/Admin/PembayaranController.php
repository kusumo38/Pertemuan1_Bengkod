<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Notifications\PaymentVerifiedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PembayaranController extends Controller
{
    public function index(): View
    {
        $pembayaran = Pembayaran::with([
            'periksa.daftarPoli.pasien',
            'periksa.daftarPoli.jadwalPeriksa.dokter.poli',
        ])
            ->latest('waktu_upload')
            ->paginate(20);

        return view('admin.pembayaran.index', compact('pembayaran'));
    }

    public function proof(Pembayaran $pembayaran): BinaryFileResponse
    {
        abort_unless(Storage::disk('local')->exists($pembayaran->bukti_path), 404);

        return response()->file(Storage::disk('local')->path($pembayaran->bukti_path));
    }

    public function verify(Pembayaran $pembayaran): RedirectResponse
    {
        $result = DB::transaction(function () use ($pembayaran) {
            $pembayaran = Pembayaran::whereKey($pembayaran->id)->lockForUpdate()->firstOrFail();

            if ($pembayaran->status === Pembayaran::STATUS_LUNAS) {
                return false;
            }

            $pembayaran->update([
                'status' => Pembayaran::STATUS_LUNAS,
                'id_verifikator' => auth()->id(),
                'waktu_verifikasi' => now(),
            ]);

            $pembayaran->periksa->daftarPoli->pasien
                ->notify(new PaymentVerifiedNotification($pembayaran));

            return true;
        });

        if (! $result) {
            return back()->with('error', 'Pembayaran ini sudah pernah diverifikasi.');
        }

        return redirect()->route('admin.pembayaran.index')->with('success', 'Pembayaran berhasil diverifikasi.');
    }
}
