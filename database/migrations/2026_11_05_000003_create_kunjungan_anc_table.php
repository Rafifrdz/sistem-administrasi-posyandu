<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kunjungan_anc', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ibu_hamil_id')->constrained('ibu_hamil')->cascadeOnDelete();
            $table->foreignId('kegiatan_id')->nullable()->constrained('kegiatan')->nullOnDelete();

            $table->date('tanggal');
            $table->unsignedTinyInteger('kunjungan_ke');          // K1, K2, ...
            $table->unsignedTinyInteger('usia_kehamilan_minggu'); // dari HPHT
            $table->unsignedTinyInteger('trimester');             // 1 / 2 / 3

            /* ── 10T: antropometri & tanda vital ─────── */
            $table->decimal('berat_badan', 5, 1)->nullable();     // kg
            $table->decimal('lila', 4, 1)->nullable();            // cm
            $table->unsignedSmallInteger('tensi_sistolik')->nullable();
            $table->unsignedSmallInteger('tensi_diastolik')->nullable();
            $table->decimal('tfu', 4, 1)->nullable();             // tinggi fundus uteri, cm
            $table->unsignedSmallInteger('djj')->nullable();      // denyut jantung janin, bpm
            $table->enum('presentasi_janin', ['kepala', 'sungsang', 'lintang', 'belum_tentu'])
                  ->default('belum_tentu');

            /* ── 10T: laboratorium & suplemen ────────── */
            $table->decimal('hb', 4, 1)->nullable();              // g/dL, < 11 = anemia
            $table->enum('protein_urin', ['negatif', '+1', '+2', '+3', '+4'])->default('negatif');
            $table->unsignedSmallInteger('tablet_fe')->default(0); // butir diberikan
            $table->enum('imunisasi_tt', ['tidak', 'TT1', 'TT2', 'TT3', 'TT4', 'TT5'])->default('tidak');

            /* ── Klinis ──────────────────────────────── */
            $table->json('tanda_bahaya')->nullable();
            $table->text('keluhan')->nullable();
            $table->text('diagnosa')->nullable();
            $table->enum('status_rujukan', ['tidak', 'puskesmas', 'rumah_sakit'])->default('tidak');

            $table->foreignId('petugas_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['ibu_hamil_id', 'tanggal']);
            $table->index('tanggal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kunjungan_anc');
    }
};
