<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Kegiatan;
use Illuminate\Http\Request;

class KegiatanController extends Controller
{
    public function index(Request $request)
    {
        $jenis  = $request->query('jenis');
        $status = $request->query('status');

        $query = Kegiatan::query()->orderByDesc('tanggal');

        if ($jenis) {
            $query->where('jenis', $jenis);
        }
        if ($status) {
            $query->where('status', $status);
        }

        $daftar = $query->paginate(12)->withQueryString();

        $statistik = [
            'terjadwal' => Kegiatan::where('status', 'terjadwal')->count(),
            'selesai'   => Kegiatan::where('status', 'selesai')->count(),
            'mendatang' => Kegiatan::mendatang()->count(),
            'kelas'     => Kegiatan::where('jenis', 'kelas_ibu_hamil')->count(),
        ];

        $kegiatanJson = $daftar->getCollection()->mapWithKeys(fn (Kegiatan $k) => [
            $k->id => [
                'id'         => $k->id,
                'nama'       => $k->nama,
                'tanggal'    => optional($k->tanggal)->format('Y-m-d'),
                'tanggal_label' => $k->tanggal_label,
                'jam_mulai'  => substr((string) $k->jam_mulai, 0, 5),
                'jam_selesai' => substr((string) $k->jam_selesai, 0, 5),
                'jam'        => $k->jam_label,
                'lokasi'     => $k->lokasi,
                'jenis'      => $k->jenis,
                'jenis_label' => $k->jenis_label,
                'trimester_target' => $k->trimester_target,
                'trimester_label'  => $k->trimester_label,
                'status'     => $k->status,
                'status_label' => $k->status_label,
                'keterangan' => $k->keterangan,
                'jumlah_kunjungan' => $k->kunjungan()->count(),
            ],
        ]);

        return view('kegiatan.index', compact('daftar', 'statistik', 'jenis', 'status', 'kegiatanJson'));
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);
        $kegiatan = Kegiatan::create($data);

        $this->catat('kegiatan_tambah', 'Menambahkan jadwal kegiatan', $kegiatan->nama);

        return redirect()->route('kegiatan.index')
            ->with('sukses', "Jadwal \"{$kegiatan->nama}\" berhasil ditambahkan.");
    }

    public function update(Request $request, Kegiatan $kegiatan)
    {
        $data = $this->validasi($request);
        $kegiatan->update($data);

        $this->catat('kegiatan_update', 'Memperbarui jadwal kegiatan', $kegiatan->nama);

        return redirect()->route('kegiatan.index')
            ->with('sukses', "Jadwal \"{$kegiatan->nama}\" berhasil diperbarui.");
    }

    public function destroy(Kegiatan $kegiatan)
    {
        $nama = $kegiatan->nama;
        $kegiatan->delete();

        $this->catat('kegiatan_hapus', 'Menghapus jadwal kegiatan', $nama);

        return redirect()->route('kegiatan.index')
            ->with('sukses', "Jadwal \"{$nama}\" telah dihapus.");
    }

    private function validasi(Request $request): array
    {
        return $request->validate([
            'nama'             => ['required', 'string', 'max:150'],
            'tanggal'          => ['required', 'date'],
            'jam_mulai'        => ['required', 'date_format:H:i'],
            'jam_selesai'      => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'lokasi'           => ['required', 'string', 'max:150'],
            'jenis'            => ['required', 'in:posyandu_rutin,kelas_ibu_hamil,kunjungan_rumah,usg,skrining'],
            'trimester_target' => ['required', 'in:semua,1,2,3'],
            'status'           => ['required', 'in:terjadwal,selesai,dibatalkan'],
            'keterangan'       => ['nullable', 'string', 'max:255'],
        ], [
            'jam_selesai.after' => 'Jam selesai harus lebih besar daripada jam mulai.',
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
