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
        Schema::create('catatan_pelanggaran', function (Blueprint $table) {
            $table->id('id_catatan');
            $table->unsignedBigInteger('id_anggota');
            $table->unsignedBigInteger('id_penilai');
            $table->date('tanggal_penilaian');
            $table->integer('minggu_ke');
            $table->string('bulan', 20);
            $table->integer('tahun');
            $table->enum('kategori_indikator', ['Kedisiplinan', 'Kehadiran', 'Kerapihan', 'Komunikasi']);
            $table->enum('tingkat_penilaian', ['Ringan', 'Sedang', 'Berat']);
            $table->text('deskripsi_penilaian');
            
            // Constraints
            $table->foreign('id_anggota')->references('id_user')->on('users')->onDelete('cascade');
            $table->foreign('id_penilai')->references('id_user')->on('users')->onDelete('cascade');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catatan_pelanggaran');
    }
};
