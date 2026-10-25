<?php

namespace Tests\Feature;

use App\Events\QueueUpdated;
use App\Models\DaftarPoli;
use App\Models\JadwalPeriksa;
use App\Models\Obat;
use App\Models\Pembayaran;
use App\Models\Periksa;
use App\Models\Poli;
use App\Models\User;
use App\Notifications\PaymentVerifiedNotification;
use Illuminate\Broadcasting\BroadcastEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FastTrackFeaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_dashboard_updates_queue_and_blocks_multiple_active_registrations(): void
    {
        $poli = $this->makePoli();
        $doctor = $this->makeUser('dokter', 'dokter@example.com', $poli);
        $patient = $this->makeUser('pasien', 'pasien@example.com');
        $schedule = $this->makeSchedule($doctor);
        $otherSchedule = $this->makeSchedule($doctor, 'Selasa');

        $this->actingAs($patient)
            ->get(route('pasien.dashboard'))
            ->assertOk()
            ->assertSee('Jadwal dan Antrian Poliklinik')
            ->assertDontSee('Antrian pemeriksaan Anda');

        $this->post(route('pasien.daftar.submit'), [
            'id_jadwal' => $schedule->id,
            'keluhan' => 'Demam',
        ])->assertRedirect(route('pasien.daftar'))
            ->assertSessionHas('success');

        $registration = DaftarPoli::firstOrFail();
        $this->post(route('pasien.daftar.submit'), [
            'id_jadwal' => $otherSchedule->id,
            'keluhan' => 'Batuk',
        ])->assertSessionHasErrors('id_jadwal');
        $this->assertDatabaseCount('daftar_poli', 1);

        $this->get(route('pasien.dashboard'))
            ->assertOk()
            ->assertSee('Antrian pemeriksaan Anda')
            ->assertSee('Nomor Anda');

        $this->get(route('pasien.antrian.data'))
            ->assertOk()
            ->assertJsonPath('queues.0.current_number', null);

        Queue::fake();
        $this->actingAs($doctor)
            ->patch(route('periksa-pasien.call', $registration))
            ->assertRedirect(route('periksa-pasien.index'));
        Queue::assertPushed(
            BroadcastEvent::class,
            fn (BroadcastEvent $job) => $job->event instanceof QueueUpdated
                && $job->event->poliId === $poli->id
        );

        $this->actingAs($patient)
            ->get(route('pasien.antrian.data'))
            ->assertJsonFragment([
                'schedule_id' => $schedule->id,
                'current_number' => 1,
            ]);

        $this->actingAs($doctor)
            ->post(route('periksa-pasien.store'), [
                'id_daftar_poli' => $registration->id,
                'biaya_periksa' => 0,
            ])
            ->assertRedirect(route('periksa-pasien.index'))
            ->assertSessionHas('success');

        $this->actingAs($patient)
            ->post(route('pasien.daftar.submit'), [
                'id_jadwal' => $otherSchedule->id,
                'keluhan' => 'Batuk',
            ])
            ->assertRedirect(route('pasien.daftar'))
            ->assertSessionHas('success');
        $this->assertDatabaseCount('daftar_poli', 2);
    }

    public function test_patient_registration_history_is_private_and_shows_completed_exam_details(): void
    {
        $poli = $this->makePoli();
        $doctor = $this->makeUser('dokter', 'dokter@example.com', $poli);
        $patient = $this->makeUser('pasien', 'pasien@example.com');
        $otherPatient = $this->makeUser('pasien', 'other@example.com');
        $registration = $this->makeRegistration($patient, $this->makeSchedule($doctor));
        $exam = Periksa::create([
            'id_daftar_poli' => $registration->id,
            'tgl_periksa' => now(),
            'catatan' => 'Istirahat cukup.',
            'biaya_periksa' => 150000,
        ]);
        $registration->update(['status_antrian' => 'selesai']);

        $this->actingAs($patient)
            ->get(route('pasien.riwayat.index'))
            ->assertOk()
            ->assertSee('Poli Umum')
            ->assertSee('Selesai diperiksa');

        $this->get(route('pasien.riwayat.show', $exam))
            ->assertOk()
            ->assertSee('Istirahat cukup.')
            ->assertSee('150.000');

        $this->actingAs($otherPatient)
            ->get(route('pasien.riwayat.show', $exam))
            ->assertNotFound();
    }

    public function test_payment_upload_verification_and_notifications_are_scoped_and_not_duplicated(): void
    {
        Storage::fake('local');
        $poli = $this->makePoli();
        $doctor = $this->makeUser('dokter', 'dokter@example.com', $poli);
        $patient = $this->makeUser('pasien', 'pasien@example.com');
        $admin = $this->makeUser('admin', 'admin@example.com');
        $registration = $this->makeRegistration($patient, $this->makeSchedule($doctor));
        $exam = Periksa::create([
            'id_daftar_poli' => $registration->id,
            'tgl_periksa' => now(),
            'catatan' => 'Perlu kontrol.',
            'biaya_periksa' => 150000,
        ]);
        $proof = UploadedFile::fake()->createWithContent(
            'bukti.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/jXcAAAAASUVORK5CYII=')
        );

        $this->actingAs($patient)
            ->post(route('pasien.pembayaran.store'), ['id_periksa' => $exam->id, 'bukti' => $proof])
            ->assertRedirect(route('pasien.pembayaran.index'))
            ->assertSessionHas('success');

        $pembayaran = Pembayaran::firstOrFail();
        $this->assertSame(Pembayaran::STATUS_MENUNGGU, $pembayaran->status);
        Storage::disk('local')->assertExists($pembayaran->bukti_path);
        $this->assertDatabaseCount('notifications', 1);
        $this->assertSame(1, $doctor->notifications()->count());

        $this->actingAs($patient)
            ->get(route('admin.pembayaran.bukti', $pembayaran))
            ->assertForbidden();
        $this->actingAs($admin)
            ->get(route('admin.pembayaran.bukti', $pembayaran))
            ->assertOk();
        $this->patch(route('admin.pembayaran.verify', $pembayaran))
            ->assertRedirect(route('admin.pembayaran.index'))
            ->assertSessionHas('success');

        $pembayaran->refresh();
        $this->assertSame(Pembayaran::STATUS_LUNAS, $pembayaran->status);
        $this->assertSame(1, $patient->notifications()->count());

        $this->patch(route('admin.pembayaran.verify', $pembayaran))
            ->assertSessionHas('error');
        $this->assertSame(1, $patient->notifications()->count());
    }

    public function test_users_only_read_their_own_notifications_and_can_change_their_own_password(): void
    {
        $patient = $this->makeUser('pasien', 'pasien@example.com');
        $otherPatient = $this->makeUser('pasien', 'other@example.com');
        $poli = $this->makePoli();
        $doctor = $this->makeUser('dokter', 'dokter@example.com', $poli);
        $registration = $this->makeRegistration($patient, $this->makeSchedule($doctor));
        $exam = Periksa::create([
            'id_daftar_poli' => $registration->id,
            'tgl_periksa' => now(),
            'biaya_periksa' => 150000,
        ]);
        $pembayaran = Pembayaran::create([
            'id_periksa' => $exam->id,
            'bukti_path' => 'bukti-pembayaran/example.png',
            'status' => Pembayaran::STATUS_LUNAS,
        ]);
        $patient->notify(new PaymentVerifiedNotification($pembayaran));
        $notification = $patient->notifications()->firstOrFail();

        $this->actingAs($otherPatient)
            ->patch(route('notifications.read', $notification->id))
            ->assertNotFound();
        $this->assertNull($notification->fresh()->read_at);

        $this->actingAs($patient)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('telah diverifikasi');
        $this->patch(route('notifications.read', $notification->id))->assertRedirect();
        $this->assertNotNull($notification->fresh()->read_at);

        $this->put(route('profile.password.update'), [
            'password_lama' => 'wrong-password',
            'password_baru' => 'new-password-123',
            'password_baru_confirmation' => 'new-password-123',
        ])->assertSessionHasErrors('password_lama');
        $this->put(route('profile.password.update'), [
            'password_lama' => 'password',
            'password_baru' => 'password',
            'password_baru_confirmation' => 'password',
        ])->assertSessionHasErrors('password_baru');
        $this->put(route('profile.password.update'), [
            'password_lama' => 'password',
            'password_baru' => 'short',
            'password_baru_confirmation' => 'short',
        ])->assertSessionHasErrors('password_baru');

        $this->put(route('profile.password.update'), [
            'password_lama' => 'password',
            'password_baru' => 'new-password-123',
            'password_baru_confirmation' => 'new-password-123',
        ])->assertRedirect(route('profile.password.edit'))
            ->assertSessionHas('success');
        $this->assertTrue(password_verify('new-password-123', $patient->fresh()->password));

        $this->actingAs($this->makeUser('admin', 'profile-admin@example.com'))
            ->get(route('profile.password.edit'))
            ->assertOk();
        $this->actingAs($this->makeUser('dokter', 'profile-doctor@example.com', $this->makePoli()))
            ->get(route('profile.password.edit'))
            ->assertOk();
    }

    public function test_admin_and_doctor_exports_include_only_their_role_data(): void
    {
        $poli = $this->makePoli();
        $doctor = $this->makeUser('dokter', 'dokter@example.com', $poli);
        $patient = $this->makeUser('pasien', 'pasien@example.com');
        $admin = $this->makeUser('admin', 'admin@example.com');
        $schedule = $this->makeSchedule($doctor);
        $this->makeSchedule($doctor, 'Selasa');
        $this->makeRegistration($patient, $schedule);
        Obat::create(['nama_obat' => 'Obat & Aman', 'kemasan' => 'Tablet', 'harga' => 5000, 'stok' => 12]);

        $this->actingAs($admin)
            ->get(route('admin.export.dokter'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
            ->assertSee('Nama Dokter')
            ->assertSee('Poli Umum');

        $this->get(route('admin.export.pasien'))->assertOk()->assertSee('No. Rekam Medis');
        $this->get(route('admin.export.obat'))->assertOk()->assertSee('Stok');
        $this->get(route('dokter.export.jadwal'))->assertForbidden();

        $this->actingAs($doctor)
            ->get(route('dokter.export.jadwal'))
            ->assertOk()
            ->assertSee('Hari')
            ->assertSee('Selasa');
        $this->get(route('dokter.export.riwayat'))->assertOk()->assertSee('Keluhan');
        $this->get(route('admin.export.obat'))->assertForbidden();
    }

    private function makePoli(): Poli
    {
        return Poli::create(['nama_poli' => 'Poli Umum', 'keterangan' => 'Pemeriksaan umum']);
    }

    private function makeUser(string $role, string $email, ?Poli $poli = null): User
    {
        return User::create([
            'nama' => ucfirst($role === 'dokter' ? 'Dokter' : $role),
            'email' => $email,
            'password' => 'password',
            'role' => $role,
            'id_poli' => $poli?->id,
            'no_rm' => $role === 'pasien' ? 'RM-'.$email : null,
        ]);
    }

    private function makeSchedule(User $doctor, string $day = 'Senin'): JadwalPeriksa
    {
        static $counter = 0;
        $counter++;

        return JadwalPeriksa::create([
            'id_dokter' => $doctor->id,
            'hari' => $day,
            'jam_mulai' => sprintf('%02d:00', 8 + $counter),
            'jam_selesai' => sprintf('%02d:00', 12 + $counter),
            'status' => true,
        ]);
    }

    private function makeRegistration(User $patient, JadwalPeriksa $schedule): DaftarPoli
    {
        return DaftarPoli::create([
            'id_pasien' => $patient->id,
            'id_jadwal' => $schedule->id,
            'keluhan' => 'Keluhan pasien',
            'no_antrian' => 1,
        ]);
    }
}
