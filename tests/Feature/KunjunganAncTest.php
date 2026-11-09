<?php

namespace Tests\Feature;

use App\Models\IbuHamil;
use App\Models\KunjunganAnc;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KunjunganAncTest extends TestCase
{
    use RefreshDatabase;

    private function siapkanIbu(int $minggu = 20): IbuHamil
    {
        $this->actingAs(User::factory()->create(['role' => 'kader']))
            ->post('/ibu-hamil', [
                'nik'             => '3573010101990001',
                'nama'            => 'Siti Aminah',
                'tanggal_lahir'   => '1999-01-01',
                'alamat'          => 'Jl. Soekarno Hatta No. 1',
                'golongan_darah'  => 'O',
                'hpht'            => now()->subWeeks($minggu)->toDateString(),
                'gravida'         => 1,
                'para'            => 0,
                'abortus'         => 0,
                'status'          => 'aktif',
                'tinggi_badan'    => 155,
                'berat_pra_hamil' => 50,
            ]);

        return IbuHamil::firstOrFail();
    }

    private function dataKunjungan(IbuHamil $ibu, array $override = []): array
    {
        return array_merge([
            'ibu_hamil_id'     => $ibu->id,
            'tanggal'          => now()->toDateString(),
            'berat_badan'      => 55,
            'lila'             => 24.0,
            'tensi_sistolik'   => 120,
            'tensi_diastolik'  => 80,
            'tfu'              => 20,
            'djj'              => 140,
            'presentasi_janin' => 'kepala',
            'hb'               => 12.0,
            'protein_urin'     => 'negatif',
            'tablet_fe'        => 30,
            'imunisasi_tt'     => 'TT1',
            'status_rujukan'   => 'tidak',
        ], $override);
    }

    public function test_halaman_kunjungan_dapat_dibuka(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'kader']))
            ->get('/kunjungan')
            ->assertOk()
            ->assertSee('Catatan Kunjungan ANC');
    }

    public function test_mencatat_kunjungan_menghitung_usia_dan_kode_k_otomatis(): void
    {
        $ibu = $this->siapkanIbu(20);

        $this->actingAs(User::factory()->create(['role' => 'kader']))
            ->post('/kunjungan', $this->dataKunjungan($ibu))
            ->assertRedirect(route('kunjungan.index'));

        $k = KunjunganAnc::first();

        $this->assertNotNull($k);
        $this->assertSame(1, $k->kunjungan_ke, 'Kunjungan pertama harus K1.');
        $this->assertSame(20, $k->usia_kehamilan_minggu);
        $this->assertSame(2, $k->trimester);
    }

    public function test_kunjungan_berikutnya_menjadi_k2(): void
    {
        $ibu  = $this->siapkanIbu(20);
        $user = User::factory()->create(['role' => 'kader']);

        $this->actingAs($user)->post('/kunjungan', $this->dataKunjungan($ibu));
        $this->actingAs($user)->post('/kunjungan', $this->dataKunjungan($ibu));

        $this->assertSame(2, KunjunganAnc::max('kunjungan_ke'));
    }

    public function test_tensi_dan_berat_badan_wajib_diisi(): void
    {
        $ibu = $this->siapkanIbu();

        $this->actingAs(User::factory()->create(['role' => 'kader']))
            ->post('/kunjungan', $this->dataKunjungan($ibu, [
                'tensi_sistolik' => '',
                'berat_badan'    => '',
            ]))
            ->assertSessionHasErrors(['tensi_sistolik', 'berat_badan']);

        $this->assertSame(0, KunjunganAnc::count());
    }

    public function test_peringatan_klinis_terdeteksi(): void
    {
        $ibu = $this->siapkanIbu();

        $this->actingAs(User::factory()->create(['role' => 'kader']))
            ->post('/kunjungan', $this->dataKunjungan($ibu, [
                'tensi_sistolik'  => 145,
                'tensi_diastolik' => 95,
                'hb'              => 10.2,
                'lila'            => 22.0,
                'djj'             => 118,
                'protein_urin'    => '+2',
            ]));

        $k = KunjunganAnc::first();

        $this->assertTrue($k->hipertensi);
        $this->assertTrue($k->anemia);
        $this->assertTrue($k->kek);
        $this->assertTrue($k->djj_abnormal);
        $this->assertTrue($k->protein_uria);
        $this->assertCount(5, $k->peringatan);
    }

    public function test_ibu_normal_tidak_memunculkan_peringatan(): void
    {
        $ibu = $this->siapkanIbu();

        $this->actingAs(User::factory()->create(['role' => 'kader']))
            ->post('/kunjungan', $this->dataKunjungan($ibu));

        $this->assertCount(0, KunjunganAnc::first()->peringatan);
    }

    public function test_kunjungan_dapat_dihapus(): void
    {
        $ibu  = $this->siapkanIbu();
        $user = User::factory()->create(['role' => 'kader']);

        $this->actingAs($user)->post('/kunjungan', $this->dataKunjungan($ibu));
        $k = KunjunganAnc::first();

        $this->actingAs($user)->delete("/kunjungan/{$k->id}")
            ->assertRedirect(route('kunjungan.index'));

        $this->assertSame(0, KunjunganAnc::count());
    }
}
