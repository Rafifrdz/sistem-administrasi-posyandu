<?php
namespace App\Http\Controllers;
use App\Models\{Peserta, Kegiatan, Kehadiran};
use Carbon\Carbon;
class LaporanController extends Controller {
    public function index() {
        $year = now()->year;
        $bulanLabels = [];
        $bulanData = [];
        for ($i = 1; $i <= 12; $i++) {
            $bulanLabels[] = Carbon::create($year, $i)->translatedFormat('M');
            $bulanData[] = Kehadiran::whereHas('kegiatan', fn($q) => $q->whereYear('tanggal',$year)->whereMonth('tanggal',$i))->where('status','hadir')->count();
        }
        $totalPeserta = Peserta::count();
        $kegiatanSelesai = Kegiatan::where('status','selesai')->whereYear('tanggal',$year)->count();
        $rataKehadiran = $totalPeserta > 0 ? round(array_sum($bulanData) / max(array_sum(array_map(fn($v) => $totalPeserta, $bulanData)),1) * 100, 1) : 0;
        $perKategori = [
            'balita' => Peserta::where('kategori','balita')->count(),
            'ibu_hamil' => Peserta::where('kategori','ibu_hamil')->count(),
            'lansia' => Peserta::where('kategori','lansia')->count(),
        ];
        return view('laporan.index', compact('bulanLabels','bulanData','totalPeserta','kegiatanSelesai','rataKehadiran','perKategori','year'));
    }
}
