<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\IbuHamil;
use App\Models\Kegiatan;
use App\Models\KunjunganAnc;
use App\Models\User;
use App\Services\KehamilanService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        /* ── 1. Pengguna ─────────────────────────────── */
        $pengguna = [
            ['name' => 'Siti Rahmawati', 'username' => 'siti',  'role' => 'kader', 'email' => 'siti@posyandu.test',  'password' => 'posyandu123'],
            ['name' => 'Ani Wijayanti',  'username' => 'bidan', 'role' => 'bidan', 'email' => 'bidan@posyandu.test', 'password' => 'bidan123'],
            ['name' => 'Admin Posyandu', 'username' => 'admin', 'role' => 'admin', 'email' => 'admin@posyandu.test', 'password' => 'admin123'],
        ];

        foreach ($pengguna as $p) {
            User::updateOrCreate(
                ['username' => $p['username']],
                ['name' => $p['name'], 'role' => $p['role'], 'email' => $p['email'], 'password' => Hash::make($p['password'])]
            );
        }

        $kader = User::where('username', 'siti')->first();

        /* ── 2. Ibu hamil ────────────────────────────── */
        // [nama, tgl lahir, TB, BB, LiLA, usia kehamilan (minggu), G, P, A,
        //  sesar, keguguran, preeklamsia, tindakan, jarak (tahun), penyakit]
        $daftar = [
            ['Siti Aminah',       '1996-03-12', 155, 52, 24.5,  8, 1, 0, 0, false, false, false, false, null, null],
            ['Dewi Lestari',      '2001-07-08', 158, 55, 25.0, 10, 1, 0, 0, false, false, false, false, null, null],
            ['Nur Aisyah',        '1994-11-20', 152, 48, 23.0,  6, 2, 1, 0, false, false, false, false, 3.0,  null],
            ['Rina Wati',         '2009-05-14', 150, 45, 22.8, 12, 1, 0, 0, false, false, false, false, null, null],
            ['Endang Susilowati', '1989-02-02', 148, 58, 26.0, 14, 3, 2, 0, true,  false, false, false, 4.0,  null],
            ['Yuni Kartika',      '1998-09-30', 160, 60, 26.5, 16, 1, 0, 0, false, false, false, false, null, null],
            ['Wulan Sari',        '1992-12-11', 155, 63, 27.0, 18, 2, 1, 0, true,  false, false, false, 3.0,  null],
            ['Anisa Putri',       '2003-04-25', 157, 50, 23.8, 20, 1, 0, 0, false, false, false, false, null, null],
            ['Marni',             '1985-08-19', 145, 47, 22.5, 22, 4, 3, 0, false, false, false, false, 2.5,  null],
            ['Sri Wahyuni',       '1997-01-07', 159, 57, 25.5, 24, 2, 1, 0, false, false, true,  false, 3.5,  null],
            ['Fitri Handayani',   '1995-06-16', 154, 54, 24.0, 26, 2, 1, 0, false, false, false, false, 2.8,  null],
            ['Lilis Suryani',     '1991-10-03', 150, 61, 27.5, 28, 3, 2, 0, false, false, false, false, 3.2,  'Hipertensi'],
            ['Ratna Dewi',        '1999-03-28', 162, 58, 25.0, 30, 1, 0, 0, false, false, false, false, null, null],
            ['Novi Astuti',       '1993-07-22', 156, 66, 28.0, 32, 2, 1, 0, false, false, false, true,  2.6,  null],
            ['Tri Wahyuni',       '1990-05-09', 153, 59, 25.8, 34, 3, 2, 0, false, false, false, false, 3.8,  null],
            ['Emi Sulastri',      '1996-12-01', 151, 53, 23.2, 36, 2, 1, 0, false, false, false, false, 3.0,  null],
            ['Nurul Hidayah',     '2002-02-14', 158, 56, 24.8, 38, 1, 0, 0, false, false, false, false, null, null],
            ['Ika Puspita',       '1994-09-05', 160, 62, 26.2,  9, 2, 1, 0, false, false, false, false, 1.5,  null],
            ['Sulis Setiyowati',  '1988-04-17', 149, 55, 24.2, 25, 3, 2, 0, false, false, false, false, 2.0,  null],
            ['Ayu Lestari',       '2000-11-23', 157, 51, 23.6, 31, 1, 0, 0, false, false, false, false, null, 'Hipertensi'],
        ];

        $jalan      = ['Jl. Soekarno Hatta', 'Jl. Ijen', 'Jl. Semeru', 'Jl. Basuki Rahmat', 'Jl. Ahmad Yani', 'Jl. Diponegoro'];
        $kelurahan  = ['Lowokwaru', 'Klojen', 'Blimbing', 'Sukun', 'Tlogomas'];
        $suami      = ['Budi Santoso', 'Agus Setiawan', 'Hendra Pratama', 'Joko Susilo', 'Rudi Hartono'];
        $goldar     = ['A', 'B', 'O', 'AB', 'O', 'B'];

        $ibuTersimpan = [];

        foreach ($daftar as $i => $d) {
            [$nama, $lahir, $tb, $bb, $lila, $minggu, $g, $p, $a, $sesar, $keguguran, $preeklamsia, $tindakan, $jarak, $penyakit] = $d;

            $ibuTersimpan[] = IbuHamil::create([
                'nik'                   => '3573' . Carbon::parse($lahir)->format('dmy') . str_pad((string) ($i + 1), 6, '0', STR_PAD_LEFT),
                'nama'                  => $nama,
                'tanggal_lahir'         => $lahir,
                'alamat'                => $jalan[$i % count($jalan)] . ' No. ' . (10 + $i) . ', Kel. ' . $kelurahan[$i % count($kelurahan)] . ', Kota Malang',
                'no_hp'                 => '0812' . str_pad((string) (34000000 + $i * 137), 8, '0', STR_PAD_LEFT),
                'nama_suami'            => $suami[$i % count($suami)],
                'no_hp_suami'           => '0813' . str_pad((string) (55000000 + $i * 211), 8, '0', STR_PAD_LEFT),
                'golongan_darah'        => $goldar[$i % count($goldar)],
                'hpht'                  => now()->subWeeks($minggu)->subDays(3),
                'gravida'               => $g,
                'para'                  => $p,
                'abortus'               => $a,
                'status'                => $i === 19 ? 'rujuk' : 'aktif',
                'tinggi_badan'          => $tb,
                'berat_pra_hamil'       => $bb,
                'lila_pra_hamil'        => $lila,
                'pernah_sesar'          => $sesar,
                'pernah_keguguran'      => $keguguran,
                'pernah_preeklamsia'    => $preeklamsia,
                'pernah_lahir_tindakan' => $tindakan,
                'jarak_hamil_tahun'     => $jarak,
                'riwayat_penyakit'      => $penyakit,
            ]);
        }

        /* ── 3. Kegiatan ─────────────────────────────── */
        $kegiatanData = [
            ['Pemeriksaan ANC Rutin Bulan Lalu',        -35, 'posyandu_rutin',  'semua', 'selesai'],
            ['Kelas Ibu Hamil Sesi 1 — Gizi Kehamilan', -21, 'kelas_ibu_hamil', 'semua', 'selesai'],
            ['Skrining Risiko & Pemeriksaan Lab',        -7, 'skrining',        'semua', 'selesai'],
            ['Pemeriksaan ANC Rutin',                     3, 'posyandu_rutin',  'semua', 'terjadwal'],
            ['Kelas Ibu Hamil Sesi 2 — Persalinan Aman',  7, 'kelas_ibu_hamil', '3',     'terjadwal'],
            ['Pemeriksaan USG Trimester 1',              10, 'usg',             '1',     'terjadwal'],
            ['Kunjungan Rumah Ibu Hamil Risiko Tinggi',  14, 'kunjungan_rumah', 'semua', 'terjadwal'],
            ['Pemeriksaan ANC Rutin',                    21, 'posyandu_rutin',  'semua', 'terjadwal'],
            ['Kelas Ibu Hamil Sesi 3 — Menyusui',        28, 'kelas_ibu_hamil', '2',     'terjadwal'],
            ['Pemeriksaan USG Trimester 3',              35, 'usg',             '3',     'terjadwal'],
        ];

        $kegiatanTersimpan = [];
        foreach ($kegiatanData as $i => [$nama, $offset, $jenis, $target, $status]) {
            $kegiatanTersimpan[] = Kegiatan::create([
                'nama'             => $nama,
                'tanggal'          => now()->addDays($offset)->toDateString(),
                'jam_mulai'        => sprintf('%02d:00', 8 + ($i % 3)),
                'jam_selesai'      => sprintf('%02d:00', 11 + ($i % 3)),
                'lokasi'           => $i % 3 === 2 ? 'Rumah warga' : 'Balai RW 05',
                'jenis'            => $jenis,
                'trimester_target' => $target,
                'status'           => $status,
                'keterangan'       => $jenis === 'kelas_ibu_hamil' ? 'Bawa Buku KIA' : null,
            ]);
        }

        /* ── 4. Kunjungan ANC ────────────────────────── */
        $kehamilan = app(KehamilanService::class);

        // Ibu yang sengaja belum pernah ANC → memunculkan peringatan di dashboard
        $tanpaAnc          = [3, 8, 17];
        $jadwalKunjungan   = [10, 16, 22, 28, 32, 36];
        $kegiatanKunjungan = $kegiatanTersimpan[0];

        foreach ($ibuTersimpan as $index => $ibu) {
            if (in_array($index, $tanpaAnc, true)) {
                continue;
            }

            $mingguSekarang = $kehamilan->usiaKehamilan($ibu->hpht);
            $ke = 0;

            foreach ($jadwalKunjungan as $minggu) {
                if ($minggu > $mingguSekarang) {
                    break;
                }

                $ke++;
                $tanggal   = Carbon::parse($ibu->hpht)->addWeeks($minggu);
                $trimester = $kehamilan->trimester($minggu);

                $bb = (float) $ibu->berat_pra_hamil + max(0, $minggu - 12) * 0.4;

                $sistolik  = 115 + ($index % 4) * 5;
                $diastolik = 72 + ($index % 3) * 4;
                $hb        = 12.2 - ($index % 5) * 0.35;
                $lila      = (float) $ibu->lila_pra_hamil;
                $djj       = $minggu >= 20 ? 132 + ($index % 6) * 3 : null;
                $tfu       = $minggu >= 20 ? round($minggu * 0.95, 1) : null;
                $protein   = 'negatif';

                // Kasus khusus agar laporan punya variasi klinis nyata
                if ($ibu->nama === 'Ayu Lestari') {
                    $sistolik  = 148;
                    $diastolik = 96;
                    $protein   = '+2';
                }
                if ($ibu->nama === 'Lilis Suryani') {
                    $sistolik  = 142;
                    $diastolik = 92;
                }
                if ($ibu->nama === 'Sri Wahyuni' && $minggu >= 22) {
                    $protein = '+1';
                }
                if ($ibu->nama === 'Marni') {
                    $lila = 22.1;
                }
                if ($ibu->nama === 'Emi Sulastri') {
                    $hb = 10.2;
                }
                if ($ibu->nama === 'Tri Wahyuni' && $minggu >= 32) {
                    $djj = 118;
                }

                KunjunganAnc::create([
                    'ibu_hamil_id'          => $ibu->id,
                    'kegiatan_id'           => $kegiatanKunjungan->id,
                    'tanggal'               => $tanggal->toDateString(),
                    'kunjungan_ke'          => $ke,
                    'usia_kehamilan_minggu' => $minggu,
                    'trimester'             => $trimester,
                    'berat_badan'           => round($bb, 1),
                    'lila'                  => $lila,
                    'tensi_sistolik'        => $sistolik,
                    'tensi_diastolik'       => $diastolik,
                    'tfu'                   => $tfu,
                    'djj'                   => $djj,
                    'presentasi_janin'      => $minggu >= 28 ? 'kepala' : 'belum_tentu',
                    'hb'                    => round($hb, 1),
                    'protein_urin'          => $protein,
                    'tablet_fe'             => 30,
                    'imunisasi_tt'          => match ($ke) {
                        1 => 'TT1', 2 => 'TT2', 3 => 'TT3', 4 => 'TT4', 5 => 'TT5', default => 'tidak',
                    },
                    'tanda_bahaya'          => $sistolik >= 140 ? ['sakit_kepala'] : null,
                    'keluhan'               => $sistolik >= 140 ? 'Kadang pusing dan pandangan berkunang' : 'Tidak ada keluhan',
                    'diagnosa'              => $sistolik >= 140 ? 'Hipertensi dalam kehamilan' : 'Kehamilan normal',
                    'status_rujukan'        => $sistolik >= 140 ? 'puskesmas' : 'tidak',
                    'petugas_id'            => $kader?->id,
                ]);
            }
        }

        /* ── 5. Log aktivitas ────────────────────────── */
        $log = [
            ['ibu_tambah',       'Menambahkan data ibu hamil baru',      'Data sasaran bertambah',              '08.15'],
            ['kunjungan_simpan', 'Menyimpan hasil kunjungan ANC',        'K1 • Hipertensi dalam kehamilan',     '09.40'],
            ['ibu_update',       'Memperbarui data ibu hamil',           'Perubahan nomor telepon',             '10.05'],
            ['kegiatan_tambah',  'Menambahkan jadwal kegiatan',          'Kelas Ibu Hamil Sesi 2',              '13.20'],
            ['rujukan',          'Merujuk ibu hamil ke Puskesmas',       'Tekanan darah 148/96 mmHg',           '14.10'],
        ];

        foreach ($log as [$type, $deskripsi, $meta, $waktu]) {
            ActivityLog::create(compact('type', 'deskripsi', 'meta', 'waktu'));
        }
    }
}
