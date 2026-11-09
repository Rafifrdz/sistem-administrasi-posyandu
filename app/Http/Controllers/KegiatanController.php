<?php

namespace App\Http\Controllers;

use App\Models\{Kegiatan, ActivityLog};
use Illuminate\Http\Request;

class KegiatanController extends Controller
{
    public function index(Request $request)
    {
        $q = Kegiatan::query();

        if ($request->filled('search')) {
            $s = $request->search;
            $q->where(function ($w) use ($s) {
                $w->where('nama', 'like', "%{$s}%")->orWhere('lokasi', 'like', "%{$s}%");
            });
        }
        if ($request->filled('status') && $request->status !== 'semua') {
            $q->where('status', $request->status);
        }
        if ($request->filled('kategori') && $request->kategori !== 'semua') {
            $q->where('kategori', $request->kategori);
        }

        $kegiatan = $q->orderBy('tanggal', 'desc')->paginate(20)->withQueryString();

        $totalKegiatan = Kegiatan::count();
        $terjadwal     = Kegiatan::where('status', 'terjadwal')->count();
        $selesai       = Kegiatan::where('status', 'selesai')->count();
        $dibatalkan    = Kegiatan::where('status', 'dibatalkan')->count();

        $kegiatanJson = $kegiatan->getCollection()->map(fn($k) => [
            'id'           => $k->id,
            'nama'         => $k->nama,
            'tanggal'      => $k->tanggal ? $k->tanggal->format('Y-m-d') : '',
            'tanggal_fmt'  => $k->tanggal ? $k->tanggal->translatedFormat('l, d F Y') : '',
            'jam_mulai'    => $k->jam_mulai ? substr($k->jam_mulai, 0, 5) : '',
            'jam_selesai'  => $k->jam_selesai ? substr($k->jam_selesai, 0, 5) : '',
            'lokasi'       => $k->lokasi,
            'kategori'     => $k->kategori,
            'kategori_lbl' => ucfirst(str_replace('_', ' ', $k->kategori)),
            'status'       => $k->status,
            'status_lbl'   => $k->status_label,
            'keterangan'   => $k->keterangan,
            'jumlah'       => $k->kehadiran()->count(),
        ])->values();

        return view('kegiatan.index', compact(
            'kegiatan', 'totalKegiatan', 'terjadwal', 'selesai', 'dibatalkan', 'kegiatanJson'
        ));
    }

    public function create()
    {
        return view('kegiatan.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama'        => 'required|string|max:255',
            'tanggal'     => 'required|date',
            'jam_mulai'   => 'required',
            'jam_selesai' => 'required|after:jam_mulai',
            'lokasi'      => 'required|string|max:255',
            'kategori'    => 'required|in:balita,ibu_hamil,lansia,umum',
            'keterangan'  => 'nullable|string',
        ], [
            'jam_selesai.after' => 'Jam selesai harus lebih dari jam mulai.',
        ]);

        $kegiatan = Kegiatan::create($data);

        ActivityLog::create([
            'type'      => 'kegiatan_tambah',
            'deskripsi' => "{$kegiatan->nama} dijadwalkan",
            'meta'      => $kegiatan->tanggal->format('d F Y') . ' • oleh ' . (auth()->user()?->name ?? 'Sistem'),
            'waktu'     => now()->format('H.i'),
        ]);

        return redirect()->route('kegiatan.index')->with('success', 'Kegiatan berhasil ditambahkan.');
    }

    public function show(Kegiatan $kegiatan)
    {
        $kehadiran = $kegiatan->kehadiran()->with('peserta')->get();
        return view('kegiatan.show', compact('kegiatan', 'kehadiran'));
    }

    public function edit(Kegiatan $kegiatan)
    {
        return view('kegiatan.edit', compact('kegiatan'));
    }

    public function update(Request $request, Kegiatan $kegiatan)
    {
        $data = $request->validate([
            'nama'        => 'required|string|max:255',
            'tanggal'     => 'required|date',
            'jam_mulai'   => 'required',
            'jam_selesai' => 'required|after:jam_mulai',
            'lokasi'      => 'required|string|max:255',
            'kategori'    => 'required|in:balita,ibu_hamil,lansia,umum',
            'status'      => 'required|in:terjadwal,selesai,dibatalkan',
            'keterangan'  => 'nullable|string',
        ], [
            'jam_selesai.after' => 'Jam selesai harus lebih dari jam mulai.',
        ]);

        $kegiatan->update($data);

        ActivityLog::create([
            'type'      => 'kegiatan_update',
            'deskripsi' => "Jadwal {$kegiatan->nama} diperbarui",
            'meta'      => $kegiatan->tanggal->format('d F Y') . ' • oleh ' . (auth()->user()?->name ?? 'Sistem'),
            'waktu'     => now()->format('H.i'),
        ]);

        return redirect()->route('kegiatan.index')->with('success', 'Kegiatan berhasil diperbarui.');
    }

    public function destroy(Kegiatan $kegiatan)
    {
        $nama = $kegiatan->nama;
        $kegiatan->delete();

        ActivityLog::create([
            'type'      => 'kegiatan_hapus',
            'deskripsi' => "Kegiatan {$nama} dihapus",
            'meta'      => 'oleh ' . (auth()->user()?->name ?? 'Sistem'),
            'waktu'     => now()->format('H.i'),
        ]);

        return redirect()->route('kegiatan.index')->with('success', "Kegiatan {$nama} berhasil dihapus.");
    }
}
