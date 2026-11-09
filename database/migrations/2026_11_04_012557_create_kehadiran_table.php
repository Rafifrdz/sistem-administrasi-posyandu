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
        Schema::create('kehadiran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('peserta_id')->constrained('peserta')->onDelete('cascade');
            $table->foreignId('kegiatan_id')->constrained('kegiatan')->onDelete('cascade');
            $table->enum('status', ['hadir', 'tidak_hadir'])->default('hadir');
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->unique(['peserta_id', 'kegiatan_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kehadiran');
    }
};
