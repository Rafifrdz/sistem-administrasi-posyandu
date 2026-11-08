<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\IbuHamil;
use App\Services\KehamilanService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class IbuHamilController extends Controller
{
    public function __construct(private KehamilanService $kehamilan) {}

    public function index(Request $request)
    {
        $kata   = $request->query('q');
        $filter = $request->query('filter');   // trimester1..3 | risiko
        $risiko = $request->query('risiko');   // KRR | KRT | KRST

        $query = IbuHamil::query()->with('kunjungan')->cari($kata);   // eager load: cegah N+1

        // Filter trimester memakai perbandingan tanggal HPHT
        if ($filter && str_starts_with($filter, 'tm')) {
            $tm = (int) substr($filter, 2);
            $batas12 = now()->subWeeks(12)->toDateString();
            $batas27 = now()->subWeeks(27)->toDateString();

            $query = match ($tm) {
                1 => $query->whereDate('hpht', '>', $batas12),
                2 => $query->whereDate('hpht', '<=', $batas12)->whereDate('hpht', '>', $batas27),
                3 => $query->whereDate('hpht', '<=', $batas27),
                default => $query,
            };
        }

        if ($risiko) {
            $query->where('kategori_risiko', $risiko);
        }

        $daftar = $query->orderBy('hpht')->paginate(12)->withQueryString();

        // Statistik ringkas
        $semua = IbuHamil::all();
        $statistik = [
            'total'    => $semua->count(),
            'aktif'    => $semua->where('status', 'aktif')->count(),
            'tm1'      => $semua->filter(fn ($i) => $i->trimester === 1)->count(),
            'tm2'      => $semua->filter(fn ($i) => $i->trimester === 2)->count(),
            'tm3'      => $semua->filter(fn ($i) => $i->trimester === 3)->count(),
            'risiko'   => $semua->whereIn('kategori_risiko', ['KRT', 'KRST'])->count(),
            'kek'      => $semua->filter(fn ($i) => $i->kek)->count(),
        ];

        // Data untuk modal detail & edit
        $ibuJson = $daftar->getCollection()->mapWithKeys(fn (IbuHamil $i) => [
            $i->id => [
                'id'               => $i->id,
                'nik'              => $i->nik,
                'nama'             => $i->nama,
                'tanggal_lahir'    => optional($i->tanggal_lahir)->format('Y-m-d'),
                'umur'             => $i->umur,
                'alamat'           => $i->alamat,
                'no_hp'            => $i->no_hp,
                'nama_suami'       => $i->nama_suami,
                'no_hp_suami'      => $i->no_hp_suami,
                'golongan_darah'   => $i->golongan_darah,
                'hpht'             => optional($i->hpht)->format('Y-m-d'),
                'hpl'              => $i->hpl_label,
                'gravida'          => $i->gravida,
                'para'             => $i->para,
                'abortus'          => $i->abortus,
                'gpa'              => $i->gpa,
                'status'           => $i->status,
                'status_label'     => $i->status_label,
                'tinggi_badan'     => $i->tinggi_badan,
                'berat_pra_hamil'  => $i->berat_pra_hamil,
                'imt'              => $i->imt_pra_hamil,
                'imt_kategori'     => $i->imt_kategori,
                'lila'             => $i->lila_pra_hamil,
                'kek'              => $i->kek,
                'pernah_sesar'     => (bool) $i->pernah_sesar,
                'pernah_keguguran' => (bool) $i->pernah_keguguran,
                'pernah_preeklamsia' => (bool) $i->pernah_preeklamsia,
                'pernah_lahir_tindakan' => (bool) $i->pernah_lahir_tindakan,
                'jarak_hamil_tahun' => $i->jarak_hamil_tahun,
                'riwayat_penyakit' => $i->riwayat_penyakit,
                'usia_kehamilan'   => $i->usia_kehamilan,
                'usia_label'       => $i->usia_kehamilan_label,
                'trimester'        => $i->trimester,
                'skor_kspr'        => $i->skor_kspr,
                'kategori_risiko'  => $i->kategori_risiko,
                'risiko_label'     => $i->kategori_risiko_label,
                'faktor_risiko'    => $i->faktor_risiko,
                'jumlah_kunjungan' => $i->jumlah_kunjungan,
                'status_anc_label' => $i->status_anc_label,
                'terlambat_anc'    => $i->terlambat_anc,
            ],
        ]);

        return view('ibu-hamil.index', compact('daftar', 'statistik', 'ibuJson', 'kata', 'filter', 'risiko'));
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);

        $ibu = IbuHamil::create($data);

        $this->catat('ibu_tambah', 'Menambahkan data ibu hamil', $ibu->nama . ' • ' . $ibu->usia_kehamilan_label);

        return redirect()->route('ibu-hamil.index')
            ->with('sukses', "Data {$ibu->nama} berhasil ditambahkan.");
    }

    public function show(IbuHamil $ibu)
    {
        $ibu->load(['kunjungan.kegiatan', 'kunjungan.petugas']);

        $kunjungan = $ibu->kunjungan()->reorder()->orderByDesc('tanggal')->get();

        // Data grafik kenaikan berat badan
        $grafik = $kunjungan->sortBy('tanggal')->map(fn ($k) => [
            'minggu' => $k->usia_kehamilan_minggu,
            'bb'     => (float) $k->berat_badan,
        ])->values();

        $anjuran = $this->kehamilan->kenaikanBbAnjuran($ibu->imt_pra_hamil);
        $statusAnc = $this->kehamilan->statusAncPerTrimester($ibu->anc_per_trimester);
        $kspr = app(\App\Services\KsprService::class);
        $risikoKunjungan = $kunjungan->first()
            ? $kspr->hitungDenganKunjungan($ibu, $kunjungan->first())
            : $kspr->hitung($ibu);

        return view('ibu-hamil.show', compact('ibu', 'kunjungan', 'grafik', 'anjuran', 'statusAnc', 'risikoKunjungan'));
    }

    public function update(Request $request, IbuHamil $ibu)
    {
        $data = $this->validasi($request, $ibu->id);

        $ibu->update($data);

        $this->catat('ibu_update', 'Memperbarui data ibu hamil', $ibu->nama);

        return redirect()->route('ibu-hamil.index')
            ->with('sukses', "Data {$ibu->nama} berhasil diperbarui.");
    }

    public function destroy(IbuHamil $ibu)
    {
        $nama = $ibu->nama;
        $ibu->delete();

        $this->catat('ibu_hapus', 'Menghapus data ibu hamil', $nama);

        return redirect()->route('ibu-hamil.index')
            ->with('sukses', "Data {$nama} beserta riwayat kunjungannya telah dihapus.");
    }

    /* ─────────────────────────────────────────── */

    private function validasi(Request $request, ?int $abaikanId = null): array
    {
        return $request->validate([
            'nik'             => ['required', 'digits:16', Rule::unique('ibu_hamil', 'nik')->ignore($abaikanId)],
            'nama'            => ['required', 'string', 'max:100'],
            'tanggal_lahir'   => ['required', 'date', 'before:today'],
            'alamat'          => ['required', 'string', 'max:255'],
            'no_hp'           => ['nullable', 'string', 'max:20'],
            'nama_suami'      => ['nullable', 'string', 'max:100'],
            'no_hp_suami'     => ['nullable', 'string', 'max:20'],
            'golongan_darah'  => ['required', 'in:A,B,AB,O,-'],
            'hpht'            => ['required', 'date', 'before_or_equal:today'],
            'gravida'         => ['required', 'integer', 'min:1', 'max:20'],
            'para'            => ['required', 'integer', 'min:0', 'max:20'],
            'abortus'         => ['required', 'integer', 'min:0', 'max:20'],
            'status'          => ['required', 'in:aktif,selesai,rujuk'],
            'tinggi_badan'    => ['required', 'numeric', 'min:100', 'max:200'],
            'berat_pra_hamil' => ['required', 'numeric', 'min:25', 'max:150'],
            'lila_pra_hamil'  => ['nullable', 'numeric', 'min:10', 'max:50'],
            'jarak_hamil_tahun' => ['nullable', 'numeric', 'min:0', 'max:30'],
            'riwayat_penyakit'  => ['nullable', 'string', 'max:255'],
            'pernah_sesar'          => ['nullable', 'boolean'],
            'pernah_keguguran'      => ['nullable', 'boolean'],
            'pernah_preeklamsia'    => ['nullable', 'boolean'],
            'pernah_lahir_tindakan' => ['nullable', 'boolean'],
        ], [
            'nik.digits'      => 'NIK harus tepat 16 digit angka.',
            'nik.unique'      => 'NIK ini sudah terdaftar pada data ibu hamil lain.',
            'hpht.before_or_equal' => 'Tanggal HPHT tidak boleh melewati hari ini.',
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
