<?php

namespace App\Models;

use App\Services\KehamilanService;
use App\Services\KsprService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class IbuHamil extends Model
{
    protected $table = 'ibu_hamil';

    protected $fillable = [
        'nik', 'nama', 'tanggal_lahir', 'alamat', 'no_hp', 'nama_suami', 'no_hp_suami', 'golongan_darah',
        'hpht', 'hpl', 'gravida', 'para', 'abortus', 'status',
        'tinggi_badan', 'berat_pra_hamil', 'imt_pra_hamil', 'lila_pra_hamil',
        'pernah_sesar', 'pernah_keguguran', 'pernah_preeklamsia', 'pernah_lahir_tindakan',
        'jarak_hamil_tahun', 'riwayat_penyakit',
        'skor_kspr', 'kategori_risiko',
    ];

    protected $casts = [
        'tanggal_lahir'       => 'date',
        'hpht'                => 'date',
        'hpl'                 => 'date',
        'pernah_sesar'        => 'boolean',
        'pernah_keguguran'    => 'boolean',
        'pernah_preeklamsia'  => 'boolean',
        'pernah_lahir_tindakan' => 'boolean',
        'tinggi_badan'        => 'float',
        'berat_pra_hamil'     => 'float',
        'imt_pra_hamil'       => 'float',
        'lila_pra_hamil'      => 'float',
        'jarak_hamil_tahun'   => 'float',
    ];

    /**
     * HPL, IMT, dan skor KSPR selalu dihitung ulang setiap kali data disimpan
     * supaya tidak pernah basi terhadap perubahan HPHT atau riwayat.
     */
    protected static function booted(): void
    {
        static::saving(function (self $ibu) {
            $kehamilan = app(KehamilanService::class);

            if ($ibu->hpht) {
                $ibu->hpl = $kehamilan->hpl($ibu->hpht);
            }

            if ($ibu->tinggi_badan && $ibu->berat_pra_hamil) {
                $ibu->imt_pra_hamil = $kehamilan->imt((float) $ibu->berat_pra_hamil, (float) $ibu->tinggi_badan);
            }

            $kspr = app(KsprService::class)->hitung($ibu);
            $ibu->skor_kspr        = $kspr['skor'];
            $ibu->kategori_risiko  = $kspr['kategori'];
        });
    }

    /* ── Relasi ─────────────────────────────────── */

    public function kunjungan()
    {
        return $this->hasMany(KunjunganAnc::class)->orderBy('tanggal');
    }

    public function kunjunganTerakhir()
    {
        return $this->hasOne(KunjunganAnc::class)->latestOfMany('tanggal');
    }

    /* ── Scope ──────────────────────────────────── */

    public function scopeAktif(Builder $q): Builder
    {
        return $q->where('status', 'aktif');
    }

    public function scopeRisiko(Builder $q): Builder
    {
        return $q->whereIn('kategori_risiko', ['KRT', 'KRST']);
    }

    public function scopeCari(Builder $q, ?string $kata): Builder
    {
        if (! $kata) {
            return $q;
        }

        return $q->where(function (Builder $sub) use ($kata) {
            $sub->where('nama', 'like', "%{$kata}%")
                ->orWhere('nik', 'like', "%{$kata}%")
                ->orWhere('nama_suami', 'like', "%{$kata}%");
        });
    }

    /* ── Turunan kehamilan ──────────────────────── */

    public function getUmurAttribute(): int
    {
        return $this->tanggal_lahir ? (int) Carbon::parse($this->tanggal_lahir)->age : 0;
    }

    public function getUsiaKehamilanAttribute(): int
    {
        return app(KehamilanService::class)->usiaKehamilan($this->hpht);
    }

    public function getUsiaKehamilanLabelAttribute(): string
    {
        return app(KehamilanService::class)->labelUsia($this->usia_kehamilan);
    }

    public function getTrimesterAttribute(): int
    {
        return app(KehamilanService::class)->trimester($this->usia_kehamilan);
    }

    public function getHplLabelAttribute(): string
    {
        return $this->hpl ? Carbon::parse($this->hpl)->translatedFormat('d F Y') : '—';
    }

    public function getSisaHariAttribute(): ?int
    {
        return app(KehamilanService::class)->sisaHariMenujuHpl($this->hpht);
    }

    public function getImtKategoriAttribute(): string
    {
        return app(KehamilanService::class)->imtKategori($this->imt_pra_hamil);
    }

    /** KEK = Kurang Energi Kronik, LiLA pra-hamil < 23,5 cm. */
    public function getKekAttribute(): bool
    {
        return $this->lila_pra_hamil !== null && (float) $this->lila_pra_hamil < 23.5;
    }

    /* ── Status risiko ──────────────────────────── */

    public function getKategoriRisikoLabelAttribute(): string
    {
        return app(KsprService::class)->labelKategori($this->kategori_risiko);
    }

    public function getFaktorRisikoAttribute(): array
    {
        return app(KsprService::class)->hitung($this)['faktor'];
    }

    /* ── Status ANC ─────────────────────────────── */

    public function getJumlahKunjunganAttribute(): int
    {
        if (array_key_exists('kunjungan_count', $this->attributes)) {
            return (int) $this->attributes['kunjungan_count'];
        }

        return $this->relationLoaded('kunjungan')
            ? $this->kunjungan->count()
            : $this->kunjungan()->count();
    }

    public function getAncPerTrimesterAttribute(): array
    {
        if ($this->relationLoaded('kunjungan')) {
            return $this->kunjungan
                ->groupBy('trimester')
                ->map(fn ($baris) => $baris->count())
                ->toArray();
        }

        return $this->kunjungan()
            ->reorder()                       // buang orderBy relasi agar tidak bentrok dengan GROUP BY
            ->selectRaw('trimester, count(*) as jumlah')
            ->groupBy('trimester')
            ->pluck('jumlah', 'trimester')
            ->toArray();
    }

    public function getStatusAncAttribute(): string
    {
        $kehamilan = app(KehamilanService::class);
        $status    = $kehamilan->statusAncPerTrimester($this->anc_per_trimester);

        $target = 0;
        $dicapai = 0;
        foreach ($status as $trimester => $row) {
            if ($trimester <= $this->trimester) {
                $target  += $row['target'];
                $dicapai += $row['dicapai'];
            }
        }

        if ($dicapai >= $target) {
            return 'sesuai';
        }

        return $this->jumlah_kunjungan === 0 ? 'belum' : 'kurang';
    }

    public function getStatusAncLabelAttribute(): string
    {
        return match ($this->status_anc) {
            'sesuai' => 'Sesuai jadwal',
            'kurang' => 'Kurang dari target',
            default  => 'Belum pernah ANC',
        };
    }

    /** True bila ibu sudah lewat jadwal kunjungan berikutnya. */
    public function getTerlambatAncAttribute(): bool
    {
        // Kalau relasi sudah dimuat, hitung di memori; jangan query per baris.
        // reorder() wajib pada jalur query: relasi kunjungan() membawa orderBy('tanggal') naik
        // sehingga latest() tanpa reorder akan tetap mengambil kunjungan terlama.
        $terakhir = $this->relationLoaded('kunjungan')
            ? $this->kunjungan->sortByDesc('tanggal')->first()
            : $this->kunjungan()->reorder()->latest('tanggal')->first();

        if (! $terakhir) {
            return $this->usia_kehamilan >= 12;
        }

        $interval = app(KehamilanService::class)->intervalKunjunganBerikutnya($this->usia_kehamilan);
        $batas    = Carbon::parse($terakhir->tanggal)->addWeeks($interval)->addDays(7);

        return now()->startOfDay()->greaterThan($batas);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'aktif'  => 'Aktif',
            'selesai' => 'Selesai',
            'rujuk'  => 'Dirujuk',
            default  => $this->status,
        };
    }

    /** GPA dalam format obstetri, contoh "G2P1A0". */
    public function getGpaAttribute(): string
    {
        return "G{$this->gravida}P{$this->para}A{$this->abortus}";
    }
}
