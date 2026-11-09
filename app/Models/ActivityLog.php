<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ActivityLog extends Model {
    protected $table = 'activity_log';
    protected $fillable = ['type','deskripsi','meta','waktu'];
    protected static function booted() {
        // Auto-set waktu on create
        static::creating(function($log) {
            if (!$log->waktu) $log->waktu = now()->format('H.i');
        });
    }
}
