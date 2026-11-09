<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Kegiatan extends Model {
    protected $table = 'kegiatan';
    protected $fillable = ['nama','tanggal','jam_mulai','jam_selesai','lokasi','kategori','status','keterangan'];
    protected $casts = ['tanggal' => 'date'];
    public function kehadiran() { return $this->hasMany(Kehadiran::class); }
    public function getStatusLabelAttribute() {
        return match($this->status) {
            'terjadwal' => 'Terjadwal', 'selesai' => 'Selesai', 'dibatalkan' => 'Dibatalkan', default => $this->status
        };
    }
    public function getJumlahPesertaAttribute() { return $this->kehadiran()->count(); }
}
