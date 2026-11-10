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
        // Bulan berjalan: untuk tahun ini cukup sampai bulan sekarang,
        // supaya tidak muncul baris bulan depan yang masih nol.
        $bulanAkhir = $tahun === (int) now()->year ? (int) now()->month : 12;

        // Satu query untuk seluruh tahun. Rekap bulanan dan cakupan K1–K6
        // dihitung dari koleksi yang sama, bukan 78 query terpisah.
        $kunjunganTahun = KunjunganAnc::whereYear('tanggal', $tahun)->get();

        $bulanan = [];
        for ($b = 1; $b <= $bulanAkhir; $b++) {
            $baris = $kunjunganTahun->filter(fn ($k) => (int) $k->tanggal->month === $b);

            $bulanan[] = [
                'bulan'      => Carbon::create($tahun, $b, 1)->translatedFormat('M'),
                'kunjungan'  => $baris->count(),
                'ibu'        => $baris->pluck('ibu_hamil_id')->unique()->count(),
                'k1'         => $baris->where('kunjungan_ke', 1)->count(),
                'hipertensi' => $baris->filter(fn ($k) => $k->hipertensi)->count(),
                'anemia'     => $baris->filter(fn ($k) => $k->anemia)->count(),
                'dirujuk'    => $baris->where('status_rujukan', '!=', 'tidak')->count(),
            ];
        }

        /* ── Cakupan kumulatif K1–K6 (tahun terpilih) ─ */
        $cakupan = [];
        for ($k = 1; $k <= 6; $k++) {
            $cakupan[$k] = $kunjunganTahun->where('kunjungan_ke', $k)
                ->pluck('ibu_hamil_id')
                ->unique()
                ->count();
        }

        /* ── Distribusi risiko ───────────────────── */
        $semua = IbuHamil::with('kunjungan')->get();   // eager load: cegah N+1
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
        // $kunjunganTahun sudah diambil di atas (satu query untuk seluruh tahun).
        // Jumlah ibu unik sepanjang tahun — BUKAN penjumlahan angka bulanan,
        // karena satu ibu bisa berkunjung di beberapa bulan.
        $ibuUnikTahun = $kunjunganTahun->pluck('ibu_hamil_id')->unique()->count();

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
            'risikoTinggi', 'rekapKlinis', 'perTrimester', 'ibuUnikTahun'
        ));
    }
}
