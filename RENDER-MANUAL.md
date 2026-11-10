# Deploy Manual ke Render

Panduan klik-per-klik. Semua konfigurasi sudah ada di repo — kamu tidak perlu menulis kode apa pun.

> **Penting:** Render mewajibkan **kartu terdaftar** di level akun, walaupun plan-nya Free ($0).
> Ini kebijakan anti-penyalahgunaan Render, bukan biaya. Kartu tidak akan ditagih selama
> kamu tetap di plan Free.

---

## Langkah 0 — Kalau kartu ditolak

Kartu debit Indonesia sering ditolak Render karena verifikasi otomatis. Yang biasanya menyelesaikan:

1. **Aktifkan transaksi online / internasional** di aplikasi bank
   - BCA: `myBCA` → Kartu → Pengaturan → aktifkan **Transaksi Online**
   - Mandiri: Livin → Kartu → **Aktifkan Transaksi Online/Internasional**
   - BRI: BRImo → Kartu → **Transaksi E-Commerce**
2. **Pastikan saldo cukup** — Render melakukan *pre-authorization* (hold sementara).
   Saldo tipis sering langsung ditolak.
3. **Coba kartu virtual** kalau ada: Jenius (m-Card), Jago, Blu, SeaBank — ini paling sering lolos.
4. Kalau tetap gagal, telepon bank: minta **"aktifkan transaksi e-commerce/internasional untuk kartu debit saya"**.

Kalau semua gagal, jalur gratis-tanpa-kartu ada di `DEPLOY.md` bagian InfinityFree (manual upload).

---

## Langkah 1 — Ambil connection string Neon

Project Neon sudah terhubung (`proud-recipe-92785484`). Yang dipakai **endpoint unpooled**.

**Cara cepat (paling aman, tidak tampil di layar):**

```bash
cd "D:/KULIAH/sem 3/pemweb/projek akhir/posyandu"
neon connection-string --project-id proud-recipe-92785484 | clip
```

String-nya langsung masuk clipboard — tinggal paste ke Render nanti.

**Cara manual:** buka https://console.neon.tech → project `proud-recipe-92785484` →
**Connect** → **Connection pooling: OFF** → copy connection string
(format `postgresql://...?sslmode=require`).

> Jangan pakai host yang mengandung `-pooler`. Pooler Neon memutus transaksi DDL
> sehingga `migrate` gagal (`current transaction is aborted`).

---

## Langkah 2 — Buat Web Service

1. Buka **https://dashboard.render.com** → login GitHub
2. Klik **New +** → **Web Service**
3. **Connect a repository** → pilih `Rafifrdz/sistem-administrasi-posyandu`
   (kalau belum muncul, klik *Configure account* lalu beri akses ke repo itu)
4. Isi form:

| Field | Nilai |
|---|---|
| **Name** | `posyandu-ibu-hamil` |
| **Language** | **Docker** |
| **Branch** | `main` |
| **Region** | **Singapore** |
| **Root Directory** | *(kosongkan)* |
| **Dockerfile Path** | `./Dockerfile` |
| **Docker Context** | `.` |
| **Plan** | **Free** |
| **Health Check Path** | `/up` |
| **Auto-Deploy** | Yes |

5. Scroll ke **Environment Variables** → klik **Add Environment Variable** untuk tiap baris:

| Key | Value |
|---|---|
| `APP_NAME` | `Posyandu Digital` |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_KEY` | `base64:1RUn7IHL9F5VP5J1aBXOnuZVLMOoeYz2ey6ZcvBiAMc=` |
| `APP_LOCALE` | `id` |
| `APP_TIMEZONE` | `Asia/Jakarta` |
| `LOG_CHANNEL` | `stderr` |
| `LOG_LEVEL` | `warning` |
| `DB_CONNECTION` | `pgsql` |
| `DB_URL` | *(paste dari Langkah 1)* |
| `DB_SSLMODE` | `require` |
| `SESSION_DRIVER` | `database` |
| `CACHE_STORE` | `database` |
| `QUEUE_CONNECTION` | `sync` |
| `FILESYSTEM_DISK` | `local` |

6. Klik **Deploy Web Service**

Build pertama **5–8 menit**. Yang terjadi otomatis saat container start
(lewat `docker/entrypoint.sh`):

- menjalankan migrasi ke Neon
- mengisi data contoh **hanya jika database kosong**
- memanaskan cache
- menyalakan Apache di port dari Render

---

## Langkah 3 — Cek

Buka `https://posyandu-ibu-hamil.onrender.com`

| Username | Password | Role |
|---|---|---|
| `siti` | `posyandu123` | Kader |
| `bidan` | `bidan123` | Bidan |
| `admin` | `admin123` | Admin |

---

## Langkah 4 — Set APP_URL

1. Render → service kamu → **Environment**
2. Tambah `APP_URL` = `https://posyandu-ibu-hamil.onrender.com` (URL asli dari Render)
3. **Save Changes** → redeploy otomatis

---

## Troubleshooting

| Gejala | Solusi |
|---|---|
| Build gagal: `composer install` | Pastikan `composer.lock` ada di repo (sudah ada) |
| Halaman 500 | Render → **Logs**. Biasanya `APP_KEY` salah atau `DB_URL` pakai `-pooler` |
| `could not connect to server` | `DB_SSLMODE` harus `require`; pastikan pakai endpoint unpooled |
| Deploy sukses tapi data kosong | Tab **Shell** → `php artisan db:seed --force` |
| Perubahan tidak muncul | **Manual Deploy** → **Clear build cache & deploy** |
| Service tidur | Normal di Free. Kunjungan pertama butuh ~30–60 detik |

---

## Yang terjadi setelah online

Push ke GitHub → Render build & deploy otomatis (`autoDeploy: yes`).

```bash
git add -A && git commit -m "update" && git push origin main
```
