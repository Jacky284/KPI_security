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
        Schema::create('jadwal_bulanans', function (Blueprint $table) {
            $table->id('id_jadwal');
            $table->unsignedBigInteger('id_anggota');
            $table->unsignedBigInteger('id_danru_pembuat')->nullable();
            $table->string('bulan', 20);
            $table->integer('tahun');
            $table->json('jadwal_harian'); // JSON object storing "day": "shift"
            $table->timestamps();

            // Constraints
            $table->foreign('id_anggota')->references('id_user')->on('users')->onDelete('cascade');
            $table->foreign('id_danru_pembuat')->references('id_user')->on('users')->onDelete('set null');
            
            // Unique constraint for one schedule per user per month
            $table->unique(['id_anggota', 'bulan', 'tahun']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_bulanans');
    }
};
