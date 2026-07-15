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
        Schema::create('laporan_bulanan', function (Blueprint $table) {
            $table->id('id_laporan_bulanan');
            $table->unsignedBigInteger('id_danru_pembuat');
            $table->string('regu', 50);
            $table->string('bulan', 20);
            $table->integer('tahun');
            $table->enum('status_dokumen', ['Draft', 'Review_Chief', 'Review_Klien', 'Approved'])->default('Draft');
            
            // Signature fields (longText for Base64 data URLs)
            $table->longText('ttd_danru_url')->nullable();
            $table->longText('ttd_chief_url')->nullable();
            $table->longText('ttd_klien_url')->nullable();
            
            $table->string('file_pdf_url', 255)->nullable();
            
            // Constraints
            $table->foreign('id_danru_pembuat')->references('id_user')->on('users')->onDelete('cascade');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_bulanan');
    }
};
