<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pembayaran extends Model
{
    public const STATUS_MENUNGGU = 'menunggu_verifikasi';

    public const STATUS_LUNAS = 'lunas';

    protected $table = 'pembayaran';

    protected $fillable = [
        'id_periksa',
        'id_verifikator',
        'bukti_path',
        'status',
        'waktu_upload',
        'waktu_verifikasi',
    ];

    protected function casts(): array
    {
        return [
            'waktu_upload' => 'datetime',
            'waktu_verifikasi' => 'datetime',
        ];
    }

    public function periksa(): BelongsTo
    {
        return $this->belongsTo(Periksa::class, 'id_periksa');
    }

    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_verifikator');
    }
}
