<?php

namespace Tests\Feature;

use App\Models\Peserta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PesertaCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Semua route aplikasi memakai middleware auth
        $this->actingAs(User::factory()->create());
    }


    private function payload(array $override = []): array
    {
        return array_merge([
            'nama'          => 'Uji Coba',
            'nik'           => '3573018888880002',
            'tanggal_lahir' => '2024-03-03',
            'jenis_kelamin' => 'L',
            'kategori'      => 'balita',
            'alamat'        => 'Jl. Uji No. 1',
            'nama_wali'     => 'Wali Uji',
            'no_telepon'    => '081211112222',
        ], $override);
    }

    public function test_index_tampil(): void
    {
        Peserta::create($this->payload());
        $this->get('/peserta')
            ->assertOk()
            ->assertSee('Data Peserta')
            ->assertSee('Tambah peserta')
            ->assertSee('const DATA');
    }

    public function test_tambah_peserta(): void
    {
        $res = $this->post('/peserta', $this->payload());
        $res->assertRedirect(route('peserta.index'));
        $this->assertDatabaseHas('peserta', ['nik' => '3573018888880002', 'nama' => 'Uji Coba']);
    }

    public function test_edit_peserta(): void
    {
        $p = Peserta::create($this->payload());

        $res = $this->put("/peserta/{$p->id}", $this->payload([
            'nama'          => 'Nama Baru',
            'tanggal_lahir' => '1970-05-05',
            'jenis_kelamin' => 'P',
            'kategori'      => 'lansia',
            'alamat'        => 'Jl. Baru No. 2',
        ]));

        $res->assertRedirect(route('peserta.index'));
        $p->refresh();
        $this->assertSame('Nama Baru', $p->nama);
        $this->assertSame('lansia', $p->kategori);
        $this->assertSame('P', $p->jenis_kelamin);
    }

    public function test_hapus_peserta(): void
    {
        $p = Peserta::create($this->payload());
        $res = $this->delete("/peserta/{$p->id}");
        $res->assertRedirect(route('peserta.index'));
        $this->assertDatabaseMissing('peserta', ['id' => $p->id]);
    }

    public function test_nik_duplikat_ditolak(): void
    {
        Peserta::create($this->payload());
        $res = $this->post('/peserta', $this->payload(['nama' => 'Duplikat']));
        $res->assertSessionHasErrors('nik');
        $this->assertSame(1, Peserta::where('nik', '3573018888880002')->count());
    }

    public function test_filter_kategori_dan_search(): void
    {
        Peserta::create($this->payload(['nama' => 'Anak Balita', 'nik' => '1111111111110001', 'kategori' => 'balita']));
        Peserta::create($this->payload(['nama' => 'Kakek Lansia', 'nik' => '1111111111110002', 'kategori' => 'lansia']));

        $this->get('/peserta?kategori=lansia')->assertOk()->assertSee('Kakek Lansia')->assertDontSee('Anak Balita');
        $this->get('/peserta?search=Kakek')->assertOk()->assertSee('Kakek Lansia')->assertDontSee('Anak Balita');
    }
}
