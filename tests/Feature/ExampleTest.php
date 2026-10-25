<?php

namespace Tests\Feature;

use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Login')
            ->assertSee('Register')
            ->assertSee(route('login'), false)
            ->assertSee(route('register'), false);
    }

    public function test_login_form_posts_to_the_current_host(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('action="/login"', false)
            ->assertSee('name="_token"', false);
    }

    public function test_admin_can_login_and_view_dashboard(): void
    {
        $this->seed(UserSeeder::class);

        $this->post('/login', ['email' => 'admin@gmail.com', 'password' => 'admin'])
            ->assertRedirect(route('admin.dashboard'));

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Halo Selamat Datang Admin');
    }

    public function test_doctor_can_login_and_view_dashboard(): void
    {
        $this->seed(UserSeeder::class);

        $this->post('/login', ['email' => 'dokter@gmail.com', 'password' => 'dokter'])
            ->assertRedirect(route('dokter.dashboard'));

        $this->get(route('dokter.dashboard'))
            ->assertOk()
            ->assertSee('Halo Selamat Datang Dokter');
    }

    public function test_patient_can_register_login_and_view_dashboard(): void
    {
        $this->post('/register', [
            'nama' => 'Pasien Baru',
            'alamat' => 'Jl. Sehat No. 1',
            'no_ktp' => '1234567890123456',
            'no_hp' => '08123456789',
            'email' => 'pasien@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('login'));

        $this->assertDatabaseHas('users', [
            'email' => 'pasien@example.com',
            'role' => 'pasien',
        ]);

        $this->post('/login', ['email' => 'pasien@example.com', 'password' => 'password'])
            ->assertRedirect(route('pasien.dashboard'));

        $this->get(route('pasien.dashboard'))->assertOk();
    }
}
