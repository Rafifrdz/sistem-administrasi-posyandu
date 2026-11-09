@extends('layouts.app')
@section('title','Detail Kegiatan')
@section('breadcrumb','Detail Kegiatan')
@section('nav-active-kegiatan','active')

@section('content')
<div class="page-heading">
    <div class="title-group">
        <h1>{{ $kegiatan->nama }}</h1>
        <p>{{ $kegiatan->tanggal->translatedFormat('l, d F Y') }} • {{ substr($kegiatan->jam_mulai,0,5) }}–{{ substr($kegiatan->jam_selesai,0,5) }} WIB</p>
    </div>
    <div style="display:flex;gap:10px;">
        <a href="{{ route('kegiatan.edit',$kegiatan) }}" class="btn-secondary">Edit</a>
        <a href="{{ route('kehadiran.create') }}" class="btn-primary">Catat kehadiran</a>
    </div>
</div>

<div style="display:flex;gap:24px;align-items:flex-start;">
    <div style="flex:1;display:flex;flex-direction:column;gap:18px;">
        <div class="card">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                @foreach([['Lokasi',$kegiatan->lokasi],['Kategori',ucfirst(str_replace('_',' ',$kegiatan->kategori))],['Status',$kegiatan->status_label],['Jumlah hadir',$kegiatan->jumlah_peserta.' peserta'],['Keterangan',$kegiatan->keterangan ?: '—']] as [$l,$v])
                <div style="display:flex;flex-direction:column;gap:4px;">
                    <span style="font-size:12px;font-weight:500;color:#a5b1a8;text-transform:uppercase;letter-spacing:0.03em;">{{ $l }}</span>
                    <span style="font-size:14px;color:#233c32;">{{ $v }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <div class="card" style="padding:0;overflow:hidden;">
            <div style="padding:20px 24px;border-bottom:1px solid #e4eae5;font-size:16px;font-weight:600;color:#233c32;">
                Daftar kehadiran
            </div>
            @if($kehadiran->isEmpty())
            <div style="padding:40px;text-align:center;color:#758179;font-size:13px;">
                Belum ada data kehadiran untuk kegiatan ini.
                <div style="margin-top:12px;"><a href="{{ route('kehadiran.create') }}" class="btn-primary">Catat kehadiran</a></div>
            </div>
            @else
            <table>
                <thead>
                    <tr><th>Peserta</th><th>Kategori</th><th>Status</th><th>Catatan</th></tr>
                </thead>
                <tbody>
                    @foreach($kehadiran as $kh)
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div class="avatar" style="width:28px;height:28px;font-size:10px;flex-shrink:0;">{{ strtoupper(substr($kh->peserta->nama,0,2)) }}</div>
                                <span>{{ $kh->peserta->nama }}</span>
                            </div>
                        </td>
                        <td><span class="badge badge-green">{{ $kh->peserta->kategori_label }}</span></td>
                        <td><span class="badge {{ $kh->status==='hadir'?'badge-green':'badge-red' }}">{{ ucfirst(str_replace('_',' ',$kh->status)) }}</span></td>
                        <td style="font-size:12px;color:#758179;">{{ $kh->catatan ?: '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>

    <div style="width:260px;flex-shrink:0;">
        <div class="card">
            <div style="font-size:14px;font-weight:600;color:#233c32;margin-bottom:12px;">Aksi cepat</div>
            <div style="display:flex;flex-direction:column;gap:8px;">
                <a href="{{ route('kehadiran.create') }}" class="btn-primary" style="width:100%;justify-content:center;">Catat kehadiran</a>
                <a href="{{ route('kegiatan.edit',$kegiatan) }}" class="btn-secondary" style="width:100%;justify-content:center;">Edit kegiatan</a>
                <form method="POST" action="{{ route('kegiatan.destroy',$kegiatan) }}" onsubmit="return confirm('Hapus kegiatan ini?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-danger" style="width:100%;justify-content:center;">Hapus kegiatan</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
