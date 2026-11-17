<?php

namespace Tests\Unit;

use App\Services\KehamilanService;
use Carbon\Carbon;
use Tests\TestCase;

class KehamilanServiceTest extends TestCase
{
    private KehamilanService $svc;

    protected function setUp(): void
    {
        parent::setUp();
        $this->svc = new KehamilanService();
    }

    public function test_usia_kehamilan_dihitung_dari_hpht(): void
    {
        $hpht = Carbon::parse('2026-01-01');

        $this->assertSame(0, $this->svc->usiaKehamilan($hpht, Carbon::parse('2026-01-01')));
        $this->assertSame(4, $this->svc->usiaKehamilan($hpht, Carbon::parse('2026-01-29')));
        $this->assertSame(20, $this->svc->usiaKehamilan($hpht, Carbon::parse('2026-05-21')));
    }

    public function test_usia_kehamilan_tidak_negatif_untuk_hpht_masa_depan(): void
    {
        $hpht = Carbon::parse('2026-06-01');

        $this->assertSame(0, $this->svc->usiaKehamilan($hpht, Carbon::parse('2026-01-01')));
    }

    public function test_hpl_memakai_rumus_naegele_280_hari(): void
    {
        $hpl = $this->svc->hpl(Carbon::parse('2026-01-01'));

        $this->assertSame('2026-10-08', $hpl->toDateString());
    }

    public function test_pembagian_trimester(): void
    {
        $this->assertSame(1, $this->svc->trimester(0));
        $this->assertSame(1, $this->svc->trimester(12));
        $this->assertSame(2, $this->svc->trimester(13));
        $this->assertSame(2, $this->svc->trimester(27));
        $this->assertSame(3, $this->svc->trimester(28));
        $this->assertSame(3, $this->svc->trimester(40));
    }

    public function test_imt_dan_kategorinya(): void
    {
        $this->assertEqualsWithDelta(20.8, $this->svc->imt(50, 155), 0.1);
        $this->assertSame('kurus', $this->svc->imtKategori(17.0));
        $this->assertSame('normal', $this->svc->imtKategori(21.0));
        $this->assertSame('gemuk', $this->svc->imtKategori(27.0));
        $this->assertSame('obesitas', $this->svc->imtKategori(32.0));
    }

    public function test_anjuran_kenaikan_berat_badan_per_kategori_imt(): void
    {
        $this->assertSame([12.5, 18.0], $this->svc->kenaikanBbAnjuran(17.0));
        $this->assertSame([11.5, 16.0], $this->svc->kenaikanBbAnjuran(21.0));
        $this->assertSame([7.0, 11.5], $this->svc->kenaikanBbAnjuran(27.0));
        $this->assertSame([5.0, 9.0], $this->svc->kenaikanBbAnjuran(32.0));
    }

    public function test_status_anc_per_trimester_sesuai_target_permenkes(): void
    {
        $status = $this->svc->statusAncPerTrimester([1 => 1, 2 => 2, 3 => 1]);

        $this->assertSame(1, $status[1]['target']);
        $this->assertSame(2, $status[2]['target']);
        $this->assertSame(3, $status[3]['target']);
        $this->assertTrue($status[1]['cukup']);
        $this->assertTrue($status[2]['cukup']);
        $this->assertFalse($status[3]['cukup'], 'Trimester 3 baru 1 dari 3 kunjungan.');
    }

    public function test_label_usia_menampilkan_minggu_dan_bulan(): void
    {
        $this->assertSame('24 minggu (6 bulan)', $this->svc->labelUsia(24));
        $this->assertSame('2 minggu', $this->svc->labelUsia(2));
    }

    public function test_interval_kunjungan_berikutnya(): void
    {
        $this->assertSame(4, $this->svc->intervalKunjunganBerikutnya(20));
        $this->assertSame(2, $this->svc->intervalKunjunganBerikutnya(30));
    }
}
