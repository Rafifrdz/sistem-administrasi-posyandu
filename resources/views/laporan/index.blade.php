@extends('layouts.app')

@section('title', 'Laporan')
@section('breadcrumb', 'Laporan')
@section('nav-active-laporan', 'active')

@push('head')
<style>
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    @media (max-width: 1000px) { .grid-2 { grid-template-columns: 1fr; } }
    .card-h { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; gap: 10px; }
    .card-h h3 { margin: 0; font-size: 14px; font-weight: 700; color: #1e3528; }
    .cov-row { display: flex; align-items: center; gap: 12px; margin-bottom: 11px; }
    .cov-lbl { width: 34px; font-size: 12px; font-weight: 700; color: #4a6b57; flex-shrink: 0; }
    .cov-bar { flex: 1; height: 9px; background: #eef3ef; border-radius: 5px; overflow: hidden; }
    .cov-bar > i { display: block; height: 100%; border-radius: 5px; background: linear-gradient(90deg,#6f9d84,#4f7561); }
    .cov-val { width: 66px; text-align: right; font-size: 12px; color: #4a6b57; font-weight: 600; }
    .risk-box { border-radius: 12px; padding: 15px; text-align: center; }
    .risk-box b { display: block; font-size: 26px; line-height: 1.1; margin-bottom: 3px; }
    .risk-box span { font-size: 11.5px; font-weight: 600; }
    .rk-krr { background: #e8f3ec; color: #2f6b4a; }
    .rk-krt { background: #fdf3e0; color: #9a6b12; }
    .rk-krst { background: #fdeaea; color: #b3261e; }
    @media print {
        .sidebar, .topbar, .page-footer, .no-print { display: none !important; }
        .page-content { padding: 0 !important; }
        .card { box-shadow: none !important; border: 1px solid #ddd !important; break-inside: avoid; }
    }
</style>
@endpush

@section('content')

<div class="page-heading no-print" style="margin-bottom:18px;">
    <div>
        <h2 style="margin:0;font-size:19px;color:#35271e;">Laporan Pelayanan Ibu Hamil</h2>
        <div class="sub" style="margin-top:4px;">Cakupan ANC, deteksi risiko, dan kondisi klinis tahun {{ $tahun }}</div>
    </div>
    <div style="display:flex;gap:8px;align-items:center;">
        <form method="GET" action="{{ route('laporan.index') }}" style="margin:0;">
            <select name="tahun" class="form-control" style="width:auto;height:38px;font-size:13px;" onchange="this.form.submit()">
                @foreach ($tahunTersedia as $th)
                    <option value="{{ $th }}" @selected((int) $th === (int) $tahun)>{{ $th }}</option>
                @endforeach
            </select>
        </form>
        <button class="btn-secondary" onclick="window.print()">
            <img src="/icons/printer.svg" alt="" style="width:15px;height:15px;"> Cetak
        </button>
    </div>
</div>

{{-- ── Rekap klinis ────────────────────────────── --}}
<div style="display:flex;gap:14px;flex-wrap:wrap;margin-bottom:16px;">
    <div class="card" style="flex:1;min-width:150px;"><span class="stat-label">Total Kunjungan</span><div class="stat-num">{{ $rekapKlinis['total_kunjungan'] }}</div><span class="stat-label">tahun {{ $tahun }}</span></div>
    <div class="card" style="flex:1;min-width:150px;"><span class="stat-label">Hipertensi</span><div class="stat-num" style="color:#b3261e;">{{ $rekapKlinis['hipertensi'] }}</div><span class="stat-label">≥ 140/90 mmHg</span></div>
    <div class="card" style="flex:1;min-width:150px;"><span class="stat-label">Anemia</span><div class="stat-num" style="color:#9a6b12;">{{ $rekapKlinis['anemia'] }}</div><span class="stat-label">Hb &lt; 11 g/dL</span></div>
    <div class="card" style="flex:1;min-width:150px;"><span class="stat-label">KEK</span><div class="stat-num">{{ $rekapKlinis['kek'] }}</div><span class="stat-label">LiLA &lt; 23,5 cm</span></div>
    <div class="card" style="flex:1;min-width:150px;"><span class="stat-label">Dirujuk</span><div class="stat-num">{{ $rekapKlinis['dirujuk'] }}</div><span class="stat-label">Puskesmas / RS</span></div>
</div>

<div class="grid-2" style="margin-bottom:16px;">
    {{-- Cakupan ANC --}}
    <div class="card">
        <div class="card-h">
            <div>
                <h3>Cakupan Kunjungan ANC (K1–K6)</h3>
                <div class="sub">Jumlah ibu unik yang mencapai tiap kunjungan</div>
            </div>
        </div>
        @php $maksCakupan = max(1, max($cakupan)); @endphp
        @foreach ($cakupan as $k => $jumlah)
            <div class="cov-row">
                <div class="cov-lbl">K{{ $k }}</div>
                <div class="cov-bar"><i style="width: {{ round($jumlah / $maksCakupan * 100) }}%;"></i></div>
                <div class="cov-val">{{ $jumlah }} ibu</div>
            </div>
        @endforeach
        <div class="sub" style="margin-top:10px;padding-top:10px;border-top:1px solid #f0f5f1;">
            Target nasional: minimal 6 kunjungan selama kehamilan (TM1 1×, TM2 2×, TM3 3×).
        </div>
    </div>

    {{-- Distribusi risiko --}}
    <div class="card">
        <div class="card-h"><h3>Distribusi Risiko Kehamilan</h3></div>
        <div style="display:flex;gap:10px;margin-bottom:14px;">
            <div class="risk-box rk-krr" style="flex:1;"><b>{{ $distribusiRisiko['KRR'] }}</b><span>Risiko Rendah</span></div>
            <div class="risk-box rk-krt" style="flex:1;"><b>{{ $distribusiRisiko['KRT'] }}</b><span>Risiko Tinggi</span></div>
            <div class="risk-box rk-krst" style="flex:1;"><b>{{ $distribusiRisiko['KRST'] }}</b><span>Sangat Tinggi</span></div>
        </div>
        <div class="card-h" style="margin-top:6px;"><h3 style="font-size:13px;">Sebaran Kunjungan per Trimester</h3></div>
        @php $totTm = max(1, array_sum($perTrimester)); @endphp
        @foreach ($perTrimester as $tm => $jml)
            <div class="cov-row">
                <div class="cov-lbl">TM{{ $tm }}</div>
                <div class="cov-bar"><i style="width: {{ round($jml / $totTm * 100) }}%;background:linear-gradient(90deg,#c8a45c,#a8843c);"></i></div>
                <div class="cov-val">{{ $jml }} kunjungan</div>
            </div>
        @endforeach
    </div>
</div>

{{-- ── Rekap bulanan ───────────────────────────── --}}
<div class="card" style="padding:0;overflow:hidden;margin-bottom:16px;">
    <div style="padding:18px 20px 0;"><h3 style="margin:0;font-size:14px;font-weight:700;color:#1e3528;">Rekapitulasi Bulanan {{ $tahun }}</h3></div>
    <div class="table-wrap" style="margin-top:12px;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Bulan</th>
                    <th>Kunjungan</th>
                    <th>Ibu Unik</th>
                    <th>K1 Baru</th>
                    <th>Hipertensi</th>
                    <th>Anemia</th>
                    <th>Dirujuk</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($bulanan as $b)
                    <tr>
                        <td data-l="Bulan"><span class="nm" style="font-size:13px;">{{ $b['bulan'] }}</span></td>
                        <td data-l="Kunjungan">{{ $b['kunjungan'] }}</td>
                        <td data-l="Ibu Unik">{{ $b['ibu'] }}</td>
                        <td data-l="K1 Baru">{{ $b['k1'] }}</td>
                        <td data-l="Hipertensi">{{ $b['hipertensi'] }}</td>
                        <td data-l="Anemia">{{ $b['anemia'] }}</td>
                        <td data-l="Dirujuk">{{ $b['dirujuk'] }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background:#f8faf8;font-weight:700;">
                    <td data-l="Bulan">Total</td>
                    <td data-l="Kunjungan">{{ collect($bulanan)->sum('kunjungan') }}</td>
                    <td data-l="Ibu Unik">{{ collect($bulanan)->sum('ibu') }}</td>
                    <td data-l="K1 Baru">{{ collect($bulanan)->sum('k1') }}</td>
                    <td data-l="Hipertensi">{{ collect($bulanan)->sum('hipertensi') }}</td>
                    <td data-l="Anemia">{{ collect($bulanan)->sum('anemia') }}</td>
                    <td data-l="Dirujuk">{{ collect($bulanan)->sum('dirujuk') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

{{-- ── Daftar risiko tinggi ────────────────────── --}}
<div class="card" style="padding:0;overflow:hidden;">
    <div style="padding:18px 20px 0;">
        <h3 style="margin:0;font-size:14px;font-weight:700;color:#1e3528;">Daftar Ibu Hamil Risiko Tinggi</h3>
        <div class="sub" style="margin-top:2px;">Berdasarkan Kartu Skor Poedji Rochjati (KSPR)</div>
    </div>
    <div class="table-wrap" style="margin-top:12px;">
        <table class="data-table">
            <thead>
                <tr><th>Ibu Hamil</th><th>Usia Kandungan</th><th>Skor</th><th>Kategori</th><th>Faktor Risiko</th></tr>
            </thead>
            <tbody>
            @forelse ($risikoTinggi as $i)
                <tr>
                    <td data-l="Ibu Hamil">
                        <div class="nm">{{ $i->nama }}</div>
                        <div class="sub">{{ $i->umur }} tahun · {{ $i->gpa }}</div>
                    </td>
                    <td data-l="Usia Kandungan">{{ $i->usia_kehamilan }} minggu <span class="sub">(TM{{ $i->trimester }})</span></td>
                    <td data-l="Skor"><b>{{ $i->skor_kspr }}</b></td>
                    <td data-l="Kategori">
                        <span style="padding:3px 9px;border-radius:20px;font-size:10.5px;font-weight:700;background:{{ $i->kategori_risiko === 'KRST' ? '#fdeaea' : '#fdf3e0' }};color:{{ $i->kategori_risiko === 'KRST' ? '#b3261e' : '#9a6b12' }};">
                            {{ $i->kategori_risiko }}
                        </span>
                    </td>
                    <td data-l="Faktor Risiko">
                        <div class="sub">{{ collect($i->faktor_risiko)->pluck('label')->implode(', ') ?: '—' }}</div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5"><div class="sub" style="padding:14px;">Tidak ada ibu hamil berisiko tinggi.</div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="sub" style="margin-top:14px;text-align:right;">
    Dicetak {{ now()->translatedFormat('d F Y') }} · Posyandu Melati
</div>

@endsection
