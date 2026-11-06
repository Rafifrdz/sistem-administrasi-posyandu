<?php

namespace App\Services;

use Carbon\Carbon;
use Carbon\CarbonInterface;

/**
 * Seluruh perhitungan kebidanan yang dipakai aplikasi.
 *
 * Acuan:
 *  - Usia kehamilan dihitung dari HPHT (Hari Pertama Haid Terakhir).
 *  - Taksiran persalinan memakai rumus Naegele: HPHT + 280 hari.
 *  - Pembagian trimester mengikuti Permenkes 21/2021.
 */
class KehamilanService
{
    /** Panjang kehamilan aterm (hari). */
    public const HARI_KEHAMILAN = 280;

    /** Target kunjungan ANC minimal per trimester (Permenkes 21/2021). */
    public const TARGET_ANC = [1 => 1, 2 => 2, 3 => 3];

    /** Usia kehamilan dalam minggu, dihitung dari HPHT. */
    public function usiaKehamilan(CarbonInterface|string|null $hpht, CarbonInterface|string|null $ref = null): int
    {
        if (! $hpht) {
            return 0;
        }

        $hpht = $this->toCarbon($hpht)->startOfDay();
        $ref  = $ref ? $this->toCarbon($ref)->startOfDay() : now()->startOfDay();

        $hari = (int) $hpht->diffInDays($ref, false);
        if ($hari < 0) {
            return 0;
        }

        return min(42, intdiv($hari, 7));
    }

    /** Taksiran persalinan (Naegele). */
    public function hpl(CarbonInterface|string|null $hpht): ?Carbon
    {
        if (! $hpht) {
            return null;
        }

        return $this->toCarbon($hpht)->startOfDay()->addDays(self::HARI_KEHAMILAN);
    }

    /** Trimester ke-1/2/3 dari usia kehamilan (minggu). */
    public function trimester(int $minggu): int
    {
        if ($minggu <= 12) {
            return 1;
        }

        return $minggu <= 27 ? 2 : 3;
    }

    /** Sisa hari menuju taksiran persalinan (negatif = sudah lewat). */
    public function sisaHariMenujuHpl(CarbonInterface|string|null $hpht): ?int
    {
        $hpl = $this->hpl($hpht);

        return $hpl ? (int) now()->startOfDay()->diffInDays($hpl, false) : null;
    }

    /** Indeks Massa Tubuh. */
    public function imt(float $beratKg, float $tinggiCm): float
    {
        $m = $tinggiCm / 100;

        return $m > 0 ? round($beratKg / ($m * $m), 1) : 0.0;
    }

    /** Kategori IMT pra-hamil (Asia-Pasifik). */
    public function imtKategori(?float $imt): string
    {
        if ($imt === null) {
            return 'tidak diketahui';
        }

        return match (true) {
            $imt < 18.5 => 'kurus',
            $imt < 25.0 => 'normal',
            $imt < 30.0 => 'gemuk',
            default     => 'obesitas',
        };
    }

    /** Anjuran kenaikan berat badan selama hamil (kg) berdasarkan IMT pra-hamil. */
    public function kenaikanBbAnjuran(?float $imt): array
    {
        return match (true) {
            $imt === null   => [11.5, 16.0],
            $imt < 18.5     => [12.5, 18.0],
            $imt < 25.0     => [11.5, 16.0],
            $imt < 30.0     => [7.0, 11.5],
            default         => [5.0, 9.0],
        };
    }

    /** Label ramah pengguna: "24 minggu (6 bulan)". */
    public function labelUsia(int $minggu): string
    {
        $bulan = intdiv($minggu, 4);

        return $bulan > 0
            ? "{$minggu} minggu ({$bulan} bulan)"
            : "{$minggu} minggu";
    }

    /**
     * Status kecukupan ANC per trimester.
     *
     * @param  array<int,int>  $jumlahPerTrimester  contoh: [1 => 1, 2 => 2, 3 => 1]
     * @return array<int,array{target:int,dicapai:int,cukup:bool}>
     */
    public function statusAncPerTrimester(array $jumlahPerTrimester): array
    {
        $hasil = [];
        foreach (self::TARGET_ANC as $trimester => $target) {
            $dicapai = (int) ($jumlahPerTrimester[$trimester] ?? 0);
            $hasil[$trimester] = [
                'target'  => $target,
                'dicapai' => $dicapai,
                'cukup'   => $dicapai >= $target,
            ];
        }

        return $hasil;
    }

    /**
     * Interval anjuran kunjungan berikutnya (minggu) berdasarkan usia kehamilan.
     * TM1 & TM2 tiap 4 minggu, TM3 tiap 2 minggu.
     */
    public function intervalKunjunganBerikutnya(int $minggu): int
    {
        return $minggu >= 28 ? 2 : 4;
    }

    /** Label K1..K6 dari urutan kunjungan. */
    public function labelKunjungan(int $ke): string
    {
        return 'K' . max(1, $ke);
    }

    private function toCarbon(CarbonInterface|string $value): Carbon
    {
        return $value instanceof CarbonInterface
            ? Carbon::instance($value->toDateTime())
            : Carbon::parse($value);
    }
}
