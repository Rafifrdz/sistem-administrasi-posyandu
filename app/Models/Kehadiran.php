<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Kehadiran extends Model {
    protected $table = 'kehadiran';
    protected $fillable = ['peserta_id','kegiatan_id','status','catatan'];
    public function peserta() { return $this->belongsTo(Peserta::class); }
    public function kegiatan() { return $this->belongsTo(Kegiatan::class); }
}
