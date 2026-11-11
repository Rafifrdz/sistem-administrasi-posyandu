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
    <meta name="theme-color" content="#4f7561">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background: #f6f8f6;
            color: #233c32;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            -webkit-font-smoothing: antialiased;
        }

        /* Kartu utama: panel hijau + formulir */
        .panel-masuk {
            display: flex;
            width: 100%;
            max-width: 1160px;
            background: #fff;
            border: 1px solid #e4eae5;
            border-radius: 14px;
            overflow: hidden;
            animation: cardIn .35s cubic-bezier(.2,.8,.3,1);
        }
        @keyframes cardIn {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: none; }
        }

        /* Panel identitas layanan (kiri) */
        .identitas {
            width: 460px;
            flex-shrink: 0;
            background: #4f7561;
            padding: 48px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 32px;
            color: #fff;
        }
        .identitas-atas { display: flex; flex-direction: column; gap: 32px; }

        .lambang {
            width: 48px; height: 48px;
            border-radius: 12px;
            background: #fff;
            display: flex; align-items: center; justify-content: center;
            overflow: hidden;
            flex-shrink: 0;
        }
        .lambang img { width: 100%; height: 100%; object-fit: contain; display: block; }

        .nama-layanan {
            font-size: 21px;
            font-weight: 600;
            letter-spacing: -.2px;
            margin-top: 12px;
        }

        .pesan-utama { display: flex; flex-direction: column; gap: 24px; }
        .pesan-besar {
            font-size: 34px;
            font-weight: 600;
            line-height: 1.25;
            letter-spacing: -.6px;
        }
        .pesan-sedang {
            font-size: 21px;
            font-weight: 600;
            line-height: 1.35;
            letter-spacing: -.2px;
        }
        .pesan-kecil {
            font-size: 14px;
            line-height: 1.6;
            color: rgba(255,255,255,.82);
        }
        .versi { font-size: 12px; color: rgba(255,255,255,.7); }

        /* Formulir masuk (kanan) */
        .formulir {
            flex: 1;
            padding: 64px;
            display: flex;
            flex-direction: column;
            gap: 32px;
            justify-content: center;
        }
        .judul-masuk { display: flex; flex-direction: column; gap: 8px; }
        .judul-masuk h1 {
            font-size: 32px;
            font-weight: 600;
            color: #233c32;
            letter-spacing: -.6px;
        }
        .judul-masuk p { font-size: 14px; color: #758179; }

        .kredensial { display: flex; flex-direction: column; gap: 20px; }
        .field { display: flex; flex-direction: column; gap: 8px; }
        .field label { font-size: 13px; font-weight: 500; color: #233c32; }

        .input-wrap { position: relative; }
        .input-wrap input {
            width: 100%;
            height: 48px;
            padding: 0 14px;
            border: 1px solid #e4eae5;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            color: #233c32;
            background: #fff;
            outline: none;
            transition: border-color .15s ease, box-shadow .15s ease;
        }
        .input-wrap input::placeholder { color: #a8b3ab; }
        .input-wrap input:focus {
            border-color: #4f7561;
            box-shadow: 0 0 0 3px rgba(79,117,97,.12);
        }
        .input-wrap input.has-error { border-color: #a3473e; }
        .input-wrap input.has-error:focus { box-shadow: 0 0 0 3px rgba(163,71,62,.1); }

        /* ikon mata */
        .input-wrap.has-toggle input { padding-right: 46px; }
        .toggle-pw {
            position: absolute;
            right: 7px; top: 50%;
            transform: translateY(-50%);
            width: 34px; height: 34px;
            border: none; background: none; cursor: pointer;
            border-radius: 7px;
            display: flex; align-items: center; justify-content: center;
            color: #758179;
            transition: background .15s ease, color .15s ease;
            padding: 0;
        }
        .toggle-pw:hover { background: #f0f5f1; color: #4f7561; }
        .toggle-pw svg { width: 17px; height: 17px; }

        .btn-masuk {
            width: 100%;
            height: 48px;
            background: #4f7561;
            border: none;
            border-radius: 8px;
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: background .15s ease;
        }
        .btn-masuk:hover { background: #3f604e; }
        .btn-masuk:disabled { opacity: .65; cursor: progress; }

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

        .alert {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 12px 14px;
            border-radius: 8px;
            font-size: 13px;
            line-height: 1.5;
            animation: slideIn .22s ease;
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-6px); }
            to   { opacity: 1; transform: none; }
        }
        .alert-error { background: #fdf1f0; border: 1px solid #f0cdc9; color: #a3473e; }
        .alert-success { background: #e8f1eb; border: 1px solid #c0d9c8; color: #233c32; }
        .alert svg { width: 15px; height: 15px; flex-shrink: 0; }

        .helper { font-size: 12px; color: #758179; line-height: 1.6; }

        /* Responsif */
        @media (max-width: 1000px) {
            .panel-masuk { flex-direction: column; max-width: 520px; }
            .identitas { width: 100%; padding: 32px; gap: 24px; }
            .pesan-besar { font-size: 26px; }
            .pesan-sedang { font-size: 18px; }
            .formulir { padding: 32px; gap: 24px; }
            .judul-masuk h1 { font-size: 26px; }
        }
        @media (max-width: 480px) {
            body { padding: 20px 14px; }
            .identitas { padding: 26px 22px; }
            .formulir { padding: 26px 22px; }
            .judul-masuk h1 { font-size: 23px; }
        }
    </style>
</head>
<body>
    <div class="panel-masuk">

        {{-- Panel identitas layanan --}}
        <aside class="identitas">
            <div class="identitas-atas">
                <div>
                    <div class="lambang">
                        <img src="/logo.png?v=2" alt="Logo Posyandu Melati">
                    </div>
                    <div class="nama-layanan">Posyandu Digital</div>
                </div>

                <div class="pesan-utama">
                    <div class="pesan-besar">Lebih dekat,<br>lebih terawat.</div>
                    <div class="pesan-sedang">Catatan rapi,<br>pelayanan lebih berarti.</div>
                    <div class="pesan-kecil">Terima kasih sudah merawat warga hari ini.</div>
                </div>
            </div>

            <div class="versi">Posyandu Digital · v1.0</div>
        </aside>

        {{-- Formulir --}}
        <main class="formulir">
            <div class="judul-masuk">
                <h1>Masuk ke Sistem</h1>
                <p>Sistem Administrasi Posyandu Ibu Hamil</p>
            </div>

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

                <div class="kredensial">
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
                </div>

                <button type="submit" class="btn-masuk" id="btnMasuk" style="margin-top:20px;">
                    <span class="spinner"></span>
                    <span class="btn-label">Masuk</span>
                </button>
            </form>

            <p class="helper">Gunakan akun yang diberikan admin Posyandu.</p>
        </main>
    </div>

    <script>
        // Toggle tampil / sembunyikan kata sandi
        const pw        = document.getElementById('password');
        const toggle    = document.getElementById('togglePw');
        const eyeOpen   = document.getElementById('eyeOpen');
        const eyeClosed = document.getElementById('eyeClosed');

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
