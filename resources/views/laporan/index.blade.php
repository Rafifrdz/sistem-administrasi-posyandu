@extends('layouts.app')
@section('title','Laporan')
@section('breadcrumb','Laporan')
@section('nav-active-laporan','active')

@push('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')
<div class="page-heading">
    <div class="title-group">
        <h1>Laporan</h1>
        <p>Ringkasan data dan statistik posyandu tahun {{ $year }}.</p>
    </div>
</div>

{{-- Stat summary --}}
<div style="display:flex;gap:18px;">
    @foreach([['Total peserta',$totalPeserta,'Semua terdaftar'],['Kegiatan selesai',$kegiatanSelesai,'Tahun '.$year],['Rata-rata kehadiran',$rataKehadiran.'%','Tingkat kehadiran']] as [$lbl,$val,$sub])
    <div class="card" style="flex:1;display:flex;flex-direction:column;gap:9px;">
        <span class="stat-label">{{ $lbl }}</span>
        <span class="stat-num">{{ $val }}</span>
        <span style="font-size:11px;color:#a5b1a8;">{{ $sub }}</span>
    </div>
    @endforeach
</div>

<div style="display:flex;gap:20px;align-items:flex-start;">
    {{-- Bar chart --}}
    <div class="card" style="flex:1;">
        <div style="font-size:16px;font-weight:600;color:#233c32;margin-bottom:4px;">Kehadiran per bulan</div>
        <div style="font-size:13px;color:#758179;margin-bottom:20px;">Jumlah peserta hadir sepanjang tahun {{ $year }}</div>
        <canvas id="barChart" height="200"></canvas>
    </div>

    {{-- Pie kategori --}}
    <div class="card" style="width:300px;flex-shrink:0;">
        <div style="font-size:16px;font-weight:600;color:#233c32;margin-bottom:4px;">Distribusi peserta</div>
        <div style="font-size:13px;color:#758179;margin-bottom:20px;">Berdasarkan kategori</div>
        <canvas id="pieChart" height="260"></canvas>
        <div style="display:flex;flex-direction:column;gap:9px;margin-top:18px;">
            @foreach(['balita'=>['Balita','#4f7561'],'ibu_hamil'=>['Ibu Hamil','#8db39d'],'lansia'=>['Lansia','#c5dace']] as $k=>[$lbl,$clr])
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="width:12px;height:12px;border-radius:3px;background:{{ $clr }};flex-shrink:0;"></div>
                <span style="flex:1;font-size:13px;color:#233c32;">{{ $lbl }}</span>
                <span style="font-size:13px;font-weight:600;color:#233c32;">{{ $perKategori[$k] }}</span>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Tabel bulanan --}}
<div class="card" style="padding:0;overflow:hidden;">
    <div style="padding:20px 24px;border-bottom:1px solid #e4eae5;font-size:16px;font-weight:600;color:#233c32;">Data kehadiran per bulan</div>
    <table>
        <thead>
            <tr><th>Bulan</th><th>Jumlah hadir</th><th>Grafik</th></tr>
        </thead>
        <tbody>
            @php $maxBulan = max(array_merge([1],$bulanData)); @endphp
            @foreach($bulanLabels as $i => $lbl)
            <tr>
                <td style="font-weight:500;">{{ $lbl }}</td>
                <td>{{ $bulanData[$i] }}</td>
                <td style="width:50%;padding:10px 16px;">
                    <div style="height:8px;background:#f0f5f1;border-radius:9px;overflow:hidden;">
                        <div style="height:100%;width:{{ $maxBulan>0?round($bulanData[$i]/$maxBulan*100):0 }}%;background:#4f7561;border-radius:9px;"></div>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@push('scripts')
<script>
const labels = @json($bulanLabels);
const data   = @json($bulanData);
const perKat = @json(array_values($perKategori));

new Chart(document.getElementById('barChart'), {
    type: 'bar',
    data: { labels, datasets: [{ data, backgroundColor: '#4f7561', borderRadius: 6, borderSkipped: false }] },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: '#f0f5f1' } }, x: { grid: { display: false } } } }
});

new Chart(document.getElementById('pieChart'), {
    type: 'doughnut',
    data: { labels: ['Balita','Ibu Hamil','Lansia'], datasets: [{ data: perKat, backgroundColor: ['#4f7561','#8db39d','#c5dace'], borderWidth: 0, hoverOffset: 4 }] },
    options: { cutout: '65%', plugins: { legend: { display: false } } }
});
</script>
@endpush
@endsection
