<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\IbuHamil;
use App\Models\Kegiatan;
use App\Models\KunjunganAnc;
use App\Services\KehamilanService;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct(private KehamilanService $kehamilan) {}

    public function index()
    {
        $semua = IbuHamil::all();

        /* ── Kartu ringkasan ─────────────────────── */
        $statistik = [
            'total'  => $semua->count(),
            'aktif'  => $semua->where('status', 'aktif')->count(),
            'tm1'    => $semua->filter(fn ($i) => $i->trimester === 1)->count(),
            'tm2'    => $semua->filter(fn ($i) => $i->trimester === 2)->count(),
            'tm3'    => $semua->filter(fn ($i) => $i->trimester === 3)->count(),
            'risiko' => $semua->whereIn('kategori_risiko', ['KRT', 'KRST'])->count(),
            'kek'    => $semua->filter(fn ($i) => $i->kek)->count(),
        ];

        /* ── Anemia dari kunjungan terakhir tiap ibu ─ */
        $anemia = KunjunganAnc::whereIn('id', function ($q) {
            $q->selectRaw('max(id)')->from('kunjungan_anc')->groupBy('ibu_hamil_id');
        })->where('hb', '<', 11)->count();

        $statistik['anemia'] = $anemia;

        /* ── Tren kunjungan 6 bulan terakhir ─────── */
        $tren = [];
        for ($i = 5; $i >= 0; $i--) {
            $bulan = now()->subMonths($i);
            $tren[] = [
                'label'  => $bulan->translatedFormat('M'),
                'jumlah' => KunjunganAnc::whereYear('tanggal', $bulan->year)
                                ->whereMonth('tanggal', $bulan->month)->count(),
            ];
        }
        $trenMaks = max(1, collect($tren)->max('jumlah'));

        /* ── Distribusi trimester (donat) ────────── */
        $donat = [
            ['label' => 'Trimester 1', 'nilai' => $statistik['tm1'], 'warna' => '#4f7561'],
            ['label' => 'Trimester 2', 'nilai' => $statistik['tm2'], 'warna' => '#7ba98c'],
            ['label' => 'Trimester 3', 'nilai' => $statistik['tm3'], 'warna' => '#c8a45c'],
        ];

        /* ── Ibu yang terlambat / belum ANC ──────── */
        $terlambat = $semua->filter(fn ($i) => $i->terlambat_anc)
            ->sortByDesc('usia_kehamilan')
            ->take(5)
            ->values();

        /* ── Ibu risiko tinggi ───────────────────── */
        $risikoTinggi = $semua->whereIn('kategori_risiko', ['KRT', 'KRST'])
            ->sortByDesc('skor_kspr')
            ->take(5)
            ->values();

        /* ── Kegiatan terdekat ───────────────────── */
        $kegiatan = Kegiatan::mendatang()->orderBy('tanggal')->take(4)->get();

        /* ── Aktivitas terbaru ───────────────────── */
        $log = ActivityLog::latest()->take(5)->get();

        return view('dashboard', compact(
            'statistik', 'tren', 'trenMaks', 'donat', 'terlambat', 'risikoTinggi', 'kegiatan', 'log'
        ));
    }
}
