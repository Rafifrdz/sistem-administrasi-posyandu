<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private function buatUser(array $override = []): User
    {
        return User::create(array_merge([
            'name'     => 'Siti Rahmawati',
            'username' => 'siti',
            'role'     => 'Kader Posyandu',
            'email'    => 'siti@posyandu.test',
            'password' => 'posyandu123',
        ], $override));
    }

    public function test_halaman_login_tampil(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Selamat datang')
            ->assertSee('Nama pengguna')
            ->assertSee('Kata sandi')
            ->assertSee('Masuk')
            ->assertSee('Gunakan akun yang diberikan admin Posyandu.');
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        foreach (['/', '/peserta', '/kegiatan', '/kehadiran', '/laporan'] as $uri) {
            $this->get($uri)->assertRedirect(route('login'));
        }
    }

    public function test_login_berhasil_dengan_username_dan_password(): void
    {
        $this->buatUser();

        $res = $this->post('/login', [
            'username' => 'siti',
            'password' => 'posyandu123',
        ]);

        $res->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs(User::where('username', 'siti')->first());
    }

    public function test_password_tersimpan_ter_hash(): void
    {
        $user = $this->buatUser();
        $this->assertNotSame('posyandu123', $user->password);
        $this->assertTrue(Hash::check('posyandu123', $user->password));
    }

    public function test_password_salah_ditolak(): void
    {
        $this->buatUser();

        $res = $this->from('/login')
            ->post('/login', ['username' => 'siti', 'password' => 'salah']);

        $res->assertRedirect('/login')
            ->assertSessionHasErrors('username');

        $this->assertGuest();

        // Pesan error benar-benar tampil di halaman login
        $this->followingRedirects()
            ->from('/login')
            ->post('/login', ['username' => 'siti', 'password' => 'salah'])
            ->assertOk()
            ->assertSee('Nama pengguna atau kata sandi salah.');
    }

    public function test_username_tidak_dikenal_ditolak(): void
    {
        $this->buatUser();

        $this->from('/login')
            ->post('/login', ['username' => 'tidakada', 'password' => 'posyandu123'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_field_kosong_ditolak(): void
    {
        $this->post('/login', [])
            ->assertSessionHasErrors(['username', 'password']);

        $this->assertGuest();
    }

    public function test_user_yang_sudah_masuk_tidak_bisa_buka_login(): void
    {
        $this->actingAs($this->buatUser())
            ->get('/login')
            ->assertRedirect(route('dashboard'));
    }

    public function test_logout_mengakhiri_sesi(): void
    {
        $user = $this->buatUser();

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_setelah_login_bisa_akses_dashboard(): void
    {
        $this->actingAs($this->buatUser())
            ->get('/')
            ->assertOk()
            ->assertSee('Dashboard');
    }

    public function test_sidebar_menampilkan_nama_user_yang_login(): void
    {
        $this->actingAs($this->buatUser([
            'name' => 'Dewi Kader',
            'username' => 'dewi',
        ]))
            ->get('/')
            ->assertOk()
            ->assertSee('Dewi Kader')
            ->assertSee('DK');   // inisial avatar
    }
}
