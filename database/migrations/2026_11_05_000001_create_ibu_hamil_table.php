<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ibu_hamil', function (Blueprint $table) {
            $table->id();

            /* ── Identitas ───────────────────────────── */
            $table->string('nik', 16)->unique();
            $table->string('nama');
            $table->date('tanggal_lahir');
            $table->text('alamat');
            $table->string('no_hp', 20)->nullable();
            $table->string('nama_suami')->nullable();
            $table->string('no_hp_suami', 20)->nullable();
            $table->enum('golongan_darah', ['A', 'B', 'AB', 'O', '-'])->default('-');

            /* ── Kehamilan ───────────────────────────── */
            $table->date('hpht');                       // hari pertama haid terakhir
            $table->date('hpl')->nullable();            // taksiran persalinan (otomatis)
            $table->unsignedTinyInteger('gravida')->default(1);
            $table->unsignedTinyInteger('para')->default(0);
            $table->unsignedTinyInteger('abortus')->default(0);
            $table->enum('status', ['aktif', 'selesai', 'rujuk'])->default('aktif');

            /* ── Antropometri pra-hamil ──────────────── */
            $table->decimal('tinggi_badan', 5, 1);          // cm
            $table->decimal('berat_pra_hamil', 5, 1);       // kg
            $table->decimal('imt_pra_hamil', 4, 1)->nullable();
            $table->decimal('lila_pra_hamil', 4, 1)->nullable(); // cm, < 23,5 = KEK

            /* ── Riwayat obstetri ────────────────────── */
            $table->boolean('pernah_sesar')->default(false);
            $table->boolean('pernah_keguguran')->default(false);
            $table->boolean('pernah_preeklamsia')->default(false);
            $table->boolean('pernah_lahir_tindakan')->default(false);
            $table->decimal('jarak_hamil_tahun', 3, 1)->nullable(); // jarak dari persalinan terakhir
            $table->string('riwayat_penyakit')->nullable();

            /* ── Skor risiko (otomatis) ──────────────── */
            $table->unsignedSmallInteger('skor_kspr')->default(0);
            $table->enum('kategori_risiko', ['KRR', 'KRT', 'KRST'])->default('KRR');

            $table->timestamps();

            $table->index('status');
            $table->index('kategori_risiko');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ibu_hamil');
    }
};
