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
        Schema::create('laporan_mingguan', function (Blueprint $table) {
            $table->id('id_laporan_mingguan');
            $table->unsignedBigInteger('id_danru');
            $table->string('regu', 50);
            $table->integer('minggu_ke');
            $table->string('bulan', 20);
            $table->integer('tahun');
            $table->string('file_pdf_url', 255)->nullable();
            
            // Constraints
            $table->foreign('id_danru')->references('id_user')->on('users')->onDelete('cascade');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_mingguan');
    }
};
