@extends('layouts.app')

@section('title', 'Data Ibu Hamil')
@section('breadcrumb', 'Data Ibu Hamil')
@section('nav-active-ibu-hamil', 'active')

@push('head')
<style>
    .risk { padding: 3px 9px; border-radius: 20px; font-size: 10.5px; font-weight: 700; letter-spacing: .02em; display: inline-block; white-space: nowrap; }
    .risk-krr  { background: #e8f1eb; color: #4f7561; }
    .risk-krt  { background: #fbf3e3; color: #8a6a2a; }
    .risk-krst { background: #fdf1f0; color: #b3261e; }
    .tm { padding: 2px 8px; border-radius: 6px; font-size: 10.5px; font-weight: 700; background: #f0f5f1; color: #758179; }
    .wk { font-weight: 700; color: #233c32; font-size: 13.5px; }
    .wk small { font-weight: 500; color: #758179; font-size: 11px; }
    .bar { height: 5px; border-radius: 3px; background: #eef3ef; overflow: hidden; margin-top: 5px; }
    .bar > i { display: block; height: 100%; border-radius: 3px; background: #4f7561; }
    .filter-row { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
    .stat-mini { display: flex; gap: 16px; flex-wrap: wrap; }
    .stat-mini .card { flex: 1; min-width: 160px; display: flex; flex-direction: column; gap: 9px; padding: 16px; }
    /* Kartu pencarian & filter — label di atas tiap kontrol */
    .filter-card {
        display: flex; gap: 16px; align-items: flex-end; flex-wrap: wrap;
        padding: 20px; border-radius: 14px; background: #fff;
        border: 1px solid #e4eae5; margin-bottom: 16px;
    }
    .filter-field { display: flex; flex-direction: column; gap: 8px; }
    .filter-field > label { font-size: 13px; font-weight: 500; color: #233c32; }
    .filter-field.w-cari { flex: 1 1 320px; max-width: 412px; }
    .filter-field.w-pilih { flex: 0 0 220px; }
    /* tinggi kontrol filter disamakan dengan halaman lain (40px) */
    .filter-card .form-control { height: 40px; }
    .filter-card .btn-primary { height: 40px; }
</style>
@endpush

@section('content')

{{-- ── Judul halaman ───────────────────────────── --}}
<div class="page-heading" style="margin-bottom:24px;">
    <div class="title-group">
        <h1>Data Ibu Hamil</h1>
        <p>Kelola data kehamilan dan pantau kondisi ibu hamil.</p>
    </div>
    <button class="btn-primary" onclick="bukaTambah()">
        <img src="/icons/plus.svg" alt="" style="width:16px;height:16px;"> Tambah Ibu Hamil
    </button>
</div>

{{-- ── Kartu ringkasan ─────────────────────────── --}}
<div class="stat-mini" style="margin-bottom:18px;">
    <div class="card">
        <span class="stat-label">Total Ibu Hamil</span>
        <div class="stat-num">{{ $statistik['total'] }}</div>
        <span class="stat-label">{{ $statistik['aktif'] }} berstatus aktif</span>
    </div>
    <div class="card">
        <span class="stat-label">Trimester 1</span>
        <div class="stat-num">{{ $statistik['tm1'] }}</div>
        <span class="stat-label">0–12 minggu</span>
    </div>
    <div class="card">
        <span class="stat-label">Trimester 2</span>
        <div class="stat-num">{{ $statistik['tm2'] }}</div>
        <span class="stat-label">13–27 minggu</span>
    </div>
    <div class="card">
        <span class="stat-label">Trimester 3</span>
        <div class="stat-num">{{ $statistik['tm3'] }}</div>
        <span class="stat-label">28 minggu ke atas</span>
    </div>
    <div class="card">
        <span class="stat-label">Risiko Tinggi</span>
        <div class="stat-num" style="color:#b3261e;">{{ $statistik['risiko'] }}</div>
        <span class="stat-label">KRT &amp; KRST</span>
    </div>
    <div class="card">
        <span class="stat-label">Kurang Energi (KEK)</span>
        <div class="stat-num" style="color:#8a6a2a;">{{ $statistik['kek'] }}</div>
        <span class="stat-label">LiLA &lt; 23,5 cm</span>
    </div>
</div>

{{-- ── Pencarian dan filter ────────────────────── --}}
<form method="GET" action="{{ route('ibu-hamil.index') }}" class="filter-card">
    <div class="filter-field w-cari">
        <label for="q">Cari ibu hamil</label>
        <div class="search-bar">
            <img class="search-icon" src="/icons/search.svg" alt="">
            <input type="text" id="q" name="q" class="form-control"
                   value="{{ $kata }}" placeholder="Cari nama, NIK, atau suami">
        </div>
    </div>

    <div class="filter-field w-pilih">
        <label for="filter">Trimester</label>
        <select id="filter" name="filter" class="form-control" onchange="this.form.submit()">
            <option value="">Semua trimester</option>
            <option value="tm1" @selected($filter === 'tm1')>Trimester 1</option>
            <option value="tm2" @selected($filter === 'tm2')>Trimester 2</option>
            <option value="tm3" @selected($filter === 'tm3')>Trimester 3</option>
        </select>
    </div>

    <div class="filter-field w-pilih">
        <label for="risiko">Kategori risiko</label>
        <select id="risiko" name="risiko" class="form-control" onchange="this.form.submit()">
            <option value="">Semua risiko</option>
            <option value="KRR"  @selected($risiko === 'KRR')>Risiko Rendah</option>
            <option value="KRT"  @selected($risiko === 'KRT')>Risiko Tinggi</option>
            <option value="KRST" @selected($risiko === 'KRST')>Risiko Sangat Tinggi</option>
        </select>
    </div>

    <button type="submit" class="btn-primary">Terapkan</button>
</form>

{{-- ── Tabel ───────────────────────────────────── --}}
<div class="card" style="padding:0;overflow:hidden;">
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Ibu Hamil</th>
                    <th>Usia Kandungan</th>
                    <th>HPL</th>
                    <th>Risiko</th>
                    <th>Kunjungan ANC</th>
                    <th style="text-align:right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($daftar as $i)
                <tr>
                    <td data-l="Ibu Hamil">
                        <div class="cell">
                            <div class="av">{{ strtoupper(mb_substr($i->nama, 0, 1)) }}{{ strtoupper(mb_substr(explode(' ', $i->nama)[1] ?? '', 0, 1)) }}</div>
                            <div style="min-width:0;">
                                <div class="nm">{{ $i->nama }}</div>
                                <div class="sub mono">{{ $i->nik }}</div>
                            </div>
                        </div>
                    </td>
                    <td data-l="Usia Kandungan">
                        <div class="wk">{{ $i->usia_kehamilan }} <small>minggu</small></div>
                        <span class="tm">Trimester {{ $i->trimester }}</span>
                    </td>
                    <td data-l="HPL">
                        <div class="nm" style="font-size:13px;">{{ $i->hpl?->translatedFormat('d M Y') ?? '—' }}</div>
                        <div class="sub">
                            @if ($i->sisa_hari !== null)
                                {{ $i->sisa_hari >= 0 ? $i->sisa_hari . ' hari lagi' : 'Sudah lewat ' . abs($i->sisa_hari) . ' hari' }}
                            @endif
                        </div>
                    </td>
                    <td data-l="Risiko">
                        <span class="risk risk-{{ strtolower($i->kategori_risiko) }}">{{ $i->kategori_risiko }}</span>
                        <div class="sub">skor {{ $i->skor_kspr }}</div>
                    </td>
                    <td data-l="Kunjungan ANC">
                        <div class="nm" style="font-size:13px;">{{ $i->jumlah_kunjungan }}× kunjungan</div>
                        <div class="sub">{{ $i->status_anc_label }}</div>
                        @if ($i->terlambat_anc)
                            <div class="sub" style="color:#b3261e;font-weight:600;">⚠ Terlambat ANC</div>
                        @endif
                    </td>
                    <td data-l="Aksi">
                        <div class="act-group">
                            <button class="act-btn" title="Detail" onclick="bukaDetail({{ $i->id }})">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                            <button class="act-btn" title="Ubah" onclick="bukaEdit({{ $i->id }})">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                            </button>
                            <button class="act-btn danger" title="Hapus" onclick="bukaHapus({{ $i->id }}, '{{ addslashes($i->nama) }}')">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <div class="empty-icon"><img src="/icons/users-round.svg" alt="" style="width:22px;height:22px;opacity:.4;"></div>
                            <div style="font-weight:600;color:#758179;">Belum ada data ibu hamil</div>
                            <div class="sub" style="margin-top:4px;">Tambahkan data sasaran untuk mulai memantau kehamilan.</div>
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
    <div class="md-box lg">
        <form method="POST" action="{{ route('ibu-hamil.store') }}">
            @csrf
            <div class="md-head">
                <div>
                    <div class="md-title">Tambah Ibu Hamil</div>
                    <div class="md-desc">Data sasaran posyandu beserta riwayat obstetri</div>
                </div>
                <button type="button" class="md-close" onclick="closeModal('mdTambah')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="md-body">
                <div class="form-section">
                    <div class="form-section-title">Identitas</div>
                    <div class="fgrid">
                        <div class="fld full">
                            <label>NIK <span class="req">*</span></label>
                            <input type="text" name="nik" maxlength="16" inputmode="numeric" placeholder="16 digit" value="{{ old('nik') }}" required>
                            @error('nik') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="fld"><label>Nama Lengkap <span class="req">*</span></label><input type="text" name="nama" value="{{ old('nama') }}" required></div>
                        <div class="fld"><label>Tanggal Lahir <span class="req">*</span></label><input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}" required></div>
                        <div class="fld full"><label>Alamat <span class="req">*</span></label><input type="text" name="alamat" value="{{ old('alamat') }}" required></div>
                        <div class="fld"><label>No. HP Ibu</label><input type="text" name="no_hp" value="{{ old('no_hp') }}"></div>
                        <div class="fld"><label>Golongan Darah <span class="req">*</span></label>
                            <select name="golongan_darah" required>
                                @foreach (['-','A','B','AB','O'] as $g)<option value="{{ $g }}" @selected(old('golongan_darah') === $g)>{{ $g }}</option>@endforeach
                            </select>
                        </div>
                        <div class="fld"><label>Nama Suami</label><input type="text" name="nama_suami" value="{{ old('nama_suami') }}"></div>
                        <div class="fld"><label>No. HP Suami</label><input type="text" name="no_hp_suami" value="{{ old('no_hp_suami') }}"></div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-title">Data Kehamilan</div>
                    <div class="fgrid">
                        <div class="fld"><label>HPHT <span class="req">*</span></label><input type="date" name="hpht" value="{{ old('hpht') }}" required>
                            <div class="form-hint">Hari pertama haid terakhir — dasar usia kehamilan &amp; HPL</div>
                        </div>
                        <div class="fld"><label>Status <span class="req">*</span></label>
                            <select name="status" required>
                                <option value="aktif">Aktif</option>
                                <option value="selesai">Selesai</option>
                                <option value="rujuk">Dirujuk</option>
                            </select>
                        </div>
                        <div class="fld"><label>Gravida (G) <span class="req">*</span></label><input type="number" name="gravida" min="1" max="20" value="{{ old('gravida', 1) }}" required></div>
                        <div class="fld"><label>Para (P) <span class="req">*</span></label><input type="number" name="para" min="0" max="20" value="{{ old('para', 0) }}" required></div>
                        <div class="fld"><label>Abortus (A) <span class="req">*</span></label><input type="number" name="abortus" min="0" max="20" value="{{ old('abortus', 0) }}" required></div>
                        <div class="fld"><label>Jarak dari Persalinan Terakhir (tahun)</label><input type="number" name="jarak_hamil_tahun" step="0.1" min="0" max="30" value="{{ old('jarak_hamil_tahun') }}"></div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-title">Antropometri Pra-Hamil</div>
                    <div class="fgrid">
                        <div class="fld"><label>Tinggi Badan (cm) <span class="req">*</span></label><input type="number" name="tinggi_badan" step="0.1" min="100" max="200" value="{{ old('tinggi_badan') }}" required></div>
                        <div class="fld"><label>Berat Badan Pra-Hamil (kg) <span class="req">*</span></label><input type="number" name="berat_pra_hamil" step="0.1" min="25" max="150" value="{{ old('berat_pra_hamil') }}" required></div>
                        <div class="fld full"><label>LiLA Pra-Hamil (cm)</label><input type="number" name="lila_pra_hamil" step="0.1" min="10" max="50" value="{{ old('lila_pra_hamil') }}">
                            <div class="form-hint">LiLA &lt; 23,5 cm menandakan Kurang Energi Kronik (KEK)</div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-title">Riwayat Obstetri</div>
                    <div class="fgrid">
                        <label class="fld" style="flex-direction:row;align-items:center;gap:8px;"><input type="checkbox" name="pernah_sesar" value="1" style="width:16px;height:16px;"> <span>Pernah operasi sesar</span></label>
                        <label class="fld" style="flex-direction:row;align-items:center;gap:8px;"><input type="checkbox" name="pernah_keguguran" value="1" style="width:16px;height:16px;"> <span>Pernah keguguran</span></label>
                        <label class="fld" style="flex-direction:row;align-items:center;gap:8px;"><input type="checkbox" name="pernah_preeklamsia" value="1" style="width:16px;height:16px;"> <span>Riwayat preeklamsia</span></label>
                        <label class="fld" style="flex-direction:row;align-items:center;gap:8px;"><input type="checkbox" name="pernah_lahir_tindakan" value="1" style="width:16px;height:16px;"> <span>Persalinan dengan tindakan</span></label>
                        <div class="fld full"><label>Riwayat Penyakit</label><input type="text" name="riwayat_penyakit" value="{{ old('riwayat_penyakit') }}" placeholder="cth. Hipertensi, Diabetes"></div>
                    </div>
                </div>
            </div>
            <div class="md-foot">
                <button type="button" class="btn-secondary" onclick="closeModal('mdTambah')">Batal</button>
                <button type="submit" class="btn-primary">Simpan Data</button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════ MODAL DETAIL ══════════ --}}
<div class="md-overlay" id="mdDetail">
    <div class="md-box lg">
        <div class="md-head">
            <div>
                <div class="md-title" id="dtNama">—</div>
                <div class="md-desc" id="dtSub">—</div>
            </div>
            <button type="button" class="md-close" onclick="closeModal('mdDetail')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="md-body">
            <div id="dtRisiko"></div>
            <div class="dt-grid" id="dtGrid"></div>
        </div>
        <div class="md-foot">
            <button type="button" class="btn-secondary" onclick="closeModal('mdDetail')">Tutup</button>
            <a href="#" id="dtLink" class="btn-primary">Buka Kartu Ibu</a>
        </div>
    </div>
</div>

{{-- ══════════ MODAL UBAH ══════════ --}}
<div class="md-overlay" id="mdEdit">
    <div class="md-box lg">
        <form method="POST" id="formEdit">
            @csrf @method('PUT')
            <div class="md-head">
                <div>
                    <div class="md-title">Ubah Data Ibu Hamil</div>
                    <div class="md-desc" id="edSub">—</div>
                </div>
                <button type="button" class="md-close" onclick="closeModal('mdEdit')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="md-body">
                <div class="fgrid">
                    <div class="fld full"><label>NIK <span class="req">*</span></label><input type="text" name="nik" id="edNik" maxlength="16" required></div>
                    <div class="fld"><label>Nama Lengkap <span class="req">*</span></label><input type="text" name="nama" id="edNama" required></div>
                    <div class="fld"><label>Tanggal Lahir <span class="req">*</span></label><input type="date" name="tanggal_lahir" id="edLahir" required></div>
                    <div class="fld full"><label>Alamat <span class="req">*</span></label><input type="text" name="alamat" id="edAlamat" required></div>
                    <div class="fld"><label>No. HP Ibu</label><input type="text" name="no_hp" id="edHp"></div>
                    <div class="fld"><label>Golongan Darah</label>
                        <select name="golongan_darah" id="edGoldar">
                            @foreach (['-','A','B','AB','O'] as $g)<option value="{{ $g }}">{{ $g }}</option>@endforeach
                        </select>
                    </div>
                    <div class="fld"><label>Nama Suami</label><input type="text" name="nama_suami" id="edSuami"></div>
                    <div class="fld"><label>No. HP Suami</label><input type="text" name="no_hp_suami" id="edHpSuami"></div>
                    <div class="fld"><label>HPHT <span class="req">*</span></label><input type="date" name="hpht" id="edHpht" required></div>
                    <div class="fld"><label>Status</label>
                        <select name="status" id="edStatus">
                            <option value="aktif">Aktif</option>
                            <option value="selesai">Selesai</option>
                            <option value="rujuk">Dirujuk</option>
                        </select>
                    </div>
                    <div class="fld"><label>Gravida</label><input type="number" name="gravida" id="edG" min="1" max="20" required></div>
                    <div class="fld"><label>Para</label><input type="number" name="para" id="edP" min="0" max="20" required></div>
                    <div class="fld"><label>Abortus</label><input type="number" name="abortus" id="edA" min="0" max="20" required></div>
                    <div class="fld"><label>Jarak Persalinan (tahun)</label><input type="number" name="jarak_hamil_tahun" id="edJarak" step="0.1" min="0" max="30"></div>
                    <div class="fld"><label>Tinggi Badan (cm)</label><input type="number" name="tinggi_badan" id="edTb" step="0.1" required></div>
                    <div class="fld"><label>Berat Pra-Hamil (kg)</label><input type="number" name="berat_pra_hamil" id="edBb" step="0.1" required></div>
                    <div class="fld full"><label>LiLA Pra-Hamil (cm)</label><input type="number" name="lila_pra_hamil" id="edLila" step="0.1"></div>
                    <label class="fld full" style="flex-direction:row;align-items:center;gap:8px;"><input type="checkbox" name="pernah_sesar" id="edSesar" value="1" style="width:16px;height:16px;"> <span>Pernah operasi sesar</span></label>
                    <label class="fld full" style="flex-direction:row;align-items:center;gap:8px;"><input type="checkbox" name="pernah_keguguran" id="edKeguguran" value="1" style="width:16px;height:16px;"> <span>Pernah keguguran</span></label>
                    <label class="fld full" style="flex-direction:row;align-items:center;gap:8px;"><input type="checkbox" name="pernah_preeklamsia" id="edPreeklamsia" value="1" style="width:16px;height:16px;"> <span>Riwayat preeklamsia</span></label>
                    <label class="fld full" style="flex-direction:row;align-items:center;gap:8px;"><input type="checkbox" name="pernah_lahir_tindakan" id="edTindakan" value="1" style="width:16px;height:16px;"> <span>Persalinan dengan tindakan</span></label>
                    <div class="fld full"><label>Riwayat Penyakit</label><input type="text" name="riwayat_penyakit" id="edPenyakit"></div>
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
                <div style="font-size:16px;font-weight:700;color:#233c32;">Hapus data ibu hamil?</div>
                <div class="sub" id="hpNama" style="margin-top:6px;"></div>
                <div class="sub" style="margin-top:10px;color:#b3261e;">Seluruh riwayat kunjungan ANC miliknya ikut terhapus dan tidak dapat dikembalikan.</div>
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
    const IBU = @json($ibuJson);
    const BASE = "{{ url('ibu-hamil') }}";

    function bukaTambah() { openModal('mdTambah'); }

    function bukaDetail(id) {
        const d = IBU[id];
        if (!d) return;
        document.getElementById('dtNama').textContent = d.nama;
        document.getElementById('dtSub').textContent = d.nik + ' • ' + d.umur + ' tahun • ' + d.status_label;
        document.getElementById('dtLink').href = BASE + '/' + id;

        const faktor = (d.faktor_risiko || []).length
            ? d.faktor_risiko.map(f => `<span class="risk risk-${d.kategori_risiko.toLowerCase()}" style="margin:0 4px 4px 0;">${f.label} (+${f.skor})</span>`).join('')
            : '<span class="sub">Tidak ada faktor risiko terdeteksi</span>';

        document.getElementById('dtRisiko').innerHTML = `
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:6px;">
                <span class="risk risk-${d.kategori_risiko.toLowerCase()}" style="font-size:11.5px;padding:5px 12px;">${d.kategori_risiko} — ${d.risiko_label}</span>
                <span class="sub">Skor KSPR: <b>${d.skor_kspr}</b></span>
            </div>
            <div>${faktor}</div>`;

        const items = [
            ['Usia Kandungan', d.usia_label], ['Trimester', 'Trimester ' + d.trimester],
            ['HPL', d.hpl], ['GPA', d.gpa],
            ['Tinggi Badan', d.tinggi_badan + ' cm'], ['Berat Pra-Hamil', d.berat_pra_hamil + ' kg'],
            ['IMT', d.imt + ' (' + d.imt_kategori + ')'], ['LiLA', (d.lila ?? '—') + (d.kek ? ' cm — KEK' : ' cm')],
            ['Golongan Darah', d.golongan_darah], ['Kunjungan ANC', d.jumlah_kunjungan + '× • ' + d.status_anc_label],
            ['Suami', d.nama_suami || '—'], ['No. HP', d.no_hp || '—'],
            ['Alamat', d.alamat], ['Riwayat Penyakit', d.riwayat_penyakit || '—'],
        ];
        document.getElementById('dtGrid').innerHTML = items.map(([l, v]) =>
            `<div class="dt-item${l === 'Alamat' ? ' full' : ''}"><div class="dt-lbl">${l}</div><div class="dt-val">${v ?? '—'}</div></div>`
        ).join('');
        openModal('mdDetail');
    }

    function bukaEdit(id) {
        const d = IBU[id];
        if (!d) return;
        document.getElementById('formEdit').action = BASE + '/' + id;
        document.getElementById('edSub').textContent = d.nama + ' • ' + d.nik;
        const set = (el, v) => document.getElementById(el).value = v ?? '';
        set('edNik', d.nik); set('edNama', d.nama); set('edLahir', d.tanggal_lahir);
        set('edAlamat', d.alamat); set('edHp', d.no_hp); set('edSuami', d.nama_suami);
        set('edHpSuami', d.no_hp_suami); set('edGoldar', d.golongan_darah); set('edHpht', d.hpht);
        set('edStatus', d.status); set('edG', d.gravida); set('edP', d.para); set('edA', d.abortus);
        set('edJarak', d.jarak_hamil_tahun); set('edTb', d.tinggi_badan); set('edBb', d.berat_pra_hamil);
        set('edLila', d.lila); set('edPenyakit', d.riwayat_penyakit);
        document.getElementById('edSesar').checked = d.pernah_sesar;
        document.getElementById('edKeguguran').checked = d.pernah_keguguran;
        document.getElementById('edPreeklamsia').checked = d.pernah_preeklamsia;
        document.getElementById('edTindakan').checked = d.pernah_lahir_tindakan;
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
