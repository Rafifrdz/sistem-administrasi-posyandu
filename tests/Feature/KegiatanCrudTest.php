<?php

namespace Tests\Feature;

use App\Models\Kegiatan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KegiatanCrudTest extends TestCase
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
            'nama'        => 'Penimbangan Balita',
            'tanggal'     => '2026-12-15',
            'jam_mulai'   => '08:00',
            'jam_selesai' => '10:00',
            'lokasi'      => 'Balai RW 04',
            'kategori'    => 'balita',
            'keterangan'  => null,
        ], $override);
    }

    public function test_index_tampil_dengan_modal(): void
    {
        Kegiatan::create($this->payload());
        $this->get('/kegiatan')
            ->assertOk()
            ->assertSee('Jadwal Kegiatan')
            ->assertSee('Tambah kegiatan')
            ->assertSee('const KEGIATAN')
            ->assertSee('mdCreate')
            ->assertSee('mdDetail')
            ->assertSee('mdEdit')
            ->assertSee('mdDelete');
    }

    public function test_tambah_kegiatan(): void
    {
        $res = $this->post('/kegiatan', $this->payload());
        $res->assertRedirect(route('kegiatan.index'));
        $this->assertDatabaseHas('kegiatan', ['nama' => 'Penimbangan Balita', 'lokasi' => 'Balai RW 04']);
    }

    public function test_edit_kegiatan(): void
    {
        $k = Kegiatan::create($this->payload());

        $res = $this->put("/kegiatan/{$k->id}", $this->payload([
            'nama'        => 'Nama Diubah',
            'jam_selesai' => '11:00',
            'status'      => 'selesai',
        ]));

        $res->assertRedirect(route('kegiatan.index'));
        $k->refresh();
        $this->assertSame('Nama Diubah', $k->nama);
        $this->assertSame('selesai', $k->status);
        $this->assertSame('11:00', substr($k->jam_selesai, 0, 5));
    }

    public function test_hapus_kegiatan(): void
    {
        $k = Kegiatan::create($this->payload());
        $this->delete("/kegiatan/{$k->id}")->assertRedirect(route('kegiatan.index'));
        $this->assertDatabaseMissing('kegiatan', ['id' => $k->id]);
    }

    public function test_jam_selesai_harus_setelah_jam_mulai(): void
    {
        $res = $this->post('/kegiatan', $this->payload(['jam_mulai' => '10:00', 'jam_selesai' => '08:00']));
        $res->assertSessionHasErrors('jam_selesai');
        $this->assertSame(0, Kegiatan::count());
    }

    public function test_filter_status_dan_search(): void
    {
        Kegiatan::create($this->payload(['nama' => 'Aktif Terjadwal', 'status' => 'terjadwal']));
        Kegiatan::create($this->payload(['nama' => 'Sudah Selesai', 'status' => 'selesai']));

        $this->get('/kegiatan?status=selesai')->assertOk()->assertSee('Sudah Selesai')->assertDontSee('Aktif Terjadwal');
        $this->get('/kegiatan?search=Terjadwal')->assertOk()->assertSee('Aktif Terjadwal');
    }
}
