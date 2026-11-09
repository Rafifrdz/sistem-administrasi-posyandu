<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') – Posyandu Digital</title>

    {{-- Favicon (v=2 : cache-busting saat logo diganti) --}}
    <link rel="icon" href="/favicon.ico?v=2" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png?v=2">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png?v=2">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png?v=2">
    <link rel="mask-icon" href="/favicon-32x32.png?v=2" color="#148a7e">
    <meta name="theme-color" content="#148a7e">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body {
            font-family: 'Inter', sans-serif;
            background: #f5f7f5;
            display: flex;
            min-height: 100vh;
            color: #1e3528;
            -webkit-font-smoothing: antialiased;
        }

        /* ─── SIDEBAR ─────────────────────────────── */
        .sidebar {
            width: 240px;
            min-height: 100vh;
            background: #fff;
            border-right: 1px solid #e3eae4;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 24px 16px 20px;
            flex-shrink: 0;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
            transition: transform 0.25s ease;
        }

        .sidebar-top { display: flex; flex-direction: column; gap: 20px; }

        /* Brand */
        .brand { display: flex; align-items: center; gap: 10px; padding: 0 4px; }
        .brand-mark {
            width: 38px; height: 38px;
            border-radius: 11px;
            overflow: hidden;
            flex-shrink: 0;
            background: #148a7e;
            box-shadow: 0 2px 8px rgba(20,138,126,0.3);
            transition: transform .18s ease, box-shadow .18s ease;
        }
        .brand:hover .brand-mark {
            transform: scale(1.04);
            box-shadow: 0 4px 14px rgba(20,138,126,0.36);
        }
        .brand-mark img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .brand-name .name { font-size: 15px; font-weight: 700; color: #1e3528; }
        .brand-name .tagline { font-size: 10.5px; color: #90a898; margin-top: 1px; }

        /* Location chip */
        .location-chip {
            display: flex; align-items: center; gap: 9px;
            background: #f0f6f2; border-radius: 10px;
            padding: 10px 12px;
        }
        .location-dot { width: 8px; height: 8px; background: #4f7561; border-radius: 50%; flex-shrink: 0; }
        .location-chip .loc-name { font-size: 12.5px; font-weight: 600; color: #1e3528; }
        .location-chip .loc-area { font-size: 11px; color: #90a898; margin-top: 1px; }

        /* Nav menu */
        .menu { display: flex; flex-direction: column; gap: 2px; }
        .nav-item {
            display: flex; align-items: center; gap: 10px;
            height: 42px; padding: 0 12px;
            border-radius: 9px;
            cursor: pointer;
            text-decoration: none;
            color: #6b8075;
            font-size: 13.5px;
            font-weight: 500;
            position: relative;
            transition: background 0.15s ease, color 0.15s ease, transform 0.1s ease;
            will-change: transform;
        }
        .nav-item:hover {
            background: #f0f6f2;
            color: #2d5540;
            transform: translateX(2px);
        }
        .nav-item:active { transform: translateX(1px) scale(0.99); }
        .nav-item.active {
            background: #e6f1ea;
            color: #3a5c48;
            font-weight: 600;
        }
        .nav-item.active::before {
            content: '';
            position: absolute;
            left: 0; top: 8px; bottom: 8px;
            width: 3px;
            background: #4f7561;
            border-radius: 0 3px 3px 0;
        }
        .nav-item .nav-icon {
            width: 18px; height: 18px;
            opacity: 0.5;
            flex-shrink: 0;
            transition: opacity 0.15s ease;
        }
        .nav-item:hover .nav-icon,
        .nav-item.active .nav-icon { opacity: 1; }

        /* Sidebar footer */
        .sidebar-footer { display: flex; flex-direction: column; gap: 12px; padding: 0 4px; }
        .care-note {
            background: linear-gradient(135deg, #f0f6f2, #e8f1eb);
            border-radius: 12px;
            padding: 13px 14px;
            display: flex; flex-direction: column; gap: 6px;
        }
        .care-note .care-icon { width: 20px; height: 20px; opacity: 0.7; }
        .care-note .msg { font-size: 12.5px; font-weight: 600; color: #2d5540; line-height: 1.4; }
        .care-note .sub { font-size: 11px; color: #7a9b88; line-height: 1.5; }
        .version { font-size: 10.5px; color: #b0bdb5; }

        /* ─── WORKSPACE ───────────────────────────── */
        .workspace { flex: 1; display: flex; flex-direction: column; min-width: 0; overflow: hidden; }

        /* Topbar */
        .topbar {
            background: #fff;
            border-bottom: 1px solid #e3eae4;
            height: 64px;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 32px;
            flex-shrink: 0;
            position: sticky; top: 0; z-index: 10;
        }
        .page-title { font-size: 15px; font-weight: 600; color: #1e3528; }
        .profile-area { display: flex; align-items: center; gap: 12px; }
        .topbar-sep { width: 1px; height: 24px; background: #e3eae4; }
        .profile {
            display: flex; align-items: center; gap: 9px;
            cursor: pointer;
            padding: 6px 10px 6px 6px;
            border-radius: 10px;
            transition: background 0.15s ease;
        }
        .profile:hover { background: #f0f6f2; }

        .logout-btn {
            width: 34px; height: 34px;
            border-radius: 9px;
            border: 1px solid #e3eae4;
            background: #fff;
            color: #90a898;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            padding: 0;
            transition: all .15s ease;
        }
        .logout-btn:hover {
            background: #fff0f0;
            border-color: #f0b8b8;
            color: #c0392b;
            transform: translateY(-1px);
        }
        .logout-btn:active { transform: translateY(0); }
        .logout-btn svg { width: 16px; height: 16px; }
        .avatar {
            width: 32px; height: 32px;
            background: linear-gradient(135deg, #4f7561, #3a5c48);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 11px; font-weight: 700; color: #fff;
            flex-shrink: 0;
        }
        .uname { font-size: 13px; font-weight: 600; color: #1e3528; }
        .urole { font-size: 11px; color: #90a898; }

        /* ─── PAGE CONTENT ────────────────────────── */
        .page-content {
            padding: 28px 32px 32px;
            display: flex; flex-direction: column; gap: 22px;
            overflow-y: auto; flex: 1;
        }

        /* ─── CARDS ───────────────────────────────── */
        .card {
            background: #fff;
            border: 1px solid #e3eae4;
            border-radius: 14px;
            padding: 20px;
            box-shadow: 0 1px 4px rgba(30,53,40,0.04);
            transition: box-shadow 0.2s ease;
        }
        .card:hover { box-shadow: 0 4px 16px rgba(30,53,40,0.07); }

        /* ─── PAGE HEADING ────────────────────────── */
        .page-heading { display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
        .title-group { display: flex; flex-direction: column; gap: 4px; }
        .title-group h1 { font-size: 24px; font-weight: 700; color: #1e3528; letter-spacing: -0.3px; }
        .title-group p { font-size: 13.5px; color: #7a9b88; }

        /* ─── BUTTONS ─────────────────────────────── */
        .btn-primary {
            display: inline-flex; align-items: center; gap: 7px;
            height: 40px; padding: 0 16px;
            background: #4f7561; border: none; border-radius: 9px;
            color: #fff; font-size: 13.5px; font-weight: 600;
            cursor: pointer; text-decoration: none;
            transition: background 0.15s ease, transform 0.1s ease, box-shadow 0.15s ease;
            box-shadow: 0 1px 4px rgba(79,117,97,0.25);
        }
        .btn-primary:hover { background: #3a5c48; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(79,117,97,0.3); }
        .btn-primary:active { transform: translateY(0); box-shadow: none; }
        .btn-primary img { width: 15px; height: 15px; filter: brightness(0) invert(1); }

        .btn-secondary {
            display: inline-flex; align-items: center; gap: 7px;
            height: 40px; padding: 0 14px;
            background: #fff; border: 1px solid #dde5de; border-radius: 9px;
            color: #3a5540; font-size: 13.5px; font-weight: 500;
            cursor: pointer; text-decoration: none;
            transition: background 0.15s ease, border-color 0.15s ease, transform 0.1s ease;
        }
        .btn-secondary:hover { background: #f5f9f5; border-color: #c5d5c8; transform: translateY(-1px); }
        .btn-secondary:active { transform: translateY(0); }

        .btn-danger {
            display: inline-flex; align-items: center; gap: 6px;
            height: 34px; padding: 0 11px;
            background: #fff5f5; border: 1px solid #f5c5c5; border-radius: 8px;
            color: #c0392b; font-size: 12.5px; font-weight: 500;
            cursor: pointer; text-decoration: none;
            transition: background 0.15s ease, transform 0.1s ease;
        }
        .btn-danger:hover { background: #ffe8e8; transform: translateY(-1px); }
        .btn-sm { height: 32px; padding: 0 10px; font-size: 12px; }

        /* ─── BADGE ───────────────────────────────── */
        .badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 9px; border-radius: 6px; font-size: 11.5px; font-weight: 500; }
        .badge-green { background: #e6f1ea; color: #3a5c48; }
        .badge-gray  { background: #f2f5f2; color: #6b8075; }
        .badge-red   { background: #fff0f0; color: #c0392b; }

        /* ─── TABLE ───────────────────────────────── */
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
        thead th {
            text-align: left; padding: 11px 16px;
            font-size: 11.5px; font-weight: 600; color: #90a898;
            background: #f8faf8; border-bottom: 1px solid #e3eae4;
            letter-spacing: 0.03em; text-transform: uppercase;
        }
        tbody td {
            padding: 13px 16px; border-bottom: 1px solid #f0f5f1;
            color: #1e3528; vertical-align: middle;
        }
        tbody tr { transition: background 0.12s ease; }
        tbody tr:hover td { background: #f8faf8; }
        tbody tr:last-child td { border-bottom: none; }

        /* ─── FORM ────────────────────────────────── */
        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-label { font-size: 13.5px; font-weight: 500; color: #1e3528; }
        .form-hint { font-size: 11.5px; color: #90a898; }
        .form-control {
            height: 44px; padding: 0 12px;
            border: 1.5px solid #dde5de; border-radius: 9px;
            font-size: 13.5px; font-family: inherit; color: #1e3528;
            outline: none; width: 100%; background: #fff;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }
        .form-control:focus {
            border-color: #4f7561;
            box-shadow: 0 0 0 3px rgba(79,117,97,0.12);
        }
        .form-control.is-invalid { border-color: #c0392b; }
        .form-control.is-invalid:focus { box-shadow: 0 0 0 3px rgba(192,57,43,0.1); }
        textarea.form-control { height: auto; padding: 11px 12px; resize: vertical; }
        select.form-control { cursor: pointer; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
        .form-section { display: flex; flex-direction: column; gap: 16px; }
        .form-section-title { font-size: 15px; font-weight: 600; color: #1e3528; }
        .form-section-desc { font-size: 12.5px; color: #90a898; margin-top: 2px; }
        .invalid-feedback { font-size: 11.5px; color: #c0392b; }
        .privacy-note {
            background: #f0f6f2; border-radius: 9px; padding: 13px;
            display: flex; gap: 10px;
        }
        .privacy-note img { width: 16px; height: 16px; flex-shrink: 0; margin-top: 1px; opacity: 0.6; }
        .privacy-note p { font-size: 12px; color: #7a9b88; line-height: 1.6; }

        /* ─── ALERT ───────────────────────────────── */
        .alert {
            padding: 13px 16px; border-radius: 9px; font-size: 13.5px;
            display: flex; align-items: center; gap: 10px;
            animation: slideDown 0.25s ease;
        }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-8px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .alert-success { background: #e6f1ea; color: #2d5540; border: 1px solid #c0d9c8; }
        .alert-error   { background: #fff0f0; color: #c0392b; border: 1px solid #f5c5c5; }

        /* ─── DIVIDER ─────────────────────────────── */
        .divider { height: 1px; background: #e3eae4; width: 100%; }

        /* ─── PAGE FOOTER ─────────────────────────── */
        .page-footer { display: flex; justify-content: space-between; font-size: 11px; color: #b0bdb5; padding-top: 6px; }

        /* ─── EMPTY STATE ─────────────────────────── */
        .empty-state { display: flex; flex-direction: column; align-items: center; gap: 14px; padding: 70px 60px; text-align: center; }
        .empty-icon { width: 68px; height: 68px; background: #f0f6f2; border-radius: 20px; display: flex; align-items: center; justify-content: center; }
        .empty-icon img { width: 30px; height: 30px; opacity: 0.6; }
        .empty-state h2 { font-size: 17px; font-weight: 600; color: #1e3528; }
        .empty-state p { font-size: 13.5px; color: #7a9b88; line-height: 1.6; max-width: 300px; }

        /* ─── PAGINATION ──────────────────────────── */
        .pagination-wrap { display: flex; gap: 5px; align-items: center; flex-wrap: wrap; }
        .pagination-wrap a, .pagination-wrap span {
            display: flex; align-items: center; justify-content: center;
            min-width: 34px; height: 34px; padding: 0 10px;
            border-radius: 8px; font-size: 12.5px; text-decoration: none;
            border: 1px solid #dde5de; color: #6b8075; background: #fff;
            transition: background 0.15s ease, border-color 0.15s ease;
        }
        .pagination-wrap span.current { background: #4f7561; color: #fff; border-color: #4f7561; }
        .pagination-wrap a:hover { background: #f0f6f2; border-color: #c5d5c8; }

        /* ─── SEARCH BAR ──────────────────────────── */
        .search-bar { position: relative; }
        .search-bar .search-icon { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; opacity: 0.4; pointer-events: none; }
        .search-bar input { padding-left: 40px; }

        /* ─── STAT ────────────────────────────────── */
        .stat-num { font-size: 30px; font-weight: 700; line-height: 1.1; color: #1e3528; letter-spacing: -0.5px; }
        .stat-label { font-size: 12.5px; color: #90a898; }

        /* ─── ACTIVITY ICON ───────────────────────── */
        .activity-icon {
            width: 32px; height: 32px; background: #f0f6f2;
            border-radius: 9px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .activity-icon img { width: 15px; height: 15px; opacity: 0.7; }

        /* ─── RESPONSIVE ──────────────────────────── */
        @media (max-width: 1024px) {
            .sidebar { width: 220px; }
            .page-content { padding: 22px 24px 28px; }
            .topbar { padding: 0 24px; }
        }
        @media (max-width: 768px) {
            .sidebar {
                position: fixed; left: 0; top: 0; bottom: 0; z-index: 100;
                transform: translateX(-100%);
                box-shadow: 4px 0 24px rgba(0,0,0,0.1);
            }
            .sidebar.open { transform: translateX(0); }
            .workspace { width: 100%; }
            .topbar { padding: 0 16px; }
            .page-content { padding: 16px; gap: 16px; }
            .form-row { grid-template-columns: 1fr; }
            .hamburger { display: flex; }
        }

        /* Hamburger button (mobile) */
        .hamburger {
            display: none;
            align-items: center; justify-content: center;
            width: 36px; height: 36px;
            background: none; border: none; cursor: pointer;
            border-radius: 8px; transition: background 0.15s ease;
        }
        .hamburger:hover { background: #f0f6f2; }
        .hamburger img { width: 20px; height: 20px; opacity: 0.6; }

        /* Overlay (mobile) */
        .sidebar-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,0.3); z-index: 99;
            backdrop-filter: blur(2px);
        }
        .sidebar-overlay.show { display: block; }
    </style>
    @stack('head')
</head>
<body>
    <!-- Mobile overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-top">
            <!-- Brand -->
            <div class="brand">
                <div class="brand-mark">
                    <img src="/logo.png?v=2" alt="Logo Posyandu Melati">
                </div>
                <div class="brand-name">
                    <span class="name">Posyandu Digital</span>
                    <span class="tagline">Lebih dekat, lebih terawat.</span>
                </div>
            </div>

            <!-- Location -->
            <div class="location-chip">
                <div class="location-dot"></div>
                <div>
                    <div class="loc-name">Posyandu Melati</div>
                    <div class="loc-area">RW 04 · Kel. Sukamaju</div>
                </div>
            </div>

            <!-- Menu — no section label -->
            <nav class="menu">
                <a href="{{ route('dashboard') }}" class="nav-item @yield('nav-active-dashboard')">
                    <img class="nav-icon" src="/icons/layout-dashboard.svg" alt=""> Dashboard
                </a>
                <a href="{{ route('peserta.index') }}" class="nav-item @yield('nav-active-peserta')">
                    <img class="nav-icon" src="/icons/users-round.svg" alt=""> Data Peserta
                </a>
                <a href="{{ route('kegiatan.index') }}" class="nav-item @yield('nav-active-kegiatan')">
                    <img class="nav-icon" src="/icons/calendar-days.svg" alt=""> Jadwal Kegiatan
                </a>
                <a href="{{ route('kehadiran.index') }}" class="nav-item @yield('nav-active-kehadiran')">
                    <img class="nav-icon" src="/icons/clipboard-check.svg" alt=""> Kehadiran
                </a>
                <a href="{{ route('laporan.index') }}" class="nav-item @yield('nav-active-laporan')">
                    <img class="nav-icon" src="/icons/chart-no-axes-combined.svg" alt=""> Laporan
                </a>
            </nav>
        </div>

        <!-- Footer -->
        <div class="sidebar-footer">
            <div class="care-note">
                <img class="care-icon" src="/icons/sprout.svg" alt="">
                <div class="msg">Catatan rapi,<br>pelayanan lebih berarti.</div>
                <div class="sub">Terima kasih sudah merawat warga hari ini.</div>
            </div>
            <span class="version">Posyandu Digital · v1.0</span>
        </div>
    </aside>

    <!-- WORKSPACE -->
    <div class="workspace">
        <header class="topbar">
            <div style="display:flex;align-items:center;gap:10px;">
                <button class="hamburger" onclick="openSidebar()" aria-label="Menu">
                    <img src="/icons/layout-dashboard.svg" alt="">
                </button>
                <span class="page-title">@yield('breadcrumb', 'Dashboard')</span>
            </div>
            <div class="profile-area">
                <div class="topbar-sep"></div>
                <div class="profile">
                    <div class="avatar">{{ auth()->user()?->initials ?? '?' }}</div>
                    <div>
                        <div class="uname">{{ auth()->user()?->name ?? 'Tamu' }}</div>
                        <div class="urole">{{ auth()->user()?->role ?? '—' }}</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                    @csrf
                    <button type="submit" class="logout-btn" title="Keluar" aria-label="Keluar"
                            onclick="return confirm('Keluar dari sistem?')">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                            <path d="m16 17 5-5-5-5"/>
                            <path d="M21 12H9"/>
                        </svg>
                    </button>
                </form>
            </div>
        </header>

        <main class="page-content">
            @if(session('success'))
                <div class="alert alert-success" id="flash-alert">
                    <span>✓</span> {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-error" id="flash-alert">
                    <span>✕</span> {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script>
        // Auto-dismiss flash alert
        const alert = document.getElementById('flash-alert');
        if (alert) setTimeout(() => {
            alert.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-6px)';
            setTimeout(() => alert.remove(), 400);
        }, 3500);

        // Mobile sidebar
        function openSidebar() {
            document.getElementById('sidebar').classList.add('open');
            document.getElementById('sidebarOverlay').classList.add('show');
        }
        function closeSidebar() {
            document.getElementById('sidebar').classList.remove('open');
            document.getElementById('sidebarOverlay').classList.remove('show');
        }
    </script>
    @stack('scripts')
</body>
</html>
