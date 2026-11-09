@extends('layouts.app')
@section('title','Data Peserta')
@section('breadcrumb','Data Peserta')
@section('nav-active-peserta','active')

@push('head')
<style>
    /* ── Table: 5 kolom, aksi selalu terlihat ── */
    .pst-table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
    .pst-table thead th {
        text-align: left; padding: 11px 14px;
        font-size: 11px; font-weight: 600; color: #90a898;
        background: #f8faf8; border-bottom: 1px solid #e3eae4;
        letter-spacing: 0.04em; text-transform: uppercase; white-space: nowrap;
    }
    .pst-table tbody td { padding: 12px 14px; border-bottom: 1px solid #f0f5f1; vertical-align: middle; }
    .pst-table tbody tr { transition: background .12s ease; }
    .pst-table tbody tr:hover td { background: #f8faf8; }
    .pst-table tbody tr:last-child td { border-bottom: none; }

    .pst-name { display: flex; align-items: center; gap: 10px; min-width: 0; }
    .pst-av {
        width: 34px; height: 34px; border-radius: 50%;
        background: linear-gradient(135deg,#4f7561,#3a5c48);
        color: #fff; font-size: 11.5px; font-weight: 700;
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .pst-nm { font-weight: 600; color: #1e3528; font-size: 13.5px; line-height: 1.3; }
    .pst-sub { font-size: 11px; color: #90a898; font-family: ui-monospace, monospace; margin-top: 2px; }

    /* ── Action buttons ── */
    .act-group { display: flex; gap: 5px; justify-content: flex-end; }
    .act-btn {
        width: 32px; height: 32px; border-radius: 8px;
        display: inline-flex; align-items: center; justify-content: center;
        border: 1px solid #dde5de; background: #fff; cursor: pointer;
        color: #6b8075; transition: all .15s ease; padding: 0;
    }
    .act-btn:hover { background: #e6f1ea; border-color: #b9d2c1; color: #3a5c48; transform: translateY(-1px); }
    .act-btn:active { transform: translateY(0); }
    .act-btn.danger:hover { background: #fff0f0; border-color: #f0b8b8; color: #c0392b; }
    .act-btn svg { width: 15px; height: 15px; }

    /* ── Modal ── */
    .md-overlay {
        position: fixed; inset: 0; background: rgba(20,35,27,.42);
        backdrop-filter: blur(3px); z-index: 500;
        display: none; align-items: center; justify-content: center; padding: 20px;
    }
    .md-overlay.open { display: flex; animation: mdFade .18s ease; }
    @keyframes mdFade { from { opacity: 0 } to { opacity: 1 } }

    .md-box {
        background: #fff; border-radius: 16px; width: 100%;
        max-width: 620px; max-height: 90vh; overflow-y: auto;
        box-shadow: 0 20px 60px rgba(20,35,27,.22);
        animation: mdUp .22s cubic-bezier(.2,.8,.3,1);
    }
    .md-box.sm { max-width: 440px; }
    @keyframes mdUp { from { opacity: 0; transform: translateY(16px) scale(.98) } to { opacity: 1; transform: none } }

    .md-head {
        padding: 18px 22px; border-bottom: 1px solid #e3eae4;
        display: flex; align-items: flex-start; justify-content: space-between; gap: 14px;
        position: sticky; top: 0; background: #fff; border-radius: 16px 16px 0 0; z-index: 2;
    }
    .md-title { font-size: 16px; font-weight: 700; color: #1e3528; }
    .md-desc { font-size: 12.5px; color: #90a898; margin-top: 2px; }
    .md-close {
        width: 32px; height: 32px; border-radius: 8px; border: none;
        background: none; cursor: pointer; color: #90a898;
        display: flex; align-items: center; justify-content: center;
        transition: all .15s ease; flex-shrink: 0; padding: 0;
    }
    .md-close:hover { background: #f0f6f2; color: #3a5c48; }
    .md-close svg { width: 17px; height: 17px; }

    .md-body { padding: 20px 22px; display: flex; flex-direction: column; gap: 16px; }
    .md-foot {
        padding: 15px 22px; background: #f8faf8;
        border-top: 1px solid #e3eae4; display: flex; gap: 10px; justify-content: flex-end;
        border-radius: 0 0 16px 16px; position: sticky; bottom: 0;
    }

    /* form grid inside modal */
    .fgrid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .fgrid .full { grid-column: 1 / -1; }
    .fld { display: flex; flex-direction: column; gap: 5px; }
    .fld label { font-size: 12.5px; font-weight: 600; color: #2d5540; }
    .fld label .req { color: #c0392b; }
    .fld input, .fld select, .fld textarea {
        height: 40px; padding: 0 11px; border: 1.5px solid #dde5de;
        border-radius: 8px; font-size: 13px; font-family: inherit; color: #1e3528;
        outline: none; background: #fff; width: 100%;
        transition: border-color .15s, box-shadow .15s;
    }
    .fld textarea { height: auto; padding: 9px 11px; resize: vertical; }
    .fld input:focus, .fld select:focus, .fld textarea:focus {
        border-color: #4f7561; box-shadow: 0 0 0 3px rgba(79,117,97,.12);
    }
    .fld .msg { font-size: 11px; color: #c0392b; }

    /* detail view */
    .dt-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px 20px; }
    .dt-item { display: flex; flex-direction: column; gap: 3px; }
    .dt-item.full { grid-column: 1 / -1; }
    .dt-lbl { font-size: 10.5px; font-weight: 600; color: #90a898; text-transform: uppercase; letter-spacing: .05em; }
    .dt-val { font-size: 13.5px; color: #1e3528; }
    .dt-head { display: flex; align-items: center; gap: 13px; padding-bottom: 16px; border-bottom: 1px solid #e3eae4; margin-bottom: 4px; }
    .dt-head .pst-av { width: 46px; height: 46px; font-size: 15px; }

    .del-icon {
        width: 46px; height: 46px; border-radius: 50%; background: #fff0f0;
        display: flex; align-items: center; justify-content: center; margin: 0 auto 4px;
    }
    .del-icon svg { width: 21px; height: 21px; color: #c0392b; }

    /* ── Responsive: table → kartu di HP ── */
    @media (max-width: 720px) {
        .pst-table thead { display: none; }
        .pst-table, .pst-table tbody, .pst-table tr, .pst-table td { display: block; width: 100%; }
        .pst-table tbody tr {
            border: 1px solid #e3eae4; border-radius: 12px;
            margin-bottom: 10px; padding: 6px 2px; background: #fff;
        }
        .pst-table tbody td { border: none; padding: 7px 14px; }
        .pst-table tbody td[data-l]::before {
            content: attr(data-l); display: block;
            font-size: 10px; font-weight: 600; color: #90a898;
            text-transform: uppercase; letter-spacing: .05em; margin-bottom: 3px;
        }
        .act-group { justify-content: flex-start; }
        .act-btn { width: 36px; height: 36px; }
        .fgrid, .dt-grid { grid-template-columns: 1fr; }
        .md-box { max-height: 94vh; }
        .md-body { padding: 16px; }
    }
</style>
@endpush

@section('content')
{{-- Header --}}
<div class="page-heading">
    <div class="title-group">
        <h1>Data Peserta</h1>
        <p>Kelola data warga yang terdaftar di Posyandu Melati.</p>
    </div>
    <button type="button" class="btn-primary" onclick="openCreate()">
        <img src="/icons/plus.svg" alt=""> Tambah peserta
    </button>
</div>

{{-- Statistik --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:14px;">
    @foreach([['Total peserta',$totalPeserta,'Semua kategori'],['Balita',$balita,'Usia 0–5 tahun'],['Ibu hamil',$ibuHamil,'Masa kehamilan'],['Lansia',$lansia,'Usia 60+ tahun']] as [$lbl,$val,$sub])
    <div class="card" style="display:flex;flex-direction:column;gap:7px;padding:16px 18px;">
        <span class="stat-label">{{ $lbl }}</span>
        <span class="stat-num" style="font-size:26px;">{{ $val }}</span>
        <span style="font-size:10.5px;color:#b0bdb5;">{{ $sub }}</span>
    </div>
    @endforeach
</div>

{{-- Search & filter --}}
<div class="card" style="padding:14px 16px;">
    <form method="GET" action="{{ route('peserta.index') }}" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
        <div class="search-bar" style="flex:1;min-width:180px;">
            <img class="search-icon" src="/icons/search.svg" alt="">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau NIK..." class="form-control" style="height:40px;">
        </div>
        <select name="kategori" class="form-control" style="width:auto;min-width:150px;height:40px;" onchange="this.form.submit()">
            <option value="semua">Semua kategori</option>
            <option value="balita" {{ request('kategori')=='balita'?'selected':'' }}>Balita</option>
            <option value="ibu_hamil" {{ request('kategori')=='ibu_hamil'?'selected':'' }}>Ibu Hamil</option>
            <option value="lansia" {{ request('kategori')=='lansia'?'selected':'' }}>Lansia</option>
        </select>
        @if(request('search')||request('kategori'))
        <a href="{{ route('peserta.index') }}" class="btn-secondary" style="height:40px;">Reset</a>
        @endif
    </form>
</div>

{{-- Tabel --}}
<div class="card" style="padding:0;overflow:hidden;">
    @if($peserta->isEmpty())
    <div class="empty-state">
        <div class="empty-icon"><img src="/icons/users-round.svg" alt=""></div>
        <h2>Belum ada peserta terdaftar</h2>
        <p>Tambahkan peserta pertama untuk mulai mengelola data posyandu.</p>
        <button type="button" class="btn-primary" onclick="openCreate()">Tambah peserta</button>
    </div>
    @else
    <div style="overflow-x:auto;">
        <table class="pst-table">
            <thead>
                <tr>
                    <th>Peserta</th>
                    <th style="width:110px;">Kategori</th>
                    <th style="width:130px;">L/P · Usia</th>
                    <th style="width:170px;">Wali / Telepon</th>
                    <th style="width:120px;text-align:right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($peserta as $p)
                <tr>
                    <td data-l="Peserta">
                        <div class="pst-name">
                            <div class="pst-av">{{ strtoupper(substr($p->nama,0,2)) }}</div>
                            <div style="min-width:0;">
                                <div class="pst-nm">{{ $p->nama }}</div>
                                <div class="pst-sub">{{ $p->nik }}</div>
                            </div>
                        </div>
                    </td>
                    <td data-l="Kategori">
                        <span class="badge badge-green">{{ $p->kategori_label }}</span>
                    </td>
                    <td data-l="L/P · Usia" style="white-space:nowrap;color:#6b8075;font-size:13px;">
                        {{ $p->jenis_kelamin_label }} · {{ $p->usia_label }}
                    </td>
                    <td data-l="Wali / Telepon">
                        <div style="font-size:13px;color:#1e3528;">{{ $p->nama_wali ?: '—' }}</div>
                        <div style="font-size:11px;color:#90a898;">{{ $p->no_telepon ?: '—' }}</div>
                    </td>
                    <td data-l="Aksi">
                        <div class="act-group">
                            <button type="button" class="act-btn" title="Lihat detail" onclick="openDetail({{ $p->id }})">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                            <button type="button" class="act-btn" title="Edit data" onclick="openEdit({{ $p->id }})">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                                </svg>
                            </button>
                            <button type="button" class="act-btn danger" title="Hapus peserta" onclick="openDelete({{ $p->id }})">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div style="padding:14px 18px;display:flex;align-items:center;justify-content:space-between;gap:12px;border-top:1px solid #f0f5f1;flex-wrap:wrap;">
        <span style="font-size:12px;color:#90a898;">Menampilkan {{ $peserta->firstItem() }}–{{ $peserta->lastItem() }} dari {{ $peserta->total() }} peserta</span>
        <div class="pagination-wrap">{{ $peserta->links('vendor.pagination.simple-tailwind') }}</div>
    </div>
    @endif
</div>

{{-- ══════════ MODAL: TAMBAH ══════════ --}}
<div class="md-overlay" id="mdCreate" onclick="if(event.target===this)closeModal('mdCreate')">
    <div class="md-box" role="dialog" aria-modal="true">
        <form method="POST" action="{{ route('peserta.store') }}" id="formCreate">
            @csrf
            <div class="md-head">
                <div>
                    <div class="md-title">Tambah peserta baru</div>
                    <div class="md-desc">Isi data peserta dengan lengkap dan benar.</div>
                </div>
                <button type="button" class="md-close" onclick="closeModal('mdCreate')" aria-label="Tutup">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="md-body">
                <div class="fgrid">
                    <div class="fld full">
                        <label>Nama lengkap <span class="req">*</span></label>
                        <input type="text" name="nama" id="c_nama" value="{{ old('nama') }}" placeholder="Nama sesuai KTP" required>
                    </div>
                    <div class="fld">
                        <label>NIK <span class="req">*</span></label>
                        <input type="text" name="nik" id="c_nik" value="{{ old('nik') }}" placeholder="16 digit" maxlength="16" inputmode="numeric" required>
                    </div>
                    <div class="fld">
                        <label>Tanggal lahir <span class="req">*</span></label>
                        <input type="date" name="tanggal_lahir" id="c_tgl" value="{{ old('tanggal_lahir') }}" required>
                    </div>
                    <div class="fld">
                        <label>Jenis kelamin <span class="req">*</span></label>
                        <select name="jenis_kelamin" id="c_jk" required>
                            <option value="">Pilih...</option>
                            <option value="L" {{ old('jenis_kelamin')=='L'?'selected':'' }}>Laki-laki</option>
                            <option value="P" {{ old('jenis_kelamin')=='P'?'selected':'' }}>Perempuan</option>
                        </select>
                    </div>
                    <div class="fld">
                        <label>Kategori <span class="req">*</span></label>
                        <select name="kategori" id="c_kat" required>
                            <option value="">Pilih...</option>
                            <option value="balita" {{ old('kategori')=='balita'?'selected':'' }}>Balita</option>
                            <option value="ibu_hamil" {{ old('kategori')=='ibu_hamil'?'selected':'' }}>Ibu Hamil</option>
                            <option value="lansia" {{ old('kategori')=='lansia'?'selected':'' }}>Lansia</option>
                        </select>
                    </div>
                    <div class="fld full">
                        <label>Alamat lengkap <span class="req">*</span></label>
                        <textarea name="alamat" id="c_alamat" rows="2" placeholder="Jl. Contoh No. 1, RT/RW ..." required>{{ old('alamat') }}</textarea>
                    </div>
                    <div class="fld">
                        <label>Nama wali</label>
                        <input type="text" name="nama_wali" id="c_wali" value="{{ old('nama_wali') }}" placeholder="Opsional">
                    </div>
                    <div class="fld">
                        <label>No. telepon</label>
                        <input type="text" name="no_telepon" id="c_telp" value="{{ old('no_telepon') }}" placeholder="08xx-xxxx-xxxx">
                    </div>
                </div>
                @if($errors->any())
                <div class="alert alert-error" style="font-size:12.5px;">
                    <span>✕</span> {{ $errors->first() }}
                </div>
                @endif
            </div>
            <div class="md-foot">
                <button type="button" class="btn-secondary" onclick="closeModal('mdCreate')">Batal</button>
                <button type="submit" class="btn-primary">Simpan peserta</button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════ MODAL: DETAIL ══════════ --}}
<div class="md-overlay" id="mdDetail" onclick="if(event.target===this)closeModal('mdDetail')">
    <div class="md-box" role="dialog" aria-modal="true">
        <div class="md-head">
            <div>
                <div class="md-title">Detail peserta</div>
                <div class="md-desc">Informasi lengkap peserta posyandu.</div>
            </div>
            <button type="button" class="md-close" onclick="closeModal('mdDetail')" aria-label="Tutup">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="md-body">
            <div class="dt-head">
                <div class="pst-av" id="d_av">--</div>
                <div>
                    <div style="font-size:17px;font-weight:700;color:#1e3528;" id="d_nama">—</div>
                    <span class="badge badge-green" id="d_kat" style="margin-top:4px;">—</span>
                </div>
            </div>
            <div class="dt-grid">
                <div class="dt-item"><span class="dt-lbl">NIK</span><span class="dt-val" id="d_nik">—</span></div>
                <div class="dt-item"><span class="dt-lbl">Jenis kelamin</span><span class="dt-val" id="d_jk">—</span></div>
                <div class="dt-item"><span class="dt-lbl">Tanggal lahir</span><span class="dt-val" id="d_tgl">—</span></div>
                <div class="dt-item"><span class="dt-lbl">Usia</span><span class="dt-val" id="d_usia">—</span></div>
                <div class="dt-item full"><span class="dt-lbl">Alamat</span><span class="dt-val" id="d_alamat">—</span></div>
                <div class="dt-item"><span class="dt-lbl">Nama wali</span><span class="dt-val" id="d_wali">—</span></div>
                <div class="dt-item"><span class="dt-lbl">No. telepon</span><span class="dt-val" id="d_telp">—</span></div>
                <div class="dt-item full"><span class="dt-lbl">Terdaftar sejak</span><span class="dt-val" id="d_daftar">—</span></div>
            </div>
        </div>
        <div class="md-foot">
            <button type="button" class="btn-secondary" onclick="closeModal('mdDetail')">Tutup</button>
            <button type="button" class="btn-primary" id="d_editbtn">Edit data</button>
        </div>
    </div>
</div>

{{-- ══════════ MODAL: EDIT ══════════ --}}
<div class="md-overlay" id="mdEdit" onclick="if(event.target===this)closeModal('mdEdit')">
    <div class="md-box" role="dialog" aria-modal="true">
        <form method="POST" id="formEdit">
            @csrf @method('PUT')
            <div class="md-head">
                <div>
                    <div class="md-title">Edit data peserta</div>
                    <div class="md-desc" id="e_desc">Perbarui informasi peserta.</div>
                </div>
                <button type="button" class="md-close" onclick="closeModal('mdEdit')" aria-label="Tutup">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="md-body">
                <div class="fgrid">
                    <div class="fld full">
                        <label>Nama lengkap <span class="req">*</span></label>
                        <input type="text" name="nama" id="e_nama" required>
                    </div>
                    <div class="fld">
                        <label>NIK <span class="req">*</span></label>
                        <input type="text" name="nik" id="e_nik" maxlength="16" inputmode="numeric" required>
                    </div>
                    <div class="fld">
                        <label>Tanggal lahir <span class="req">*</span></label>
                        <input type="date" name="tanggal_lahir" id="e_tgl" required>
                    </div>
                    <div class="fld">
                        <label>Jenis kelamin <span class="req">*</span></label>
                        <select name="jenis_kelamin" id="e_jk" required>
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>
                    <div class="fld">
                        <label>Kategori <span class="req">*</span></label>
                        <select name="kategori" id="e_kat" required>
                            <option value="balita">Balita</option>
                            <option value="ibu_hamil">Ibu Hamil</option>
                            <option value="lansia">Lansia</option>
                        </select>
                    </div>
                    <div class="fld full">
                        <label>Alamat lengkap <span class="req">*</span></label>
                        <textarea name="alamat" id="e_alamat" rows="2" required></textarea>
                    </div>
                    <div class="fld">
                        <label>Nama wali</label>
                        <input type="text" name="nama_wali" id="e_wali">
                    </div>
                    <div class="fld">
                        <label>No. telepon</label>
                        <input type="text" name="no_telepon" id="e_telp">
                    </div>
                </div>
            </div>
            <div class="md-foot">
                <button type="button" class="btn-secondary" onclick="closeModal('mdEdit')">Batal</button>
                <button type="submit" class="btn-primary">Simpan perubahan</button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════ MODAL: HAPUS ══════════ --}}
<div class="md-overlay" id="mdDelete" onclick="if(event.target===this)closeModal('mdDelete')">
    <div class="md-box sm" role="dialog" aria-modal="true">
        <form method="POST" id="formDelete">
            @csrf @method('DELETE')
            <div class="md-head" style="border-bottom:none;padding-bottom:8px;">
                <div></div>
                <button type="button" class="md-close" onclick="closeModal('mdDelete')" aria-label="Tutup">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="md-body" style="text-align:center;padding-top:0;gap:10px;">
                <div class="del-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/>
                    </svg>
                </div>
                <div style="font-size:16px;font-weight:700;color:#1e3528;">Hapus peserta ini?</div>
                <div style="font-size:13px;color:#7a9b88;line-height:1.6;">
                    <strong id="del_name" style="color:#1e3528;"></strong> akan dihapus permanen beserta seluruh riwayat kehadirannya. Tindakan ini tidak bisa dibatalkan.
                </div>
            </div>
            <div class="md-foot" style="justify-content:center;">
                <button type="button" class="btn-secondary" onclick="closeModal('mdDelete')">Batal</button>
                <button type="submit" class="btn-danger" style="height:40px;padding:0 16px;font-size:13.5px;">Ya, hapus</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
/* ── Data peserta untuk modal ── */
const DATA = @json($pesertaJson);

function fmtTgl(iso) {
    if (!iso) return '—';
    const d = new Date(iso + 'T00:00:00');
    return d.toLocaleDateString('id-ID', { day:'2-digit', month:'long', year:'numeric' });
}

function openModal(id) {
    document.getElementById(id).classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeModal(id) {
    document.getElementById(id).classList.remove('open');
    if (!document.querySelector('.md-overlay.open')) document.body.style.overflow = '';
}
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') document.querySelectorAll('.md-overlay.open').forEach(m => closeModal(m.id));
});

function openCreate() {
    openModal('mdCreate');
    setTimeout(() => document.getElementById('c_nama')?.focus(), 120);
}

function openDetail(id) {
    const p = DATA.find(x => x.id === id);
    if (!p) return;
    document.getElementById('d_av').textContent     = p.nama.substring(0,2).toUpperCase();
    document.getElementById('d_nama').textContent   = p.nama;
    document.getElementById('d_kat').textContent    = p.kategori_label;
    document.getElementById('d_nik').textContent    = p.nik;
    document.getElementById('d_jk').textContent     = p.jenis_kelamin_label;
    document.getElementById('d_tgl').textContent    = fmtTgl(p.tanggal_lahir);
    document.getElementById('d_usia').textContent   = p.usia_label;
    document.getElementById('d_alamat').textContent = p.alamat;
    document.getElementById('d_wali').textContent   = p.nama_wali || '—';
    document.getElementById('d_telp').textContent   = p.no_telepon || '—';
    document.getElementById('d_daftar').textContent = p.created_at || '—';
    document.getElementById('d_editbtn').onclick = () => { closeModal('mdDetail'); setTimeout(() => openEdit(id), 180); };
    openModal('mdDetail');
}

function openEdit(id) {
    const p = DATA.find(x => x.id === id);
    if (!p) return;
    document.getElementById('formEdit').action = '/peserta/' + id;
    document.getElementById('e_desc').textContent = 'Perbarui informasi ' + p.nama + '.';
    document.getElementById('e_nama').value   = p.nama;
    document.getElementById('e_nik').value    = p.nik;
    document.getElementById('e_tgl').value    = p.tanggal_lahir;
    document.getElementById('e_jk').value     = p.jenis_kelamin;
    document.getElementById('e_kat').value    = p.kategori;
    document.getElementById('e_alamat').value = p.alamat;
    document.getElementById('e_wali').value   = p.nama_wali || '';
    document.getElementById('e_telp').value   = p.no_telepon || '';
    openModal('mdEdit');
}

function openDelete(id) {
    const p = DATA.find(x => x.id === id);
    if (!p) return;
    document.getElementById('formDelete').action = '/peserta/' + id;
    document.getElementById('del_name').textContent = p.nama;
    openModal('mdDelete');
}

@if($errors->any())
document.addEventListener('DOMContentLoaded', () => openModal('mdCreate'));
@endif
</script>
@endpush
