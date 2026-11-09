@extends('layouts.app')

@section('title', 'Jadwal Kegiatan')
@section('breadcrumb', 'Jadwal Kegiatan')
@section('nav-active-kegiatan', 'active')

@push('head')
<style>
    .chip { padding: 7px 13px; border-radius: 20px; border: 1.5px solid #dde5de; background: #fff; font-size: 12.5px; color: #4a6b57; text-decoration: none; font-weight: 600; transition: all .15s; }
    .chip:hover { border-color: #b9d2c1; background: #f6faf7; }
    .chip.on { background: #2f7d78; border-color: #2f7d78; color: #fff; }
    .st { padding: 3px 9px; border-radius: 20px; font-size: 10.5px; font-weight: 700; white-space: nowrap; }
    .st-terjadwal { background: #e8f3ec; color: #2f6b4a; }
    .st-selesai { background: #eef2f0; color: #6b8075; }
    .st-dibatalkan { background: #fdeaea; color: #b3261e; }
    .jenis-tag { display: inline-block; padding: 2px 8px; border-radius: 5px; font-size: 10px; font-weight: 700; background: #f0f5f1; color: #4a6b57; }
</style>
@endpush

@section('content')

<div class="page-heading" style="margin-bottom:18px;">
    <div>
        <h2 style="margin:0;font-size:19px;color:#35271e;">Jadwal Kegiatan Posyandu</h2>
        <div class="sub" style="margin-top:4px;">Pemeriksaan ANC, kelas ibu hamil, USG, dan kunjungan rumah</div>
    </div>
    <button class="btn-primary" onclick="openModal('mdTambah')">
        <img src="/icons/plus.svg" alt="" style="width:15px;height:15px;"> Tambah Jadwal
    </button>
</div>

<div style="display:flex;gap:14px;flex-wrap:wrap;margin-bottom:16px;">
    <div class="card" style="flex:1;min-width:150px;"><span class="stat-label">Terjadwal</span><div class="stat-num">{{ $statistik['terjadwal'] }}</div></div>
    <div class="card" style="flex:1;min-width:150px;"><span class="stat-label">Akan Datang</span><div class="stat-num" style="color:#2f7d78;">{{ $statistik['mendatang'] }}</div></div>
    <div class="card" style="flex:1;min-width:150px;"><span class="stat-label">Selesai</span><div class="stat-num">{{ $statistik['selesai'] }}</div></div>
    <div class="card" style="flex:1;min-width:150px;"><span class="stat-label">Kelas Ibu Hamil</span><div class="stat-num">{{ $statistik['kelas'] }}</div></div>
</div>

<div class="card" style="margin-bottom:16px;">
    <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <a href="{{ route('kegiatan.index') }}" class="chip {{ ! $jenis ? 'on' : '' }}">Semua jenis</a>
            @foreach (\App\Models\Kegiatan::JENIS as $key => $label)
                <a href="{{ route('kegiatan.index', ['jenis' => $key, 'status' => $status]) }}" class="chip {{ $jenis === $key ? 'on' : '' }}">{{ $label }}</a>
            @endforeach
        </div>
        <form method="GET" action="{{ route('kegiatan.index') }}" style="margin-left:auto;">
            <input type="hidden" name="jenis" value="{{ $jenis }}">
            <select name="status" class="form-control" style="width:auto;height:38px;font-size:13px;" onchange="this.form.submit()">
                <option value="">Semua status</option>
                <option value="terjadwal" @selected($status === 'terjadwal')>Terjadwal</option>
                <option value="selesai" @selected($status === 'selesai')>Selesai</option>
                <option value="dibatalkan" @selected($status === 'dibatalkan')>Dibatalkan</option>
            </select>
        </form>
    </div>
</div>

<div class="card" style="padding:0;overflow:hidden;">
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Kegiatan</th>
                    <th>Waktu</th>
                    <th>Sasaran</th>
                    <th>Peserta</th>
                    <th>Status</th>
                    <th style="text-align:right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($daftar as $k)
                <tr>
                    <td data-l="Kegiatan">
                        <div class="nm">{{ $k->nama }}</div>
                        <div class="sub"><span class="jenis-tag">{{ $k->jenis_label }}</span></div>
                    </td>
                    <td data-l="Waktu">
                        <div class="nm" style="font-size:13px;">{{ $k->tanggal->translatedFormat('d M Y') }}</div>
                        <div class="sub">{{ $k->jam_label }} · {{ $k->lokasi }}</div>
                    </td>
                    <td data-l="Sasaran"><span class="jenis-tag">{{ $k->trimester_label }}</span></td>
                    <td data-l="Peserta"><span class="vital">{{ $k->kunjungan()->count() }}</span> <span class="sub">kunjungan</span></td>
                    <td data-l="Status"><span class="st st-{{ $k->status }}">{{ $k->status_label }}</span></td>
                    <td data-l="Aksi">
                        <div class="act-group">
                            <button class="act-btn" title="Detail" onclick="bukaDetail({{ $k->id }})">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                            <button class="act-btn" title="Ubah" onclick="bukaEdit({{ $k->id }})">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                            </button>
                            <button class="act-btn danger" title="Hapus" onclick="bukaHapus({{ $k->id }}, '{{ addslashes($k->nama) }}')">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <div class="empty-icon"><img src="/icons/calendar-days.svg" alt="" style="width:22px;height:22px;opacity:.4;"></div>
                            <div style="font-weight:600;color:#4a6b57;">Belum ada jadwal kegiatan</div>
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="pagination-wrap">{{ $daftar->links() }}</div>

{{-- ══════════ MODAL TAMBAH ══════════ --}}
<div class="md-overlay" id="mdTambah">
    <div class="md-box">
        <form method="POST" action="{{ route('kegiatan.store') }}">
            @csrf
            <div class="md-head">
                <div><div class="md-title">Tambah Jadwal Kegiatan</div><div class="md-desc">Kegiatan untuk sasaran ibu hamil</div></div>
                <button type="button" class="md-close" onclick="closeModal('mdTambah')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="md-body">
                <div class="fgrid">
                    <div class="fld full"><label>Nama Kegiatan <span class="req">*</span></label><input type="text" name="nama" value="{{ old('nama') }}" placeholder="cth. Pemeriksaan ANC Rutin" required></div>
                    <div class="fld full"><label>Jenis <span class="req">*</span></label>
                        <select name="jenis" required>
                            @foreach (\App\Models\Kegiatan::JENIS as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="fld"><label>Tanggal <span class="req">*</span></label><input type="date" name="tanggal" value="{{ old('tanggal') }}" required></div>
                    <div class="fld"><label>Sasaran Trimester <span class="req">*</span></label>
                        <select name="trimester_target" required>
                            <option value="semua">Semua trimester</option>
                            <option value="1">Trimester 1</option>
                            <option value="2">Trimester 2</option>
                            <option value="3">Trimester 3</option>
                        </select>
                    </div>
                    <div class="fld"><label>Jam Mulai <span class="req">*</span></label><input type="time" name="jam_mulai" value="{{ old('jam_mulai', '08:00') }}" required></div>
                    <div class="fld"><label>Jam Selesai <span class="req">*</span></label><input type="time" name="jam_selesai" value="{{ old('jam_selesai', '11:00') }}" required></div>
                    <div class="fld full"><label>Lokasi <span class="req">*</span></label><input type="text" name="lokasi" value="{{ old('lokasi', 'Balai RW 05') }}" required></div>
                    <div class="fld full"><label>Status <span class="req">*</span></label>
                        <select name="status" required>
                            <option value="terjadwal">Terjadwal</option>
                            <option value="selesai">Selesai</option>
                            <option value="dibatalkan">Dibatalkan</option>
                        </select>
                    </div>
                    <div class="fld full"><label>Keterangan</label><input type="text" name="keterangan" value="{{ old('keterangan') }}" placeholder="cth. Bawa Buku KIA"></div>
                </div>
            </div>
            <div class="md-foot">
                <button type="button" class="btn-secondary" onclick="closeModal('mdTambah')">Batal</button>
                <button type="submit" class="btn-primary">Simpan Jadwal</button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════ MODAL DETAIL ══════════ --}}
<div class="md-overlay" id="mdDetail">
    <div class="md-box">
        <div class="md-head">
            <div><div class="md-title" id="dtNama">—</div><div class="md-desc" id="dtJenis">—</div></div>
            <button type="button" class="md-close" onclick="closeModal('mdDetail')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="md-body"><div class="dt-grid" id="dtGrid"></div></div>
        <div class="md-foot"><button type="button" class="btn-secondary" onclick="closeModal('mdDetail')">Tutup</button></div>
    </div>
</div>

{{-- ══════════ MODAL UBAH ══════════ --}}
<div class="md-overlay" id="mdEdit">
    <div class="md-box">
        <form method="POST" id="formEdit">
            @csrf @method('PUT')
            <div class="md-head">
                <div><div class="md-title">Ubah Jadwal</div><div class="md-desc" id="edSub">—</div></div>
                <button type="button" class="md-close" onclick="closeModal('mdEdit')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="md-body">
                <div class="fgrid">
                    <div class="fld full"><label>Nama Kegiatan <span class="req">*</span></label><input type="text" name="nama" id="edNama" required></div>
                    <div class="fld full"><label>Jenis</label>
                        <select name="jenis" id="edJenis">
                            @foreach (\App\Models\Kegiatan::JENIS as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach
                        </select>
                    </div>
                    <div class="fld"><label>Tanggal</label><input type="date" name="tanggal" id="edTanggal" required></div>
                    <div class="fld"><label>Sasaran</label>
                        <select name="trimester_target" id="edTarget">
                            <option value="semua">Semua trimester</option><option value="1">Trimester 1</option>
                            <option value="2">Trimester 2</option><option value="3">Trimester 3</option>
                        </select>
                    </div>
                    <div class="fld"><label>Jam Mulai</label><input type="time" name="jam_mulai" id="edMulai" required></div>
                    <div class="fld"><label>Jam Selesai</label><input type="time" name="jam_selesai" id="edSelesai" required></div>
                    <div class="fld full"><label>Lokasi</label><input type="text" name="lokasi" id="edLokasi" required></div>
                    <div class="fld full"><label>Status</label>
                        <select name="status" id="edStatus">
                            <option value="terjadwal">Terjadwal</option><option value="selesai">Selesai</option><option value="dibatalkan">Dibatalkan</option>
                        </select>
                    </div>
                    <div class="fld full"><label>Keterangan</label><input type="text" name="keterangan" id="edKet"></div>
                </div>
            </div>
            <div class="md-foot">
                <button type="button" class="btn-secondary" onclick="closeModal('mdEdit')">Batal</button>
                <button type="submit" class="btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════ MODAL HAPUS ══════════ --}}
<div class="md-overlay" id="mdHapus">
    <div class="md-box sm">
        <form method="POST" id="formHapus">
            @csrf @method('DELETE')
            <div class="md-body" style="text-align:center;padding:28px 24px 18px;">
                <div class="del-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/></svg>
                </div>
                <div style="font-size:16px;font-weight:700;color:#1e3528;">Hapus jadwal ini?</div>
                <div class="sub" id="hpNama" style="margin-top:6px;"></div>
            </div>
            <div class="md-foot">
                <button type="button" class="btn-secondary" onclick="closeModal('mdHapus')">Batal</button>
                <button type="submit" class="btn-danger">Ya, Hapus</button>
            </div>
        </form>
    </div>
</div>

@endsection

@include('partials.ui')

@push('scripts')
<script>
    const KEG = @json($kegiatanJson);
    const BASE = "{{ url('kegiatan') }}";

    function bukaDetail(id) {
        const d = KEG[id]; if (!d) return;
        document.getElementById('dtNama').textContent = d.nama;
        document.getElementById('dtJenis').textContent = d.jenis_label + ' • ' + d.trimester_label;
        const items = [
            ['Tanggal', d.tanggal_label], ['Waktu', d.jam],
            ['Lokasi', d.lokasi], ['Status', d.status_label],
            ['Peserta tercatat', d.jumlah_kunjungan + ' kunjungan'],
            ['Keterangan', d.keterangan || '—'],
        ];
        document.getElementById('dtGrid').innerHTML = items.map(([l, v]) =>
            `<div class="dt-item${l === 'Keterangan' ? ' full' : ''}"><div class="dt-lbl">${l}</div><div class="dt-val">${v ?? '—'}</div></div>`
        ).join('');
        openModal('mdDetail');
    }

    function bukaEdit(id) {
        const d = KEG[id]; if (!d) return;
        document.getElementById('formEdit').action = BASE + '/' + id;
        document.getElementById('edSub').textContent = d.nama;
        const set = (el, v) => document.getElementById(el).value = v ?? '';
        set('edNama', d.nama); set('edJenis', d.jenis); set('edTanggal', d.tanggal);
        set('edTarget', d.trimester_target); set('edMulai', d.jam_mulai); set('edSelesai', d.jam_selesai);
        set('edLokasi', d.lokasi); set('edStatus', d.status); set('edKet', d.keterangan);
        openModal('mdEdit');
    }

    function bukaHapus(id, nama) {
        document.getElementById('formHapus').action = BASE + '/' + id;
        document.getElementById('hpNama').textContent = nama;
        openModal('mdHapus');
    }

    @if ($errors->any())
        openModal('mdTambah');
    @endif
</script>
@endpush
