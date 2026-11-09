@extends('layouts.app')
@section('title','Riwayat Kehadiran')
@section('breadcrumb','Riwayat Kehadiran')
@section('nav-active-kehadiran','active')

@include('partials.ui')

@push('head')
<style>
    /* Daftar checkbox peserta di modal catat */
    .pick-list {
        display: flex; flex-direction: column; gap: 6px;
        max-height: 300px; overflow-y: auto;
        border: 1px solid #e3eae4; border-radius: 9px; padding: 10px;
    }
    .pick-item {
        display: flex; align-items: center; gap: 11px;
        padding: 8px 10px; border-radius: 7px; cursor: pointer;
        transition: background .12s ease;
    }
    .pick-item:hover { background: #f5f9f5; }
    .pick-item input[type="checkbox"] {
        width: 16px; height: 16px; accent-color: #4f7561; cursor: pointer; flex-shrink: 0;
    }
    .pick-item .nm { flex: 1; font-weight: 500; }
    .pick-empty { font-size: 12.5px; color: #90a898; padding: 12px; text-align: center; }

    /* Chip kegiatan */
    .chip {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 500;
        border: 1px solid #dde5de; background: #fff; color: #3a5540;
        cursor: pointer; transition: all .15s ease; text-decoration: none;
    }
    .chip:hover { background: #e6f1ea; border-color: #b9d2c1; transform: translateY(-1px); }
    .chip:active { transform: translateY(0); }
    .chip .dot { width: 6px; height: 6px; border-radius: 50%; background: #4f7561; }

    /* Daftar hadir di modal detail */
    .att-list { display: flex; flex-direction: column; gap: 7px; max-height: 210px; overflow-y: auto; }
    .att-row {
        display: flex; align-items: center; gap: 9px;
        padding: 7px 10px; border-radius: 8px; background: #f8faf8; font-size: 13px;
    }
    .att-row .nm { flex: 1; font-weight: 500; color: #1e3528; }
</style>
@endpush

@section('content')
{{-- Header --}}
<div class="page-heading">
    <div class="title-group">
        <h1>Riwayat Kehadiran</h1>
        <p>Catat dan pantau kehadiran peserta posyandu.</p>
    </div>
    <button type="button" class="btn-primary" onclick="openCatat()">
        <img src="/icons/plus.svg" alt=""> Catat kehadiran
    </button>
</div>

{{-- Statistik --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:14px;">
    @foreach([
        ['Hadir bulan ini',$totalHadirBulanIni,now()->translatedFormat('F Y')],
        ['Kegiatan bulan ini',$kegiatanBulanIni,'Terjadwal & selesai'],
        ['Total peserta',$peserta->count(),'Terdaftar'],
    ] as [$lbl,$val,$sub])
    <div class="card" style="display:flex;flex-direction:column;gap:7px;padding:16px 18px;">
        <span class="stat-label">{{ $lbl }}</span>
        <span class="stat-num" style="font-size:26px;">{{ $val }}</span>
        <span style="font-size:10.5px;color:#b0bdb5;">{{ $sub }}</span>
    </div>
    @endforeach
</div>

{{-- Chip per kegiatan --}}
@if($kegiatan->isNotEmpty())
<div class="card" style="padding:14px 16px;display:flex;flex-direction:column;gap:10px;">
    <span style="font-size:13px;font-weight:600;color:#1e3528;">Lihat kehadiran per kegiatan</span>
    <div style="display:flex;flex-wrap:wrap;gap:7px;">
        @foreach($kegiatan->take(12) as $kg)
        <button type="button" class="chip" onclick="openKegiatan({{ $kg->id }})">
            <span class="dot" style="background:{{ $kg->status === 'terjadwal' ? '#4f7561' : ($kg->status === 'dibatalkan' ? '#c0392b' : '#90a898') }}"></span>
            {{ $kg->nama }} · {{ $kg->tanggal ? $kg->tanggal->format('d/m') : '—' }}
        </button>
        @endforeach
    </div>
</div>
@endif

{{-- Tabel kehadiran bulan ini --}}
<div class="card" style="padding:0;overflow:hidden;">
    <div style="padding:16px 18px;border-bottom:1px solid #e3eae4;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
        <div>
            <div style="font-size:15px;font-weight:600;color:#1e3528;">Data kehadiran</div>
            <div style="font-size:12px;color:#90a898;margin-top:2px;">
                @if($periode === 'bulan') {{ now()->translatedFormat('F Y') }}
                @elseif($periode === '3bulan') 3 bulan terakhir
                @else Semua periode @endif
            </div>
        </div>
        <form method="GET" action="{{ route('kehadiran.index') }}">
            <select name="periode" class="form-control" style="height:36px;width:auto;min-width:160px;font-size:12.5px;" onchange="this.form.submit()">
                <option value="bulan"  {{ $periode==='bulan'?'selected':'' }}>Bulan ini</option>
                <option value="3bulan" {{ $periode==='3bulan'?'selected':'' }}>3 bulan terakhir</option>
                <option value="semua"  {{ $periode==='semua'?'selected':'' }}>Semua periode</option>
            </select>
        </form>
    </div>

    @if($kehadiranList->isEmpty())
    <div class="empty-state">
        <div class="empty-icon"><img src="/icons/clipboard-check.svg" alt=""></div>
        <h2>Belum ada data kehadiran</h2>
        <p>Mulai catat kehadiran untuk kegiatan yang sudah berlangsung.</p>
        <button type="button" class="btn-primary" onclick="openCatat()">Catat kehadiran</button>
    </div>
    @else
    <div style="overflow-x:auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Peserta</th>
                    <th>Kegiatan</th>
                    <th style="width:120px;">Tanggal</th>
                    <th style="width:110px;">Kategori</th>
                    <th style="width:110px;">Status</th>
                    <th style="width:80px;text-align:right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($kehadiranList as $kh)
                <tr>
                    <td data-l="Peserta">
                        <div class="cell">
                            <div class="av">{{ strtoupper(substr($kh->peserta->nama,0,2)) }}</div>
                            <div style="min-width:0;">
                                <div class="nm">{{ $kh->peserta->nama }}</div>
                                <div class="sub mono">{{ $kh->peserta->nik }}</div>
                            </div>
                        </div>
                    </td>
                    <td data-l="Kegiatan" style="font-size:13px;">{{ $kh->kegiatan->nama }}</td>
                    <td data-l="Tanggal" style="font-size:12.5px;white-space:nowrap;color:#6b8075;">
                        {{ $kh->kegiatan->tanggal ? $kh->kegiatan->tanggal->translatedFormat('d M Y') : '—' }}
                    </td>
                    <td data-l="Kategori">
                        <span class="badge badge-green">{{ $kh->peserta->kategori_label }}</span>
                    </td>
                    <td data-l="Status">
                        <span class="badge {{ $kh->status === 'hadir' ? 'badge-green' : 'badge-red' }}">
                            {{ ucfirst(str_replace('_',' ',$kh->status)) }}
                        </span>
                    </td>
                    <td data-l="Aksi">
                        <div class="act-group">
                            <button type="button" class="act-btn" title="Lihat detail" onclick="openDetailKehadiran({{ $kh->id }})">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                            <button type="button" class="act-btn" title="Edit kehadiran" onclick="openEditKehadiran({{ $kh->id }})">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                                </svg>
                            </button>
                            <button type="button" class="act-btn danger" title="Hapus data kehadiran" onclick="openHapus({{ $kh->id }})">
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
        <span style="font-size:12px;color:#90a898;">Menampilkan {{ $kehadiranList->firstItem() }}–{{ $kehadiranList->lastItem() }} dari {{ $kehadiranList->total() }} data</span>
        <div class="pagination-wrap">{{ $kehadiranList->links('vendor.pagination.simple-tailwind') }}</div>
    </div>
    @endif
</div>

{{-- ══════════ MODAL: CATAT KEHADIRAN ══════════ --}}
<div class="md-overlay" id="mdCatat" onclick="if(event.target===this)closeModal('mdCatat')">
    <div class="md-box" role="dialog" aria-modal="true">
        <form method="POST" action="{{ route('kehadiran.store') }}" id="formCatat">
            @csrf
            <div class="md-head">
                <div>
                    <div class="md-title">Catat kehadiran</div>
                    <div class="md-desc">Pilih kegiatan, lalu centang peserta yang hadir.</div>
                </div>
                <button type="button" class="md-close" onclick="closeModal('mdCatat')" aria-label="Tutup">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="md-body">
                <div class="fld">
                    <label>Kegiatan <span class="req">*</span></label>
                    <select name="kegiatan_id" id="kat_select" required onchange="filterPeserta(this.value)">
                        <option value="">Pilih kegiatan...</option>
                        @foreach($kegiatan as $kg)
                        <option value="{{ $kg->id }}" data-kat="{{ $kg->kategori }}">
                            {{ $kg->nama }} — {{ $kg->tanggal ? $kg->tanggal->translatedFormat('d M Y') : '' }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div id="peserta-section" style="display:none;flex-direction:column;gap:10px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;">
                        <label style="font-size:12.5px;font-weight:600;color:#2d5540;">
                            Peserta hadir <span id="pick-count" style="color:#90a898;font-weight:500;"></span>
                        </label>
                        <div style="display:flex;gap:7px;">
                            <button type="button" class="btn-secondary btn-sm" onclick="checkAll(true)">Pilih semua</button>
                            <button type="button" class="btn-secondary btn-sm" onclick="checkAll(false)">Batalkan</button>
                        </div>
                    </div>
                    <div class="pick-list" id="peserta-list"></div>
                    <div style="font-size:11.5px;color:#90a898;line-height:1.6;">
                        Peserta yang tidak dicentang otomatis tercatat sebagai <strong>tidak hadir</strong>.
                        Hanya peserta sesuai kategori kegiatan yang ditampilkan (kegiatan "Umum" menampilkan semua).
                    </div>
                </div>
            </div>
            <div class="md-foot">
                <button type="button" class="btn-secondary" onclick="closeModal('mdCatat')">Batal</button>
                <button type="submit" class="btn-primary" id="submit-catat" disabled>Simpan kehadiran</button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════ MODAL: DETAIL PER KEGIATAN ══════════ --}}
<div class="md-overlay" id="mdKegiatan" onclick="if(event.target===this)closeModal('mdKegiatan')">
    <div class="md-box lg" role="dialog" aria-modal="true">
        <div class="md-head">
            <div>
                <div class="md-title" id="k_nama">—</div>
                <div class="md-desc" id="k_sub">—</div>
            </div>
            <button type="button" class="md-close" onclick="closeModal('mdKegiatan')" aria-label="Tutup">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="md-body">
            <div class="dt-grid">
                <div class="dt-item"><span class="dt-lbl">Lokasi</span><span class="dt-val" id="k_lok">—</span></div>
                <div class="dt-item"><span class="dt-lbl">Kategori</span><span class="dt-val" id="k_kat">—</span></div>
                <div class="dt-item"><span class="dt-lbl">Status</span><span class="dt-val" id="k_status">—</span></div>
                <div class="dt-item"><span class="dt-lbl">Waktu</span><span class="dt-val" id="k_jam">—</span></div>
            </div>

            <div>
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:9px;">
                    <span style="font-size:13px;font-weight:600;color:#1e3528;">Hadir</span>
                    <span class="badge badge-green" id="k_nhadir">0</span>
                </div>
                <div class="att-list" id="k_hadir"></div>
            </div>

            <div id="k_tidak_wrap">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:9px;">
                    <span style="font-size:13px;font-weight:600;color:#1e3528;">Tidak hadir</span>
                    <span class="badge badge-red" id="k_ntidak">0</span>
                </div>
                <div class="att-list" id="k_tidak"></div>
            </div>
        </div>
        <div class="md-foot">
            <button type="button" class="btn-secondary" onclick="closeModal('mdKegiatan')">Tutup</button>
            <button type="button" class="btn-primary" id="k_catatbtn">Catat / ubah kehadiran</button>
        </div>
    </div>
</div>

{{-- ══════════ MODAL: DETAIL KEHADIRAN ══════════ --}}
<div class="md-overlay" id="mdDetailKehadiran" onclick="if(event.target===this)closeModal('mdDetailKehadiran')">
    <div class="md-box" role="dialog" aria-modal="true">
        <div class="md-head">
            <div>
                <div class="md-title">Detail kehadiran</div>
                <div class="md-desc" id="dk_sub">—</div>
            </div>
            <button type="button" class="md-close" onclick="closeModal('mdDetailKehadiran')" aria-label="Tutup">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="md-body">
            <div class="dt-head">
                <div class="av lg" id="dk_av">—</div>
                <div style="min-width:0;">
                    <div style="font-size:17px;font-weight:700;color:#1e3528;" id="dk_nama">—</div>
                    <div style="display:flex;gap:6px;margin-top:5px;flex-wrap:wrap;">
                        <span class="badge badge-green" id="dk_kat">—</span>
                        <span class="badge badge-gray" id="dk_status">—</span>
                    </div>
                </div>
            </div>
            <div class="dt-grid">
                <div class="dt-item"><span class="dt-lbl">NIK</span><span class="dt-val" id="dk_nik">—</span></div>
                <div class="dt-item"><span class="dt-lbl">Status kehadiran</span><span class="dt-val" id="dk_status2">—</span></div>
                <div class="dt-item full"><span class="dt-lbl">Kegiatan</span><span class="dt-val" id="dk_kegiatan">—</span></div>
                <div class="dt-item"><span class="dt-lbl">Tanggal</span><span class="dt-val" id="dk_tgl">—</span></div>
                <div class="dt-item"><span class="dt-lbl">Waktu</span><span class="dt-val" id="dk_jam">—</span></div>
                <div class="dt-item full"><span class="dt-lbl">Lokasi</span><span class="dt-val" id="dk_lokasi">—</span></div>
                <div class="dt-item full"><span class="dt-lbl">Catatan</span><span class="dt-val" id="dk_catatan">—</span></div>
            </div>
        </div>
        <div class="md-foot">
            <button type="button" class="btn-secondary" onclick="closeModal('mdDetailKehadiran')">Tutup</button>
            <button type="button" class="btn-primary" id="dk_editbtn">Edit kehadiran</button>
        </div>
    </div>
</div>

{{-- ══════════ MODAL: EDIT KEHADIRAN ══════════ --}}
<div class="md-overlay" id="mdEditKehadiran" onclick="if(event.target===this)closeModal('mdEditKehadiran')">
    <div class="md-box" role="dialog" aria-modal="true">
        <form method="POST" id="formEditKehadiran">
            @csrf @method('PUT')
            <div class="md-head">
                <div>
                    <div class="md-title">Edit kehadiran</div>
                    <div class="md-desc" id="ek_sub">Perbarui status kehadiran peserta.</div>
                </div>
                <button type="button" class="md-close" onclick="closeModal('mdEditKehadiran')" aria-label="Tutup">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="md-body">
                <div style="background:#f8faf8;border:1px solid #e3eae4;border-radius:9px;padding:12px 14px;display:flex;flex-direction:column;gap:3px;">
                    <span style="font-size:13.5px;font-weight:600;color:#1e3528;" id="ek_peserta">—</span>
                    <span style="font-size:12px;color:#90a898;" id="ek_kegiatan">—</span>
                </div>
                <div class="fgrid">
                    <div class="fld full">
                        <label>Status kehadiran <span class="req">*</span></label>
                        <select name="status" id="ek_status" required>
                            <option value="hadir">Hadir</option>
                            <option value="tidak_hadir">Tidak hadir</option>
                        </select>
                    </div>
                    <div class="fld full">
                        <label>Catatan</label>
                        <textarea name="catatan" id="ek_catatan" rows="2" placeholder="Alasan tidak hadir, kondisi kesehatan, dll. (opsional)"></textarea>
                    </div>
                </div>
            </div>
            <div class="md-foot">
                <button type="button" class="btn-secondary" onclick="closeModal('mdEditKehadiran')">Batal</button>
                <button type="submit" class="btn-primary">Simpan perubahan</button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════ MODAL: HAPUS ══════════ --}}
<div class="md-overlay" id="mdHapus" onclick="if(event.target===this)closeModal('mdHapus')">
    <div class="md-box sm" role="dialog" aria-modal="true">
        <form method="POST" id="formHapus">
            @csrf @method('DELETE')
            <div class="md-head" style="border-bottom:none;padding-bottom:8px;">
                <div></div>
                <button type="button" class="md-close" onclick="closeModal('mdHapus')" aria-label="Tutup">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="md-body" style="text-align:center;padding-top:0;gap:10px;">
                <div class="del-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/>
                    </svg>
                </div>
                <div style="font-size:16px;font-weight:700;color:#1e3528;">Hapus data kehadiran?</div>
                <div style="font-size:13px;color:#7a9b88;line-height:1.6;">
                    Kehadiran <strong id="h_peserta" style="color:#1e3528;"></strong>
                    pada kegiatan <strong id="h_kegiatan" style="color:#1e3528;"></strong>
                    akan dihapus.
                </div>
            </div>
            <div class="md-foot" style="justify-content:center;">
                <button type="button" class="btn-secondary" onclick="closeModal('mdHapus')">Batal</button>
                <button type="submit" class="btn-danger" style="height:40px;padding:0 16px;font-size:13.5px;">Ya, hapus</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
const PESERTA   = @json($pesertaJson);
const KEG       = @json($kegiatanJson);
const KEHADIRAN = @json($kehadiranJson);

/* ── Catat kehadiran ── */
function openCatat() {
    openModal('mdCatat');
}

function filterPeserta(kegiatanId) {
    const sel  = document.getElementById('kat_select');
    const opt  = sel.options[sel.selectedIndex];
    const kat  = opt ? opt.dataset.kat : '';
    const box  = document.getElementById('peserta-section');
    const list = document.getElementById('peserta-list');
    const btn  = document.getElementById('submit-catat');

    if (!kegiatanId) { box.style.display = 'none'; btn.disabled = true; return; }

    box.style.display = 'flex';
    btn.disabled = false;

    const filtered = kat === 'umum' ? PESERTA : PESERTA.filter(p => p.kategori === kat);

    list.innerHTML = filtered.length === 0
        ? '<div class="pick-empty">Tidak ada peserta untuk kategori ini.</div>'
        : filtered.map(p => `
            <label class="pick-item">
                <input type="checkbox" name="peserta_ids[]" value="${p.id}" onchange="updateCount()">
                <span class="nm">${p.nama}</span>
                <span class="badge badge-green">${p.kategori_lbl}</span>
            </label>`).join('');

    updateCount();
}

function checkAll(state) {
    document.querySelectorAll('#peserta-list input[type="checkbox"]').forEach(cb => cb.checked = state);
    updateCount();
}

function updateCount() {
    const total = document.querySelectorAll('#peserta-list input[type="checkbox"]').length;
    const on    = document.querySelectorAll('#peserta-list input[type="checkbox"]:checked').length;
    const lbl   = document.getElementById('pick-count');
    if (lbl) lbl.textContent = total ? `(${on} dari ${total} dipilih)` : '';
}

/* ── Detail per kegiatan ── */
function openKegiatan(id) {
    const k = KEG.find(x => x.id === id);
    if (!k) return;

    document.getElementById('k_nama').textContent  = k.nama;
    document.getElementById('k_sub').textContent   = k.tanggal_fmt;
    document.getElementById('k_lok').textContent   = k.lokasi;
    document.getElementById('k_kat').textContent   = k.kategori_lbl;
    document.getElementById('k_status').textContent= k.status_lbl;
    document.getElementById('k_jam').textContent   = k.jam;
    document.getElementById('k_nhadir').textContent= k.n_hadir;
    document.getElementById('k_ntidak').textContent= k.n_tidak;

    const row = h => `<div class="att-row">
            <div class="av" style="width:26px;height:26px;font-size:9.5px;">${h.nama.substring(0,2).toUpperCase()}</div>
            <span class="nm">${h.nama}</span>
            <span class="badge badge-green">${h.kategori}</span>
        </div>`;

    document.getElementById('k_hadir').innerHTML = k.hadir.length
        ? k.hadir.map(row).join('')
        : '<div class="pick-empty">Belum ada peserta hadir.</div>';

    document.getElementById('k_tidak').innerHTML = k.tidak.length
        ? k.tidak.map(row).join('')
        : '<div class="pick-empty">Semua peserta hadir.</div>';

    document.getElementById('k_catatbtn').onclick = () => {
        closeModal('mdKegiatan');
        setTimeout(() => {
            const sel = document.getElementById('kat_select');
            sel.value = id;
            filterPeserta(String(id));
            openModal('mdCatat');
        }, 180);
    };

    openModal('mdKegiatan');
}

/* ── Detail kehadiran ── */
function openDetailKehadiran(id) {
    const h = KEHADIRAN.find(x => x.id === id);
    if (!h) return;

    document.getElementById('dk_av').textContent      = h.peserta.substring(0,2).toUpperCase();
    document.getElementById('dk_nama').textContent    = h.peserta;
    document.getElementById('dk_sub').textContent     = h.kegiatan;
    document.getElementById('dk_kat').textContent     = h.kategori;
    document.getElementById('dk_nik').textContent     = h.peserta_nik;
    document.getElementById('dk_status2').textContent = h.status_lbl;
    document.getElementById('dk_kegiatan').textContent= h.kegiatan;
    document.getElementById('dk_tgl').textContent     = h.tanggal_fmt || '—';
    document.getElementById('dk_jam').textContent     = h.jam || '—';
    document.getElementById('dk_lokasi').textContent  = h.lokasi || '—';
    document.getElementById('dk_catatan').textContent = h.catatan || '—';

    const st = document.getElementById('dk_status');
    st.textContent = h.status_lbl;
    st.className = 'badge ' + (h.status === 'hadir' ? 'badge-green' : 'badge-red');

    document.getElementById('dk_editbtn').onclick = () => {
        closeModal('mdDetailKehadiran');
        setTimeout(() => openEditKehadiran(id), 180);
    };

    openModal('mdDetailKehadiran');
}

/* ── Edit kehadiran ── */
function openEditKehadiran(id) {
    const h = KEHADIRAN.find(x => x.id === id);
    if (!h) return;

    document.getElementById('formEditKehadiran').action = '/kehadiran/' + id;
    document.getElementById('ek_sub').textContent      = 'Perbarui kehadiran ' + h.peserta + '.';
    document.getElementById('ek_peserta').textContent  = h.peserta;
    document.getElementById('ek_kegiatan').textContent = h.kegiatan + ' · ' + (h.tanggal_fmt || '');
    document.getElementById('ek_status').value         = h.status;
    document.getElementById('ek_catatan').value        = h.catatan || '';

    openModal('mdEditKehadiran');
}

/* ── Hapus ── */
function openHapus(id) {
    const h = KEHADIRAN.find(x => x.id === id);
    if (!h) return;
    document.getElementById('formHapus').action = '/kehadiran/' + id;
    document.getElementById('h_peserta').textContent  = h.peserta;
    document.getElementById('h_kegiatan').textContent = h.kegiatan;
    openModal('mdHapus');
}

@if($errors->any())
document.addEventListener('DOMContentLoaded', () => openModal('mdCatat'));
@endif
</script>
@endpush
