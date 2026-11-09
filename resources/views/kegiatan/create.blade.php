@extends('layouts.app')
@section('title','Tambah Kegiatan')
@section('breadcrumb','Tambah Kegiatan')
@section('nav-active-kegiatan','active')

@section('content')
<div class="page-heading">
    <div class="title-group">
        <h1>Tambah kegiatan baru</h1>
        <p>Jadwalkan kegiatan posyandu berikutnya.</p>
    </div>
    <a href="{{ route('kegiatan.index') }}" class="btn-secondary">Kembali</a>
</div>

<div style="display:flex;gap:24px;align-items:flex-start;">
    <div class="card" style="flex:1;">
        <form method="POST" action="{{ route('kegiatan.store') }}" style="display:flex;flex-direction:column;gap:22px;">
            @csrf
            <div class="form-group">
                <label class="form-label">Nama kegiatan <span style="color:red">*</span></label>
                <input type="text" name="nama" value="{{ old('nama') }}" class="form-control {{ $errors->has('nama')?'is-invalid':'' }}" placeholder="cth. Penimbangan Balita">
                @error('nama')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Tanggal <span style="color:red">*</span></label>
                    <input type="date" name="tanggal" value="{{ old('tanggal') }}" class="form-control {{ $errors->has('tanggal')?'is-invalid':'' }}">
                    @error('tanggal')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Lokasi <span style="color:red">*</span></label>
                    <input type="text" name="lokasi" value="{{ old('lokasi') }}" class="form-control" placeholder="cth. Balai RW 04">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Jam mulai <span style="color:red">*</span></label>
                    <input type="time" name="jam_mulai" value="{{ old('jam_mulai','08:00') }}" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Jam selesai <span style="color:red">*</span></label>
                    <input type="time" name="jam_selesai" value="{{ old('jam_selesai','10:00') }}" class="form-control">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Kategori <span style="color:red">*</span></label>
                <select name="kategori" class="form-control">
                    <option value="">Pilih kategori...</option>
                    @foreach(['balita'=>'Balita','ibu_hamil'=>'Ibu Hamil','lansia'=>'Lansia','umum'=>'Umum (semua kategori)'] as $v=>$l)
                    <option value="{{ $v }}" {{ old('kategori')==$v?'selected':'' }}>{{ $l }}</option>
                    @endforeach
                </select>
                @error('kategori')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Keterangan tambahan</label>
                <textarea name="keterangan" class="form-control" rows="3" placeholder="Catatan, persiapan, dll.">{{ old('keterangan') }}</textarea>
            </div>
            <div class="divider"></div>
            <div style="display:flex;gap:12px;">
                <button type="submit" class="btn-primary">Simpan kegiatan</button>
                <a href="{{ route('kegiatan.index') }}" class="btn-secondary">Batal</a>
            </div>
        </form>
    </div>
    <div style="width:280px;flex-shrink:0;">
        <div class="privacy-note">
            <img src="/icons/info.svg" alt="">
            <p>Kegiatan yang disimpan akan langsung muncul di dashboard dan dapat digunakan untuk mencatat kehadiran peserta.</p>
        </div>
    </div>
</div>
@endsection
