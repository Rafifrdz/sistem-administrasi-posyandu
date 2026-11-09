@extends('layouts.app')
@section('title','Detail Peserta')
@section('breadcrumb','Detail Peserta')
@section('nav-active-peserta','active')

@section('content')
<div class="page-heading">
    <div class="title-group">
        <h1>{{ $peserta->nama }}</h1>
        <p>Detail informasi peserta posyandu.</p>
    </div>
    <div style="display:flex;gap:10px;">
        <a href="{{ route('peserta.edit',$peserta) }}" class="btn-secondary">Edit</a>
        <a href="{{ route('peserta.index') }}" class="btn-secondary">Kembali</a>
    </div>
</div>

<div style="display:flex;gap:24px;align-items:flex-start;">
    {{-- Detail kiri --}}
    <div style="flex:1;display:flex;flex-direction:column;gap:18px;">
        <div class="card">
            <div style="display:flex;gap:18px;align-items:center;margin-bottom:22px;">
                <div class="avatar" style="width:60px;height:60px;font-size:22px;">{{ strtoupper(substr($peserta->nama,0,2)) }}</div>
                <div>
                    <div style="font-size:20px;font-weight:600;color:#233c32;">{{ $peserta->nama }}</div>
                    <span class="badge badge-green" style="margin-top:5px;">{{ $peserta->kategori_label }}</span>
                </div>
            </div>
            <div class="divider" style="margin-bottom:22px;"></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px 24px;">
                @foreach([['NIK',$peserta->nik],['Jenis kelamin',$peserta->jenis_kelamin_label],['Tanggal lahir',\Carbon\Carbon::parse($peserta->tanggal_lahir)->translatedFormat('d F Y')],['Alamat',$peserta->alamat],['Nama wali',$peserta->nama_wali ?: '—'],['No. telepon',$peserta->no_telepon ?: '—']] as [$lbl,$val])
                <div style="display:flex;flex-direction:column;gap:4px;">
                    <span style="font-size:12px;font-weight:500;color:#a5b1a8;text-transform:uppercase;letter-spacing:0.03em;">{{ $lbl }}</span>
                    <span style="font-size:14px;color:#233c32;">{{ $val }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Riwayat kehadiran --}}
        <div class="card" style="padding:0;overflow:hidden;">
            <div style="padding:20px 24px;border-bottom:1px solid #e4eae5;">
                <div style="font-size:16px;font-weight:600;color:#233c32;">Riwayat kehadiran</div>
                <div style="font-size:13px;color:#758179;margin-top:3px;">10 kehadiran terakhir</div>
            </div>
            @if($kehadiran->isEmpty())
            <div style="padding:40px;text-align:center;color:#758179;font-size:13px;">Belum ada riwayat kehadiran.</div>
            @else
            <table>
                <thead>
                    <tr>
                        <th>Kegiatan</th><th>Tanggal</th><th>Status</th><th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($kehadiran as $kh)
                    <tr>
                        <td>{{ $kh->kegiatan->nama }}</td>
                        <td>{{ $kh->kegiatan->tanggal->translatedFormat('d M Y') }}</td>
                        <td>
                            <span class="badge {{ $kh->status==='hadir'?'badge-green':'badge-red' }}">{{ ucfirst(str_replace('_',' ',$kh->status)) }}</span>
                        </td>
                        <td style="font-size:12px;color:#758179;">{{ $kh->catatan ?: '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>

    {{-- Sidebar --}}
    <div style="width:280px;flex-shrink:0;display:flex;flex-direction:column;gap:14px;">
        <div class="card">
            <div style="font-size:14px;font-weight:600;color:#233c32;margin-bottom:12px;">Statistik kehadiran</div>
            @php
            $totalKg = $kehadiran->count();
            $hadirCount = $kehadiran->where('status','hadir')->count();
            $pct = $totalKg > 0 ? round($hadirCount/$totalKg*100) : 0;
            @endphp
            <div style="display:flex;flex-direction:column;gap:8px;">
                <div style="display:flex;justify-content:space-between;font-size:13px;"><span style="color:#758179;">Hadir</span><span style="font-weight:600;color:#4f7561;">{{ $hadirCount }}</span></div>
                <div style="display:flex;justify-content:space-between;font-size:13px;"><span style="color:#758179;">Tidak hadir</span><span style="font-weight:600;color:#c0392b;">{{ $totalKg - $hadirCount }}</span></div>
                <div style="height:6px;background:#f0f5f1;border-radius:9px;overflow:hidden;margin-top:6px;">
                    <div style="height:100%;width:{{ $pct }}%;background:#4f7561;border-radius:9px;"></div>
                </div>
                <span style="font-size:12px;color:#758179;text-align:right;">{{ $pct }}% tingkat kehadiran</span>
            </div>
        </div>
        <div class="card">
            <div style="font-size:14px;font-weight:600;color:#233c32;margin-bottom:12px;">Aksi cepat</div>
            <div style="display:flex;flex-direction:column;gap:8px;">
                <a href="{{ route('peserta.edit',$peserta) }}" class="btn-secondary" style="width:100%;justify-content:center;">Edit data</a>
                <form method="POST" action="{{ route('peserta.destroy',$peserta) }}" onsubmit="return confirm('Hapus peserta ini? Semua data kehadiran ikut terhapus.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-danger" style="width:100%;justify-content:center;">Hapus peserta</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
