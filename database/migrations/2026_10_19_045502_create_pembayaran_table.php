<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_periksa')->unique()->constrained('periksa')->cascadeOnDelete();
            $table->foreignId('id_verifikator')->nullable()->constrained('users')->nullOnDelete();
            $table->string('bukti_path');
            $table->string('status')->default('menunggu_verifikasi')->index();
            $table->timestamp('waktu_upload')->nullable();
            $table->timestamp('waktu_verifikasi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};
