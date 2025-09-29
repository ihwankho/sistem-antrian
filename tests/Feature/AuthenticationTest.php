<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    /**
     * Trait ini akan secara otomatis me-reset database Anda setelah setiap
     * fungsi test selesai dijalankan. Ini memastikan pengujian Anda
     * bersih dan tidak bergantung satu sama lain.
     */
    use RefreshDatabase;

    /**
     * @test
     * Menguji skenario AUTH-01: Pengguna (Admin/Petugas) berhasil login.
     */
    public function pengguna_bisa_login_dengan_kredensial_valid()
    {
        // 1. Arrange (Persiapan)
        // Kita membuat satu user palsu di database testing.
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
        ]);

        // 2. Act (Eksekusi)
        // Kita mensimulasikan pengguna mengirimkan form login.
        $response = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);

        // 3. Assert (Pengecekan)
        // Kita pastikan bahwa setelah aksi di atas, pengguna sudah ter-autentikasi.
        $this->assertAuthenticated();

        // Kita pastikan pengguna diarahkan ke halaman dashboard setelah login.
        $response->assertRedirect('/dashboard');
    }

    /**
     * @test
     * Menguji skenario AUTH-02: Pengguna gagal login dengan password salah.
     */
    public function pengguna_tidak_bisa_login_dengan_password_salah()
    {
        // 1. Arrange
        $user = User::factory()->create([
            'email' => 'petugas@example.com',
            'password' => Hash::make('password_benar'),
        ]);

        // 2. Act
        // Kita simulasikan pengguna mengirim form dengan password yang SALAH.
        $response = $this->post('/login', [
            'email' => 'petugas@example.com',
            'password' => 'password_salah',
        ]);

        // 3. Assert
        // Kita pastikan pengguna TIDAK berhasil login (statusnya tetap 'guest').
        $this->assertGuest();
        
        // Pastikan ada pesan error validasi untuk field 'email'
        $response->assertSessionHasErrors('email');
    }

    /**
     * @test
     * Menguji skenario AUTH-03: Pengguna gagal login dengan email yang tidak terdaftar.
     */
    public function pengguna_tidak_bisa_login_dengan_email_tidak_terdaftar()
    {
        // 1. Arrange (Tidak perlu membuat user karena kita menguji email yang tidak ada)

        // 2. Act
        $response = $this->post('/login', [
            'email' => 'tidakada@example.com',
            'password' => 'password_apapun',
        ]);

        // 3. Assert
        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    /**
     * @test
     * Menguji skenario AUTH-04: Pengguna berhasil logout.
     */
    public function pengguna_yang_sudah_login_bisa_logout()
    {
        // 1. Arrange
        // Kita buat user dan langsung loginkan dia untuk simulasi.
        $user = User::factory()->create();
        $this->actingAs($user);

        // 2. Act
        // Kita simulasikan pengguna mengklik tombol logout.
        $response = $this->post('/logout');

        // 3. Assert
        // Kita pastikan pengguna sudah tidak login lagi.
        $this->assertGuest();

        // Kita pastikan pengguna diarahkan ke halaman login setelah logout.
        $response->assertRedirect('/login');
    }
}