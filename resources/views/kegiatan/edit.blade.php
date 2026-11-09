@extends('layouts.app')
@section('title','Edit Kegiatan')
@section('breadcrumb','Edit Kegiatan')
@section('nav-active-kegiatan','active')

@section('content')
<div class="page-heading">
    <div class="title-group"><h1>Edit kegiatan</h1><p>Perbarui informasi kegiatan.</p></div>
    <a href="{{ route('kegiatan.index') }}" class="btn-secondary">Kembali</a>
</div>

<div class="card" style="max-width:720px;">
    <form method="POST" action="{{ route('kegiatan.update',$kegiatan) }}" style="display:flex;flex-direction:column;gap:22px;">
        @csrf @method('PUT')
        <div class="form-group">
            <label class="form-label">Nama kegiatan <span style="color:red">*</span></label>
            <input type="text" name="nama" value="{{ old('nama',$kegiatan->nama) }}" class="form-control">
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Tanggal</label>
                <input type="date" name="tanggal" value="{{ old('tanggal',$kegiatan->tanggal->format('Y-m-d')) }}" class="form-control">
            </div>
            <div class="form-group">
                <label class="form-label">Lokasi</label>
                <input type="text" name="lokasi" value="{{ old('lokasi',$kegiatan->lokasi) }}" class="form-control">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Jam mulai</label>
                <input type="time" name="jam_mulai" value="{{ old('jam_mulai',substr($kegiatan->jam_mulai,0,5)) }}" class="form-control">
            </div>
            <div class="form-group">
                <label class="form-label">Jam selesai</label>
                <input type="time" name="jam_selesai" value="{{ old('jam_selesai',substr($kegiatan->jam_selesai,0,5)) }}" class="form-control">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Kategori</label>
                <select name="kategori" class="form-control">
                    @foreach(['balita'=>'Balita','ibu_hamil'=>'Ibu Hamil','lansia'=>'Lansia','umum'=>'Umum'] as $v=>$l)
                    <option value="{{ $v }}" {{ old('kategori',$kegiatan->kategori)==$v?'selected':'' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                    @foreach(['terjadwal'=>'Terjadwal','selesai'=>'Selesai','dibatalkan'=>'Dibatalkan'] as $v=>$l)
                    <option value="{{ $v }}" {{ old('status',$kegiatan->status)==$v?'selected':'' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Keterangan</label>
            <textarea name="keterangan" class="form-control" rows="3">{{ old('keterangan',$kegiatan->keterangan) }}</textarea>
        </div>
        <div class="divider"></div>
        <div style="display:flex;gap:12px;">
            <button type="submit" class="btn-primary">Perbarui kegiatan</button>
            <a href="{{ route('kegiatan.index') }}" class="btn-secondary">Batal</a>
        </div>
    </form>
</div>
@endsection
