@extends('layouts.app')

@section('title', 'Kartu Ibu — ' . $ibu->nama)
@section('breadcrumb', 'Data Ibu Hamil / Kartu Ibu')
@section('nav-active-ibu-hamil', 'active')

@push('head')
<style>
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
    @media (max-width: 1000px) { .grid-2, .grid-3 { grid-template-columns: 1fr; } }
    .card-h { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; gap: 10px; }
    .card-h h3 { margin: 0; font-size: 14px; font-weight: 700; color: #233c32; }
    .risk { padding: 4px 12px; border-radius: 20px; font-size: 11.5px; font-weight: 700; }
    .risk-krr { background: #e8f1eb; color: #4f7561; }
    .risk-krt { background: #fbf3e3; color: #8a6a2a; }
    .risk-krst { background: #fdf1f0; color: #b3261e; }
    .kv { display: grid; grid-template-columns: 1fr 1fr; gap: 13px 18px; }
    .kv .it { display: flex; flex-direction: column; gap: 2px; }
    .kv .lb { font-size: 10.5px; font-weight: 600; color: #758179; text-transform: uppercase; letter-spacing: .04em; }
    .kv .vl { font-size: 13.5px; color: #233c32; font-weight: 500; }
    .anc-box { border: 1.5px solid #e4eae5; border-radius: 12px; padding: 13px; text-align: center; }
    .anc-box.ok { border-color: #b9d2c1; background: #f6faf7; }
    .anc-box.no { border-color: #f0dfbe; background: #fdfaf3; }
    .anc-box b { display: block; font-size: 17px; color: #233c32; }
    .anc-box span { font-size: 10.5px; color: #758179; }
    .faktor { display: inline-block; padding: 3px 9px; border-radius: 6px; font-size: 11px; font-weight: 600; background: #fdf1f0; color: #b3261e; margin: 0 4px 4px 0; }
</style>
@endpush

@section('content')

<div class="page-heading" style="margin-bottom:18px;">
    <div style="display:flex;align-items:center;gap:13px;">
        <a href="{{ route('ibu-hamil.index') }}" class="act-btn" style="text-decoration:none;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:15px;height:15px;"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h2 style="margin:0;font-size:19px;color:#233c32;">{{ $ibu->nama }}</h2>
            <div class="sub" style="margin-top:3px;">{{ $ibu->nik }} · {{ $ibu->umur }} tahun · {{ $ibu->gpa }}</div>
        </div>
        <span class="risk risk-{{ strtolower($ibu->kategori_risiko) }}">{{ $ibu->kategori_risiko }} — {{ $ibu->kategori_risiko_label }}</span>
    </div>
    <a href="{{ route('kunjungan.index', ['ibu' => $ibu->id, 'periode' => 'semua']) }}" class="btn-primary">
        <img src="/icons/plus.svg" alt="" style="width:15px;height:15px;"> Catat Kunjungan
    </a>
</div>

{{-- ── Ringkasan kehamilan ─────────────────────── --}}
<div class="grid-3" style="margin-bottom:16px;">
    <div class="card" style="text-align:center;">
        <div class="stat-label">Usia Kehamilan</div>
        <div class="stat-num" style="margin:6px 0 2px;">{{ $ibu->usia_kehamilan }}<span style="font-size:15px;font-weight:600;"> mgg</span></div>
        <div class="stat-label">Trimester {{ $ibu->trimester }}</div>
    </div>
    <div class="card" style="text-align:center;">
        <div class="stat-label">Taksiran Persalinan</div>
        <div class="stat-num" style="font-size:20px;margin:10px 0 2px;">{{ $ibu->hpl?->translatedFormat('d M Y') ?? '—' }}</div>
        <div class="stat-label">
            @if ($ibu->sisa_hari !== null)
                {{ $ibu->sisa_hari >= 0 ? $ibu->sisa_hari . ' hari lagi' : 'Sudah lewat ' . abs($ibu->sisa_hari) . ' hari' }}
            @endif
        </div>
    </div>
    <div class="card" style="text-align:center;">
        <div class="stat-label">Kunjungan ANC</div>
        <div class="stat-num" style="margin:6px 0 2px;">{{ $ibu->jumlah_kunjungan }}<span style="font-size:15px;font-weight:600;">×</span></div>
        <div class="stat-label">{{ $ibu->status_anc_label }}</div>
    </div>
</div>

<div class="grid-2" style="margin-bottom:16px;">
    {{-- Profil --}}
    <div class="card">
        <div class="card-h"><h3>Profil &amp; Data Kehamilan</h3></div>
        <div class="kv">
            <div class="it"><div class="lb">HPHT</div><div class="vl">{{ $ibu->hpht?->translatedFormat('d F Y') }}</div></div>
            <div class="it"><div class="lb">Golongan Darah</div><div class="vl">{{ $ibu->golongan_darah }}</div></div>
            <div class="it"><div class="lb">Tinggi Badan</div><div class="vl">{{ $ibu->tinggi_badan }} cm</div></div>
            <div class="it"><div class="lb">Berat Pra-Hamil</div><div class="vl">{{ $ibu->berat_pra_hamil }} kg</div></div>
            <div class="it"><div class="lb">IMT Pra-Hamil</div><div class="vl">{{ $ibu->imt_pra_hamil }} ({{ $ibu->imt_kategori }})</div></div>
            <div class="it"><div class="lb">LiLA Pra-Hamil</div><div class="vl">{{ $ibu->lila_pra_hamil ?? '—' }} cm {{ $ibu->kek ? '— KEK' : '' }}</div></div>
            <div class="it"><div class="lb">Suami</div><div class="vl">{{ $ibu->nama_suami ?? '—' }}</div></div>
            <div class="it"><div class="lb">No. HP</div><div class="vl">{{ $ibu->no_hp ?? '—' }}</div></div>
            <div class="it" style="grid-column:1/-1;"><div class="lb">Alamat</div><div class="vl">{{ $ibu->alamat }}</div></div>
            <div class="it" style="grid-column:1/-1;"><div class="lb">Riwayat Penyakit</div><div class="vl">{{ $ibu->riwayat_penyakit ?: 'Tidak ada' }}</div></div>
        </div>
    </div>

    {{-- Risiko --}}
    <div class="card">
        <div class="card-h">
            <div><h3>Skor Risiko (KSPR)</h3><div class="sub">Skor {{ $ibu->skor_kspr }} — {{ $ibu->kategori_risiko_label }}</div></div>
            @if ($risikoKunjungan['skor'] !== $ibu->skor_kspr)
                <span class="sub">Dengan kunjungan terakhir: {{ $risikoKunjungan['skor'] }}</span>
            @endif
        </div>
        @forelse ($ibu->faktor_risiko as $f)
            <span class="faktor">{{ $f['label'] }} (+{{ $f['skor'] }})</span>
        @empty
            <div class="sub">Tidak ada faktor risiko terdeteksi pada data ibu.</div>
        @endforelse

        @if (count($risikoKunjungan['faktor']) > count($ibu->faktor_risiko))
            <div class="sub" style="margin-top:10px;padding-top:10px;border-top:1px solid #f0f5f1;">Tambahan dari kunjungan terakhir:</div>
            @foreach (array_slice($risikoKunjungan['faktor'], count($ibu->faktor_risiko)) as $f)
                <span class="faktor">{{ $f['label'] }} (+{{ $f['skor'] }})</span>
            @endforeach
        @endif

        <div class="card-h" style="margin-top:16px;"><h3 style="font-size:13px;">Kecukupan ANC per Trimester</h3></div>
        <div style="display:flex;gap:10px;">
            @foreach ($statusAnc as $tm => $row)
                <div class="anc-box {{ $row['cukup'] ? 'ok' : 'no' }}" style="flex:1;">
                    <b>{{ $row['dicapai'] }}/{{ $row['target'] }}</b>
                    <span>Trimester {{ $tm }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ── Grafik berat badan ──────────────────────── --}}
<div class="card" style="margin-bottom:16px;">
    <div class="card-h">
        <div>
            <h3>Kenaikan Berat Badan</h3>
            <div class="sub">Anjuran total kenaikan: {{ $anjuran[0] }}–{{ $anjuran[1] }} kg (IMT {{ $ibu->imt_kategori }})</div>
        </div>
    </div>
    @if ($grafik->count() >= 2)
        @php
            $w = 720; $h = 150; $pad = 34;
            $bbs = $grafik->pluck('bb');
            $minBb = floor($bbs->min() - 2); $maxBb = ceil($bbs->max() + 2);
            $minX = $grafik->min('minggu'); $maxX = max($grafik->max('minggu'), $minX + 1);
            $px = fn ($m) => $pad + (($m - $minX) / max(1, ($maxX - $minX))) * ($w - $pad * 2);
            $py = fn ($b) => $h - $pad - (($b - $minBb) / max(1, ($maxBb - $minBb))) * ($h - $pad * 2);
            $titik = $grafik->map(fn ($g) => round($px($g['minggu']), 1) . ',' . round($py($g['bb']), 1))->implode(' ');
        @endphp
        <svg viewBox="0 0 {{ $w }} {{ $h }}" style="width:100%;height:auto;">
            <line x1="{{ $pad }}" y1="{{ $h - $pad }}" x2="{{ $w - $pad }}" y2="{{ $h - $pad }}" stroke="#e4eae5" stroke-width="1"/>
            <line x1="{{ $pad }}" y1="{{ $pad }}" x2="{{ $pad }}" y2="{{ $h - $pad }}" stroke="#e4eae5" stroke-width="1"/>
            <polyline points="{{ $titik }}" fill="none" stroke="#4f7561" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round"/>
            @foreach ($grafik as $g)
                <circle cx="{{ round($px($g['minggu']), 1) }}" cy="{{ round($py($g['bb']), 1) }}" r="4" fill="#fff" stroke="#4f7561" stroke-width="2.5"/>
                <text x="{{ round($px($g['minggu']), 1) }}" y="{{ round($py($g['bb']), 1) - 11 }}" text-anchor="middle" font-size="10" fill="#758179" font-weight="700">{{ $g['bb'] }}</text>
            @endforeach
            <text x="{{ $pad }}" y="{{ $h - 10 }}" font-size="10" fill="#758179">minggu ke-{{ $minX }}</text>
            <text x="{{ $w - $pad }}" y="{{ $h - 10 }}" font-size="10" fill="#758179" text-anchor="end">minggu ke-{{ $maxX }}</text>
        </svg>
    @else
        <div class="sub" style="padding:16px 0;">Grafik muncul setelah ada minimal dua kunjungan dengan data berat badan.</div>
    @endif
</div>

{{-- ── Riwayat kunjungan ───────────────────────── --}}
<div class="card" style="padding:0;overflow:hidden;">
    <div style="padding:18px 20px 0;"><h3 style="margin:0;font-size:14px;font-weight:700;color:#233c32;">Riwayat Kunjungan ANC</h3></div>
    <div class="table-wrap" style="margin-top:12px;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Kunjungan</th><th>Usia</th><th>BB</th><th>Tensi</th>
                    <th>TFU / DJJ</th><th>Hb</th><th>Catatan</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($kunjungan as $k)
                <tr>
                    <td data-l="Kunjungan">
                        <div class="nm" style="font-size:13px;">{{ $k->label_kunjungan }}</div>
                        <div class="sub">{{ $k->tanggal_label }}</div>
                    </td>
                    <td data-l="Usia">{{ $k->usia_kehamilan_minggu }} mgg <span class="sub">(TM{{ $k->trimester }})</span></td>
                    <td data-l="BB">{{ $k->berat_badan }} kg</td>
                    <td data-l="Tensi">{{ $k->tensi_label }}</td>
                    <td data-l="TFU / DJJ">{{ $k->tfu ?? '—' }} / {{ $k->djj ?? '—' }}</td>
                    <td data-l="Hb">{{ $k->hb ?? '—' }}</td>
                    <td data-l="Catatan">
                        @forelse ($k->peringatan as $p)
                            <span style="display:inline-block;padding:2px 7px;border-radius:5px;font-size:10px;font-weight:700;background:#fdf1f0;color:#b3261e;margin:1px 2px 1px 0;">{{ $p }}</span>
                        @empty
                            <span style="display:inline-block;padding:2px 7px;border-radius:5px;font-size:10px;font-weight:700;background:#e8f1eb;color:#4f7561;">Normal</span>
                        @endforelse
                    </td>
                </tr>
            @empty
                <tr><td colspan="7"><div class="sub" style="padding:14px;">Belum ada kunjungan tercatat untuk ibu ini.</div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
