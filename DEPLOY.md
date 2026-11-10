# Deploy Gratis — Render + Neon

Hasil akhir: aplikasi online 24 jam di URL `https://<nama>.onrender.com`, **tanpa perlu laptop menyala**.
Semua layanan di bawah ini **gratis dan tidak minta kartu kredit**.

| Bagian | Layanan | Kenapa |
|---|---|---|
| Aplikasi (PHP + Laravel) | **Render** — Web Service, Docker | Gratis, deploy otomatis dari GitHub |
| Database | **Neon** — PostgreSQL | Gratis permanen, tidak hangus |

> Catatan: Vercel **tidak dipakai** karena tidak mendukung PHP secara native. Runtime PHP komunitas di Vercel tidak cocok untuk Laravel + database (koneksi per-request cepat habis).

---

## Langkah 1 — Buat database di Neon (5 menit)

1. Buka **https://neon.tech** → **Sign up** → pilih **Continue with GitHub**
2. Klik **Create project**
   - Name: `posyandu`
   - Region: **Singapore** (paling dekat)
3. Setelah selesai, buka **Connection string** / **Connect** → pilih **Laravel** atau **PHP**
4. Catat 5 nilai ini dari connection string:

```
postgresql://USER:PASSWORD@HOST/DATABASE?sslmode=require
```

| Env var | Ambil dari |
|---|---|
| `DB_HOST` | bagian setelah `@`, sebelum `/` |
| `DB_PORT` | `5432` |
| `DB_DATABASE` | bagian setelah `/` (biasanya `neondb`) |
| `DB_USERNAME` | sebelum `:` |
| `DB_PASSWORD` | setelah `:` sampai `@` |

**Alternatif:** kalau mau pakai **Supabase** (https://supabase.com), caranya sama —
Project Settings → Database → Connection string → mode **Session**.
Nilainya dipetakan ke env var yang sama di atas.

---

## Langkah 2 — Deploy ke Render (5 menit)

1. Buka **https://render.com** → **Get Started** → **GitHub**
2. Klik **New +** → **Blueprint**
3. Pilih repo **`Rafifrdz/sistem-administrasi-posyandu`**
4. Render otomatis membaca `render.yaml`. Klik **Apply**
5. Render akan bertanya beberapa nilai rahasia. Isi:

| Key | Nilai |
|---|---|
| `APP_KEY` | `base64:1RUn7IHL9F5VP5J1aBXOnuZVLMOoeYz2ey6ZcvBiAMc=` |
| `APP_URL` | kosongkan dulu, diisi di langkah 4 |
| `DB_HOST` | dari Neon |
| `DB_DATABASE` | dari Neon |
| `DB_USERNAME` | dari Neon |
| `DB_PASSWORD` | dari Neon |

6. Klik **Deploy**. Build pertama ~5–8 menit (install PHP, composer, dll).

Saat container start, `docker/entrypoint.sh` otomatis:
- menjalankan migrasi
- mengisi data contoh **hanya jika database masih kosong**
- memanaskan cache
- menyalakan Apache

---

## Langkah 3 — Cek hasilnya

Buka `https://posyandu-ibu-hamil.onrender.com`

Login dengan akun demo:

| Username | Password | Role |
|---|---|---|
| `siti` | `posyandu123` | Kader |
| `bidan` | `bidan123` | Bidan |
| `admin` | `admin123` | Admin |

---

## Langkah 4 — Set APP_URL

1. Render Dashboard → service kamu → **Environment**
2. Isi `APP_URL` = `https://posyandu-ibu-hamil.onrender.com` (URL asli dari Render)
3. **Save** → Render redeploy otomatis

---

## Yang perlu diketahui soal free tier Render

- Service **tidur setelah 15 menit** tidak ada kunjungan
- Kunjungan pertama setelah tidur butuh **~30–60 detik** untuk bangun (cold start)
- Setelah bangun, normal kembali
- Kuota: 750 jam/bulan — cukup untuk 1 service nyala terus

Kalau tidak mau cold start, Render berbayar $7/bulan, atau pakai **Koyeb** (free, tetap scale-to-zero juga).

---

## Update kode setelah online

Cukup push ke GitHub:

```bash
git add -A
git commit -m "perubahan"
git push origin main
```

Render otomatis build & deploy ulang (`autoDeploy: true`).

---

## Troubleshooting

| Gejala | Penyebab & solusi |
|---|---|
| Build gagal di `composer install` | Cek `composer.lock` ikut ter-commit |
| Halaman 500 setelah deploy | Buka **Logs** di Render. Biasanya `APP_KEY` salah atau DB belum terhubung |
| `could not connect to server` | `DB_SSLMODE` harus `require`, dan host Neon harus lengkap |
| Tampil tapi data kosong | Cek log: kalau seed dilewati, jalankan manual di tab **Shell**: `php artisan db:seed --force` |
| Perubahan tidak muncul | Render → **Manual Deploy** → **Clear build cache & deploy** |

---

## Struktur berkas deploy

```
Dockerfile            # image PHP 8.4 + Apache + pdo_pgsql
docker/vhost.conf     # DocumentRoot -> public/, rewrite Laravel
docker/entrypoint.sh  # migrate + seed + cache + start Apache
render.yaml           # blueprint Render (plan free, region singapore)
.dockerignore         # .env & folder lokal tidak ikut ke image
```
