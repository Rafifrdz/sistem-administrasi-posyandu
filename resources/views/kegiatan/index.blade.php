@extends('layouts.app')
@section('title','Jadwal Kegiatan')
@section('breadcrumb','Jadwal Kegiatan')
@section('nav-active-kegiatan','active')

@include('partials.ui')

@section('content')
{{-- Header --}}
<div class="page-heading">
    <div class="title-group">
        <h1>Jadwal Kegiatan</h1>
        <p>Kelola dan pantau semua kegiatan posyandu.</p>
    </div>
    <button type="button" class="btn-primary" onclick="openCreate()">
        <img src="/icons/plus.svg" alt=""> Tambah kegiatan
    </button>
</div>

{{-- Statistik --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:14px;">
    @foreach([
        ['Total kegiatan',$totalKegiatan,'Semua jadwal'],
        ['Terjadwal',$terjadwal,'Akan datang'],
        ['Selesai',$selesai,'Sudah berlangsung'],
        ['Dibatalkan',$dibatalkan,'Tidak jadi'],
    ] as [$lbl,$val,$sub])
    <div class="card" style="display:flex;flex-direction:column;gap:7px;padding:16px 18px;">
        <span class="stat-label">{{ $lbl }}</span>
        <span class="stat-num" style="font-size:26px;">{{ $val }}</span>
        <span style="font-size:10.5px;color:#b0bdb5;">{{ $sub }}</span>
    </div>
    @endforeach
</div>

{{-- Filter --}}
<div class="card" style="padding:14px 16px;">
    <form method="GET" action="{{ route('kegiatan.index') }}" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
        <div class="search-bar" style="flex:1;min-width:180px;">
            <img class="search-icon" src="/icons/search.svg" alt="">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama kegiatan atau lokasi..." class="form-control" style="height:40px;">
        </div>
        <select name="status" class="form-control" style="width:auto;min-width:135px;height:40px;" onchange="this.form.submit()">
            <option value="semua">Semua status</option>
            @foreach(['terjadwal'=>'Terjadwal','selesai'=>'Selesai','dibatalkan'=>'Dibatalkan'] as $v=>$l)
            <option value="{{ $v }}" {{ request('status')==$v?'selected':'' }}>{{ $l }}</option>
            @endforeach
        </select>
        <select name="kategori" class="form-control" style="width:auto;min-width:135px;height:40px;" onchange="this.form.submit()">
            <option value="semua">Semua kategori</option>
            @foreach(['balita'=>'Balita','ibu_hamil'=>'Ibu Hamil','lansia'=>'Lansia','umum'=>'Umum'] as $v=>$l)
            <option value="{{ $v }}" {{ request('kategori')==$v?'selected':'' }}>{{ $l }}</option>
            @endforeach
        </select>
        @if(request('search')||request('status')||request('kategori'))
        <a href="{{ route('kegiatan.index') }}" class="btn-secondary" style="height:40px;">Reset</a>
        @endif
    </form>
</div>

{{-- Tabel --}}
<div class="card" style="padding:0;overflow:hidden;">
    @if($kegiatan->isEmpty())
    <div class="empty-state">
        <div class="empty-icon"><img src="/icons/calendar-days.svg" alt=""></div>
        <h2>Belum ada jadwal kegiatan</h2>
        <p>Tambahkan kegiatan pertama untuk mulai mengelola jadwal posyandu.</p>
        <button type="button" class="btn-primary" onclick="openCreate()">Tambah kegiatan</button>
    </div>
    @else
    <div style="overflow-x:auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Kegiatan</th>
                    <th style="width:150px;">Tanggal &amp; Waktu</th>
                    <th style="width:105px;">Kategori</th>
                    <th style="width:100px;">Status</th>
                    <th style="width:80px;text-align:center;">Hadir</th>
                    <th style="width:120px;text-align:right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($kegiatan as $kg)
                @php
                    $sBadge = ['terjadwal'=>'badge-green','selesai'=>'badge-gray','dibatalkan'=>'badge-red'];
                    $tgl = $kg->tanggal;
                @endphp
                <tr>
                    <td data-l="Kegiatan">
                        <div class="cell">
                            <div class="av sq" style="background:#e6f1ea;color:#3a5c48;font-size:12px;">
                                {{ $tgl ? $tgl->format('d') : '—' }}
                            </div>
                            <div style="min-width:0;">
                                <div class="nm">{{ $kg->nama }}</div>
                                <div class="sub">{{ $kg->lokasi }}</div>
                            </div>
                        </div>
                    </td>
                    <td data-l="Tanggal & Waktu" style="white-space:nowrap;">
                        <div style="font-size:13px;color:#1e3528;">{{ $tgl ? $tgl->translatedFormat('d M Y') : '—' }}</div>
                        <div class="sub">{{ substr($kg->jam_mulai,0,5) }}–{{ substr($kg->jam_selesai,0,5) }}</div>
                    </td>
                    <td data-l="Kategori">
                        <span class="badge badge-green">{{ ucfirst(str_replace('_',' ',$kg->kategori)) }}</span>
                    </td>
                    <td data-l="Status">
                        <span class="badge {{ $sBadge[$kg->status] ?? 'badge-gray' }}">{{ $kg->status_label }}</span>
                    </td>
                    <td data-l="Hadir" style="text-align:center;font-weight:600;color:#3a5c48;">
                        {{ $kg->kehadiran()->count() }}
                    </td>
                    <td data-l="Aksi">
                        <div class="act-group">
                            <button type="button" class="act-btn" title="Lihat detail" onclick="openDetail({{ $kg->id }})">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                            <button type="button" class="act-btn" title="Edit kegiatan" onclick="openEdit({{ $kg->id }})">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                                </svg>
                            </button>
                            <button type="button" class="act-btn danger" title="Hapus kegiatan" onclick="openDelete({{ $kg->id }})">
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
        <span style="font-size:12px;color:#90a898;">Menampilkan {{ $kegiatan->firstItem() }}–{{ $kegiatan->lastItem() }} dari {{ $kegiatan->total() }} kegiatan</span>
        <div class="pagination-wrap">{{ $kegiatan->links('vendor.pagination.simple-tailwind') }}</div>
    </div>
    @endif
</div>

{{-- ══════════ MODAL: TAMBAH ══════════ --}}
<div class="md-overlay" id="mdCreate" onclick="if(event.target===this)closeModal('mdCreate')">
    <div class="md-box" role="dialog" aria-modal="true">
        <form method="POST" action="{{ route('kegiatan.store') }}">
            @csrf
            <div class="md-head">
                <div>
                    <div class="md-title">Tambah kegiatan baru</div>
                    <div class="md-desc">Jadwalkan kegiatan posyandu berikutnya.</div>
                </div>
                <button type="button" class="md-close" onclick="closeModal('mdCreate')" aria-label="Tutup">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="md-body">
                <div class="fgrid">
                    <div class="fld full">
                        <label>Nama kegiatan <span class="req">*</span></label>
                        <input type="text" name="nama" id="c_nama" value="{{ old('nama') }}" placeholder="cth. Penimbangan Balita" required>
                    </div>
                    <div class="fld">
                        <label>Tanggal <span class="req">*</span></label>
                        <input type="date" name="tanggal" id="c_tgl" value="{{ old('tanggal', date('Y-m-d')) }}" required>
                    </div>
                    <div class="fld">
                        <label>Lokasi <span class="req">*</span></label>
                        <input type="text" name="lokasi" id="c_lok" value="{{ old('lokasi') }}" placeholder="cth. Balai RW 04" required>
                    </div>
                    <div class="fld">
                        <label>Jam mulai <span class="req">*</span></label>
                        <input type="time" name="jam_mulai" id="c_jm" value="{{ old('jam_mulai','08:00') }}" required>
                    </div>
                    <div class="fld">
                        <label>Jam selesai <span class="req">*</span></label>
                        <input type="time" name="jam_selesai" id="c_js" value="{{ old('jam_selesai','10:00') }}" required>
                    </div>
                    <div class="fld full">
                        <label>Kategori <span class="req">*</span></label>
                        <select name="kategori" id="c_kat" required>
                            <option value="">Pilih kategori...</option>
                            @foreach(['balita'=>'Balita','ibu_hamil'=>'Ibu Hamil','lansia'=>'Lansia','umum'=>'Umum (semua kategori)'] as $v=>$l)
                            <option value="{{ $v }}" {{ old('kategori')==$v?'selected':'' }}>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="fld full">
                        <label>Keterangan</label>
                        <textarea name="keterangan" id="c_ket" rows="2" placeholder="Catatan, persiapan, dll. (opsional)">{{ old('keterangan') }}</textarea>
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
                <button type="submit" class="btn-primary">Simpan kegiatan</button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════ MODAL: DETAIL ══════════ --}}
<div class="md-overlay" id="mdDetail" onclick="if(event.target===this)closeModal('mdDetail')">
    <div class="md-box" role="dialog" aria-modal="true">
        <div class="md-head">
            <div>
                <div class="md-title">Detail kegiatan</div>
                <div class="md-desc" id="d_sub">—</div>
            </div>
            <button type="button" class="md-close" onclick="closeModal('mdDetail')" aria-label="Tutup">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="md-body">
            <div class="dt-head">
                <div class="av sq lg" id="d_av" style="background:#e6f1ea;color:#3a5c48;">—</div>
                <div style="min-width:0;">
                    <div style="font-size:17px;font-weight:700;color:#1e3528;" id="d_nama">—</div>
                    <div style="display:flex;gap:6px;margin-top:5px;flex-wrap:wrap;">
                        <span class="badge badge-green" id="d_kat">—</span>
                        <span class="badge badge-gray" id="d_status">—</span>
                    </div>
                </div>
            </div>
            <div class="dt-grid">
                <div class="dt-item"><span class="dt-lbl">Tanggal</span><span class="dt-val" id="d_tgl">—</span></div>
                <div class="dt-item"><span class="dt-lbl">Waktu</span><span class="dt-val" id="d_jam">—</span></div>
                <div class="dt-item full"><span class="dt-lbl">Lokasi</span><span class="dt-val" id="d_lok">—</span></div>
                <div class="dt-item"><span class="dt-lbl">Peserta tercatat</span><span class="dt-val" id="d_jml">—</span></div>
                <div class="dt-item full"><span class="dt-lbl">Keterangan</span><span class="dt-val" id="d_ket">—</span></div>
            </div>
        </div>
        <div class="md-foot">
            <button type="button" class="btn-secondary" onclick="closeModal('mdDetail')">Tutup</button>
            <button type="button" class="btn-primary" id="d_editbtn">Edit kegiatan</button>
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
                    <div class="md-title">Edit kegiatan</div>
                    <div class="md-desc" id="e_desc">Perbarui informasi kegiatan.</div>
                </div>
                <button type="button" class="md-close" onclick="closeModal('mdEdit')" aria-label="Tutup">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="md-body">
                <div class="fgrid">
                    <div class="fld full">
                        <label>Nama kegiatan <span class="req">*</span></label>
                        <input type="text" name="nama" id="e_nama" required>
                    </div>
                    <div class="fld">
                        <label>Tanggal <span class="req">*</span></label>
                        <input type="date" name="tanggal" id="e_tgl" required>
                    </div>
                    <div class="fld">
                        <label>Lokasi <span class="req">*</span></label>
                        <input type="text" name="lokasi" id="e_lok" required>
                    </div>
                    <div class="fld">
                        <label>Jam mulai <span class="req">*</span></label>
                        <input type="time" name="jam_mulai" id="e_jm" required>
                    </div>
                    <div class="fld">
                        <label>Jam selesai <span class="req">*</span></label>
                        <input type="time" name="jam_selesai" id="e_js" required>
                    </div>
                    <div class="fld">
                        <label>Kategori <span class="req">*</span></label>
                        <select name="kategori" id="e_kat" required>
                            @foreach(['balita'=>'Balita','ibu_hamil'=>'Ibu Hamil','lansia'=>'Lansia','umum'=>'Umum'] as $v=>$l)
                            <option value="{{ $v }}">{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="fld">
                        <label>Status <span class="req">*</span></label>
                        <select name="status" id="e_status" required>
                            @foreach(['terjadwal'=>'Terjadwal','selesai'=>'Selesai','dibatalkan'=>'Dibatalkan'] as $v=>$l)
                            <option value="{{ $v }}">{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="fld full">
                        <label>Keterangan</label>
                        <textarea name="keterangan" id="e_ket" rows="2"></textarea>
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
                <div style="font-size:16px;font-weight:700;color:#1e3528;">Hapus kegiatan ini?</div>
                <div style="font-size:13px;color:#7a9b88;line-height:1.6;">
                    <strong id="del_name" style="color:#1e3528;"></strong> akan dihapus permanen beserta seluruh data kehadirannya. Tindakan ini tidak bisa dibatalkan.
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
const KEGIATAN = @json($kegiatanJson);

const BADGE = { terjadwal:'badge-green', selesai:'badge-gray', dibatalkan:'badge-red' };

function openCreate() {
    openModal('mdCreate');
    setTimeout(() => document.getElementById('c_nama')?.focus(), 120);
}

function openDetail(id) {
    const k = KEGIATAN.find(x => x.id === id);
    if (!k) return;
    document.getElementById('d_av').textContent     = k.tanggal ? k.tanggal.slice(8,10) : '—';
    document.getElementById('d_nama').textContent   = k.nama;
    document.getElementById('d_sub').textContent    = k.tanggal_fmt || '—';
    document.getElementById('d_kat').textContent    = k.kategori_lbl;
    const st = document.getElementById('d_status');
    st.textContent = k.status_lbl;
    st.className = 'badge ' + (BADGE[k.status] || 'badge-gray');
    document.getElementById('d_tgl').textContent    = k.tanggal_fmt || '—';
    document.getElementById('d_jam').textContent    = k.jam_mulai + '–' + k.jam_selesai + ' WIB';
    document.getElementById('d_lok').textContent    = k.lokasi;
    document.getElementById('d_jml').textContent    = k.jumlah + ' peserta';
    document.getElementById('d_ket').textContent    = k.keterangan || '—';
    document.getElementById('d_editbtn').onclick = () => { closeModal('mdDetail'); setTimeout(() => openEdit(id), 180); };
    openModal('mdDetail');
}

function openEdit(id) {
    const k = KEGIATAN.find(x => x.id === id);
    if (!k) return;
    document.getElementById('formEdit').action = '/kegiatan/' + id;
    document.getElementById('e_desc').textContent = 'Perbarui informasi ' + k.nama + '.';
    document.getElementById('e_nama').value   = k.nama;
    document.getElementById('e_tgl').value    = k.tanggal;
    document.getElementById('e_lok').value    = k.lokasi;
    document.getElementById('e_jm').value     = k.jam_mulai;
    document.getElementById('e_js').value     = k.jam_selesai;
    document.getElementById('e_kat').value    = k.kategori;
    document.getElementById('e_status').value = k.status;
    document.getElementById('e_ket').value    = k.keterangan || '';
    openModal('mdEdit');
}

function openDelete(id) {
    const k = KEGIATAN.find(x => x.id === id);
    if (!k) return;
    document.getElementById('formDelete').action = '/kegiatan/' + id;
    document.getElementById('del_name').textContent = k.nama;
    openModal('mdDelete');
}

@if($errors->any())
document.addEventListener('DOMContentLoaded', () => openModal('mdCreate'));
@endif
</script>
@endpush
