<?php

namespace App\Http\Controllers;

use App\Models\IbuHamil;
use App\Models\KunjunganAnc;
use App\Services\KehamilanService;
use Carbon\Carbon;

class LaporanController extends Controller
{
    public function __construct(private KehamilanService $kehamilan) {}

    public function index()
    {
        $tahun = (int) request()->query('tahun', now()->year);

        /* ── Cakupan ANC per bulan ───────────────── */
        $bulanan = [];
        for ($b = 1; $b <= 12; $b++) {
            $dasar = KunjunganAnc::whereYear('tanggal', $tahun)->whereMonth('tanggal', $b);

            $bulanan[] = [
                'bulan'      => Carbon::create($tahun, $b, 1)->translatedFormat('M'),
                'kunjungan'  => (clone $dasar)->count(),
                'ibu'        => (clone $dasar)->distinct('ibu_hamil_id')->count('ibu_hamil_id'),
                'k1'         => (clone $dasar)->where('kunjungan_ke', 1)->count(),
                'hipertensi' => (clone $dasar)->where(fn ($q) => $q->where('tensi_sistolik', '>=', 140)->orWhere('tensi_diastolik', '>=', 90))->count(),
                'anemia'     => (clone $dasar)->where('hb', '<', 11)->count(),
                'dirujuk'    => (clone $dasar)->where('status_rujukan', '!=', 'tidak')->count(),
            ];
        }

        /* ── Cakupan kumulatif K1–K6 (tahun terpilih) ─ */
        $cakupan = [];
        for ($k = 1; $k <= 6; $k++) {
            $cakupan[$k] = KunjunganAnc::whereYear('tanggal', $tahun)
                ->where('kunjungan_ke', $k)
                ->distinct('ibu_hamil_id')
                ->count('ibu_hamil_id');
        }

        /* ── Distribusi risiko ───────────────────── */
        $semua = IbuHamil::all();
        $distribusiRisiko = [
            'KRR'  => $semua->where('kategori_risiko', 'KRR')->count(),
            'KRT'  => $semua->where('kategori_risiko', 'KRT')->count(),
            'KRST' => $semua->where('kategori_risiko', 'KRST')->count(),
        ];

        /* ── Daftar ibu risiko tinggi + faktor ───── */
        $risikoTinggi = $semua->whereIn('kategori_risiko', ['KRT', 'KRST'])
            ->sortByDesc('skor_kspr')
            ->values();

        /* ── Rekap kondisi klinis ────────────────── */
        $kunjunganTahun = KunjunganAnc::whereYear('tanggal', $tahun)->get();

        $rekapKlinis = [
            'total_kunjungan' => $kunjunganTahun->count(),
            'hipertensi'      => $kunjunganTahun->filter(fn ($k) => $k->hipertensi)->count(),
            'anemia'          => $kunjunganTahun->filter(fn ($k) => $k->anemia)->count(),
            'kek'             => $kunjunganTahun->filter(fn ($k) => $k->kek)->count(),
            'djj_abnormal'    => $kunjunganTahun->filter(fn ($k) => $k->djj_abnormal)->count(),
            'dirujuk'         => $kunjunganTahun->where('status_rujukan', '!=', 'tidak')->count(),
        ];

        /* ── Sebaran per trimester ───────────────── */
        $perTrimester = [
            1 => $kunjunganTahun->where('trimester', 1)->count(),
            2 => $kunjunganTahun->where('trimester', 2)->count(),
            3 => $kunjunganTahun->where('trimester', 3)->count(),
        ];

        /* ── Daftar tahun tersedia ───────────────── */
        // Dihitung di PHP agar portabel (MySQL & SQLite saat pengujian)
        $tahunTersedia = KunjunganAnc::pluck('tanggal')
            ->map(fn ($t) => Carbon::parse($t)->year)
            ->unique()
            ->sortDesc()
            ->values()
            ->all();

        if (! in_array(now()->year, $tahunTersedia, true)) {
            $tahunTersedia[] = now()->year;
            rsort($tahunTersedia);
        }

        return view('laporan.index', compact(
            'tahun', 'tahunTersedia', 'bulanan', 'cakupan', 'distribusiRisiko',
            'risikoTinggi', 'rekapKlinis', 'perTrimester'
        ));
    }
}
