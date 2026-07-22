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
        Schema::table('laporan_mingguan', function (Blueprint $table) {
            $table->enum('status_dokumen', ['Draft', 'Review_Chief', 'Approved'])->default('Draft')->after('tahun');
            $table->text('ttd_danru_url')->nullable()->after('status_dokumen');
            $table->timestamp('tgl_ttd_danru')->nullable()->after('ttd_danru_url');
            $table->text('ttd_chief_url')->nullable()->after('tgl_ttd_danru');
            $table->timestamp('tgl_ttd_chief')->nullable()->after('ttd_chief_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laporan_mingguan', function (Blueprint $table) {
            $table->dropColumn(['status_dokumen', 'ttd_danru_url', 'ttd_chief_url']);
        });
    }
};
