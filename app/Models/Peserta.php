<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Peserta extends Model {
    protected $table = 'peserta';
    protected $fillable = ['nama','nik','tanggal_lahir','jenis_kelamin','kategori','alamat','nama_wali','no_telepon'];
    public function kehadiran() { return $this->hasMany(Kehadiran::class); }
    public function getKategoriLabelAttribute() {
        return match($this->kategori) {
            'balita' => 'Balita', 'ibu_hamil' => 'Ibu Hamil', 'lansia' => 'Lansia', default => $this->kategori
        };
    }
    public function getJenisKelaminLabelAttribute() { return $this->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan'; }

    public function getUsiaLabelAttribute() {
        if (!$this->tanggal_lahir) return '—';
        $lahir = \Carbon\Carbon::parse($this->tanggal_lahir);
        $bulan = (int) $lahir->diffInMonths(now());
        if ($bulan < 24) return $bulan . ' bln';
        return $lahir->age . ' th';
    }
}
