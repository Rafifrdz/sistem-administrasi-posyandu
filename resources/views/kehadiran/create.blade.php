@extends('layouts.app')
@section('title','Catat Kehadiran')
@section('breadcrumb','Catat Kehadiran')
@section('nav-active-kehadiran','active')

@section('content')
<div class="page-heading">
    <div class="title-group">
        <h1>Catat kehadiran</h1>
        <p>Pilih kegiatan dan centang peserta yang hadir.</p>
    </div>
    <a href="{{ route('kehadiran.index') }}" class="btn-secondary">Kembali</a>
</div>

<div style="display:flex;gap:24px;align-items:flex-start;">
    <div class="card" style="flex:1;">
        <form method="POST" action="{{ route('kehadiran.store') }}" style="display:flex;flex-direction:column;gap:22px;" id="kehadiran-form">
            @csrf
            <div class="form-group">
                <label class="form-label">Kegiatan <span style="color:red">*</span></label>
                <select name="kegiatan_id" id="kegiatan-select" class="form-control" required onchange="filterPeserta(this.value)">
                    <option value="">Pilih kegiatan...</option>
                    @foreach($kegiatan as $kg)
                    <option value="{{ $kg->id }}" data-kat="{{ $kg->kategori }}">
                        {{ $kg->nama }} – {{ $kg->tanggal->translatedFormat('d M Y') }}
                    </option>
                    @endforeach
                </select>
                @error('kegiatan_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>

            <div id="peserta-section" style="display:none;flex-direction:column;gap:14px;">
                <div style="display:flex;align-items:center;justify-content:space-between;">
                    <label class="form-label">Peserta hadir</label>
                    <div style="display:flex;gap:8px;">
                        <button type="button" onclick="checkAll(true)" class="btn-secondary btn-sm">Pilih semua</button>
                        <button type="button" onclick="checkAll(false)" class="btn-secondary btn-sm">Batalkan semua</button>
                    </div>
                </div>
                <div id="peserta-list" style="display:flex;flex-direction:column;gap:8px;max-height:420px;overflow-y:auto;border:1px solid #e4eae5;border-radius:8px;padding:14px;"></div>
            </div>

            <div class="divider"></div>
            <div style="display:flex;gap:12px;">
                <button type="submit" class="btn-primary" id="submit-btn" disabled>Simpan kehadiran</button>
                <a href="{{ route('kehadiran.index') }}" class="btn-secondary">Batal</a>
            </div>
        </form>
    </div>

    <div style="width:280px;flex-shrink:0;">
        <div class="privacy-note">
            <img src="/icons/info.svg" alt="">
            <p>Peserta yang tidak dicentang akan otomatis tercatat sebagai "tidak hadir". Hanya peserta yang sesuai kategori kegiatan yang ditampilkan (kecuali kegiatan "Umum").</p>
        </div>
    </div>
</div>

@php $pesertaJson = $peserta->map(fn($p) => ['id'=>$p->id,'nama'=>$p->nama,'kategori'=>$p->kategori,'label'=>$p->kategori_label])->values()->toJson(); @endphp

@push('scripts')
<script>
const semua = @json(json_decode($pesertaJson));
function filterPeserta(kegiatanId) {
    const sel = document.getElementById('kegiatan-select');
    const opt = sel.options[sel.selectedIndex];
    const kat = opt ? opt.dataset.kat : '';
    const section = document.getElementById('peserta-section');
    const list = document.getElementById('peserta-list');
    const btn = document.getElementById('submit-btn');
    if (!kegiatanId) { section.style.display = 'none'; btn.disabled = true; return; }
    section.style.display = 'flex';
    btn.disabled = false;
    const filtered = kat === 'umum' ? semua : semua.filter(p => p.kategori === kat);
    list.innerHTML = filtered.length === 0
        ? '<span style="font-size:13px;color:#758179;">Tidak ada peserta untuk kategori ini.</span>'
        : filtered.map(p => `
            <label style="display:flex;align-items:center;gap:12px;cursor:pointer;padding:8px 10px;border-radius:7px;transition:background 0.1s;" onmouseover="this.style.background='#f6f8f6'" onmouseout="this.style.background=''">
                <input type="checkbox" name="peserta_ids[]" value="${p.id}" style="width:16px;height:16px;accent-color:#4f7561;">
                <span style="flex:1;font-size:14px;color:#233c32;">${p.nama}</span>
                <span class="badge badge-green">${p.label}</span>
            </label>
        `).join('');
}
function checkAll(state) {
    document.querySelectorAll('input[name="peserta_ids[]"]').forEach(cb => cb.checked = state);
}
</script>
@endpush
@endsection
