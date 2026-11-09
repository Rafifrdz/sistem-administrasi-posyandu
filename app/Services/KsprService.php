<?php

namespace App\Services;

use App\Models\IbuHamil;
use App\Models\KunjunganAnc;
use Carbon\Carbon;

/**
 * Kartu Skor Poedji Rochjati (KSPR) — deteksi dini risiko kehamilan.
 *
 * Skor dihitung dari dua lapis:
 *  1. Faktor pada data ibu (umur, tinggi badan, riwayat obstetri, ...)
 *  2. Faktor yang muncul saat kunjungan (presentasi janin, protein urin, perdarahan)
 *
 * Kategori akhir:
 *  - KRR  : Kehamilan Risiko Rendah      (skor ≤ 4)
 *  - KRT  : Kehamilan Risiko Tinggi      (skor 6 – 10)
 *  - KRST : Kehamilan Risiko Sangat Tinggi (skor ≥ 12)
 */
class KsprService
{
    /** Faktor risiko yang dinilai dari data ibu. */
    public const FAKTOR_IBU = [
        'umur_muda'        => ['label' => 'Umur ≤ 16 tahun', 'skor' => 4],
        'umur_tua'         => ['label' => 'Umur ≥ 35 tahun', 'skor' => 4],
        'tb_pendek'        => ['label' => 'Tinggi badan ≤ 145 cm', 'skor' => 4],
        'jarak_dekat'      => ['label' => 'Jarak kehamilan ≤ 2 tahun', 'skor' => 4],
        'anak_banyak'      => ['label' => 'Jumlah anak ≥ 4', 'skor' => 4],
        'pernah_keguguran' => ['label' => 'Riwayat keguguran', 'skor' => 4],
        'lahir_tindakan'   => ['label' => 'Riwayat persalinan dengan tindakan', 'skor' => 4],
        'penyakit_berat'   => ['label' => 'Riwayat penyakit penyerta', 'skor' => 4],
        'pernah_sesar'     => ['label' => 'Riwayat operasi sesar', 'skor' => 8],
        'preeklamsia'      => ['label' => 'Riwayat preeklamsia/eklampsia', 'skor' => 8],
    ];

    /** Faktor risiko yang muncul pada saat kunjungan. */
    public const FAKTOR_KUNJUNGAN = [
        'sungsang'    => ['label' => 'Presentasi sungsang', 'skor' => 8],
        'lintang'     => ['label' => 'Presentasi lintang', 'skor' => 12],
        'proteinuria' => ['label' => 'Protein urin ≥ +2', 'skor' => 8],
        'perdarahan'  => ['label' => 'Perdarahan', 'skor' => 8],
    ];

    /** Hitung skor dari data ibu saja. */
    public function hitung(IbuHamil $ibu): array
    {
        return $this->susun($this->faktorIbu($ibu));
    }

    /** Hitung skor termasuk faktor dari satu kunjungan tertentu. */
    public function hitungDenganKunjungan(IbuHamil $ibu, ?KunjunganAnc $kunjungan = null): array
    {
        $faktor = $this->faktorIbu($ibu);

        if ($kunjungan) {
            $faktor = array_merge($faktor, $this->faktorKunjungan($kunjungan));
        }

        return $this->susun($faktor);
    }

    public function kategori(int $skor): string
    {
        return match (true) {
            $skor >= 12 => 'KRST',
            $skor >= 6  => 'KRT',
            default     => 'KRR',
        };
    }

    public function labelKategori(string $kategori): string
    {
        return match ($kategori) {
            'KRR'  => 'Risiko Rendah',
            'KRT'  => 'Risiko Tinggi',
            'KRST' => 'Risiko Sangat Tinggi',
            default => $kategori,
        };
    }

    /* ─────────────────────────────────────────── */

    private function faktorIbu(IbuHamil $ibu): array
    {
        $faktor = [];
        $umur   = $ibu->tanggal_lahir ? Carbon::parse($ibu->tanggal_lahir)->age : null;

        if ($umur !== null && $umur <= 16) {
            $faktor['umur_muda'] = true;
        }
        if ($umur !== null && $umur >= 35) {
            $faktor['umur_tua'] = true;
        }
        if ($ibu->tinggi_badan && (float) $ibu->tinggi_badan <= 145) {
            $faktor['tb_pendek'] = true;
        }
        if ($ibu->jarak_hamil_tahun !== null && (float) $ibu->jarak_hamil_tahun <= 2) {
            $faktor['jarak_dekat'] = true;
        }
        if ((int) $ibu->para >= 4) {
            $faktor['anak_banyak'] = true;
        }
        if ($ibu->pernah_keguguran) {
            $faktor['pernah_keguguran'] = true;
        }
        if ($ibu->pernah_lahir_tindakan) {
            $faktor['lahir_tindakan'] = true;
        }
        if ($ibu->pernah_sesar) {
            $faktor['pernah_sesar'] = true;
        }
        if ($ibu->pernah_preeklamsia) {
            $faktor['preeklamsia'] = true;
        }
        if ($this->punyaPenyakitBerat($ibu->riwayat_penyakit)) {
            $faktor['penyakit_berat'] = true;
        }

        return $faktor;
    }

    private function faktorKunjungan(KunjunganAnc $k): array
    {
        $faktor = [];

        if ($k->presentasi_janin === 'sungsang') {
            $faktor['sungsang'] = true;
        }
        if ($k->presentasi_janin === 'lintang') {
            $faktor['lintang'] = true;
        }
        if (in_array($k->protein_urin, ['+2', '+3', '+4'], true)) {
            $faktor['proteinuria'] = true;
        }
        if (in_array('perdarahan', (array) ($k->tanda_bahaya ?? []), true)) {
            $faktor['perdarahan'] = true;
        }

        return $faktor;
    }

    private function susun(array $faktorAktif): array
    {
        $daftar = [];
        $skor   = 0;

        foreach (self::FAKTOR_IBU as $kode => $meta) {
            if (isset($faktorAktif[$kode])) {
                $daftar[] = ['kode' => $kode, 'label' => $meta['label'], 'skor' => $meta['skor']];
                $skor += $meta['skor'];
            }
        }

        foreach (self::FAKTOR_KUNJUNGAN as $kode => $meta) {
            if (isset($faktorAktif[$kode])) {
                $daftar[] = ['kode' => $kode, 'label' => $meta['label'], 'skor' => $meta['skor']];
                $skor += $meta['skor'];
            }
        }

        return [
            'skor'     => $skor,
            'kategori' => $this->kategori($skor),
            'faktor'   => $daftar,
        ];
    }

    private function punyaPenyakitBerat(?string $riwayat): bool
    {
        if (! $riwayat) {
            return false;
        }

        $kataKunci = ['hipertensi', 'darah tinggi', 'diabetes', 'gula', 'jantung', 'asma', 'ginjal', 'epilepsi', 'tbc'];

        foreach ($kataKunci as $kata) {
            if (str_contains(mb_strtolower($riwayat), $kata)) {
                return true;
            }
        }

        return false;
    }
}
