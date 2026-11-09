<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\IbuHamil;
use App\Models\Kegiatan;
use App\Models\KunjunganAnc;
use App\Services\KehamilanService;
use App\Services\KsprService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class KunjunganAncController extends Controller
{
    public function __construct(
        private KehamilanService $kehamilan,
        private KsprService $kspr,
    ) {}

    public function index(Request $request)
    {
        $periode = $request->query('periode', 'bulan');
        $ibuId   = $request->query('ibu');

        $query = KunjunganAnc::with(['ibuHamil', 'petugas'])->orderByDesc('tanggal');

        $query = match ($periode) {
            '3bulan' => $query->where('tanggal', '>=', now()->subMonths(3)->toDateString()),
            'semua'  => $query,
            default  => $query->whereMonth('tanggal', now()->month)->whereYear('tanggal', now()->year),
        };

        if ($ibuId) {
            $query->where('ibu_hamil_id', $ibuId);
        }

        $daftar = $query->paginate(15)->withQueryString();

        $statistik = [
            'total'      => KunjunganAnc::whereMonth('tanggal', now()->month)->whereYear('tanggal', now()->year)->count(),
            'hipertensi' => KunjunganAnc::whereMonth('tanggal', now()->month)->whereYear('tanggal', now()->year)
                                ->where(fn ($q) => $q->where('tensi_sistolik', '>=', 140)->orWhere('tensi_diastolik', '>=', 90))->count(),
            'anemia'     => KunjunganAnc::whereMonth('tanggal', now()->month)->whereYear('tanggal', now()->year)
                                ->where('hb', '<', 11)->count(),
            'dirujuk'    => KunjunganAnc::whereMonth('tanggal', now()->month)->whereYear('tanggal', now()->year)
                                ->where('status_rujukan', '!=', 'tidak')->count(),
        ];

        $ibuList = IbuHamil::aktif()->orderBy('nama')->get(['id', 'nama', 'hpht']);
        $kegiatanList = Kegiatan::terjadwal()->orderBy('tanggal')->get(['id', 'nama', 'tanggal']);

        // Data untuk modal detail/ubah
        $kunjunganJson = $daftar->getCollection()->mapWithKeys(fn (KunjunganAnc $k) => [
            $k->id => [
                'id'              => $k->id,
                'ibu'             => $k->ibuHamil?->nama,
                'nik'             => $k->ibuHamil?->nik,
                'label'           => $k->label_kunjungan,
                'tanggal'         => optional($k->tanggal)->format('Y-m-d'),
                'tanggal_label'   => $k->tanggal_label,
                'usia'            => $k->usia_kehamilan_minggu,
                'trimester'       => $k->trimester,
                'berat_badan'     => $k->berat_badan,
                'lila'            => $k->lila,
                'tensi'           => $k->tensi_label,
                'tensi_sistolik'  => $k->tensi_sistolik,
                'tensi_diastolik' => $k->tensi_diastolik,
                'tfu'             => $k->tfu,
                'djj'             => $k->djj,
                'presentasi'      => $k->presentasi_label,
                'hb'              => $k->hb,
                'protein_urin'    => $k->protein_urin,
                'tablet_fe'       => $k->tablet_fe,
                'imunisasi_tt'    => $k->imunisasi_tt,
                'keluhan'         => $k->keluhan,
                'diagnosa'        => $k->diagnosa,
                'rujukan'         => $k->status_rujukan_label,
                'petugas'         => $k->petugas?->name,
                'peringatan'      => $k->peringatan,
                'tanda_bahaya'    => $k->tanda_bahaya_label,
            ],
        ]);

        return view('kunjungan.index', compact(
            'daftar', 'statistik', 'periode', 'ibuId', 'ibuList', 'kegiatanList', 'kunjunganJson'
        ));
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);

        $ibu   = IbuHamil::findOrFail($data['ibu_hamil_id']);
        $tanggal = Carbon::parse($data['tanggal']);

        $data['kunjungan_ke']          = $ibu->kunjungan()->count() + 1;
        $data['usia_kehamilan_minggu'] = $this->kehamilan->usiaKehamilan($ibu->hpht, $tanggal);
        $data['trimester']             = $this->kehamilan->trimester($data['usia_kehamilan_minggu']);
        $data['petugas_id']            = auth()->id();
        $data['tanda_bahaya']          = $request->input('tanda_bahaya', []);

        $kunjungan = KunjunganAnc::create($data);

        $this->catat('kunjungan_simpan', 'Menyimpan hasil kunjungan ANC',
            "{$ibu->nama} • K{$kunjungan->kunjungan_ke} • {$kunjungan->usia_kehamilan_minggu} minggu");

        return redirect()->route('kunjungan.index')
            ->with('sukses', "Kunjungan K{$kunjungan->kunjungan_ke} untuk {$ibu->nama} berhasil disimpan.");
    }

    public function update(Request $request, KunjunganAnc $kunjungan)
    {
        $data = $this->validasi($request, true);

        $data['tanda_bahaya'] = $request->input('tanda_bahaya', []);
        $kunjungan->update($data);

        $this->catat('kunjungan_update', 'Memperbarui hasil kunjungan ANC',
            ($kunjungan->ibuHamil?->nama ?? '-') . " • K{$kunjungan->kunjungan_ke}");

        return redirect()->route('kunjungan.index')
            ->with('sukses', 'Data kunjungan berhasil diperbarui.');
    }

    public function destroy(KunjunganAnc $kunjungan)
    {
        $nama = $kunjungan->ibuHamil?->nama ?? '-';
        $ke   = $kunjungan->kunjungan_ke;
        $kunjungan->delete();

        $this->catat('kunjungan_hapus', 'Menghapus catatan kunjungan ANC', "{$nama} • K{$ke}");

        return redirect()->route('kunjungan.index')
            ->with('sukses', "Catatan kunjungan K{$ke} milik {$nama} telah dihapus.");
    }

    /* ─────────────────────────────────────────── */

    private function validasi(Request $request, bool $saatUbah = false): array
    {
        return $request->validate([
            'ibu_hamil_id'    => [$saatUbah ? 'nullable' : 'required', 'exists:ibu_hamil,id'],
            'kegiatan_id'     => ['nullable', 'exists:kegiatan,id'],
            'tanggal'         => ['required', 'date', 'before_or_equal:today'],
            'berat_badan'     => ['required', 'numeric', 'min:25', 'max:150'],
            'lila'            => ['nullable', 'numeric', 'min:10', 'max:50'],
            'tensi_sistolik'  => ['required', 'integer', 'min:70', 'max:250'],
            'tensi_diastolik' => ['required', 'integer', 'min:40', 'max:150'],
            'tfu'             => ['nullable', 'numeric', 'min:5', 'max:45'],
            'djj'             => ['nullable', 'integer', 'min:60', 'max:220'],
            'presentasi_janin' => ['required', 'in:kepala,sungsang,lintang,belum_tentu'],
            'hb'              => ['nullable', 'numeric', 'min:3', 'max:20'],
            'protein_urin'    => ['required', 'in:negatif,+1,+2,+3,+4'],
            'tablet_fe'       => ['nullable', 'integer', 'min:0', 'max:200'],
            'imunisasi_tt'    => ['required', 'in:tidak,TT1,TT2,TT3,TT4,TT5'],
            'keluhan'         => ['nullable', 'string', 'max:255'],
            'diagnosa'        => ['nullable', 'string', 'max:255'],
            'status_rujukan'  => ['required', 'in:tidak,puskesmas,rumah_sakit'],
        ], [
            'tensi_sistolik.required' => 'Tekanan darah sistolik wajib diisi.',
            'berat_badan.required'    => 'Berat badan wajib diisi.',
        ]);
    }

    private function catat(string $type, string $deskripsi, ?string $meta = null): void
    {
        ActivityLog::create([
            'type'      => $type,
            'deskripsi' => $deskripsi,
            'meta'      => $meta,
            'waktu'     => now()->format('H.i'),
        ]);
    }
}
