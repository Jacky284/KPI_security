<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE catatan_pelanggaran MODIFY COLUMN kategori_indikator VARCHAR(255)');
        DB::statement('ALTER TABLE catatan_pelanggaran MODIFY COLUMN tingkat_penilaian VARCHAR(255)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE catatan_pelanggaran MODIFY COLUMN kategori_indikator ENUM('Kedisiplinan', 'Kehadiran', 'Kerapihan', 'Komunikasi')");
        DB::statement("ALTER TABLE catatan_pelanggaran MODIFY COLUMN tingkat_penilaian ENUM('Ringan', 'Sedang', 'Berat')");
    }
};
