<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk – Posyandu Digital</title>

    {{-- Favicon --}}
    <link rel="icon" href="/favicon.ico?v=2" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png?v=2">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png?v=2">
    <meta name="theme-color" content="#148a7e">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background: #f5f7f5;
            color: #1e3528;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            -webkit-font-smoothing: antialiased;
        }

        /* ── Header ───────────────────────────────── */
        .topbar {
            background: #fff;
            border-bottom: 1px solid #e3eae4;
            padding: 14px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }
        .brand { display: flex; align-items: center; gap: 11px; }
        .brand-mark {
            width: 40px; height: 40px;
            border-radius: 11px;
            overflow: hidden;
            flex-shrink: 0;
            background: #148a7e;
            box-shadow: 0 2px 8px rgba(20,138,126,.28);
        }
        .brand-mark img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .brand-text .name { font-size: 16px; font-weight: 700; color: #148a7e; letter-spacing: -.2px; }
        .brand-text .tagline { font-size: 11px; color: #90a898; margin-top: 1px; }
        .topbar-right { font-size: 12.5px; color: #90a898; }

        /* ── Main ─────────────────────────────────── */
        main {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            gap: 22px;
        }

        .login-card {
            background: #fff;
            border: 1px solid #e3eae4;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(20,35,27,.07);
            width: 100%;
            max-width: 424px;
            padding: 34px 32px 30px;
            animation: cardIn .35s cubic-bezier(.2,.8,.3,1);
        }
        @keyframes cardIn {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: none; }
        }

        .card-title {
            font-size: 24px;
            font-weight: 700;
            color: #148a7e;
            letter-spacing: -.4px;
        }
        .card-sub {
            font-size: 13px;
            color: #7a9b88;
            line-height: 1.6;
            margin-top: 7px;
            margin-bottom: 26px;
        }

        .field { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
        .field label { font-size: 13px; font-weight: 600; color: #2d5540; }

        .input-wrap { position: relative; }
        .input-wrap input {
            width: 100%;
            height: 46px;
            padding: 0 13px;
            border: 1.5px solid #dde5de;
            border-radius: 9px;
            font-size: 13.5px;
            font-family: inherit;
            color: #1e3528;
            background: #fff;
            outline: none;
            transition: border-color .15s ease, box-shadow .15s ease;
        }
        .input-wrap input::placeholder { color: #b0bdb5; }
        .input-wrap input:focus {
            border-color: #4f7561;
            box-shadow: 0 0 0 3px rgba(79,117,97,.12);
        }
        .input-wrap input.has-error { border-color: #c0392b; }
        .input-wrap input.has-error:focus { box-shadow: 0 0 0 3px rgba(192,57,43,.1); }

        /* ikon mata */
        .input-wrap.has-toggle input { padding-right: 44px; }
        .toggle-pw {
            position: absolute;
            right: 6px; top: 50%;
            transform: translateY(-50%);
            width: 34px; height: 34px;
            border: none; background: none; cursor: pointer;
            border-radius: 7px;
            display: flex; align-items: center; justify-content: center;
            color: #90a898;
            transition: background .15s ease, color .15s ease;
            padding: 0;
        }
        .toggle-pw:hover { background: #f0f6f2; color: #3a5c48; }
        .toggle-pw svg { width: 17px; height: 17px; }

        .error-text {
            font-size: 11.5px;
            color: #c0392b;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .btn-masuk {
            width: 100%;
            height: 46px;
            margin-top: 8px;
            background: #4f7561;
            border: none;
            border-radius: 9px;
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 1px 4px rgba(79,117,97,.25);
            transition: background .15s ease, transform .1s ease, box-shadow .15s ease;
        }
        .btn-masuk:hover { background: #3a5c48; transform: translateY(-1px); box-shadow: 0 4px 14px rgba(79,117,97,.3); }
        .btn-masuk:active { transform: translateY(0); box-shadow: none; }
        .btn-masuk:disabled { opacity: .65; cursor: progress; transform: none; }

        .spinner {
            width: 15px; height: 15px;
            border: 2px solid rgba(255,255,255,.4);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .6s linear infinite;
            display: none;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .btn-masuk.loading .spinner { display: block; }
        .btn-masuk.loading .btn-label { opacity: .75; }

        .helper {
            font-size: 11.5px;
            color: #90a898;
            text-align: center;
            line-height: 1.6;
            margin-top: 16px;
        }

        .alert {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 11px 13px;
            border-radius: 9px;
            font-size: 12.5px;
            line-height: 1.5;
            margin-bottom: 18px;
            animation: slideIn .22s ease;
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-6px); }
            to   { opacity: 1; transform: none; }
        }
        .alert-error { background: #fff0f0; border: 1px solid #f5c5c5; color: #c0392b; }
        .alert-success { background: #e6f1ea; border: 1px solid #c0d9c8; color: #2d5540; }
        .alert svg { width: 15px; height: 15px; flex-shrink: 0; }

        .below-tagline {
            font-size: 12px;
            color: #a5b1a8;
            letter-spacing: .2px;
        }

        /* ── Footer ───────────────────────────────── */
        footer {
            padding: 18px;
            text-align: center;
            font-size: 11px;
            color: #b0bdb5;
        }

        @media (max-width: 480px) {
            .topbar { padding: 12px 16px; }
            .topbar-right { display: none; }
            .login-card { padding: 26px 22px 24px; border-radius: 14px; }
            .card-title { font-size: 21px; }
            main { padding: 28px 16px; }
        }
    </style>
</head>
<body>
    {{-- Header --}}
    <header class="topbar">
        <div class="brand">
            <div class="brand-mark">
                <img src="/logo.png?v=2" alt="Logo Posyandu Melati">
            </div>
            <div class="brand-text">
                <div class="name">Posyandu Digital</div>
                <div class="tagline">Lebih dekat, lebih terawat.</div>
            </div>
        </div>
        <div class="topbar-right">Sistem Administrasi Posyandu</div>
    </header>

    {{-- Login --}}
    <main>
        <div class="login-card">
            <h1 class="card-title">Selamat datang</h1>
            <p class="card-sub">Masuk dengan akun kader untuk melanjutkan pelayanan Posyandu.</p>

            @if(session('success'))
                <div class="alert alert-success">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-error">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v5M12 16.5v.01"/></svg>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('login.attempt') }}" id="loginForm" novalidate>
                @csrf

                <div class="field">
                    <label for="username">Nama pengguna</label>
                    <div class="input-wrap">
                        <input type="text"
                               id="username"
                               name="username"
                               value="{{ old('username') }}"
                               placeholder="Masukkan nama pengguna"
                               class="{{ $errors->has('username') ? 'has-error' : '' }}"
                               autocomplete="username"
                               autofocus
                               required>
                    </div>
                </div>

                <div class="field">
                    <label for="password">Kata sandi</label>
                    <div class="input-wrap has-toggle">
                        <input type="password"
                               id="password"
                               name="password"
                               placeholder="Masukkan kata sandi"
                               class="{{ $errors->has('password') ? 'has-error' : '' }}"
                               autocomplete="current-password"
                               required>
                        <button type="button" class="toggle-pw" id="togglePw" aria-label="Tampilkan kata sandi" title="Tampilkan kata sandi">
                            <svg id="eyeOpen" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>
                            </svg>
                            <svg id="eyeClosed" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;">
                                <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 10 8 10 8a18.5 18.5 0 0 1-2.16 3.19"/>
                                <path d="M6.61 6.61A18.5 18.5 0 0 0 2 12s3 8 10 8a9.12 9.12 0 0 0 5.39-1.61"/>
                                <path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/>
                                <path d="m2 2 20 20"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-masuk" id="btnMasuk">
                    <span class="spinner"></span>
                    <span class="btn-label">Masuk</span>
                </button>
            </form>

            <p class="helper">Gunakan akun yang diberikan admin Posyandu.</p>
        </div>

        <p class="below-tagline">Lebih dekat, lebih terawat.</p>
    </main>

    <footer>Posyandu Digital • v1.0</footer>

    <script>
        // Toggle tampil / sembunyikan kata sandi
        const pw       = document.getElementById('password');
        const toggle   = document.getElementById('togglePw');
        const eyeOpen  = document.getElementById('eyeOpen');
        const eyeClosed= document.getElementById('eyeClosed');

        toggle.addEventListener('click', () => {
            const tampil = pw.type === 'password';
            pw.type = tampil ? 'text' : 'password';
            eyeOpen.style.display   = tampil ? 'none' : 'block';
            eyeClosed.style.display = tampil ? 'block' : 'none';
            toggle.setAttribute('aria-label', tampil ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
            pw.focus();
        });

        // Cegah submit ganda
        document.getElementById('loginForm').addEventListener('submit', () => {
            const btn = document.getElementById('btnMasuk');
            btn.classList.add('loading');
            btn.disabled = true;
        });
    </script>
</body>
</html>
