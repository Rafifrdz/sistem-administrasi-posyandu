<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class KunjunganAnc extends Model
{
    protected $table = 'kunjungan_anc';

    protected $fillable = [
        'ibu_hamil_id', 'kegiatan_id', 'tanggal', 'kunjungan_ke',
        'usia_kehamilan_minggu', 'trimester',
        'berat_badan', 'lila', 'tensi_sistolik', 'tensi_diastolik',
        'tfu', 'djj', 'presentasi_janin',
        'hb', 'protein_urin', 'tablet_fe', 'imunisasi_tt',
        'tanda_bahaya', 'keluhan', 'diagnosa', 'status_rujukan', 'petugas_id',
    ];

    protected $casts = [
        'tanggal'    => 'date',
        'tanda_bahaya' => 'array',
        'berat_badan'  => 'float',
        'lila'         => 'float',
        'tfu'          => 'float',
        'hb'           => 'float',
    ];

    /** Daftar tanda bahaya kehamilan yang dipantau (Buku KIA). */
    public const TANDA_BAHAYA = [
        'perdarahan'      => 'Perdarahan dari jalan lahir',
        'sakit_kepala'    => 'Sakit kepala hebat / pandangan kabur',
        'bengkak'         => 'Bengkak pada kaki, tangan, wajah',
        'demam'           => 'Demam tinggi',
        'mual_berlebihan' => 'Mual muntah berlebihan',
        'gerak_bayi'      => 'Gerakan bayi berkurang',
        'ketuban'         => 'Ketuban pecah dini',
        'kejang'          => 'Kejang',
        'sesak'           => 'Sesak napas',
    ];

    public function ibuHamil()
    {
        return $this->belongsTo(IbuHamil::class);
    }

    public function kegiatan()
    {
        return $this->belongsTo(Kegiatan::class);
    }

    public function petugas()
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    /* ── Label & indikator klinis ───────────────── */

    public function getLabelKunjunganAttribute(): string
    {
        return 'K' . $this->kunjungan_ke;
    }

    public function getTanggalLabelAttribute(): string
    {
        return $this->tanggal ? Carbon::parse($this->tanggal)->translatedFormat('d M Y') : '—';
    }

    public function getTensiLabelAttribute(): string
    {
        if (! $this->tensi_sistolik || ! $this->tensi_diastolik) {
            return '—';
        }

        return "{$this->tensi_sistolik}/{$this->tensi_diastolik} mmHg";
    }

    /** Hipertensi dalam kehamilan bila ≥ 140/90. */
    public function getHipertensiAttribute(): bool
    {
        return (int) $this->tensi_sistolik >= 140 || (int) $this->tensi_diastolik >= 90;
    }

    /** Anemia bila Hb < 11 g/dL. */
    public function getAnemiaAttribute(): bool
    {
        return $this->hb !== null && (float) $this->hb < 11.0;
    }

    /** KEK bila LiLA < 23,5 cm. */
    public function getKekAttribute(): bool
    {
        return $this->lila !== null && (float) $this->lila < 23.5;
    }

    /** DJJ normal 120–160 bpm. */
    public function getDjjAbnormalAttribute(): bool
    {
        return $this->djj !== null && ($this->djj < 120 || $this->djj > 160);
    }

    public function getProteinUriaAttribute(): bool
    {
        return in_array($this->protein_urin, ['+2', '+3', '+4'], true);
    }

    /** Kumpulan peringatan klinis pada satu kunjungan. */
    public function getPeringatanAttribute(): array
    {
        $hasil = [];

        if ($this->hipertensi) {
            $hasil[] = 'Tekanan darah tinggi (≥140/90)';
        }
        if ($this->anemia) {
            $hasil[] = 'Anemia (Hb < 11 g/dL)';
        }
        if ($this->kek) {
            $hasil[] = 'Kurang Energi Kronik (LiLA < 23,5 cm)';
        }
        if ($this->djj_abnormal) {
            $hasil[] = 'Denyut jantung janin abnormal';
        }
        if ($this->protein_uria) {
            $hasil[] = 'Protein urin positif (curiga preeklamsia)';
        }

        return $hasil;
    }

    public function getPresentasiLabelAttribute(): string
    {
        return match ($this->presentasi_janin) {
            'kepala'  => 'Kepala',
            'sungsang' => 'Sungsang',
            'lintang' => 'Lintang',
            default   => 'Belum tentu',
        };
    }

    public function getStatusRujukanLabelAttribute(): string
    {
        return match ($this->status_rujukan) {
            'puskesmas'    => 'Dirujuk ke Puskesmas',
            'rumah_sakit'  => 'Dirujuk ke Rumah Sakit',
            default        => 'Tidak dirujuk',
        };
    }

    public function getTandaBahayaLabelAttribute(): array
    {
        return array_values(array_filter(array_map(
            fn ($kode) => self::TANDA_BAHAYA[$kode] ?? null,
            (array) ($this->tanda_bahaya ?? [])
        )));
    }
}
