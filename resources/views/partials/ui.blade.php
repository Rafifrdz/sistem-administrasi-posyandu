{{--
    Partial UI bersama: tombol aksi, sistem modal, tabel data responsif.
    Dipakai oleh halaman kegiatan & kehadiran.
    Pakai: @include('partials.ui')
--}}
@push('head')
<style>
    /* ── Tabel data ───────────────────────────── */
    .data-table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
    .data-table thead th {
        text-align: left; padding: 11px 14px;
        font-size: 11px; font-weight: 600; color: #758179;
        background: #f8faf8; border-bottom: 1px solid #e4eae5;
        letter-spacing: .04em; text-transform: uppercase; white-space: nowrap;
    }
    .data-table tbody td { padding: 12px 14px; border-bottom: 1px solid #f0f5f1; vertical-align: middle; }
    .data-table tbody tr { transition: background .12s ease; }
    .data-table tbody tr:hover td { background: #f8faf8; }
    .data-table tbody tr:last-child td { border-bottom: none; }

    /* ── Sel ──────────────────────────────────── */
    .cell { display: flex; align-items: center; gap: 10px; min-width: 0; }
    .av {
        width: 34px; height: 34px; border-radius: 50%;
        background: linear-gradient(135deg,#4f7561,#4f7561);
        color: #fff; font-size: 11.5px; font-weight: 700;
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .av.sq { border-radius: 9px; }
    .av.lg { width: 46px; height: 46px; font-size: 15px; }
    .nm { font-weight: 600; color: #233c32; font-size: 13.5px; line-height: 1.3; }
    .sub { font-size: 11px; color: #758179; margin-top: 2px; }
    .sub.mono { font-family: ui-monospace, monospace; }

    /* ── Tombol aksi ──────────────────────────── */
    .act-group { display: flex; gap: 5px; justify-content: flex-end; }
    .act-btn {
        width: 32px; height: 32px; border-radius: 8px;
        display: inline-flex; align-items: center; justify-content: center;
        border: 1px solid #e4eae5; background: #fff; cursor: pointer;
        color: #758179; transition: all .15s ease; padding: 0;
    }
    .act-btn:hover { background: #e8f1eb; border-color: #b9d2c1; color: #4f7561; transform: translateY(-1px); }
    .act-btn:active { transform: translateY(0); }
    .act-btn.danger:hover { background: #fff0f0; border-color: #f0b8b8; color: #a3473e; }
    .act-btn svg { width: 15px; height: 15px; }

    /* ── Modal ────────────────────────────────── */
    .md-overlay {
        position: fixed; inset: 0; background: rgba(20,35,27,.42);
        backdrop-filter: blur(3px); z-index: 500;
        display: none; align-items: center; justify-content: center; padding: 20px;
    }
    .md-overlay.open { display: flex; animation: mdFade .18s ease; }
    @keyframes mdFade { from { opacity: 0 } to { opacity: 1 } }

    .md-box {
        background: #fff; border-radius: 16px; width: 100%;
        max-width: 620px; max-height: 90vh; overflow-y: auto;
        box-shadow: 0 20px 60px rgba(20,35,27,.22);
        animation: mdUp .22s cubic-bezier(.2,.8,.3,1);
    }
    .md-box.sm { max-width: 440px; }
    .md-box.lg { max-width: 780px; }
    @keyframes mdUp { from { opacity: 0; transform: translateY(16px) scale(.98) } to { opacity: 1; transform: none } }

    .md-head {
        padding: 18px 22px; border-bottom: 1px solid #e4eae5;
        display: flex; align-items: flex-start; justify-content: space-between; gap: 14px;
        position: sticky; top: 0; background: #fff; border-radius: 16px 16px 0 0; z-index: 2;
    }
    .md-title { font-size: 16px; font-weight: 700; color: #233c32; }
    .md-desc { font-size: 12.5px; color: #758179; margin-top: 2px; }
    .md-close {
        width: 32px; height: 32px; border-radius: 8px; border: none;
        background: none; cursor: pointer; color: #758179;
        display: flex; align-items: center; justify-content: center;
        transition: all .15s ease; flex-shrink: 0; padding: 0;
    }
    .md-close:hover { background: #f0f5f1; color: #4f7561; }
    .md-close svg { width: 17px; height: 17px; }

    .md-body { padding: 20px 22px; display: flex; flex-direction: column; gap: 16px; }
    .md-foot {
        padding: 15px 22px; background: #f8faf8;
        border-top: 1px solid #e4eae5; display: flex; gap: 10px; justify-content: flex-end;
        border-radius: 0 0 16px 16px; position: sticky; bottom: 0;
    }

    /* ── Form di dalam modal ──────────────────── */
    .fgrid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .fgrid .full { grid-column: 1 / -1; }
    .fld { display: flex; flex-direction: column; gap: 5px; }
    .fld label { font-size: 12.5px; font-weight: 600; color: #233c32; }
    .fld label .req { color: #a3473e; }
    .fld input, .fld select, .fld textarea {
        height: 40px; padding: 0 11px; border: 1.5px solid #e4eae5;
        border-radius: 8px; font-size: 13px; font-family: inherit; color: #233c32;
        outline: none; background-color: #fff; width: 100%;
        transition: border-color .15s, box-shadow .15s;
    }
    .fld select {
        -webkit-appearance: none; -moz-appearance: none; appearance: none;
        background-image: url("data:image/svg+xml;charset=UTF-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%234f7561' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right 12px center;
        background-size: 16px 16px; padding-right: 36px; cursor: pointer;
    }
    .fld textarea { height: auto; padding: 9px 11px; resize: vertical; }
    .fld input:focus, .fld select:focus, .fld textarea:focus {
        border-color: #4f7561; box-shadow: 0 0 0 3px rgba(79,117,97,.12);
    }

    /* ── Grid detail ──────────────────────────── */
    .dt-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px 20px; }
    .dt-item { display: flex; flex-direction: column; gap: 3px; }
    .dt-item.full { grid-column: 1 / -1; }
    .dt-lbl { font-size: 10.5px; font-weight: 600; color: #758179; text-transform: uppercase; letter-spacing: .05em; }
    .dt-val { font-size: 13.5px; color: #233c32; }
    .dt-head { display: flex; align-items: center; gap: 13px; padding-bottom: 16px; border-bottom: 1px solid #e4eae5; margin-bottom: 4px; }

    /* ── Ikon hapus ───────────────────────────── */
    .del-icon {
        width: 46px; height: 46px; border-radius: 50%; background: #fff0f0;
        display: flex; align-items: center; justify-content: center; margin: 0 auto 4px;
    }
    .del-icon svg { width: 21px; height: 21px; color: #a3473e; }

    /* ── Responsif: tabel → kartu ─────────────── */
    @media (max-width: 720px) {
        .data-table thead { display: none; }
        .data-table, .data-table tbody, .data-table tr, .data-table td { display: block; width: 100%; }
        .data-table tbody tr {
            border: 1px solid #e4eae5; border-radius: 12px;
            margin-bottom: 10px; padding: 6px 2px; background: #fff;
        }
        .data-table tbody td { border: none; padding: 7px 14px; }
        .data-table tbody td[data-l]::before {
            content: attr(data-l); display: block;
            font-size: 10px; font-weight: 600; color: #758179;
            text-transform: uppercase; letter-spacing: .05em; margin-bottom: 3px;
        }
        .act-group { justify-content: flex-start; }
        .act-btn { width: 36px; height: 36px; }
        .fgrid, .dt-grid { grid-template-columns: 1fr; }
        .md-box { max-height: 94vh; }
        .md-body { padding: 16px; }
    }
</style>
@endpush

@push('scripts')
<script>
    function openModal(id) {
        const el = document.getElementById(id);
        if (!el) return;
        el.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
    function closeModal(id) {
        const el = document.getElementById(id);
        if (!el) return;
        el.classList.remove('open');
        if (!document.querySelector('.md-overlay.open')) document.body.style.overflow = '';
    }
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.md-overlay.open').forEach(m => closeModal(m.id));
        }
    });
</script>
@endpush
