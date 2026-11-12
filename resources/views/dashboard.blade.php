@extends('layouts.app')

@section('title', 'Dashboard')
@section('breadcrumb', 'Dashboard')
@section('nav-active-dashboard', 'active')

@push('head')
<style>
    .grid-2 { display: grid; grid-template-columns: 1.55fr 1fr; gap: 16px; }
    @media (max-width: 1100px) { .grid-2 { grid-template-columns: 1fr; } }

    .card-h { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; gap: 10px; }
    .card-h h3 { margin: 0; font-size: 14px; font-weight: 700; color: #233c32; }
    .card-h .sub { font-size: 11.5px; color: #758179; }

    .bars { display: flex; align-items: flex-end; gap: 10px; height: 148px; padding-top: 22px; }
    .bar-col { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 7px; height: 100%; justify-content: flex-end; }
    .bar-col .b { width: 100%; max-width: 42px; border-radius: 7px 7px 3px 3px; background: linear-gradient(180deg,#6f9d84,#4f7561); min-height: 3px; position: relative; transition: height .4s cubic-bezier(.2,.8,.3,1); }
    .bar-col .b:hover { background: linear-gradient(180deg,#7fb096,#3f6350); }
    .bar-col .b span { position: absolute; top: -19px; left: 0; right: 0; text-align: center; font-size: 11px; font-weight: 700; color: #758179; }
    .bar-col .lb { font-size: 11px; color: #758179; font-weight: 600; }

    .donut-wrap { display: flex; align-items: center; gap: 20px; }
    .donut { width: 128px; height: 128px; border-radius: 50%; flex-shrink: 0; position: relative; }
    .donut::after { content: ''; position: absolute; inset: 19px; background: #fff; border-radius: 50%; }
    .donut-mid { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; z-index: 2; }
    .donut-mid b { font-size: 24px; color: #233c32; line-height: 1; }
    .donut-mid span { font-size: 10.5px; color: #758179; margin-top: 2px; }
    .legend { display: flex; flex-direction: column; gap: 9px; }
    .legend-item { display: flex; align-items: center; gap: 8px; font-size: 12.5px; color: #758179; }
    .legend-dot { width: 9px; height: 9px; border-radius: 3px; flex-shrink: 0; }

    .row-item { display: flex; align-items: center; gap: 11px; padding: 10px 0; border-bottom: 1px solid #f0f5f1; }
    .row-item:last-child { border-bottom: none; }
    .row-item .txt { flex: 1; min-width: 0; }
    .row-item .t1 { font-size: 13px; font-weight: 600; color: #233c32; }
    .row-item .t2 { font-size: 11.5px; color: #758179; margin-top: 1px; }

    .av { width: 34px; height: 34px; border-radius: 50%; background: linear-gradient(135deg,#4f7561,#4f7561); color: #fff; font-size: 12px; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .risk { padding: 3px 9px; border-radius: 20px; font-size: 10.5px; font-weight: 700; white-space: nowrap; }
    .risk-krr { background: #e8f1eb; color: #4f7561; }
    .risk-krt { background: #fbf3e3; color: #8a6a2a; }
    .risk-krst { background: #fdf1f0; color: #b3261e; }
    .warn-pill { background: #fdf1f0; color: #b3261e; padding: 2px 8px; border-radius: 6px; font-size: 10.5px; font-weight: 700; white-space: nowrap; }
    .date-box { width: 42px; height: 42px; border-radius: 10px; background: #f0f5f1; display: flex; flex-direction: column; align-items: center; justify-content: center; flex-shrink: 0; }
    .date-box b { font-size: 15px; color: #4f7561; line-height: 1; }
    .date-box span { font-size: 9.5px; color: #758179; text-transform: uppercase; }
</style>
@endpush

@section('content')

<div class="page-heading" style="margin-bottom:18px;">
    <div>
        <h2 style="margin:0;font-size:19px;color:#233c32;">Selamat datang, {{ auth()->user()?->name ?? 'Kader' }} 👋</h2>
        <div class="sub" style="margin-top:4px;">Ringkasan pemantauan ibu hamil Posyandu Melati — {{ now()->translatedFormat('F Y') }}</div>
    </div>
    <a href="{{ route('kunjungan.index') }}" class="btn-primary">
        <img src="/icons/plus.svg" alt="" style="width:15px;height:15px;"> Catat Kunjungan
    </a>
</div>

{{-- ── Kartu ringkasan ─────────────────────────── --}}
<div style="display:flex;gap:14px;flex-wrap:wrap;margin-bottom:16px;">
    <div class="card" style="flex:1;min-width:160px;display:flex;flex-direction:column;gap:12px;">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <span class="stat-label">Ibu Hamil Aktif</span>
            <div style="width:32px;height:32px;background:#f0f5f1;border-radius:9px;display:flex;align-items:center;justify-content:center;">
                <img src="/icons/users-round.svg" style="width:17px;height:17px;" alt="">
            </div>
        </div>
        <div class="stat-num">{{ $statistik['aktif'] }}</div>
        <div class="stat-label">{{ $statistik['total'] }} terdaftar total</div>
    </div>

    <div class="card" style="flex:1;min-width:160px;display:flex;flex-direction:column;gap:12px;">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <span class="stat-label">Risiko Tinggi</span>
            <div style="width:32px;height:32px;background:#fdf1f0;border-radius:9px;display:flex;align-items:center;justify-content:center;">
                <img src="/icons/heart-handshake.svg" style="width:17px;height:17px;" alt="">
            </div>
        </div>
        <div class="stat-num" style="color:#b3261e;">{{ $statistik['risiko'] }}</div>
        <div class="stat-label">KRT &amp; KRST (KSPR)</div>
    </div>

    <div class="card" style="flex:1;min-width:160px;display:flex;flex-direction:column;gap:12px;">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <span class="stat-label">Kurang Energi (KEK)</span>
            <div style="width:32px;height:32px;background:#fbf3e3;border-radius:9px;display:flex;align-items:center;justify-content:center;">
                <img src="/icons/sprout.svg" style="width:17px;height:17px;" alt="">
            </div>
        </div>
        <div class="stat-num" style="color:#8a6a2a;">{{ $statistik['kek'] }}</div>
        <div class="stat-label">LiLA &lt; 23,5 cm</div>
    </div>

    <div class="card" style="flex:1;min-width:160px;display:flex;flex-direction:column;gap:12px;">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <span class="stat-label">Anemia</span>
            <div style="width:32px;height:32px;background:#f0f5f1;border-radius:9px;display:flex;align-items:center;justify-content:center;">
                <img src="/icons/heart-pulse.svg" style="width:17px;height:17px;" alt="">
            </div>
        </div>
        <div class="stat-num">{{ $statistik['anemia'] }}</div>
        <div class="stat-label">Hb &lt; 11 g/dL</div>
    </div>
</div>

{{-- ── Grafik & donut ──────────────────────────── --}}
<div class="grid-2" style="margin-bottom:16px;">
    <div class="card">
        <div class="card-h">
            <div>
                <h3>Kunjungan ANC</h3>
                <div class="sub">Enam bulan terakhir</div>
            </div>
            <a href="{{ route('kunjungan.index') }}" class="sub" style="text-decoration:none;color:#2f7d78;font-weight:600;">Lihat semua →</a>
        </div>
        <div class="bars">
            @foreach ($tren as $t)
                <div class="bar-col">
                    <div class="b" style="height: {{ max(4, round($t['jumlah'] / $trenMaks * 100)) }}%;">
                        <span>{{ $t['jumlah'] }}</span>
                    </div>
                    <div class="lb">{{ $t['label'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="card">
        <div class="card-h"><h3>Sebaran Trimester</h3></div>
        @php
            $totalTm = max(1, $statistik['tm1'] + $statistik['tm2'] + $statistik['tm3']);
            $batas1 = $statistik['tm1'] / $totalTm * 100;
            $batas2 = $batas1 + $statistik['tm2'] / $totalTm * 100;
        @endphp
        <div class="donut-wrap">
            <div class="donut" style="background: conic-gradient(#4f7561 0 {{ $batas1 }}%, #7ba98c {{ $batas1 }}% {{ $batas2 }}%, #c8a45c {{ $batas2 }}% 100%);">
                <div class="donut-mid"><b>{{ $totalTm }}</b><span>ibu hamil</span></div>
            </div>
            <div class="legend">
                <div class="legend-item"><span class="legend-dot" style="background:#4f7561;"></span> Trimester 1 · {{ $statistik['tm1'] }}</div>
                <div class="legend-item"><span class="legend-dot" style="background:#7ba98c;"></span> Trimester 2 · {{ $statistik['tm2'] }}</div>
                <div class="legend-item"><span class="legend-dot" style="background:#c8a45c;"></span> Trimester 3 · {{ $statistik['tm3'] }}</div>
            </div>
        </div>
    </div>
</div>

{{-- ── Perlu perhatian & risiko tinggi ─────────── --}}
<div class="grid-2" style="margin-bottom:16px;">
    <div class="card">
        <div class="card-h">
            <div>
                <h3>⚠ Perlu Perhatian</h3>
                <div class="sub">Terlambat atau belum pernah ANC</div>
            </div>
            <a href="{{ route('ibu-hamil.index') }}" class="sub" style="text-decoration:none;color:#2f7d78;font-weight:600;">Lihat →</a>
        </div>
        @forelse ($terlambat as $i)
            <div class="row-item">
                <div class="av">{{ strtoupper(mb_substr($i->nama, 0, 1)) }}</div>
                <div class="txt">
                    <div class="t1">{{ $i->nama }}</div>
                    <div class="t2">{{ $i->usia_kehamilan }} minggu · {{ $i->jumlah_kunjungan }}× kunjungan</div>
                </div>
                <span class="warn-pill">{{ $i->jumlah_kunjungan === 0 ? 'Belum ANC' : 'Terlambat' }}</span>
            </div>
        @empty
            <div class="sub" style="padding:12px 0;">Semua ibu hamil sudah sesuai jadwal ANC. 👍</div>
        @endforelse
    </div>

    <div class="card">
        <div class="card-h">
            <div>
                <h3>Ibu Hamil Risiko Tinggi</h3>
                <div class="sub">Berdasarkan skor KSPR</div>
            </div>
        </div>
        @forelse ($risikoTinggi as $i)
            <div class="row-item">
                <div class="av" style="background:linear-gradient(135deg,#c96a5f,#a9453a);">{{ strtoupper(mb_substr($i->nama, 0, 1)) }}</div>
                <div class="txt">
                    <div class="t1">{{ $i->nama }}</div>
                    <div class="t2">{{ $i->usia_kehamilan }} minggu · skor {{ $i->skor_kspr }}</div>
                </div>
                <span class="risk risk-{{ strtolower($i->kategori_risiko) }}">{{ $i->kategori_risiko }}</span>
            </div>
        @empty
            <div class="sub" style="padding:12px 0;">Tidak ada ibu hamil berisiko tinggi.</div>
        @endforelse
    </div>
</div>

{{-- ── Jadwal & aktivitas ──────────────────────── --}}
<div class="grid-2">
    <div class="card">
        <div class="card-h">
            <div><h3>Jadwal Terdekat</h3><div class="sub">Kegiatan posyandu mendatang</div></div>
            <a href="{{ route('kegiatan.index') }}" class="sub" style="text-decoration:none;color:#2f7d78;font-weight:600;">Lihat →</a>
        </div>
        @forelse ($kegiatan as $k)
            <div class="row-item">
                <div class="date-box"><b>{{ $k->tanggal->format('d') }}</b><span>{{ $k->tanggal->translatedFormat('M') }}</span></div>
                <div class="txt">
                    <div class="t1">{{ $k->nama }}</div>
                    <div class="t2">{{ $k->jam_label }} · {{ $k->lokasi }}</div>
                </div>
                <span class="sub" style="white-space:nowrap;">{{ $k->trimester_label }}</span>
            </div>
        @empty
            <div class="sub" style="padding:12px 0;">Belum ada jadwal mendatang.</div>
        @endforelse
    </div>

    <div class="card">
        <div class="card-h"><h3>Aktivitas Terbaru</h3></div>
        @forelse ($log as $l)
            <div class="row-item">
                <div class="activity-icon"><img src="/icons/clipboard-check.svg" alt=""></div>
                <div class="txt">
                    <div class="t1">{{ $l->deskripsi }}</div>
                    <div class="t2">{{ $l->meta ?? '—' }}</div>
                </div>
                <span class="sub">{{ $l->waktu }}</span>
            </div>
        @empty
            <div class="sub" style="padding:12px 0;">Belum ada aktivitas.</div>
        @endforelse
    </div>
</div>

@endsection
