<?php

namespace Tests\Feature;

use App\Models\{Kegiatan, Peserta, Kehadiran};
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KehadiranCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Semua route aplikasi memakai middleware auth
        $this->actingAs(User::factory()->create());
    }


    private function setup2(): array
    {
        $balita = Peserta::create([
            'nama' => 'Anak Balita', 'nik' => '1111111111110001', 'tanggal_lahir' => '2023-01-01',
            'jenis_kelamin' => 'L', 'kategori' => 'balita', 'alamat' => 'Jl. A',
        ]);
        $lansia = Peserta::create([
            'nama' => 'Kakek Lansia', 'nik' => '1111111111110002', 'tanggal_lahir' => '1960-01-01',
            'jenis_kelamin' => 'L', 'kategori' => 'lansia', 'alamat' => 'Jl. B',
        ]);
        $keg = Kegiatan::create([
            'nama' => 'Penimbangan', 'tanggal' => now()->format('Y-m-d'), 'jam_mulai' => '08:00',
            'jam_selesai' => '10:00', 'lokasi' => 'Balai', 'kategori' => 'balita', 'status' => 'terjadwal',
        ]);
        return [$balita, $lansia, $keg];
    }

    public function test_index_tampil_dengan_modal(): void
    {
        $this->setup2();
        $this->get('/kehadiran')
            ->assertOk()
            ->assertSee('Riwayat Kehadiran')
            ->assertSee('Catat kehadiran')
            ->assertSee('const PESERTA')
            ->assertSee('mdCatat')
            ->assertSee('mdKegiatan')
            ->assertSee('mdHapus');
    }

    public function test_simpan_kehadiran_menandai_hadir(): void
    {
        [$balita, $lansia, $keg] = $this->setup2();

        $res = $this->post('/kehadiran', [
            'kegiatan_id' => $keg->id,
            'peserta_ids' => [$balita->id],
        ]);
        $res->assertRedirect(route('kehadiran.index'));

        $this->assertDatabaseHas('kehadiran', [
            'peserta_id' => $balita->id, 'kegiatan_id' => $keg->id, 'status' => 'hadir',
        ]);
        // lansia bukan kategori kegiatan → tidak dibuatkan record
        $this->assertDatabaseMissing('kehadiran', ['peserta_id' => $lansia->id, 'kegiatan_id' => $keg->id]);
    }

    public function test_peserta_kategori_sama_yang_tidak_dicentang_jadi_tidak_hadir(): void
    {
        [$balita, , $keg] = $this->setup2();
        $balita2 = Peserta::create([
            'nama' => 'Balita Dua', 'nik' => '1111111111110003', 'tanggal_lahir' => '2023-05-05',
            'jenis_kelamin' => 'P', 'kategori' => 'balita', 'alamat' => 'Jl. C',
        ]);

        $this->post('/kehadiran', [
            'kegiatan_id' => $keg->id,
            'peserta_ids' => [$balita->id],
        ]);

        $this->assertDatabaseHas('kehadiran', ['peserta_id' => $balita2->id, 'status' => 'tidak_hadir']);
    }

    public function test_tanpa_peserta_ditolak(): void
    {
        [, , $keg] = $this->setup2();
        $res = $this->post('/kehadiran', ['kegiatan_id' => $keg->id]);
        $res->assertSessionHasErrors('peserta_ids');
    }

    public function test_hapus_kehadiran(): void
    {
        [$balita, , $keg] = $this->setup2();
        $h = Kehadiran::create([
            'peserta_id' => $balita->id, 'kegiatan_id' => $keg->id, 'status' => 'hadir',
        ]);

        $this->delete("/kehadiran/{$h->id}")->assertRedirect(route('kehadiran.index'));
        $this->assertDatabaseMissing('kehadiran', ['id' => $h->id]);
    }

    public function test_tiap_baris_punya_tiga_tombol_aksi(): void
    {
        [$balita, , $keg] = $this->setup2();
        Kehadiran::create([
            'peserta_id' => $balita->id, 'kegiatan_id' => $keg->id, 'status' => 'hadir',
        ]);

        $this->get('/kehadiran')
            ->assertOk()
            ->assertSee('openDetailKehadiran(')
            ->assertSee('openEditKehadiran(')
            ->assertSee('openHapus(')
            ->assertSee('mdDetailKehadiran')
            ->assertSee('mdEditKehadiran');
    }

    public function test_edit_kehadiran_ubah_status_dan_catatan(): void
    {
        [$balita, , $keg] = $this->setup2();
        $h = Kehadiran::create([
            'peserta_id' => $balita->id, 'kegiatan_id' => $keg->id, 'status' => 'hadir',
        ]);

        $res = $this->put("/kehadiran/{$h->id}", [
            'status'  => 'tidak_hadir',
            'catatan' => 'Sakit demam',
        ]);

        $res->assertRedirect(route('kehadiran.index'));
        $h->refresh();
        $this->assertSame('tidak_hadir', $h->status);
        $this->assertSame('Sakit demam', $h->catatan);
    }

    public function test_edit_status_tidak_valid_ditolak(): void
    {
        [$balita, , $keg] = $this->setup2();
        $h = Kehadiran::create([
            'peserta_id' => $balita->id, 'kegiatan_id' => $keg->id, 'status' => 'hadir',
        ]);

        $this->put("/kehadiran/{$h->id}", ['status' => 'bolos'])
            ->assertSessionHasErrors('status');

        $this->assertSame('hadir', $h->fresh()->status);
    }
}
