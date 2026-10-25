<?php

namespace App\Notifications;

use App\Models\Pembayaran;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PaymentSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(public Pembayaran $pembayaran) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'message' => 'Bukti pembayaran pasien '.$this->pembayaran->periksa->daftarPoli->pasien->nama.' menunggu verifikasi.',
            'url' => route('admin.pembayaran.index'),
            'pembayaran_id' => $this->pembayaran->id,
        ];
    }
}
