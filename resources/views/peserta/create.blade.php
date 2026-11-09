@extends('layouts.app')
@section('title','Tambah Peserta')
@section('breadcrumb','Tambah Peserta')
@section('nav-active-peserta','active')

@section('content')
<div class="page-heading">
    <div class="title-group">
        <h1>Tambah peserta baru</h1>
        <p>Isi data peserta dengan lengkap dan benar.</p>
    </div>
    <a href="{{ route('peserta.index') }}" class="btn-secondary">Kembali</a>
</div>

<div style="display:flex;gap:24px;align-items:flex-start;">
    {{-- Form --}}
    <div class="card" style="flex:1;">
        <form method="POST" action="{{ route('peserta.store') }}" style="display:flex;flex-direction:column;gap:28px;">
            @csrf
            {{-- Data diri --}}
            <div class="form-section">
                <div>
                    <div class="form-section-title">Data diri</div>
                    <div class="form-section-desc">Informasi identitas peserta posyandu.</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Nama lengkap <span style="color:red">*</span></label>
                    <input type="text" name="nama" value="{{ old('nama') }}" class="form-control {{ $errors->has('nama')?'is-invalid':'' }}" placeholder="Nama sesuai KTP">
                    @error('nama')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">NIK <span style="color:red">*</span></label>
                        <input type="text" name="nik" value="{{ old('nik') }}" class="form-control {{ $errors->has('nik')?'is-invalid':'' }}" placeholder="16 digit NIK" maxlength="16">
                        @error('nik')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tanggal lahir <span style="color:red">*</span></label>
                        <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}" class="form-control {{ $errors->has('tanggal_lahir')?'is-invalid':'' }}">
                        @error('tanggal_lahir')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Jenis kelamin <span style="color:red">*</span></label>
                        <select name="jenis_kelamin" class="form-control {{ $errors->has('jenis_kelamin')?'is-invalid':'' }}">
                            <option value="">Pilih...</option>
                            <option value="L" {{ old('jenis_kelamin')=='L'?'selected':'' }}>Laki-laki</option>
                            <option value="P" {{ old('jenis_kelamin')=='P'?'selected':'' }}>Perempuan</option>
                        </select>
                        @error('jenis_kelamin')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kategori <span style="color:red">*</span></label>
                        <select name="kategori" class="form-control {{ $errors->has('kategori')?'is-invalid':'' }}">
                            <option value="">Pilih...</option>
                            <option value="balita" {{ old('kategori')=='balita'?'selected':'' }}>Balita</option>
                            <option value="ibu_hamil" {{ old('kategori')=='ibu_hamil'?'selected':'' }}>Ibu Hamil</option>
                            <option value="lansia" {{ old('kategori')=='lansia'?'selected':'' }}>Lansia</option>
                        </select>
                        @error('kategori')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Alamat lengkap <span style="color:red">*</span></label>
                    <textarea name="alamat" class="form-control {{ $errors->has('alamat')?'is-invalid':'' }}" rows="2" placeholder="Jl. Contoh No. 1, RT/RW ...">{{ old('alamat') }}</textarea>
                    @error('alamat')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="divider"></div>

            {{-- Kontak --}}
            <div class="form-section">
                <div>
                    <div class="form-section-title">Data wali / kontak</div>
                    <div class="form-section-desc">Opsional, khususnya untuk balita.</div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Nama wali</label>
                        <input type="text" name="nama_wali" value="{{ old('nama_wali') }}" class="form-control" placeholder="Nama orang tua/wali">
                    </div>
                    <div class="form-group">
                        <label class="form-label">No. telepon</label>
                        <input type="text" name="no_telepon" value="{{ old('no_telepon') }}" class="form-control" placeholder="08xx-xxxx-xxxx">
                    </div>
                </div>
            </div>

            <div class="divider"></div>

            <div style="display:flex;gap:12px;">
                <button type="submit" class="btn-primary">Simpan peserta</button>
                <a href="{{ route('peserta.index') }}" class="btn-secondary">Batal</a>
            </div>
        </form>
    </div>

    {{-- Sidebar info --}}
    <div style="width:300px;flex-shrink:0;display:flex;flex-direction:column;gap:14px;">
        <div class="card" style="display:flex;flex-direction:column;gap:12px;">
            <span style="font-size:14px;font-weight:600;color:#233c32;">Panduan kategori</span>
            @foreach([['Balita','Anak berusia 0–59 bulan. Wajib diisi nama wali.'],['Ibu Hamil','Warga dalam masa kehamilan yang aktif berkunjung.'],['Lansia','Warga berusia 60 tahun ke atas.']] as [$kat,$desc])
            <div style="display:flex;flex-direction:column;gap:4px;">
                <span class="badge badge-green">{{ $kat }}</span>
                <span style="font-size:12px;color:#758179;line-height:1.5;">{{ $desc }}</span>
            </div>
            @endforeach
        </div>
        <div class="privacy-note">
            <img src="/icons/info.svg" alt="">
            <p>Data peserta disimpan aman dan hanya diakses oleh kader posyandu terotorisasi.</p>
        </div>
    </div>
</div>
@endsection
