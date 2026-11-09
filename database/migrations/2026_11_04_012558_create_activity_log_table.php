<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('activity_log', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // 'kehadiran_simpan','peserta_tambah','kegiatan_update', etc
            $table->string('deskripsi');
            $table->string('meta')->nullable(); // e.g. "120 hadir • 22 tidak hadir"
            $table->string('waktu'); // e.g. "11.30"
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_log');
    }
};
