<?php

namespace Database\Seeders;

use App\Models\{User, Peserta, Kegiatan, Kehadiran, ActivityLog};
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        /* ── Akun kader (untuk login) ────────────────────── */
        // Password otomatis di-hash oleh cast 'hashed' di model User.
        User::create([
            'name'     => 'Siti Rahmawati',
            'username' => 'siti',
            'role'     => 'Kader Posyandu',
            'email'    => 'siti@posyandu.test',
            'password' => 'posyandu123',
        ]);

        User::create([
            'name'     => 'Admin Posyandu',
            'username' => 'admin',
            'role'     => 'Administrator Posyandu',
            'email'    => 'admin@posyandu.test',
            'password' => 'admin123',
        ]);

        /* ── Peserta ─────────────────────────────────────── */
        $daftar = [
            ['Aisyah Putri',   '3573010101230001', '2023-01-15', 'P', 'balita',    'Jl. Mawar No. 5 RT 02 RW 04',     'Budi Santoso',   '0811234567'],
            ['Rafi Ahmad',     '3573010203220002', '2022-02-10', 'L', 'balita',    'Jl. Melati No. 3 RT 01 RW 04',    'Siti Aminah',    '0812345678'],
            ['Zahra Nadia',    '3573010304210003', '2021-04-20', 'P', 'balita',    'Jl. Flamboyan No. 7 RT 03 RW 04', 'Ahmad Fauzi',    '0813456789'],
            ['Budi Hartono',   '3573011505701001', '1970-05-15', 'L', 'lansia',    'Jl. Kenanga No. 2 RT 04 RW 04',   null,             '0814567890'],
            ['Sri Wahyuni',    '3573014606721002', '1972-06-06', 'P', 'lansia',    'Jl. Anggrek No. 9 RT 01 RW 04',   null,             '0815678901'],
            ['Dewi Rahayu',    '3573010708850001', '1985-08-07', 'P', 'ibu_hamil', 'Jl. Dahlia No. 4 RT 02 RW 04',    'Hendra Gunawan', '0816789012'],
            ['Nurul Hidayah',  '3573010809860002', '1986-09-08', 'P', 'ibu_hamil', 'Jl. Tulip No. 6 RT 03 RW 04',     'Dian Permana',   '0817890123'],
            ['Hasan Basri',    '3573011010551003', '1955-10-10', 'L', 'lansia',    'Jl. Cempaka No. 1 RT 04 RW 04',   null,             '0818901234'],
        ];

        foreach ($daftar as [$nama, $nik, $tl, $jk, $kat, $alamat, $wali, $telp]) {
            Peserta::create([
                'nama' => $nama, 'nik' => $nik, 'tanggal_lahir' => $tl, 'jenis_kelamin' => $jk,
                'kategori' => $kat, 'alamat' => $alamat, 'nama_wali' => $wali, 'no_telepon' => $telp,
            ]);
        }

        /* ── Kegiatan (tanggal relatif ke hari ini) ──────── */
        // [bulan offset, hari ke-, nama, jam mulai, jam selesai, lokasi, kategori, status]
        $jadwal = [
            [-5, 14, 'Penimbangan Balita',  '08:00', '10:00', 'Balai RW 04',     'balita',    'selesai'],
            [-4, 12, 'Kelas Ibu Hamil',     '08:30', '10:00', 'Balai RW 04',     'ibu_hamil', 'selesai'],
            [-3, 17, 'Pemeriksaan Lansia',  '08:00', '11:00', 'Posyandu Melati', 'lansia',    'selesai'],
            [-2, 10, 'Penimbangan Balita',  '08:00', '10:00', 'Balai RW 04',     'balita',    'selesai'],
            [-1,  8, 'Kelas Ibu Hamil',     '08:30', '10:00', 'Balai RW 04',     'ibu_hamil', 'selesai'],
            [ 0,  5, 'Penimbangan Balita',  '08:00', '10:00', 'Balai RW 04',     'balita',    'selesai'],
            [ 0, 20, 'Pemeriksaan Lansia',  '08:00', '11:00', 'Posyandu Melati', 'lansia',    'terjadwal'],
            [ 1, 12, 'Kelas Ibu Hamil',     '08:30', '10:00', 'Balai RW 04',     'ibu_hamil', 'terjadwal'],
        ];

        $kegiatan = [];
        foreach ($jadwal as [$offset, $hari, $nama, $jm, $js, $lok, $kat, $sts]) {
            $tgl = Carbon::now()->startOfMonth()->addMonthsNoOverflow($offset)->addDays($hari - 1);

            $kegiatan[] = Kegiatan::create([
                'nama' => $nama, 'tanggal' => $tgl->format('Y-m-d'),
                'jam_mulai' => $jm, 'jam_selesai' => $js,
                'lokasi' => $lok, 'kategori' => $kat, 'status' => $sts,
                'keterangan' => null,
            ]);
        }

        /* ── Kehadiran untuk kegiatan selesai ────────────── */
        $semuaPeserta = Peserta::all();

        foreach ($kegiatan as $kg) {
            if ($kg->status !== 'selesai') {
                continue;
            }

            $target = $kg->kategori === 'umum'
                ? $semuaPeserta
                : $semuaPeserta->where('kategori', $kg->kategori);

            foreach ($target as $i => $p) {
                // ~80% hadir, sisanya tidak hadir
                $hadir = ($i % 5) !== 3;

                Kehadiran::create([
                    'peserta_id' => $p->id,
                    'kegiatan_id' => $kg->id,
                    'status' => $hadir ? 'hadir' : 'tidak_hadir',
                ]);
            }
        }

        /* ── Log aktivitas ───────────────────────────────── */
        ActivityLog::create(['type' => 'kehadiran_simpan', 'deskripsi' => 'Kehadiran penimbangan balita disimpan', 'meta' => '3 hadir • 0 tidak hadir', 'waktu' => '11.30']);
        ActivityLog::create(['type' => 'peserta_tambah',   'deskripsi' => 'Aisyah Putri ditambahkan sebagai peserta', 'meta' => 'Kategori Balita • oleh Siti Rahmawati', 'waktu' => '09.42']);
        ActivityLog::create(['type' => 'kegiatan_update',  'deskripsi' => 'Jadwal kelas ibu hamil diperbarui', 'meta' => 'oleh Siti Rahmawati', 'waktu' => '08.15']);
    }
}
