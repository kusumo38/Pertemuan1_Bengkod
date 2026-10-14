<?php

namespace Tests\Feature;

use App\Models\JadwalPeriksa;
use App\Models\Obat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ObatAndJadwalPeriksaTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_medicines_and_invalid_data_is_rejected(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)
            ->get(route('obat.index'))
            ->assertOk()
            ->assertSee('Manajemen Obat');
        $this->get(route('obat.create'))->assertOk()->assertSee('Tambah Obat');

        $this->post(route('obat.store'), [
            'nama_obat' => 'Paracetamol',
            'kemasan' => 'Tablet 500 mg',
            'harga' => 5000,
        ])->assertRedirect(route('obat.index'))
            ->assertSessionHas('success');

        $obat = Obat::firstOrFail();
        $this->assertDatabaseHas('obat', ['id' => $obat->id, 'nama_obat' => 'Paracetamol']);

        $this->get(route('obat.edit', $obat))->assertOk()->assertSee('Tablet 500 mg');

        $this->put(route('obat.update', $obat), [
            'nama_obat' => 'Paracetamol Forte',
            'kemasan' => 'Kaplet',
            'harga' => 7500,
        ])->assertRedirect(route('obat.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('obat', ['id' => $obat->id, 'nama_obat' => 'Paracetamol Forte', 'harga' => 7500]);

        $this->from(route('obat.create'))
            ->post(route('obat.store'), ['nama_obat' => '', 'kemasan' => '', 'harga' => -1])
            ->assertRedirect(route('obat.create'))
            ->assertSessionHasErrors(['nama_obat', 'kemasan', 'harga']);

        $this->delete(route('obat.destroy', $obat))
            ->assertRedirect(route('obat.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('obat', ['id' => $obat->id]);
    }

    public function test_doctor_can_manage_only_their_own_schedules(): void
    {
        $doctor = $this->makeUser('dokter');
        $otherDoctor = $this->makeUser('dokter', 'other-doctor@example.com');
        $this->actingAs($doctor);

        $this->get(route('jadwal-periksa.index'))
            ->assertOk()
            ->assertSee('Jadwal Periksa');
        $this->get(route('jadwal-periksa.create'))->assertOk()->assertSee('Tambah Jadwal Periksa');

        $this->post(route('jadwal-periksa.store'), [
            'hari' => 'Senin',
            'jam_mulai' => '09:00',
            'jam_selesai' => '12:00',
            'status' => '1',
        ])->assertRedirect(route('jadwal-periksa.index'))
            ->assertSessionHas('success');

        $jadwal = JadwalPeriksa::firstOrFail();
        $this->assertSame($doctor->id, $jadwal->id_dokter);
        $this->assertTrue($jadwal->status);
        $this->get(route('jadwal-periksa.edit', $jadwal))->assertOk()->assertSee('Senin');

        $this->put(route('jadwal-periksa.update', $jadwal), [
            'hari' => 'Selasa',
            'jam_mulai' => '10:00',
            'jam_selesai' => '13:00',
            'status' => '0',
        ])->assertRedirect(route('jadwal-periksa.index'))
            ->assertSessionHas('success');

        $jadwal->refresh();
        $this->assertSame('Selasa', $jadwal->hari);
        $this->assertFalse($jadwal->status);

        $otherSchedule = JadwalPeriksa::create([
            'id_dokter' => $otherDoctor->id,
            'hari' => 'Rabu',
            'jam_mulai' => '09:00',
            'jam_selesai' => '12:00',
            'status' => true,
        ]);

        $this->get(route('jadwal-periksa.index'))->assertDontSee('Rabu');
        $this->get(route('jadwal-periksa.edit', $otherSchedule))->assertForbidden();
        $this->delete(route('jadwal-periksa.destroy', $jadwal))
            ->assertRedirect(route('jadwal-periksa.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('jadwal_periksa', ['id' => $jadwal->id]);
        $this->assertDatabaseHas('jadwal_periksa', ['id' => $otherSchedule->id]);
    }

    public function test_doctor_schedule_requires_valid_day_and_end_time_after_start_time(): void
    {
        $this->actingAs($this->makeUser('dokter'))
            ->from(route('jadwal-periksa.create'))
            ->post(route('jadwal-periksa.store'), [
                'hari' => 'Hari libur',
                'jam_mulai' => '14:00',
                'jam_selesai' => '13:00',
                'status' => '1',
            ])
            ->assertRedirect(route('jadwal-periksa.create'))
            ->assertSessionHasErrors(['hari', 'jam_selesai']);

        $this->assertDatabaseCount('jadwal_periksa', 0);
    }

    private function makeUser(string $role, string $email = ''): User
    {
        static $counter = 0;
        $counter++;

        return User::create([
            'nama' => ucfirst($role),
            'email' => $email ?: "{$role}-{$counter}@example.com",
            'password' => 'password',
            'role' => $role,
        ]);
    }
}
