<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kegiatan', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->date('tanggal');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->string('lokasi');

            // Jenis layanan posyandu ibu hamil
            $table->enum('jenis', [
                'posyandu_rutin',    // pemeriksaan ANC bulanan
                'kelas_ibu_hamil',   // kelas edukasi (min. 4 sesi)
                'kunjungan_rumah',   // kunjungan rumah bumil
                'usg',               // pemeriksaan USG
                'skrining',          // skrining risiko / lab
            ])->default('posyandu_rutin');

            // Sasaran trimester kegiatan
            $table->enum('trimester_target', ['semua', '1', '2', '3'])->default('semua');

            $table->enum('status', ['terjadwal', 'selesai', 'dibatalkan'])->default('terjadwal');
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index('tanggal');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kegiatan');
    }
};
