<?php

use App\Http\Controllers\Admin\AdminPoliController;
use App\Http\Controllers\Admin\DokterController;
use App\Http\Controllers\Admin\ExportController as AdminExportController;
use App\Http\Controllers\Admin\ObatController;
use App\Http\Controllers\Admin\PasienController;
use App\Http\Controllers\Admin\PembayaranController as AdminPembayaranController;
use App\Http\Controllers\Admin\QueueDashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Dokter\ExportController as DokterExportController;
use App\Http\Controllers\Dokter\JadwalPeriksaController;
use App\Http\Controllers\Dokter\PeriksaPasienController;
use App\Http\Controllers\Dokter\RiwayatPasienController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Pasien\DashboardController as PasienDashboardController;
use App\Http\Controllers\Pasien\PembayaranController as PasienPembayaranController;
use App\Http\Controllers\Pasien\PoliController;
use App\Http\Controllers\Pasien\RiwayatPendaftaranController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister']);
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/notifikasi', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifikasi/{id}/baca', [NotificationController::class, 'markAsRead'])
        ->name('notifications.read');

    Route::get('/profile/password', [ProfileController::class, 'editPassword'])
        ->name('profile.password.edit');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])
        ->name('profile.password.update');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [QueueDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/dashboard/antrian/data', [QueueDashboardController::class, 'queueData'])
        ->name('admin.antrian.data');

    Route::resource('polis', AdminPoliController::class);
    Route::resource('dokter', DokterController::class);
    Route::resource('pasien', PasienController::class);
    Route::resource('obat', ObatController::class)->except(['show']);

    Route::get('/pembayaran', [AdminPembayaranController::class, 'index'])->name('admin.pembayaran.index');
    Route::get('/pembayaran/{pembayaran}/bukti', [AdminPembayaranController::class, 'proof'])
        ->name('admin.pembayaran.bukti');
    Route::patch('/pembayaran/{pembayaran}/verifikasi', [AdminPembayaranController::class, 'verify'])
        ->name('admin.pembayaran.verify');

    Route::get('/export/dokter', [AdminExportController::class, 'doctors'])->name('admin.export.dokter');
    Route::get('/export/pasien', [AdminExportController::class, 'patients'])->name('admin.export.pasien');
    Route::get('/export/obat', [AdminExportController::class, 'medicines'])->name('admin.export.obat');
});

Route::middleware(['auth', 'role:dokter'])->prefix('dokter')->group(function () {
    Route::get('/dashboard', function () {
        return view('dokter.dashboard');
    })->name('dokter.dashboard');

    Route::resource('jadwal-periksa', JadwalPeriksaController::class)
        ->except(['show'])
        ->parameters(['jadwal-periksa' => 'jadwalPeriksa']);

    Route::get('/periksa-pasien', [PeriksaPasienController::class, 'index'])
        ->name('periksa-pasien.index');
    Route::get('/periksa-pasien/antrian/data', [PeriksaPasienController::class, 'queueData'])
        ->name('periksa-pasien.queue-data');
    Route::patch('/periksa-pasien/{id}/panggil', [PeriksaPasienController::class, 'call'])
        ->name('periksa-pasien.call');
    Route::get('/periksa-pasien/{id}', [PeriksaPasienController::class, 'create'])
        ->name('periksa-pasien.create');
    Route::post('/periksa-pasien', [PeriksaPasienController::class, 'store'])
        ->name('periksa-pasien.store');

    Route::get('/riwayat-pasien', [RiwayatPasienController::class, 'index'])
        ->name('riwayat-pasien.index');
    Route::get('/riwayat-pasien/{id}', [RiwayatPasienController::class, 'show'])
        ->name('riwayat-pasien.show');

    Route::get('/export/jadwal-periksa', [DokterExportController::class, 'schedules'])
        ->name('dokter.export.jadwal');
    Route::get('/export/riwayat-pasien', [DokterExportController::class, 'patientHistory'])
        ->name('dokter.export.riwayat');
});

Route::middleware(['auth', 'role:pasien'])->prefix('pasien')->group(function () {
    Route::get('/dashboard', [PasienDashboardController::class, 'index'])->name('pasien.dashboard');
    Route::get('/antrian/data', [PasienDashboardController::class, 'queueData'])->name('pasien.antrian.data');

    Route::get('/daftar', [PoliController::class, 'get'])->name('pasien.daftar');
    Route::post('/daftar', [PoliController::class, 'submit'])->name('pasien.daftar.submit');

    Route::get('/riwayat-pendaftaran', [RiwayatPendaftaranController::class, 'index'])
        ->name('pasien.riwayat.index');
    Route::get('/riwayat-pendaftaran/pemeriksaan/{id}', [RiwayatPendaftaranController::class, 'show'])
        ->name('pasien.riwayat.show');

    Route::get('/pembayaran', [PasienPembayaranController::class, 'index'])
        ->name('pasien.pembayaran.index');
    Route::post('/pembayaran', [PasienPembayaranController::class, 'store'])
        ->name('pasien.pembayaran.store');
});