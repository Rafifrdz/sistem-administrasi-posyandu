@extends('layouts.app')
@section('title','Dashboard')
@section('breadcrumb','Dashboard')
@section('nav-active-dashboard','active')

@section('content')
{{-- Page heading --}}
<div class="page-heading">
    <div class="title-group">
        <h1>Dashboard</h1>
        <p>Selamat pagi, Bu Siti. Mari mulai pelayanan yang lebih teratur.</p>
    </div>
    <div style="background:#fff;border:1px solid #e4eae5;border-radius:8px;height:44px;padding:0 12px;display:flex;align-items:center;gap:10px;min-width:150px;">
        <span style="font-size:14px;color:#233c32;flex:1;">{{ $bulanIni->translatedFormat('F Y') }}</span>
    </div>
</div>

{{-- Summary cards --}}
<div style="display:flex;gap:18px;">
    <div class="card" style="flex:1;display:flex;flex-direction:column;gap:14px;">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <span class="stat-label">Total peserta</span>
            <div style="width:32px;height:32px;background:#f0f5f1;border-radius:9px;display:flex;align-items:center;justify-content:center;">
                <img src="/icons/users-round2.svg" style="width:18px;height:18px;" alt="">
            </div>
        </div>
        <div class="stat-num">{{ $totalPeserta }}</div>
        <div class="stat-label">Terdaftar di Posyandu Melati</div>
    </div>
    <div class="card" style="flex:1;display:flex;flex-direction:column;gap:14px;">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <span class="stat-label">Balita</span>
            <div style="width:32px;height:32px;background:#f0f5f1;border-radius:9px;display:flex;align-items:center;justify-content:center;">
                <img src="/icons/baby.svg" style="width:18px;height:18px;" alt="">
            </div>
        </div>
        <div class="stat-num">{{ $balita }}</div>
        <div class="stat-label">{{ $totalPeserta > 0 ? round($balita/$totalPeserta*100,1) : 0 }}% dari seluruh peserta</div>
    </div>
    <div class="card" style="flex:1;display:flex;flex-direction:column;gap:14px;">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <span class="stat-label">Ibu hamil</span>
            <div style="width:32px;height:32px;background:#f0f5f1;border-radius:9px;display:flex;align-items:center;justify-content:center;">
                <img src="/icons/heart-handshake.svg" style="width:18px;height:18px;" alt="">
            </div>
        </div>
        <div class="stat-num">{{ $ibuHamil }}</div>
        <div class="stat-label">{{ $totalPeserta > 0 ? round($ibuHamil/$totalPeserta*100,1) : 0 }}% dari seluruh peserta</div>
    </div>
    <div class="card" style="flex:1;display:flex;flex-direction:column;gap:14px;">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <span class="stat-label">Lansia</span>
            <div style="width:32px;height:32px;background:#f0f5f1;border-radius:9px;display:flex;align-items:center;justify-content:center;">
                <img src="/icons/accessibility.svg" style="width:18px;height:18px;" alt="">
            </div>
        </div>
        <div class="stat-num">{{ $lansia }}</div>
        <div class="stat-label">{{ $totalPeserta > 0 ? round($lansia/$totalPeserta*100,1) : 0 }}% dari seluruh peserta</div>
    </div>
</div>

{{-- Attendance + Schedule --}}
<div style="display:flex;gap:20px;align-items:flex-start;">
    <div class="card" style="flex:1;display:flex;flex-direction:column;gap:16px;">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;">
            <div>
                <div style="font-size:16px;font-weight:600;color:#233c32;">Statistik kehadiran</div>
                <div style="font-size:13px;color:#758179;margin-top:3px;">Jumlah peserta hadir • 6 bulan terakhir</div>
            </div>
            <span class="badge badge-gray">6 bulan terakhir</span>
        </div>
        <div style="display:flex;align-items:baseline;gap:14px;">
            <span style="font-size:29px;font-weight:600;color:#233c32;">{{ $persen }}%</span>
            @if($selisih >= 0)
                <span class="badge badge-green">↑ {{ abs($selisih) }} poin dari bulan lalu</span>
            @else
                <span class="badge badge-red">↓ {{ abs($selisih) }} poin dari bulan lalu</span>
            @endif
            <span style="font-size:12px;color:#758179;">{{ $hadir }} dari {{ $totalPeserta }} peserta</span>
        </div>
        @php $maxVal = max(array_merge([1], $bulanData)); @endphp
        <div style="display:flex;flex-direction:column;gap:8px;">
            <div style="display:flex;gap:10px;height:170px;align-items:flex-end;">
                <div style="display:flex;flex-direction:column;justify-content:space-between;height:150px;font-size:10px;color:#a5b1a8;width:23px;">
                    <span>{{ $maxVal }}</span><span>{{ intdiv($maxVal,2) }}</span><span>0</span>
                </div>
                <div style="display:flex;flex:1;gap:16px;align-items:flex-end;height:164px;padding:0 8px;">
                    @foreach($bulanData as $i => $val)
                    @php $h = $maxVal > 0 ? max(4, round(($val / $maxVal) * 150)) : 4; $isLast = $i === count($bulanData)-1; @endphp
                    <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:6px;">
                        <span style="font-size:11px;color:{{ $isLast ? '#4f7561' : '#758179' }};font-weight:{{ $isLast ? '600' : '400' }};">{{ $val }}</span>
                        <div style="width:100%;height:{{ $h }}px;background:{{ $isLast ? '#4f7561' : '#e8f1eb' }};border-radius:5px 5px 0 0;"></div>
                    </div>
                    @endforeach
                </div>
            </div>
            <div style="display:flex;gap:16px;padding-left:33px;padding-right:8px;font-size:11px;color:#758179;text-align:center;">
                @foreach($bulanLabels as $lbl)<span style="flex:1;">{{ $lbl }}</span>@endforeach
            </div>
        </div>
    </div>

    <div class="card" style="width:360px;flex-shrink:0;display:flex;flex-direction:column;gap:18px;">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <span style="font-size:16px;font-weight:600;color:#233c32;">Kegiatan terdekat</span>
            <a href="{{ route('kegiatan.index') }}" style="font-size:13px;font-weight:600;color:#4f7561;text-decoration:none;">Lihat jadwal</a>
        </div>
        @forelse($kegiatanTerdekat as $kg)
        <div style="display:flex;flex-direction:column;gap:14px;">
            <div style="display:flex;gap:14px;align-items:flex-start;">
                <div style="width:47px;background:#f0f5f1;border-radius:9px;padding:7px 0;display:flex;flex-direction:column;align-items:center;flex-shrink:0;">
                    <span style="font-size:21px;font-weight:600;color:#233c32;line-height:1.15;">{{ $kg->tanggal->format('d') }}</span>
                    <span style="font-size:10px;font-weight:500;color:#4f7561;">{{ strtoupper($kg->tanggal->translatedFormat('M')) }}</span>
                </div>
                <div style="flex:1;display:flex;flex-direction:column;gap:4px;">
                    <span style="font-size:14px;font-weight:600;color:#233c32;">{{ $kg->nama }}</span>
                    <span style="font-size:11px;color:#758179;">{{ $kg->tanggal->translatedFormat('l') }} • {{ substr($kg->jam_mulai,0,5) }}–{{ substr($kg->jam_selesai,0,5) }} WIB</span>
                    <div style="display:flex;align-items:center;gap:5px;">
                        <img src="/icons/map-pin.svg" style="width:12px;height:12px;opacity:0.5;" alt="">
                        <span style="font-size:11px;color:#758179;">{{ $kg->lokasi }}</span>
                    </div>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span class="badge badge-green">
                            <img src="/icons/calendar.svg" style="width:12px;height:12px;" alt="">
                            {{ $kg->status_label }}
                        </span>
                        <span style="font-size:11px;color:#758179;">{{ $kg->jumlah_peserta }} peserta</span>
                    </div>
                </div>
            </div>
            @if(!$loop->last)<div class="divider"></div>@endif
        </div>
        @empty
        <div style="text-align:center;padding:24px 0;color:#758179;font-size:13px;">Tidak ada kegiatan terjadwal.</div>
        @endforelse
    </div>
</div>

{{-- Aktivitas terbaru --}}
<div class="card" style="display:flex;flex-direction:column;gap:18px;">
    <div>
        <div style="font-size:16px;font-weight:600;color:#233c32;">Aktivitas terbaru</div>
        <div style="font-size:13px;color:#758179;margin-top:3px;">Perubahan administrasi hari ini</div>
    </div>
    @php $actIcons=['kehadiran_simpan'=>'clipboard-check2','peserta_tambah'=>'user-round-plus','peserta_edit'=>'edit','kegiatan_update'=>'calendar-check','kegiatan_tambah'=>'calendar-check','peserta_hapus'=>'trash']; @endphp
    <div style="display:flex;flex-direction:column;gap:13px;">
        @forelse($aktivitas as $act)
        <div style="display:flex;flex-direction:column;gap:13px;">
            <div style="display:flex;align-items:flex-start;gap:12px;">
                <div class="activity-icon"><img src="/icons/{{ $actIcons[$act->type] ?? 'clipboard-check2' }}.svg" alt=""></div>
                <div style="flex:1;display:flex;flex-direction:column;gap:2px;">
                    <span style="font-size:14px;font-weight:500;color:#233c32;">{{ $act->deskripsi }}</span>
                    <span style="font-size:11px;color:#758179;">{{ $act->meta }}</span>
                </div>
                <span style="font-size:11px;color:#758179;white-space:nowrap;">{{ $act->waktu }}</span>
            </div>
            @if(!$loop->last)<div class="divider"></div>@endif
        </div>
        @empty
        <div style="color:#758179;font-size:13px;">Belum ada aktivitas.</div>
        @endforelse
    </div>
</div>

<div class="page-footer">
    <span>Posyandu Melati • Data dari database.</span>
    <span>Diperbarui {{ now()->translatedFormat('d F Y, H.i') }} WIB</span>
</div>
@endsection
