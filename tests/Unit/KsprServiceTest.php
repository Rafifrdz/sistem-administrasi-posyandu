<?php

namespace Tests\Unit;

use App\Models\IbuHamil;
use App\Models\KunjunganAnc;
use App\Services\KsprService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KsprServiceTest extends TestCase
{
    use RefreshDatabase;

    private KsprService $svc;

    protected function setUp(): void
    {
        parent::setUp();
        $this->svc = new KsprService();
    }

    private function ibu(array $override = []): IbuHamil
    {
        return IbuHamil::create(array_merge([
            'nik'             => '3573010101990001',
            'nama'            => 'Ibu Uji',
            'tanggal_lahir'   => '1998-01-01',
            'alamat'          => 'Jl. Uji',
            'golongan_darah'  => 'O',
            'hpht'            => now()->subWeeks(20)->toDateString(),
            'gravida'         => 1,
            'para'            => 0,
            'abortus'         => 0,
            'status'          => 'aktif',
            'tinggi_badan'    => 158,
            'berat_pra_hamil' => 55,
        ], $override));
    }

    public function test_ibu_tanpa_faktor_risiko_masuk_krr(): void
    {
        $hasil = $this->svc->hitung($this->ibu());

        $this->assertSame(0, $hasil['skor']);
        $this->assertSame('KRR', $hasil['kategori']);
        $this->assertCount(0, $hasil['faktor']);
    }

    public function test_umur_di_atas_35_mendapat_skor_4(): void
    {
        $hasil = $this->svc->hitung($this->ibu(['tanggal_lahir' => now()->subYears(37)->toDateString()]));

        $this->assertSame(4, $hasil['skor']);
        $this->assertSame('KRR', $hasil['kategori']);
    }

    public function test_riwayat_sesar_menambah_8_poin(): void
    {
        $hasil = $this->svc->hitung($this->ibu(['pernah_sesar' => true]));

        $this->assertSame(8, $hasil['skor']);
        $this->assertSame('KRT', $hasil['kategori'], 'Skor 8 masuk Kehamilan Risiko Tinggi.');
    }

    public function test_kombinasi_umur_tua_dan_sesar_masuk_krst(): void
    {
        $hasil = $this->svc->hitung($this->ibu([
            'tanggal_lahir' => now()->subYears(37)->toDateString(),
            'pernah_sesar'  => true,
        ]));

        $this->assertSame(12, $hasil['skor']);
        $this->assertSame('KRST', $hasil['kategori']);
    }

    public function test_tinggi_badan_kurang_dari_145_dan_anak_banyak_terhitung(): void
    {
        $hasil = $this->svc->hitung($this->ibu([
            'tinggi_badan' => 144,
            'para'         => 4,
        ]));

        $this->assertSame(8, $hasil['skor']);
        $this->assertCount(2, $hasil['faktor']);
    }

    public function test_riwayat_penyakit_hipertensi_terdeteksi(): void
    {
        $hasil = $this->svc->hitung($this->ibu(['riwayat_penyakit' => 'Hipertensi sejak 2020']));

        $this->assertSame(4, $hasil['skor']);
        $this->assertSame('penyakit_berat', $hasil['faktor'][0]['kode']);
    }

    public function test_presentasi_lintang_pada_kunjungan_menambah_12_poin(): void
    {
        $ibu = $this->ibu();

        $kunjungan = KunjunganAnc::create([
            'ibu_hamil_id'          => $ibu->id,
            'tanggal'               => now()->toDateString(),
            'kunjungan_ke'          => 1,
            'usia_kehamilan_minggu' => 32,
            'trimester'             => 3,
            'berat_badan'           => 60,
            'tensi_sistolik'        => 120,
            'tensi_diastolik'       => 80,
            'presentasi_janin'      => 'lintang',
            'protein_urin'          => 'negatif',
            'imunisasi_tt'          => 'TT1',
        ]);

        $hasil = $this->svc->hitungDenganKunjungan($ibu, $kunjungan);

        $this->assertSame(12, $hasil['skor']);
        $this->assertSame('KRST', $hasil['kategori']);
    }

    public function test_label_kategori(): void
    {
        $this->assertSame('Risiko Rendah', $this->svc->labelKategori('KRR'));
        $this->assertSame('Risiko Tinggi', $this->svc->labelKategori('KRT'));
        $this->assertSame('Risiko Sangat Tinggi', $this->svc->labelKategori('KRST'));
    }
}
