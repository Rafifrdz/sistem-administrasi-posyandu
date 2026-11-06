<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Kegiatan extends Model
{
    protected $table = 'kegiatan';

    protected $fillable = [
        'nama', 'tanggal', 'jam_mulai', 'jam_selesai', 'lokasi',
        'jenis', 'trimester_target', 'status', 'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public const JENIS = [
        'posyandu_rutin'  => 'Pemeriksaan ANC Rutin',
        'kelas_ibu_hamil' => 'Kelas Ibu Hamil',
        'kunjungan_rumah' => 'Kunjungan Rumah',
        'usg'             => 'Pemeriksaan USG',
        'skrining'        => 'Skrining Risiko & Lab',
    ];

    public function kunjungan()
    {
        return $this->hasMany(KunjunganAnc::class);
    }

    public function scopeTerjadwal(Builder $q): Builder
    {
        return $q->where('status', 'terjadwal');
    }

    public function scopeMendatang(Builder $q): Builder
    {
        return $q->where('status', 'terjadwal')->whereDate('tanggal', '>=', now()->toDateString());
    }

    public function getJenisLabelAttribute(): string
    {
        return self::JENIS[$this->jenis] ?? $this->jenis;
    }

    public function getTrimesterLabelAttribute(): string
    {
        return $this->trimester_target === 'semua'
            ? 'Semua trimester'
            : 'Trimester ' . $this->trimester_target;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'terjadwal' => 'Terjadwal',
            'selesai'   => 'Selesai',
            'dibatalkan' => 'Dibatalkan',
            default     => $this->status,
        };
    }

    public function getTanggalLabelAttribute(): string
    {
        return $this->tanggal ? Carbon::parse($this->tanggal)->translatedFormat('l, d F Y') : '—';
    }

    public function getJamLabelAttribute(): string
    {
        return substr((string) $this->jam_mulai, 0, 5) . ' – ' . substr((string) $this->jam_selesai, 0, 5);
    }
}
