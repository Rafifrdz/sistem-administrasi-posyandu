@extends('layouts.app')

@section('title', 'Kunjungan ANC')
@section('breadcrumb', 'Kunjungan ANC')
@section('nav-active-kunjungan', 'active')

@push('head')
<style>
    .chip { padding: 7px 13px; border-radius: 20px; border: 1.5px solid #dde5de; background: #fff; font-size: 12.5px; color: #4a6b57; text-decoration: none; font-weight: 600; transition: all .15s; }
    .chip:hover { border-color: #b9d2c1; background: #f6faf7; }
    .chip.on { background: #2f7d78; border-color: #2f7d78; color: #fff; }
    .vital { font-family: ui-monospace, monospace; font-size: 12.5px; font-weight: 600; color: #1e3528; }
    .flag { display: inline-block; padding: 2px 7px; border-radius: 5px; font-size: 10px; font-weight: 700; margin: 1px 2px 1px 0; }
    .flag-red { background: #fdeaea; color: #b3261e; }
    .flag-amber { background: #fdf3e0; color: #9a6b12; }
    .flag-green { background: #e8f3ec; color: #2f6b4a; }
    .tm-badge { padding: 2px 7px; border-radius: 5px; font-size: 10px; font-weight: 700; background: #f0f5f1; color: #4a6b57; }
    .live-warn { display: none; gap: 6px; flex-wrap: wrap; padding: 9px 11px; background: #fdf3e0; border: 1px solid #f0dfbe; border-radius: 8px; font-size: 12px; color: #8a5d10; }
    .live-warn.show { display: flex; }
</style>
@endpush

@section('content')

<div class="page-heading" style="margin-bottom:18px;">
    <div>
        <h2 style="margin:0;font-size:19px;color:#35271e;">Catatan Kunjungan ANC</h2>
        <div class="sub" style="margin-top:4px;">Pemeriksaan antenatal sesuai standar 10T</div>
    </div>
    <button class="btn-primary" onclick="bukaCatat()">
        <img src="/icons/plus.svg" alt="" style="width:15px;height:15px;"> Catat Kunjungan
    </button>
</div>

{{-- ── Ringkasan bulan ini ─────────────────────── --}}
<div style="display:flex;gap:14px;flex-wrap:wrap;margin-bottom:16px;">
    <div class="card" style="flex:1;min-width:150px;">
        <span class="stat-label">Kunjungan Bulan Ini</span>
        <div class="stat-num">{{ $statistik['total'] }}</div>
    </div>
    <div class="card" style="flex:1;min-width:150px;">
        <span class="stat-label">Tekanan Darah Tinggi</span>
        <div class="stat-num" style="color:#b3261e;">{{ $statistik['hipertensi'] }}</div>
        <span class="stat-label">≥ 140/90 mmHg</span>
    </div>
    <div class="card" style="flex:1;min-width:150px;">
        <span class="stat-label">Anemia</span>
        <div class="stat-num" style="color:#9a6b12;">{{ $statistik['anemia'] }}</div>
        <span class="stat-label">Hb &lt; 11 g/dL</span>
    </div>
    <div class="card" style="flex:1;min-width:150px;">
        <span class="stat-label">Dirujuk</span>
        <div class="stat-num">{{ $statistik['dirujuk'] }}</div>
        <span class="stat-label">Puskesmas / RS</span>
    </div>
</div>

{{-- ── Filter ──────────────────────────────────── --}}
<div class="card" style="margin-bottom:16px;">
    <form method="GET" action="{{ route('kunjungan.index') }}" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;margin:0;">
        <div class="filter-row">
            @foreach (['bulan' => 'Bulan ini', '3bulan' => '3 Bulan', 'semua' => 'Semua'] as $key => $label)
                <a href="{{ route('kunjungan.index', ['periode' => $key, 'ibu' => $ibuId]) }}"
                   class="chip {{ $periode === $key ? 'on' : '' }}">{{ $label }}</a>
            @endforeach
        </div>
        <select name="ibu" class="form-control" style="width:auto;height:38px;font-size:13px;margin-left:auto;" onchange="this.form.submit()">
            <option value="">Semua ibu hamil</option>
            @foreach ($ibuList as $ib)
                <option value="{{ $ib->id }}" @selected((int) $ibuId === $ib->id)>{{ $ib->nama }}</option>
            @endforeach
        </select>
        <input type="hidden" name="periode" value="{{ $periode }}">
    </form>
</div>

{{-- ── Tabel ───────────────────────────────────── --}}
<div class="card" style="padding:0;overflow:hidden;">
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Ibu Hamil</th>
                    <th>Kunjungan</th>
                    <th>Antropometri</th>
                    <th>Tanda Vital</th>
                    <th>Lab &amp; Suplemen</th>
                    <th>Catatan</th>
                    <th style="text-align:right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($daftar as $k)
                <tr>
                    <td data-l="Ibu Hamil">
                        <div class="cell">
                            <div class="av">{{ strtoupper(mb_substr($k->ibuHamil?->nama ?? '?', 0, 1)) }}</div>
                            <div>
                                <div class="nm">{{ $k->ibuHamil?->nama ?? '—' }}</div>
                                <div class="sub mono">{{ $k->ibuHamil?->nik }}</div>
                            </div>
                        </div>
                    </td>
                    <td data-l="Kunjungan">
                        <div class="nm" style="font-size:13px;">{{ $k->label_kunjungan }} · {{ $k->tanggal_label }}</div>
                        <div class="sub">{{ $k->usia_kehamilan_minggu }} minggu · <span class="tm-badge">TM{{ $k->trimester }}</span></div>
                    </td>
                    <td data-l="Antropometri">
                        <div class="vital">BB {{ $k->berat_badan ?? '—' }} kg</div>
                        <div class="sub">LiLA {{ $k->lila ?? '—' }} cm @if($k->kek) · <span style="color:#9a6b12;font-weight:700;">KEK</span> @endif</div>
                    </td>
                    <td data-l="Tanda Vital">
                        <div class="vital">{{ $k->tensi_label }}</div>
                        <div class="sub">TFU {{ $k->tfu ?? '—' }} · DJJ {{ $k->djj ?? '—' }}</div>
                    </td>
                    <td data-l="Lab & Suplemen">
                        <div class="vital">Hb {{ $k->hb ?? '—' }}</div>
                        <div class="sub">Protein {{ $k->protein_urin }} · Fe {{ $k->tablet_fe }}</div>
                    </td>
                    <td data-l="Catatan">
                        @forelse ($k->peringatan as $p)
                            <span class="flag flag-red">{{ $p }}</span>
                        @empty
                            <span class="flag flag-green">Normal</span>
                        @endforelse
                        @if ($k->status_rujukan !== 'tidak')
                            <span class="flag flag-amber">{{ $k->status_rujukan_label }}</span>
                        @endif
                    </td>
                    <td data-l="Aksi">
                        <div class="act-group">
                            <button class="act-btn" title="Detail" onclick="bukaDetail({{ $k->id }})">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                            <button class="act-btn" title="Ubah" onclick="bukaEdit({{ $k->id }})">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                            </button>
                            <button class="act-btn danger" title="Hapus" onclick="bukaHapus({{ $k->id }}, '{{ addslashes($k->ibuHamil?->nama ?? '-') }}', '{{ $k->label_kunjungan }}')">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <div class="empty-icon"><img src="/icons/clipboard-check.svg" alt="" style="width:22px;height:22px;opacity:.4;"></div>
                            <div style="font-weight:600;color:#4a6b57;">Belum ada catatan kunjungan</div>
                            <div class="sub" style="margin-top:4px;">Catat hasil pemeriksaan ANC pertama pada periode ini.</div>
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="pagination-wrap">{{ $daftar->links() }}</div>

{{-- ══════════ MODAL CATAT ══════════ --}}
<div class="md-overlay" id="mdCatat">
    <div class="md-box lg">
        <form method="POST" action="{{ route('kunjungan.store') }}">
            @csrf
            <div class="md-head">
                <div>
                    <div class="md-title">Catat Kunjungan ANC</div>
                    <div class="md-desc">Usia kehamilan &amp; trimester dihitung otomatis dari HPHT</div>
                </div>
                <button type="button" class="md-close" onclick="closeModal('mdCatat')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="md-body">
                <div class="fgrid">
                    <div class="fld full">
                        <label>Ibu Hamil <span class="req">*</span></label>
                        <select name="ibu_hamil_id" id="ctIbu" required onchange="hitungUsia()">
                            <option value="">— pilih ibu hamil —</option>
                            @foreach ($ibuList as $ib)
                                <option value="{{ $ib->id }}" data-hpht="{{ optional($ib->hpht)->format('Y-m-d') }}">{{ $ib->nama }}</option>
                            @endforeach
                        </select>
                        <div class="form-hint" id="ctInfo">Pilih ibu untuk melihat usia kehamilan saat ini.</div>
                    </div>
                    <div class="fld"><label>Tanggal Kunjungan <span class="req">*</span></label><input type="date" name="tanggal" value="{{ now()->toDateString() }}" required></div>
                    <div class="fld"><label>Kegiatan (opsional)</label>
                        <select name="kegiatan_id">
                            <option value="">— tidak terkait kegiatan —</option>
                            @foreach ($kegiatanList as $kg)
                                <option value="{{ $kg->id }}">{{ $kg->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-title">10T — Antropometri &amp; Tanda Vital</div>
                    <div class="fgrid">
                        <div class="fld"><label>Berat Badan (kg) <span class="req">*</span></label><input type="number" name="berat_badan" id="ctBb" step="0.1" min="25" max="150" required oninput="cekVital()"></div>
                        <div class="fld"><label>LiLA (cm)</label><input type="number" name="lila" step="0.1" min="10" max="50" oninput="cekKek(this.value)"></div>
                        <div class="fld"><label>Tensi Sistolik <span class="req">*</span></label><input type="number" name="tensi_sistolik" id="ctSis" min="70" max="250" required oninput="cekVital()"></div>
                        <div class="fld"><label>Tensi Diastolik <span class="req">*</span></label><input type="number" name="tensi_diastolik" id="ctDia" min="40" max="150" required oninput="cekVital()"></div>
                        <div class="fld"><label>TFU (cm)</label><input type="number" name="tfu" step="0.1" min="5" max="45"></div>
                        <div class="fld"><label>DJJ (bpm)</label><input type="number" name="djj" id="ctDjj" min="60" max="220" oninput="cekDjj(this.value)"></div>
                        <div class="fld full"><label>Presentasi Janin <span class="req">*</span></label>
                            <select name="presentasi_janin" required>
                                <option value="belum_tentu">Belum tentu</option>
                                <option value="kepala">Kepala</option>
                                <option value="sungsang">Sungsang</option>
                                <option value="lintang">Lintang</option>
                            </select>
                        </div>
                    </div>
                    <div class="live-warn" id="ctWarn"></div>
                </div>

                <div class="form-section">
                    <div class="form-section-title">10T — Laboratorium &amp; Suplemen</div>
                    <div class="fgrid">
                        <div class="fld"><label>Hemoglobin (g/dL)</label><input type="number" name="hb" id="ctHb" step="0.1" min="3" max="20" oninput="cekVital()"></div>
                        <div class="fld"><label>Protein Urin <span class="req">*</span></label>
                            <select name="protein_urin" required>
                                <option value="negatif">Negatif</option>
                                <option value="+1">+1</option><option value="+2">+2</option>
                                <option value="+3">+3</option><option value="+4">+4</option>
                            </select>
                        </div>
                        <div class="fld"><label>Tablet Tambah Darah (butir)</label><input type="number" name="tablet_fe" min="0" max="200" value="30"></div>
                        <div class="fld"><label>Imunisasi TT <span class="req">*</span></label>
                            <select name="imunisasi_tt" required>
                                <option value="tidak">Tidak</option>
                                <option value="TT1">TT1</option><option value="TT2">TT2</option>
                                <option value="TT3">TT3</option><option value="TT4">TT4</option><option value="TT5">TT5</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-title">Tanda Bahaya &amp; Tindak Lanjut</div>
                    <div class="fgrid">
                        <div class="fld full">
                            <label>Tanda Bahaya</label>
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:7px;">
                                @foreach (\App\Models\KunjunganAnc::TANDA_BAHAYA as $kode => $label)
                                    <label style="display:flex;align-items:center;gap:7px;font-weight:500;font-size:12.5px;color:#4a6b57;">
                                        <input type="checkbox" name="tanda_bahaya[]" value="{{ $kode }}" style="width:15px;height:15px;"> {{ $label }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <div class="fld full"><label>Keluhan</label><input type="text" name="keluhan" placeholder="cth. pusing, mual"></div>
                        <div class="fld full"><label>Diagnosa / Keterangan</label><input type="text" name="diagnosa" placeholder="cth. kehamilan normal"></div>
                        <div class="fld full"><label>Tindak Lanjut <span class="req">*</span></label>
                            <select name="status_rujukan" required>
                                <option value="tidak">Tidak dirujuk</option>
                                <option value="puskesmas">Rujuk ke Puskesmas</option>
                                <option value="rumah_sakit">Rujuk ke Rumah Sakit</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="md-foot">
                <button type="button" class="btn-secondary" onclick="closeModal('mdCatat')">Batal</button>
                <button type="submit" class="btn-primary">Simpan Kunjungan</button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════ MODAL DETAIL ══════════ --}}
<div class="md-overlay" id="mdDetail">
    <div class="md-box lg">
        <div class="md-head">
            <div>
                <div class="md-title" id="dtJudul">—</div>
                <div class="md-desc" id="dtSub">—</div>
            </div>
            <button type="button" class="md-close" onclick="closeModal('mdDetail')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="md-body">
            <div id="dtWarn"></div>
            <div class="dt-grid" id="dtGrid"></div>
        </div>
        <div class="md-foot">
            <button type="button" class="btn-secondary" onclick="closeModal('mdDetail')">Tutup</button>
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
                    <div class="md-title">Ubah Catatan Kunjungan</div>
                    <div class="md-desc" id="edSub">—</div>
                </div>
                <button type="button" class="md-close" onclick="closeModal('mdEdit')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="md-body">
                <div class="fgrid">
                    <div class="fld"><label>Tanggal <span class="req">*</span></label><input type="date" name="tanggal" id="edTanggal" required></div>
                    <div class="fld"><label>Berat Badan (kg)</label><input type="number" name="berat_badan" id="edBb" step="0.1" required></div>
                    <div class="fld"><label>Tensi Sistolik</label><input type="number" name="tensi_sistolik" id="edSis" required></div>
                    <div class="fld"><label>Tensi Diastolik</label><input type="number" name="tensi_diastolik" id="edDia" required></div>
                    <div class="fld"><label>LiLA (cm)</label><input type="number" name="lila" id="edLila" step="0.1"></div>
                    <div class="fld"><label>TFU (cm)</label><input type="number" name="tfu" id="edTfu" step="0.1"></div>
                    <div class="fld"><label>DJJ (bpm)</label><input type="number" name="djj" id="edDjj"></div>
                    <div class="fld"><label>Hemoglobin (g/dL)</label><input type="number" name="hb" id="edHb" step="0.1"></div>
                    <div class="fld"><label>Presentasi Janin</label>
                        <select name="presentasi_janin" id="edPresentasi">
                            <option value="belum_tentu">Belum tentu</option>
                            <option value="kepala">Kepala</option>
                            <option value="sungsang">Sungsang</option>
                            <option value="lintang">Lintang</option>
                        </select>
                    </div>
                    <div class="fld"><label>Protein Urin</label>
                        <select name="protein_urin" id="edProtein">
                            <option value="negatif">Negatif</option><option value="+1">+1</option>
                            <option value="+2">+2</option><option value="+3">+3</option><option value="+4">+4</option>
                        </select>
                    </div>
                    <div class="fld"><label>Tablet Fe (butir)</label><input type="number" name="tablet_fe" id="edFe" min="0"></div>
                    <div class="fld"><label>Imunisasi TT</label>
                        <select name="imunisasi_tt" id="edTt">
                            <option value="tidak">Tidak</option><option value="TT1">TT1</option><option value="TT2">TT2</option>
                            <option value="TT3">TT3</option><option value="TT4">TT4</option><option value="TT5">TT5</option>
                        </select>
                    </div>
                    <div class="fld full"><label>Keluhan</label><input type="text" name="keluhan" id="edKeluhan"></div>
                    <div class="fld full"><label>Diagnosa</label><input type="text" name="diagnosa" id="edDiagnosa"></div>
                    <div class="fld full"><label>Tindak Lanjut</label>
                        <select name="status_rujukan" id="edRujukan">
                            <option value="tidak">Tidak dirujuk</option>
                            <option value="puskesmas">Rujuk ke Puskesmas</option>
                            <option value="rumah_sakit">Rujuk ke Rumah Sakit</option>
                        </select>
                    </div>
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
                <div style="font-size:16px;font-weight:700;color:#1e3528;">Hapus catatan kunjungan?</div>
                <div class="sub" id="hpInfo" style="margin-top:6px;"></div>
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
    const KUNJUNGAN = @json($kunjunganJson);
    const BASE = "{{ url('kunjungan') }}";

    function bukaCatat() { openModal('mdCatat'); }

    /* Hitung usia kehamilan saat ibu dipilih */
    function hitungUsia() {
        const sel = document.getElementById('ctIbu');
        const opt = sel.options[sel.selectedIndex];
        const info = document.getElementById('ctInfo');
        const hpht = opt?.dataset?.hpht;
        if (!hpht) { info.textContent = 'Pilih ibu untuk melihat usia kehamilan saat ini.'; return; }
        const hari = Math.floor((Date.now() - new Date(hpht).getTime()) / 86400000);
        const minggu = Math.max(0, Math.min(42, Math.floor(hari / 7)));
        const tm = minggu <= 12 ? 1 : (minggu <= 27 ? 2 : 3);
        info.innerHTML = `Usia kehamilan saat ini: <b>${minggu} minggu</b> · Trimester ${tm}`;
    }

    /* Peringatan langsung saat mengisi */
    function cekVital() {
        const sis = +document.getElementById('ctSis').value || 0;
        const dia = +document.getElementById('ctDia').value || 0;
        const hb  = +document.getElementById('ctHb').value || 0;
        const djj = +document.getElementById('ctDjj').value || 0;
        const w = [];
        if (sis >= 140 || dia >= 90) w.push('⚠ Tekanan darah tinggi — curiga hipertensi/preeklamsia');
        if (hb && hb < 11) w.push('⚠ Anemia — Hb di bawah 11 g/dL');
        if (djj && (djj < 120 || djj > 160)) w.push('⚠ DJJ di luar rentang normal 120–160 bpm');
        const box = document.getElementById('ctWarn');
        box.innerHTML = w.join('<br>');
        box.classList.toggle('show', w.length > 0);
    }

    function cekKek(v) {
        const box = document.getElementById('ctWarn');
        if (v && +v < 23.5) {
            box.innerHTML = '⚠ LiLA di bawah 23,5 cm — Kurang Energi Kronik (KEK)';
            box.classList.add('show');
        } else {
            cekVital();
        }
    }

    function bukaDetail(id) {
        const d = KUNJUNGAN[id];
        if (!d) return;
        document.getElementById('dtJudul').textContent = d.ibu + ' — ' + d.label;
        document.getElementById('dtSub').textContent = d.tanggal_label + ' · ' + d.usia + ' minggu · Trimester ' + d.trimester;

        document.getElementById('dtWarn').innerHTML = (d.peringatan && d.peringatan.length)
            ? d.peringatan.map(p => `<span class="flag flag-red" style="margin-bottom:4px;">${p}</span>`).join(' ')
            : '<span class="flag flag-green">Semua indikator dalam batas normal</span>';

        const items = [
            ['Berat Badan', d.berat_badan ? d.berat_badan + ' kg' : '—'],
            ['LiLA', d.lila ? d.lila + ' cm' : '—'],
            ['Tekanan Darah', d.tensi],
            ['TFU', d.tfu ? d.tfu + ' cm' : '—'],
            ['DJJ', d.djj ? d.djj + ' bpm' : '—'],
            ['Presentasi Janin', d.presentasi],
            ['Hemoglobin', d.hb ? d.hb + ' g/dL' : '—'],
            ['Protein Urin', d.protein_urin],
            ['Tablet Fe', d.tablet_fe + ' butir'],
            ['Imunisasi TT', d.imunisasi_tt],
            ['Tindak Lanjut', d.rujukan],
            ['Petugas', d.petugas || '—'],
            ['Keluhan', d.keluhan || '—'],
            ['Diagnosa', d.diagnosa || '—'],
        ];
        document.getElementById('dtGrid').innerHTML = items.map(([l, v]) =>
            `<div class="dt-item"><div class="dt-lbl">${l}</div><div class="dt-val">${v ?? '—'}</div></div>`
        ).join('');
        openModal('mdDetail');
    }

    function bukaEdit(id) {
        const d = KUNJUNGAN[id];
        if (!d) return;
        document.getElementById('formEdit').action = BASE + '/' + id;
        document.getElementById('edSub').textContent = d.ibu + ' · ' + d.label + ' · ' + d.usia + ' minggu';
        const set = (el, v) => document.getElementById(el).value = v ?? '';
        set('edTanggal', d.tanggal); set('edBb', d.berat_badan); set('edSis', d.tensi_sistolik);
        set('edDia', d.tensi_diastolik); set('edLila', d.lila); set('edTfu', d.tfu);
        set('edDjj', d.djj); set('edHb', d.hb); set('edProtein', d.protein_urin);
        set('edFe', d.tablet_fe); set('edTt', d.imunisasi_tt); set('edKeluhan', d.keluhan);
        set('edDiagnosa', d.diagnosa); set('edRujukan', 'tidak');
        set('edPresentasi', 'belum_tentu');
        openModal('mdEdit');
    }

    function bukaHapus(id, nama, label) {
        document.getElementById('formHapus').action = BASE + '/' + id;
        document.getElementById('hpInfo').textContent = nama + ' — ' + label;
        openModal('mdHapus');
    }

    @if ($errors->any())
        openModal('mdCatat');
    @endif
</script>
@endpush
