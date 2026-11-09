@extends('layouts.app')
@section('title','Edit Peserta')
@section('breadcrumb','Edit Peserta')
@section('nav-active-peserta','active')

@section('content')
<div class="page-heading">
    <div class="title-group">
        <h1>Edit data peserta</h1>
        <p>Perbarui informasi {{ $peserta->nama }}.</p>
    </div>
    <a href="{{ route('peserta.index') }}" class="btn-secondary">Kembali</a>
</div>

<div style="display:flex;gap:24px;align-items:flex-start;">
    <div class="card" style="flex:1;">
        <form method="POST" action="{{ route('peserta.update',$peserta) }}" style="display:flex;flex-direction:column;gap:28px;">
            @csrf @method('PUT')
            <div class="form-section">
                <div>
                    <div class="form-section-title">Data diri</div>
                    <div class="form-section-desc">Informasi identitas peserta posyandu.</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Nama lengkap <span style="color:red">*</span></label>
                    <input type="text" name="nama" value="{{ old('nama',$peserta->nama) }}" class="form-control {{ $errors->has('nama')?'is-invalid':'' }}">
                    @error('nama')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">NIK <span style="color:red">*</span></label>
                        <input type="text" name="nik" value="{{ old('nik',$peserta->nik) }}" class="form-control {{ $errors->has('nik')?'is-invalid':'' }}" maxlength="16">
                        @error('nik')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tanggal lahir <span style="color:red">*</span></label>
                        <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir',\Carbon\Carbon::parse($peserta->tanggal_lahir)->format('Y-m-d')) }}" class="form-control">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Jenis kelamin <span style="color:red">*</span></label>
                        <select name="jenis_kelamin" class="form-control">
                            <option value="L" {{ old('jenis_kelamin',$peserta->jenis_kelamin)=='L'?'selected':'' }}>Laki-laki</option>
                            <option value="P" {{ old('jenis_kelamin',$peserta->jenis_kelamin)=='P'?'selected':'' }}>Perempuan</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kategori <span style="color:red">*</span></label>
                        <select name="kategori" class="form-control">
                            <option value="balita" {{ old('kategori',$peserta->kategori)=='balita'?'selected':'' }}>Balita</option>
                            <option value="ibu_hamil" {{ old('kategori',$peserta->kategori)=='ibu_hamil'?'selected':'' }}>Ibu Hamil</option>
                            <option value="lansia" {{ old('kategori',$peserta->kategori)=='lansia'?'selected':'' }}>Lansia</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Alamat lengkap <span style="color:red">*</span></label>
                    <textarea name="alamat" class="form-control" rows="2">{{ old('alamat',$peserta->alamat) }}</textarea>
                </div>
            </div>
            <div class="divider"></div>
            <div class="form-section">
                <div>
                    <div class="form-section-title">Data wali / kontak</div>
                    <div class="form-section-desc">Opsional.</div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Nama wali</label>
                        <input type="text" name="nama_wali" value="{{ old('nama_wali',$peserta->nama_wali) }}" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">No. telepon</label>
                        <input type="text" name="no_telepon" value="{{ old('no_telepon',$peserta->no_telepon) }}" class="form-control">
                    </div>
                </div>
            </div>
            <div class="divider"></div>
            <div style="display:flex;gap:12px;">
                <button type="submit" class="btn-primary">Perbarui data</button>
                <a href="{{ route('peserta.index') }}" class="btn-secondary">Batal</a>
            </div>
        </form>
    </div>
    <div style="width:300px;flex-shrink:0;">
        <div class="card">
            <div style="font-size:14px;font-weight:600;color:#233c32;margin-bottom:10px;">Info peserta</div>
            <div style="display:flex;flex-direction:column;gap:8px;font-size:13px;color:#758179;">
                <span>ID: #{{ $peserta->id }}</span>
                <span>Terdaftar: {{ $peserta->created_at->translatedFormat('d M Y') }}</span>
                <span>Terakhir diperbarui: {{ $peserta->updated_at->translatedFormat('d M Y') }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
