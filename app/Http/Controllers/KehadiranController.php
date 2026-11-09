<?php

namespace App\Http\Controllers;

use App\Models\{Kehadiran, Kegiatan, Peserta, ActivityLog};
use Illuminate\Http\Request;

class KehadiranController extends Controller
{
    public function index(Request $request)
    {
        $periode = $request->get('periode', 'bulan');

        $q = Kehadiran::query()->with(['peserta', 'kegiatan']);

        if ($periode === 'bulan') {
            $q->whereHas('kegiatan', fn($x) => $x
                ->whereMonth('tanggal', now()->month)
                ->whereYear('tanggal', now()->year));
        } elseif ($periode === '3bulan') {
            $q->whereHas('kegiatan', fn($x) => $x->where('tanggal', '>=', now()->subMonths(3)->startOfMonth()));
        }

        $kehadiranList = $q->latest()->paginate(30)->withQueryString();

        $kegiatan = Kegiatan::with(['kehadiran.peserta'])
            ->orderBy('tanggal', 'desc')
            ->get();

        $peserta = Peserta::orderBy('nama')->get();

        // Data untuk modal "catat kehadiran"
        $pesertaJson = $peserta->map(fn($p) => [
            'id'           => $p->id,
            'nama'         => $p->nama,
            'nik'          => $p->nik,
            'kategori'     => $p->kategori,
            'kategori_lbl' => $p->kategori_label,
        ])->values();

        // Data untuk modal detail per kegiatan
        $kegiatanJson = $kegiatan->map(function ($k) {
            $hadir = $k->kehadiran->where('status', 'hadir');
            $tidak = $k->kehadiran->where('status', '!=', 'hadir');
            return [
                'id'           => $k->id,
                'nama'         => $k->nama,
                'tanggal_fmt'  => $k->tanggal ? $k->tanggal->translatedFormat('l, d F Y') : '',
                'jam'          => substr($k->jam_mulai, 0, 5) . '–' . substr($k->jam_selesai, 0, 5) . ' WIB',
                'lokasi'       => $k->lokasi,
                'kategori'     => $k->kategori,
                'kategori_lbl' => ucfirst(str_replace('_', ' ', $k->kategori)),
                'status_lbl'   => $k->status_label,
                'hadir'        => $hadir->map(fn($h) => [
                    'nama'     => $h->peserta->nama,
                    'kategori' => $h->peserta->kategori_label,
                ])->values(),
                'tidak'        => $tidak->map(fn($h) => [
                    'nama'     => $h->peserta->nama,
                    'kategori' => $h->peserta->kategori_label,
                ])->values(),
                'n_hadir'      => $hadir->count(),
                'n_tidak'      => $tidak->count(),
            ];
        })->values();

        // Data untuk modal hapus
        $kehadiranJson = $kehadiranList->getCollection()->map(fn($h) => [
            'id'          => $h->id,
            'peserta'     => $h->peserta->nama,
            'peserta_nik' => $h->peserta->nik,
            'kategori'    => $h->peserta->kategori_label,
            'kegiatan'    => $h->kegiatan->nama,
            'tanggal_fmt' => $h->kegiatan->tanggal?->translatedFormat('l, d F Y'),
            'jam'         => substr($h->kegiatan->jam_mulai, 0, 5) . '–' . substr($h->kegiatan->jam_selesai, 0, 5) . ' WIB',
            'lokasi'      => $h->kegiatan->lokasi,
            'status'      => $h->status,
            'status_lbl'  => ucfirst(str_replace('_', ' ', $h->status)),
            'catatan'     => $h->catatan,
        ])->values();

        $totalHadirBulanIni = Kehadiran::where('status', 'hadir')
            ->whereHas('kegiatan', fn($x) => $x
                ->whereMonth('tanggal', now()->month)
                ->whereYear('tanggal', now()->year))
            ->count();

        $kegiatanBulanIni = $kegiatan->filter(fn($k) => $k->tanggal
            && $k->tanggal->month === now()->month
            && $k->tanggal->year === now()->year)->count();

        return view('kehadiran.index', compact(
            'kegiatan', 'kehadiranList', 'peserta', 'pesertaJson',
            'kegiatanJson', 'kehadiranJson', 'totalHadirBulanIni',
            'kegiatanBulanIni', 'periode'
        ));
    }

    public function create()
    {
        $kegiatan = Kegiatan::where('status', 'terjadwal')->orderBy('tanggal')->get();
        $peserta  = Peserta::orderBy('nama')->get();
        return view('kehadiran.create', compact('kegiatan', 'peserta'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kegiatan_id'   => 'required|exists:kegiatan,id',
            'peserta_ids'   => 'required|array|min:1',
            'peserta_ids.*' => 'exists:peserta,id',
        ], [
            'peserta_ids.required' => 'Pilih minimal satu peserta yang hadir.',
            'peserta_ids.min'      => 'Pilih minimal satu peserta yang hadir.',
        ]);

        $kegiatan = Kegiatan::findOrFail($data['kegiatan_id']);

        // Peserta yang relevan dengan kategori kegiatan (umum = semua)
        $target = $kegiatan->kategori === 'umum'
            ? Peserta::all()
            : Peserta::where('kategori', $kegiatan->kategori)->get();

        $dipilih = collect($data['peserta_ids'])->map(fn($v) => (int) $v);

        $hadir = 0;
        $tidak = 0;

        foreach ($target as $p) {
            $status = $dipilih->contains($p->id) ? 'hadir' : 'tidak_hadir';

            Kehadiran::updateOrCreate(
                ['peserta_id' => $p->id, 'kegiatan_id' => $kegiatan->id],
                ['status' => $status]
            );

            $status === 'hadir' ? $hadir++ : $tidak++;
        }

        ActivityLog::create([
            'type'      => 'kehadiran_simpan',
            'deskripsi' => "Kehadiran {$kegiatan->nama} disimpan",
            'meta'      => "{$hadir} hadir • {$tidak} tidak hadir",
            'waktu'     => now()->format('H.i'),
        ]);

        return redirect()->route('kehadiran.index')
            ->with('success', "Kehadiran {$kegiatan->nama} tersimpan — {$hadir} hadir, {$tidak} tidak hadir.");
    }

    public function show(Kegiatan $kegiatan)
    {
        $kehadiran = $kegiatan->kehadiran()->with('peserta')->get();
        return view('kehadiran.show', compact('kegiatan', 'kehadiran'));
    }

    /**
     * Halaman edit terpisah tidak dipakai (semua lewat modal),
     * tapi route resource tetap ada — arahkan balik ke index.
     */
    public function edit(Kehadiran $kehadiran)
    {
        return redirect()->route('kehadiran.index');
    }

    public function update(Request $request, Kehadiran $kehadiran)
    {
        $data = $request->validate([
            'status'  => 'required|in:hadir,tidak_hadir',
            'catatan' => 'nullable|string|max:255',
        ]);

        $kehadiran->update($data);

        ActivityLog::create([
            'type'      => 'kehadiran_update',
            'deskripsi' => "Kehadiran {$kehadiran->peserta->nama} diperbarui",
            'meta'      => "{$kehadiran->kegiatan->nama} • " . ucfirst(str_replace('_', ' ', $data['status'])),
            'waktu'     => now()->format('H.i'),
        ]);

        return redirect()->route('kehadiran.index')
            ->with('success', "Kehadiran {$kehadiran->peserta->nama} berhasil diperbarui.");
    }

    public function destroy(Kehadiran $kehadiran)
    {
        $nama = $kehadiran->peserta?->nama ?? 'Peserta';
        $kehadiran->delete();

        return redirect()->route('kehadiran.index')
            ->with('success', "Data kehadiran {$nama} berhasil dihapus.");
    }
}
