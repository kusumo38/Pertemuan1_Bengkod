<?php

namespace Tests\Feature;

use App\Models\DaftarPoli;
use App\Models\JadwalPeriksa;
use App\Models\Obat;
use App\Models\Periksa;
use App\Models\Poli;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeriksaPasienTest extends TestCase
{
    use RefreshDatabase;

    public function test_doctor_can_examine_only_their_registered_patient_and_save_the_result(): void
    {
        $poli = Poli::create(['nama_poli' => 'Poli Umum', 'keterangan' => 'Pemeriksaan umum']);
        $doctor = $this->makeUser('dokter', 'dokter@example.com', $poli->id);
        $otherDoctor = $this->makeUser('dokter', 'dokter-lain@example.com', $poli->id);
        $patient = $this->makeUser('pasien', 'pasien@example.com');
        $schedule = $this->makeSchedule($doctor);
        $otherSchedule = $this->makeSchedule($otherDoctor);
        $registration = $this->makeRegistration($patient, $schedule);
        $otherRegistration = $this->makeRegistration($patient, $otherSchedule, 2);
        $obat = Obat::create([
            'nama_obat' => 'Paracetamol',
            'kemasan' => 'Tablet 500 mg',
            'harga' => 5000,
        ]);

        $this->actingAs($doctor)
            ->get(route('periksa-pasien.index'))
            ->assertOk()
            ->assertSee('Pasien untuk Diperiksa')
            ->assertSee('Keluhan utama');

        $this->get(route('periksa-pasien.create', $registration))
            ->assertRedirect(route('periksa-pasien.index'))
            ->assertSessionHas('error');
        $this->patch(route('periksa-pasien.call', $registration))
            ->assertRedirect(route('periksa-pasien.index'))
            ->assertSessionHas('success');

        $this->get(route('periksa-pasien.create', $registration))
            ->assertOk()
            ->assertSee('Nama pasien')
            ->assertSee('Paracetamol')
            ->assertSee('Keluhan utama');

        $this->get(route('periksa-pasien.create', $otherRegistration))->assertNotFound();

        $this->post(route('periksa-pasien.store'), [
            'id_daftar_poli' => $registration->id,
            'obat_ids' => [$obat->id],
            'catatan' => 'Istirahat dan minum obat.',
            'biaya_periksa' => 20000,
        ])->assertRedirect(route('periksa-pasien.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('periksa', [
            'id_daftar_poli' => $registration->id,
            'catatan' => 'Istirahat dan minum obat.',
            'biaya_periksa' => 175000,
        ]);

        $this->assertDatabaseHas('detail_periksa', [
            'id_periksa' => Periksa::firstOrFail()->id,
            'id_obat' => $obat->id,
        ]);

        $this->get(route('periksa-pasien.index'))
            ->assertOk()
            ->assertSee('Sudah diperiksa')
            ->assertSee('Lihat hasil');

        $this->get(route('periksa-pasien.create', $registration))
            ->assertRedirect(route('periksa-pasien.index'))
            ->assertSessionHas('error');

        $this->get(route('riwayat-pasien.index'))
            ->assertOk()
            ->assertSee('Pasien');
        $this->get(route('riwayat-pasien.show', Periksa::firstOrFail()))
            ->assertOk()
            ->assertSee('Istirahat dan minum obat.');
    }

    public function test_patient_can_register_for_an_active_schedule_without_selecting_another_patient(): void
    {
        $poli = Poli::create(['nama_poli' => 'Poli Umum', 'keterangan' => 'Pemeriksaan umum']);
        $doctor = $this->makeUser('dokter', 'dokter@example.com', $poli->id);
        $patient = $this->makeUser('pasien', 'pasien@example.com');
        $schedule = $this->makeSchedule($doctor);

        $this->actingAs($patient)
            ->get(route('pasien.daftar'))
            ->assertOk()
            ->assertSee('Pendaftaran Pemeriksaan')
            ->assertSee('Dr. Dokter');

        $this->post(route('pasien.daftar.submit'), [
            'id_jadwal' => $schedule->id,
            'keluhan' => 'Demam sejak kemarin.',
            'id_pasien' => $this->makeUser('pasien', 'pasien-lain@example.com')->id,
        ])->assertRedirect(route('pasien.daftar'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('daftar_poli', [
            'id_pasien' => $patient->id,
            'id_jadwal' => $schedule->id,
            'keluhan' => 'Demam sejak kemarin.',
            'no_antrian' => 1,
        ]);
    }

    public function test_doctor_cannot_submit_invalid_medicines_or_negative_fees(): void
    {
        $doctor = $this->makeUser('dokter', 'dokter@example.com');
        $patient = $this->makeUser('pasien', 'pasien@example.com');
        $registration = $this->makeRegistration($patient, $this->makeSchedule($doctor));

        $this->actingAs($doctor)
            ->from(route('periksa-pasien.create', $registration))
            ->post(route('periksa-pasien.store'), [
                'id_daftar_poli' => $registration->id,
                'obat_ids' => [99999],
                'catatan' => 'Catatan',
                'biaya_periksa' => -1,
            ])
            ->assertRedirect(route('periksa-pasien.create', $registration))
            ->assertSessionHasErrors(['obat_ids.0', 'biaya_periksa']);

        $this->assertDatabaseCount('periksa', 0);
    }

    private function makeUser(string $role, string $email, ?int $poliId = null): User
    {
        return User::create([
            'nama' => ucfirst($role === 'dokter' ? 'Dokter' : $role),
            'email' => $email,
            'password' => 'password',
            'role' => $role,
            'id_poli' => $poliId,
        ]);
    }

    private function makeSchedule(User $doctor): JadwalPeriksa
    {
        return JadwalPeriksa::create([
            'id_dokter' => $doctor->id,
            'hari' => 'Senin',
            'jam_mulai' => '09:00',
            'jam_selesai' => '12:00',
            'status' => true,
        ]);
    }

    private function makeRegistration(User $patient, JadwalPeriksa $schedule, int $queueNumber = 1): DaftarPoli
    {
        return DaftarPoli::create([
            'id_pasien' => $patient->id,
            'id_jadwal' => $schedule->id,
            'keluhan' => 'Keluhan utama',
            'no_antrian' => $queueNumber,
        ]);
    }
}
