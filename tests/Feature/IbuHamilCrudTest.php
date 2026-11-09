<?php

namespace Tests\Feature;

use App\Models\IbuHamil;
use App\Models\KunjunganAnc;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IbuHamilCrudTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'kader']);
    }

    private function dataIbu(array $override = []): array
    {
        return array_merge([
            'nik'               => '3573010101990001',
            'nama'              => 'Siti Aminah',
            'tanggal_lahir'     => '1999-01-01',
            'alamat'            => 'Jl. Soekarno Hatta No. 1, Kota Malang',
            'no_hp'             => '081234567890',
            'nama_suami'        => 'Budi Santoso',
            'no_hp_suami'       => '081298765432',
            'golongan_darah'    => 'O',
            'hpht'              => now()->subWeeks(20)->toDateString(),
            'gravida'           => 1,
            'para'              => 0,
            'abortus'           => 0,
            'status'            => 'aktif',
            'tinggi_badan'      => 155,
            'berat_pra_hamil'   => 50,
            'lila_pra_hamil'    => 24.5,
        ], $override);
    }

    public function test_halaman_data_ibu_hamil_dapat_dibuka(): void
    {
        $this->actingAs($this->admin())
            ->get('/ibu-hamil')
            ->assertOk()
            ->assertSee('Data Ibu Hamil');
    }

    public function test_bisa_menambah_ibu_hamil_dan_hitungan_otomatis_terisi(): void
    {
        $this->actingAs($this->admin())
            ->post('/ibu-hamil', $this->dataIbu())
            ->assertRedirect(route('ibu-hamil.index'));

        $ibu = IbuHamil::first();

        $this->assertNotNull($ibu);
        $this->assertSame('Siti Aminah', $ibu->nama);
        // HPL = HPHT + 280 hari
        $this->assertSame(
            now()->subWeeks(20)->addDays(280)->toDateString(),
            $ibu->hpl->toDateString()
        );
        // IMT = 50 / (1,55^2) = 20,8
        $this->assertEqualsWithDelta(20.8, (float) $ibu->imt_pra_hamil, 0.1);
        $this->assertSame('KRR', $ibu->kategori_risiko);
    }

    public function test_nik_harus_tepat_16_digit(): void
    {
        $this->actingAs($this->admin())
            ->post('/ibu-hamil', $this->dataIbu(['nik' => '12345']))
            ->assertSessionHasErrors('nik');

        $this->assertSame(0, IbuHamil::count());
    }

    public function test_nik_duplikat_ditolak(): void
    {
        $user = $this->admin();
        $this->actingAs($user)->post('/ibu-hamil', $this->dataIbu());

        $this->actingAs($user)
            ->post('/ibu-hamil', $this->dataIbu(['nama' => 'Ibu Lain']))
            ->assertSessionHasErrors('nik');

        $this->assertSame(1, IbuHamil::count());
    }

    public function test_hpht_tidak_boleh_melewati_hari_ini(): void
    {
        $this->actingAs($this->admin())
            ->post('/ibu-hamil', $this->dataIbu(['hpht' => now()->addWeek()->toDateString()]))
            ->assertSessionHasErrors('hpht');
    }

    public function test_bisa_mengubah_data_ibu_hamil(): void
    {
        $user = $this->admin();
        $this->actingAs($user)->post('/ibu-hamil', $this->dataIbu());
        $ibu = IbuHamil::first();

        $this->actingAs($user)
            ->put("/ibu-hamil/{$ibu->id}", $this->dataIbu(['nama' => 'Siti Aminah Baru']))
            ->assertRedirect(route('ibu-hamil.index'));

        $this->assertSame('Siti Aminah Baru', $ibu->fresh()->nama);
    }

    public function test_hapus_ibu_hamil_ikut_menghapus_kunjungan(): void
    {
        $user = $this->admin();
        $this->actingAs($user)->post('/ibu-hamil', $this->dataIbu());
        $ibu = IbuHamil::first();

        KunjunganAnc::create([
            'ibu_hamil_id'          => $ibu->id,
            'tanggal'               => now()->toDateString(),
            'kunjungan_ke'          => 1,
            'usia_kehamilan_minggu' => 20,
            'trimester'             => 2,
            'berat_badan'           => 55,
            'tensi_sistolik'        => 120,
            'tensi_diastolik'       => 80,
            'presentasi_janin'      => 'belum_tentu',
            'protein_urin'          => 'negatif',
            'imunisasi_tt'          => 'TT1',
        ]);

        $this->assertSame(1, KunjunganAnc::count());

        $this->actingAs($user)->delete("/ibu-hamil/{$ibu->id}")
            ->assertRedirect(route('ibu-hamil.index'));

        $this->assertSame(0, IbuHamil::count());
        $this->assertSame(0, KunjunganAnc::count(), 'Riwayat kunjungan harus ikut terhapus (cascade).');
    }

    public function test_filter_trimester_dan_risiko_bekerja(): void
    {
        $user = $this->admin();
        $this->actingAs($user)->post('/ibu-hamil', $this->dataIbu([
            'nik'  => '3573010101990001',
            'nama' => 'Ibu Trimester Dua',
            'hpht' => now()->subWeeks(20)->toDateString(),
        ]));
        $this->actingAs($user)->post('/ibu-hamil', $this->dataIbu([
            'nik'  => '3573010101990002',
            'nama' => 'Ibu Trimester Satu',
            'hpht' => now()->subWeeks(6)->toDateString(),
        ]));

        $this->actingAs($user)->get('/ibu-hamil?filter=tm1')
            ->assertOk()->assertSee('Ibu Trimester Satu')->assertDontSee('Ibu Trimester Dua');

        $this->actingAs($user)->get('/ibu-hamil?filter=tm2')
            ->assertOk()->assertSee('Ibu Trimester Dua')->assertDontSee('Ibu Trimester Satu');
    }

    public function test_pencarian_nama_bekerja(): void
    {
        $user = $this->admin();
        $this->actingAs($user)->post('/ibu-hamil', $this->dataIbu(['nama' => 'Dewi Lestari']));

        $this->actingAs($user)->get('/ibu-hamil?q=Dewi')
            ->assertOk()->assertSee('Dewi Lestari');
    }

    public function test_kartu_ibu_dapat_dibuka(): void
    {
        $user = $this->admin();
        $this->actingAs($user)->post('/ibu-hamil', $this->dataIbu());
        $ibu = IbuHamil::first();

        $this->actingAs($user)->get("/ibu-hamil/{$ibu->id}")
            ->assertOk()
            ->assertSee('Siti Aminah')
            ->assertSee('Riwayat Kunjungan ANC');
    }
}
