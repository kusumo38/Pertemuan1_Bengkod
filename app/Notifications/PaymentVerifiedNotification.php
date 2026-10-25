<?php

namespace App\Notifications;

use App\Models\Pembayaran;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PaymentVerifiedNotification extends Notification
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
            'message' => 'Pembayaran pemeriksaan Anda telah diverifikasi dan dinyatakan lunas.',
            'url' => route('pasien.pembayaran.index'),
            'pembayaran_id' => $this->pembayaran->id,
        ];
    }
}
