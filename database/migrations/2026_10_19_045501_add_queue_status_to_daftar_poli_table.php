<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daftar_poli', function (Blueprint $table) {
            $table->string('status_antrian')->default('menunggu')->index();
        });

        DB::table('daftar_poli')
            ->whereIn('id', DB::table('periksa')->select('id_daftar_poli'))
            ->update(['status_antrian' => 'selesai']);
    }

    public function down(): void
    {
        Schema::table('daftar_poli', function (Blueprint $table) {
            $table->dropIndex(['status_antrian']);
            $table->dropColumn('status_antrian');
        });
    }
};
