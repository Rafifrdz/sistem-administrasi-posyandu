<?php
namespace App\Http\Controllers;
use App\Models\{Peserta, ActivityLog};
use Illuminate\Http\Request;
class PesertaController extends Controller {
    public function index(Request $request) {
        $q = Peserta::query();
        if ($request->filled('search')) {
            $s = $request->search;
            $q->where(function ($w) use ($s) {
                $w->where('nama', 'like', "%{$s}%")->orWhere('nik', 'like', "%{$s}%");
            });
        }
        if ($request->filled('kategori') && $request->kategori !== 'semua') $q->where('kategori', $request->kategori);
        $peserta = $q->latest()->paginate(20)->withQueryString();
        $totalPeserta = Peserta::count();
        $balita = Peserta::where('kategori','balita')->count();
        $ibuHamil = Peserta::where('kategori','ibu_hamil')->count();
        $lansia = Peserta::where('kategori','lansia')->count();

        // Data untuk modal detail / edit (JSON)
        $pesertaJson = $peserta->getCollection()->map(fn($p) => [
            'id'                 => $p->id,
            'nama'               => $p->nama,
            'nik'                => $p->nik,
            'tanggal_lahir'      => $p->tanggal_lahir ? \Carbon\Carbon::parse($p->tanggal_lahir)->format('Y-m-d') : '',
            'jenis_kelamin'      => $p->jenis_kelamin,
            'jenis_kelamin_label'=> $p->jenis_kelamin_label,
            'kategori'           => $p->kategori,
            'kategori_label'     => $p->kategori_label,
            'usia_label'         => $p->usia_label,
            'alamat'             => $p->alamat,
            'nama_wali'          => $p->nama_wali,
            'no_telepon'         => $p->no_telepon,
            'created_at'         => $p->created_at?->translatedFormat('d F Y'),
        ])->values();

        return view('peserta.index', compact('peserta','totalPeserta','balita','ibuHamil','lansia','pesertaJson'));
    }
    public function create() { return view('peserta.create'); }
    public function store(Request $request) {
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'nik' => 'required|string|size:16|unique:peserta',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:L,P',
            'kategori' => 'required|in:balita,ibu_hamil,lansia',
            'alamat' => 'required|string',
            'nama_wali' => 'nullable|string|max:255',
            'no_telepon' => 'nullable|string|max:20',
        ]);
        $peserta = Peserta::create($data);
        ActivityLog::create(['type'=>'peserta_tambah','deskripsi'=>"{$peserta->nama} ditambahkan sebagai peserta",'meta'=>"Kategori {$peserta->kategori_label} • oleh " . (auth()->user()?->name ?? 'Sistem'),'waktu'=>now()->format('H.i')]);
        return redirect()->route('peserta.index')->with('success','Peserta berhasil ditambahkan.');
    }
    public function show(Peserta $peserta) {
        $kehadiran = $peserta->kehadiran()->with('kegiatan')->latest()->take(10)->get();
        return view('peserta.show', compact('peserta','kehadiran'));
    }
    public function edit(Peserta $peserta) { return view('peserta.edit', compact('peserta')); }
    public function update(Request $request, Peserta $peserta) {
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'nik' => 'required|string|size:16|unique:peserta,nik,'.$peserta->id,
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:L,P',
            'kategori' => 'required|in:balita,ibu_hamil,lansia',
            'alamat' => 'required|string',
            'nama_wali' => 'nullable|string|max:255',
            'no_telepon' => 'nullable|string|max:20',
        ]);
        $peserta->update($data);
        ActivityLog::create(['type'=>'peserta_edit','deskripsi'=>"Data {$peserta->nama} diperbarui",'meta'=>'oleh ' . (auth()->user()?->name ?? 'Sistem'),'waktu'=>now()->format('H.i')]);
        return redirect()->route('peserta.index')->with('success','Data peserta berhasil diperbarui.');
    }
    public function destroy(Peserta $peserta) {
        $nama = $peserta->nama;
        $peserta->delete();
        ActivityLog::create(['type'=>'peserta_hapus','deskripsi'=>"Peserta $nama dihapus",'meta'=>'oleh ' . (auth()->user()?->name ?? 'Sistem'),'waktu'=>now()->format('H.i')]);
        return redirect()->route('peserta.index')->with('success','Peserta berhasil dihapus.');
    }
}
