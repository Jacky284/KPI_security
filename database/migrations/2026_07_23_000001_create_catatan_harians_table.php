<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catatan_harians', function (Blueprint $table) {
            $table->id('id_catatan');
            $table->unsignedBigInteger('id_danru');
            $table->unsignedBigInteger('id_anggota');
            $table->date('tanggal');
            $table->integer('minggu_ke');
            $table->string('bulan', 20);
            $table->integer('tahun');
            $table->string('indikator', 50);
            $table->text('deskripsi');
            $table->text('arahan')->nullable();
            $table->enum('status_tindak_lanjut', ['Sudah', 'Belum'])->default('Belum');
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->foreign('id_danru')->references('id_user')->on('users')->onDelete('cascade');
            $table->foreign('id_anggota')->references('id_user')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catatan_harians');
    }
};
