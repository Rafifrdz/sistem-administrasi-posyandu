<?php
namespace App\Http\Controllers;
use App\Models\{Peserta, Kegiatan, Kehadiran, ActivityLog};
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class DashboardController extends Controller {
    public function index() {
        $totalPeserta = Peserta::count();
        $balita = Peserta::where('kategori','balita')->count();
        $ibuHamil = Peserta::where('kategori','ibu_hamil')->count();
        $lansia = Peserta::where('kategori','lansia')->count();
        // Statistik kehadiran 6 bulan terakhir
        $bulanLabels = [];
        $bulanData = [];
        for ($i = 5; $i >= 0; $i--) {
            $bulan = Carbon::now()->subMonths($i);
            $bulanLabels[] = $bulan->translatedFormat('M');
            $bulanData[] = Kehadiran::whereHas('kegiatan', fn($q) => $q->whereYear('tanggal', $bulan->year)->whereMonth('tanggal', $bulan->month))->where('status','hadir')->count();
        }
        $bulanIni = Carbon::now();
        $hadir = end($bulanData);
        $bulanLalu = $bulanData[count($bulanData)-2] ?? 0;
        $persen = $totalPeserta > 0 ? round(($hadir / max($totalPeserta,1)) * 100, 1) : 0;
        $selisih = $bulanLalu > 0 ? round((($hadir - $bulanLalu) / $bulanLalu) * 100, 1) : 0;
        $kegiatanTerdekat = Kegiatan::where('tanggal','>=', Carbon::today())->where('status','terjadwal')->orderBy('tanggal')->take(3)->get();
        $aktivitas = ActivityLog::latest()->take(5)->get();
        return view('dashboard', compact('totalPeserta','balita','ibuHamil','lansia','bulanLabels','bulanData','hadir','persen','selisih','kegiatanTerdekat','aktivitas','bulanIni'));
    }
}
