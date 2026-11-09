@extends('layouts.app')
@section('title','Kehadiran Kegiatan')
@section('breadcrumb','Kehadiran Kegiatan')
@section('nav-active-kehadiran','active')

@section('content')
<div class="page-heading">
    <div class="title-group">
        <h1>{{ $kegiatan->nama }}</h1>
        <p>Daftar kehadiran {{ $kegiatan->tanggal->translatedFormat('d F Y') }}</p>
    </div>
    <a href="{{ route('kehadiran.index') }}" class="btn-secondary">Kembali</a>
</div>

<div class="card" style="padding:0;overflow:hidden;">
    @if($kehadiran->isEmpty())
    <div class="empty-state">
        <div class="empty-icon"><img src="/icons/clipboard-check.svg" alt=""></div>
        <h2>Belum ada data kehadiran</h2>
        <a href="{{ route('kehadiran.create') }}" class="btn-primary">Catat kehadiran</a>
    </div>
    @else
    <table>
        <thead><tr><th>Peserta</th><th>Kategori</th><th>Status</th><th>Catatan</th></tr></thead>
        <tbody>
            @foreach($kehadiran as $kh)
            <tr>
                <td>
                    <div style="display:flex;align-items:center;gap:9px;">
                        <div class="avatar" style="width:28px;height:28px;font-size:10px;">{{ strtoupper(substr($kh->peserta->nama,0,2)) }}</div>
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
@endsection
